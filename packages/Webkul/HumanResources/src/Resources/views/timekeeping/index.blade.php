<x-admin::layouts>
    <x-slot:title>@lang('human_resources::workforce.timekeeping')</x-slot>

    <div v-pre class="flex flex-col gap-4">
        <section class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
            <h1 class="text-xl font-bold">@lang('human_resources::workforce.timekeeping')</h1>
            <p class="mt-1 text-sm">@lang('human_resources::timekeeping.intro')</p>
            <nav class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm" aria-label="{{ trans('human_resources::workforce.timekeeping') }}">
                @if ($shifts !== null)
                    <a href="#jornadas" class="font-medium underline underline-offset-2">@lang('human_resources::timekeeping.daily')</a>
                @endif
                <a href="#apuracao" class="font-medium underline underline-offset-2">@lang('human_resources::timekeeping.summary')</a>
                <a href="#marcacoes" class="font-medium underline underline-offset-2">@lang('human_resources::workforce.marks')</a>
                <a href="#importacoes" class="font-medium underline underline-offset-2">@lang('human_resources::timekeeping.batches')</a>
            </nav>
            @include('human_resources::workforce.errors')
        </section>

        <section class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
            <h2 class="mb-3 font-bold">@lang('human_resources::timekeeping.filters')</h2>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label>
                    @lang('human_resources::app.menu.employees')
                    <select data-searchable name="employee_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        <option value="">@lang('human_resources::workforce.all')</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) request('employee_id') === (string) $employee->id)>{{ $employee->full_name }} · {{ $employee->employee_number }}</option>
                        @endforeach
                    </select>
                </label>
                <label>@lang('human_resources::workforce.from') <input name="from" type="date" value="{{ $from->toDateString() }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:text-white"></label>
                <label>@lang('human_resources::workforce.to') <input name="to" type="date" value="{{ $to->toDateString() }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:text-white"></label>
                @if ($shifts !== null)
                    <label>
                        @lang('human_resources::timekeeping.site')
                        <select data-searchable name="work_site_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">@lang('human_resources::workforce.all')</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" @selected((string) request('work_site_id') === (string) $site->id)>{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        @lang('human_resources::timekeeping.status')
                        <select name="punch_status" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">@lang('human_resources::workforce.all')</option>
                            @foreach (['pending', ...$statuses] as $status)
                                <option value="{{ $status }}" @selected(request('punch_status') === $status)>@lang("human_resources::timekeeping.$status")</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <button class="primary-button" type="submit">@lang('human_resources::workforce.filter')</button>
                @if (request()->anyFilled(['employee_id', 'from', 'to', 'work_site_id', 'punch_status']))
                    <a class="secondary-button" href="{{ route('admin.human_resources.timekeeping.index') }}">@lang('human_resources::timekeeping.clear')</a>
                @endif
            </form>
            @if ($shifts !== null)
                <p class="mt-2 text-sm">@lang('human_resources::timekeeping.shift_filters')</p>
            @endif
        </section>

        @if ($shifts !== null)
            @include('human_resources::timekeeping.journeys')
        @endif
        @include('human_resources::timekeeping.summary')

        <section id="marcacoes" style="scroll-margin-top: 5rem" class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold">@lang('human_resources::workforce.marks') <span class="ms-2 text-sm font-normal tabular-nums">{{ $entries->total() }}</span></h2>
                <a class="secondary-button" href="{{ route('admin.human_resources.timekeeping.export', request()->only(['employee_id', 'from', 'to'])) }}">@lang('human_resources::workforce.export')</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead><tr>
                        <th scope="col" class="p-2">@lang('human_resources::app.employees.fields.employee_number')</th>
                        <th scope="col" class="p-2">@lang('human_resources::app.menu.employees')</th>
                        <th scope="col" class="p-2">@lang('human_resources::workforce.occurred_at')</th>
                        <th scope="col" class="p-2">@lang('human_resources::workforce.source')</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="p-2">{{ $entry->employee_number }}</td>
                                <td class="p-2">{{ $entry->full_name }}</td>
                                <td class="p-2">{{ \Carbon\CarbonImmutable::parse($entry->occurred_at, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</td>
                                <td class="p-2">@lang("human_resources::workforce.sources.{$entry->source}")</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-4 text-center">@lang('human_resources::workforce.no_marks')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('human_resources::workforce.pagination', ['page' => $entries->fragment('marcacoes')])
        </section>

        @include('human_resources::timekeeping.imports')
    </div>
</x-admin::layouts>
