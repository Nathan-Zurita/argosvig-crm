<?php

namespace Webkul\HumanResources\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Webkul\HumanResources\Repositories\EmployeeRepository;

class TimekeepingImporter
{
    public const MAX_ROWS = 10000;

    public function __construct(private EmployeeRepository $employees) {}

    /**
     * @return list<array{employee_code: string, occurred_at: string, external_id?: string|null}>
     */
    public function readCsv(string $path): array
    {
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            $this->reject('invalid_csv');
        }

        try {
            $line = fgets($stream);
            $header = $line === false ? '' : preg_replace('/^\xEF\xBB\xBF/', '', trim($line));
            $separator = str_contains($header, ';') ? ';' : ',';
            $columns = str_getcsv($header, $separator, '"', '');

            if ($columns !== ['employee_code', 'occurred_at'] && $columns !== ['employee_code', 'occurred_at', 'external_id']) {
                $this->reject('invalid_header');
            }

            $rows = [];

            while (($values = fgetcsv($stream, 0, $separator, '"', '')) !== false) {
                if ($values === [null]) {
                    continue;
                }

                if (count($values) !== count($columns)) {
                    $this->reject('invalid_row', count($rows) + 1);
                }

                if (count($rows) >= self::MAX_ROWS) {
                    $this->reject('too_many');
                }

                $rows[] = array_combine($columns, array_map(fn ($value) => trim((string) $value), $values));
            }

            return $rows;
        } finally {
            fclose($stream);
        }
    }

    /**
     * Persist one batch atomically. Timestamps are stored in UTC.
     *
     * @param  iterable<array{employee_code: string, occurred_at: string, external_id?: string|null}>  $rows
     * @return array{inserted: int, duplicates: int}
     */
    public function import(iterable $rows, string $source, ?int $actor = null): array
    {
        $validated = [];

        foreach ($rows as $row) {
            if (count($validated) >= self::MAX_ROWS) {
                $this->reject('too_many');
            }

            $number = count($validated) + 1;
            $validator = Validator::make($row, [
                'employee_code' => ['required', 'string', 'max:100'],
                'occurred_at' => ['required', 'date_format:Y-m-d H:i:s,Y-m-d\TH:i:sP'],
                'external_id' => ['nullable', 'string', 'max:100'],
            ]);

            if ($validator->fails()) {
                $this->reject('invalid_row', $number);
            }

            $row = $validator->validated();
            $validated[] = [
                'code' => $row['employee_code'],
                'occurred_at' => CarbonImmutable::parse($row['occurred_at'], config('app.timezone'))->utc()->format('Y-m-d H:i:s'),
                'external_id' => ($row['external_id'] ?? '') === '' ? null : $row['external_id'],
            ];
        }

        if (! $validated) {
            $this->reject('empty_csv');
        }

        return DB::transaction(function () use ($validated, $source, $actor) {
            $ids = $this->employees->getModel()->newQuery()->withTrashed()
                ->whereIn('alterdata_employee_id', array_unique(array_column($validated, 'code')))
                ->orderBy('id')->lockForUpdate()->pluck('id', 'alterdata_employee_id');
            $stamp = now();
            $instants = array_column($validated, 'occurred_at');
            $batch = DB::table('hr_timekeeping_batches')->insertGetId([
                'source' => $source, 'user_id' => $actor, 'received' => count($validated),
                'first_mark_at' => min($instants), 'last_mark_at' => max($instants),
                'created_at' => $stamp, 'updated_at' => $stamp,
            ]);

            $inserted = 0;

            foreach ($validated as $index => $row) {
                $employeeId = $ids->get($row['code']);

                if ($employeeId === null) {
                    $this->reject('unknown_employee', $index + 1);
                }

                $match = DB::table('hr_timekeeping_entries')->where('source', $source)
                    ->where('external_id', $row['external_id']);

                if ($row['external_id'] !== null && ($entry = $match->first())) {
                    if ((int) $entry->employee_id !== (int) $employeeId || $entry->occurred_at !== $row['occurred_at']) {
                        $this->reject('external_conflict', $index + 1);
                    }

                    continue;
                }

                if (DB::table('hr_timekeeping_entries')->where('employee_id', $employeeId)->where('occurred_at', $row['occurred_at'])->exists()) {
                    continue;
                }

                DB::table('hr_timekeeping_entries')->insert([
                    'employee_id' => $employeeId, 'batch_id' => $batch, 'occurred_at' => $row['occurred_at'],
                    'source' => $source, 'external_id' => $row['external_id'], 'created_at' => $stamp, 'updated_at' => $stamp,
                ]);
                $inserted++;
            }

            DB::table('hr_timekeeping_batches')->where('id', $batch)->update(['inserted' => $inserted]);

            return ['inserted' => $inserted, 'duplicates' => count($validated) - $inserted];
        }, 3);
    }

    private function reject(string $reason, ?int $line = null): never
    {
        throw ValidationException::withMessages([
            'file' => trans("human_resources::workforce.{$reason}", ['line' => $line]),
        ]);
    }
}
