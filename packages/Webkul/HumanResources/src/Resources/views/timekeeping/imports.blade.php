<section id="importacoes" style="scroll-margin-top: 5rem" class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
    <h2 class="font-bold">@lang('human_resources::timekeeping.batches')</h2>
    @if (bouncer()->hasPermission('timekeeping.import'))
        <form method="POST" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3" action="{{ route('admin.human_resources.timekeeping.import') }}">
            @csrf
            <label>
                @lang('human_resources::workforce.csv_file')
                <input type="file" name="file" accept=".csv" required class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
            </label>
            <button type="submit" class="primary-button">@lang('human_resources::workforce.import')</button>
            <a class="secondary-button" href="{{ route('admin.human_resources.timekeeping.template') }}">@lang('human_resources::workforce.template')</a>
        </form>
        <p class="mt-2 text-sm">@lang('human_resources::workforce.csv_hint')</p>
    @endif

    <div class="mb-2 mt-5 flex flex-wrap justify-between gap-2 text-sm">
        <h3 class="font-semibold">@lang('human_resources::workforce.recent_imports')</h3>
        @if ($latest = $batches->first())
            <span>@lang('human_resources::timekeeping.updated') · {{ \Illuminate\Support\Carbon::parse($latest->created_at)->format('d/m/Y H:i') }}</span>
        @endif
    </div>
    @if ($batches->isEmpty())
        <p class="text-sm">@lang('human_resources::workforce.no_history')</p>
    @else
        @php($formatDate = fn ($value) => \Carbon\CarbonImmutable::parse($value, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/Y'))
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-950">
                    <tr>
                        <th scope="col" class="p-2">@lang('human_resources::workforce.occurred_at')</th>
                        <th scope="col" class="p-2">@lang('human_resources::workforce.source')</th>
                        <th scope="col" class="p-2">@lang('human_resources::timekeeping.coverage')</th>
                        <th scope="col" class="p-2 text-right">@lang('human_resources::timekeeping.received')</th>
                        <th scope="col" class="p-2 text-right">@lang('human_resources::timekeeping.inserted')</th>
                        <th scope="col" class="p-2 text-right">@lang('human_resources::timekeeping.duplicates')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batches as $batch)
                        <tr class="border-t border-gray-200 dark:border-gray-800">
                            <td class="whitespace-nowrap p-2 tabular-nums">{{ \Illuminate\Support\Carbon::parse($batch->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="p-2">@lang("human_resources::workforce.sources.{$batch->source}")</td>
                            <td class="whitespace-nowrap p-2 tabular-nums">
                                @if ($batch->period_start)
                                    {{ $formatDate($batch->period_start) }}
                                    @if ($formatDate($batch->period_start) !== $formatDate($batch->period_end))
                                        – {{ $formatDate($batch->period_end) }}
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-2 text-right tabular-nums">{{ $batch->received }}</td>
                            <td class="p-2 text-right tabular-nums">{{ $batch->inserted }}</td>
                            <td class="p-2 text-right tabular-nums">{{ $batch->received - $batch->inserted }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
