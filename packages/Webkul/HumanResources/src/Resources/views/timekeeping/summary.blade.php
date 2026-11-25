<section id="apuracao" style="scroll-margin-top: 5rem" class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
    <div class="p-4">
        <h2 class="font-bold">@lang('human_resources::timekeeping.summary')</h2>
        <p class="mt-1 text-sm">@lang('human_resources::timekeeping.summary_hint')</p>
    </div>
    @if ($summary->isEmpty())
        <p class="border-t border-gray-200 px-4 py-5 text-sm dark:border-gray-800">@lang('human_resources::timekeeping.no_summary')</p>
    @else
        @php
            $columns = ['worked_minutes', 'overtime_minutes', 'late_minutes', 'early_departure_minutes', 'unexcused_absence', 'credited_minutes', 'balance_minutes'];
            $duration = fn ($minutes) => $minutes === null ? '—' : sprintf('%s%dh%02d', $minutes < 0 ? '−' : '', intdiv(abs($minutes), 60), abs($minutes) % 60);
        @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-y border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950">
                    <tr>
                        <th scope="col" class="px-4 py-2">@lang('human_resources::app.menu.employees')</th>
                        <th scope="col" class="px-3 py-2">@lang('human_resources::timekeeping.date')</th>
                        @foreach ($columns as $column)
                            <th scope="col" class="px-3 py-2 text-right">@lang("human_resources::timekeeping.$column")</th>
                        @endforeach
                        <th scope="col" class="px-4 py-2">@lang('human_resources::workforce.source')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary as $result)
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th scope="row" class="px-4 py-3 font-medium">
                                {{ $result->full_name }}
                                <span class="mt-1 block text-xs font-normal">{{ $result->employee_number }}</span>
                            </th>
                            <td class="whitespace-nowrap px-3 py-3 tabular-nums">{{ \Carbon\CarbonImmutable::parse($result->work_date)->format('d/m/Y') }}</td>
                            @foreach ($columns as $column)
                                <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums">
                                    {{ $column === 'unexcused_absence' && $result->$column !== null
                                        ? trans('human_resources::timekeeping.'.($result->$column ? 'yes' : 'no'))
                                        : $duration($result->$column) }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3">@lang("human_resources::workforce.sources.{$result->source}")</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($summary->hasPages())
            <div class="px-4 pb-4">
                @include('human_resources::workforce.pagination', ['page' => $summary->fragment('apuracao')])
            </div>
        @endif
    @endif
</section>
