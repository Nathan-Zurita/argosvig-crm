<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Webkul\HumanResources\Models\Employee;
use Webkul\HumanResources\Models\ShiftAssignment;
use Webkul\HumanResources\Models\WorkSite;
use Webkul\HumanResources\Services\TimekeepingImporter;

class DemoTimekeepingSeeder extends Seeder
{
    public function run(TimekeepingImporter $importer): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Os exemplos de ponto são para ambientes locais ou de teste.');
        }

        $day = CarbonImmutable::yesterday(config('app.timezone'));
        $dayCases = [
            ['Ana Ribeiro', 7, [-1, 300, 360, 720], [661, 0, 0, 0, 0, false, 90]],
            ['Bruno Costa', 19, [9, 300, 360, 750], [681, 21, 9, 0, 0, false, 21]],
            ['Camila Lopes', 7, [0, 300, 360], null],
            ['Diego Santos', 7, [], [0, 0, 0, 0, 0, true, -660]],
            ['Elisa Martins', 7, [0, 300, 360, 700], [640, 0, 0, 20, 20, false, 0]],
            ['Felipe Lima', 7, [300, 360, 720], null],
        ];

        DB::transaction(function () use ($importer, $day, $dayCases) {
            $sites = collect(['Portaria central', 'Centro de distribuição'])->map(
                fn ($name, $index) => WorkSite::firstOrCreate(
                    ['code' => 'DEMO-PONTO-'.($index + 1)],
                    ['name' => 'DEMO · '.$name, 'status' => 'active']
                )
            );
            $batches = [[], []];

            foreach ($dayCases as $index => [$name, $hour, $offsets, $summary]) {
                $number = 'DEMO-PONTO-'.($index + 1);
                $employee = Employee::firstOrCreate(['employee_number' => $number], [
                    'full_name' => 'DEMO · '.$name,
                    'alterdata_employee_id' => $number,
                    'status' => 'active',
                ]);
                $site = $index % 2;
                $start = $day->setTime($hour, 0);
                ShiftAssignment::firstOrCreate([
                    'work_site_id' => $sites[$site]->id,
                    'employee_id' => $employee->id,
                    'starts_at' => $start,
                ], [
                    'ends_at' => $start->addHours(12),
                    'status' => 'completed',
                    'shift_type' => $hour === 19 ? 'night' : 'day',
                    'notes' => 'Escala de demonstração da conferência de ponto.',
                ]);

                foreach ($offsets as $offset) {
                    $batches[$site][] = [
                        'employee_code' => $employee->alterdata_employee_id,
                        'occurred_at' => $start->addMinutes($offset)->toIso8601String(),
                        'external_id' => $number.'-'.$day->toDateString().'-'.$offset,
                    ];
                }

                if ($summary !== null) {
                    DB::table('hr_timekeeping_summaries')->updateOrInsert([
                        'employee_id' => $employee->id, 'work_date' => $day->toDateString(), 'source' => 'demo',
                    ], array_combine([
                        'worked_minutes', 'overtime_minutes', 'late_minutes', 'early_departure_minutes',
                        'credited_minutes', 'unexcused_absence', 'balance_minutes',
                    ], $summary) + ['created_at' => now(), 'updated_at' => now()]);
                }
            }

            foreach ($batches as $rows) {
                $existing = DB::table('hr_timekeeping_entries')->where('source', 'demo')
                    ->whereIn('external_id', array_column($rows, 'external_id'))->count();

                if ($existing < count($rows)) {
                    $importer->import($rows, 'demo');
                }
            }
        });
    }
}
