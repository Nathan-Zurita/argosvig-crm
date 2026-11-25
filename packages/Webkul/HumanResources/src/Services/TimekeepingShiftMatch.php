<?php

namespace Webkul\HumanResources\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\HumanResources\Models\ShiftAssignment;

class TimekeepingShiftMatch
{
    public const TOLERANCE = 30;

    public const STATUSES = ['matched', 'missing_start', 'missing_end', 'missing_both', 'single_mark'];

    public function forShifts(Collection $shifts): Collection
    {
        if ($shifts->isEmpty()) {
            return collect();
        }

        return DB::table('hr_timekeeping_entries')
            ->whereIn('employee_id', $shifts->pluck('employee_id')->unique())
            ->whereBetween('occurred_at', [
                $shifts->min('starts_at')->copy()->subMinutes(self::TOLERANCE)->utc(),
                $shifts->max('ends_at')->copy()->addMinutes(self::TOLERANCE)->utc(),
            ])
            ->orderBy('occurred_at')
            ->get(['employee_id', 'occurred_at', 'source'])
            ->each(function ($mark) {
                $mark->time = Carbon::parse($mark->occurred_at, 'UTC')->setTimezone(config('app.timezone'));
            })
            ->groupBy('employee_id');
    }

    public function match(ShiftAssignment $shift, Collection $byEmployee): array
    {
        $window = self::TOLERANCE * 60;
        $start = $shift->starts_at->timestamp;
        $end = $shift->ends_at->timestamp;
        $marks = $byEmployee->get($shift->employee_id, collect())->filter(
            fn ($mark) => $mark->time->timestamp >= $start - $window && $mark->time->timestamp <= $end + $window
        );
        $near = fn (int $edge) => $marks
            ->filter(fn ($mark) => abs($mark->time->timestamp - $edge) <= $window)
            ->sortBy(fn ($mark) => abs($mark->time->timestamp - $edge));
        $entries = $near($start);
        $exits = $near($end);
        $entry = $entries->first();
        $exit = $exits->first();

        $result = match (true) {
            ! $entry && ! $exit => 'missing_both',
            ! $entry => 'missing_start',
            ! $exit => 'missing_end',
            $entries->merge($exits)->unique('occurred_at')->count() < 2 => 'single_mark',
            default => 'matched',
        };

        if ($entry && $exit && $entry->time->gte($exit->time)) {
            $exit = $exits->first(fn ($mark) => $mark->time->gt($entry->time));

            if (! $exit) {
                $exit = $exits->first();
                $entry = $entries->first(fn ($mark) => $mark->time->lt($exit->time));
            }

            if (! $entry) {
                $exit = null;
                $result = 'single_mark';
            }
        }

        return compact('shift', 'result', 'entry', 'exit', 'marks');
    }
}
