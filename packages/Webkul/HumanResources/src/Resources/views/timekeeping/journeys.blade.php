<section id="jornadas" style="scroll-margin-top: 5rem" class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
    <div class="p-4">
        <h2 class="font-bold">@lang('human_resources::timekeeping.daily') <span class="ms-2 text-sm font-normal tabular-nums">{{ $shifts->total() }}</span></h2>
        <p class="mt-1 text-sm">@lang('human_resources::timekeeping.daily_hint')</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-y border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950">
                <tr>
                    <th scope="col" class="px-4 py-2">@lang('human_resources::app.menu.employees')</th>
                    <th scope="col" class="px-3 py-2">@lang('human_resources::timekeeping.site')</th>
                    <th scope="col" class="px-3 py-2">@lang('human_resources::timekeeping.schedule')</th>
                    <th scope="col" class="px-3 py-2">@lang('human_resources::timekeeping.entry')</th>
                    <th scope="col" class="px-3 py-2">@lang('human_resources::timekeeping.exit')</th>
                    <th scope="col" class="px-4 py-2">@lang('human_resources::timekeeping.status')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shifts as ['shift' => $shift, 'result' => $result, 'entry' => $entry, 'exit' => $exit, 'marks' => $marks])
                    @php($overnight = ! $shift->starts_at->isSameDay($shift->ends_at))
                    <tr class="border-b border-gray-200 align-top dark:border-gray-800">
                        <th scope="row" class="px-4 py-3 font-medium">
                            {{ $shift->employee->full_name }}
                            <span class="mt-1 block text-xs font-normal">{{ $shift->employee->employee_number }}</span>
                        </th>
                        <td class="px-3 py-3">{{ $shift->workSite?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-3 tabular-nums">
                            <span class="block font-medium">{{ $shift->starts_at->format('d/m/Y') }}</span>
                            {{ $shift->starts_at->format('H:i') }} – {{ $shift->ends_at->format($overnight ? 'd/m H:i' : 'H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 tabular-nums">{{ $entry?->time->format($entry->time->isSameDay($shift->starts_at) ? 'H:i' : 'd/m H:i') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-3 tabular-nums">{{ $exit?->time->format($exit->time->isSameDay($shift->starts_at) ? 'H:i' : 'd/m H:i') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'inline-block rounded-md px-2 py-1 text-xs font-medium',
                                'bg-green-50 text-green-900 dark:bg-green-950 dark:text-green-200' => $result === 'matched',
                                'bg-yellow-200 text-yellow-900 dark:bg-gray-950 dark:text-amber-300' => $result !== 'matched',
                            ])>@lang("human_resources::timekeeping.$result")</span>
                            <details class="mt-2">
                                <summary class="cursor-pointer underline underline-offset-2">@lang('human_resources::timekeeping.details', ['count' => $marks->count()])</summary>
                                <ul class="mt-1 space-y-1">
                                    @forelse ($marks as $mark)
                                        <li class="tabular-nums">{{ $mark->time->format('d/m H:i:s') }} · @lang("human_resources::workforce.sources.{$mark->source}")</li>
                                    @empty
                                        <li>@lang('human_resources::timekeeping.empty_detail')</li>
                                    @endforelse
                                </ul>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center">@lang('human_resources::timekeeping.no_shifts')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($shifts->hasPages())
        <div class="px-4 pb-4">
            @include('human_resources::workforce.pagination', ['page' => $shifts->fragment('jornadas')])
        </div>
    @endif
</section>
