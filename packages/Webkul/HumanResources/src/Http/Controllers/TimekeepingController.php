<?php

namespace Webkul\HumanResources\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\HumanResources\Repositories\EmployeeRepository;
use Webkul\HumanResources\Repositories\ShiftAssignmentRepository;
use Webkul\HumanResources\Repositories\WorkSiteRepository;
use Webkul\HumanResources\Services\ShiftCoverage;
use Webkul\HumanResources\Services\TimekeepingImporter;
use Webkul\HumanResources\Services\TimekeepingShiftMatch;

class TimekeepingController extends Controller
{
    public function index(Request $request, EmployeeRepository $employees, WorkSiteRepository $sites): View
    {
        $this->authorizeScope();

        [$query, $from, $to] = $this->filteredEntries($request);
        $employeeId = $request->filled('employee_id') ? $request->integer('employee_id') : null;
        $shiftAccess = ShiftCoverage::allowed();
        $summary = DB::table('hr_timekeeping_summaries as summary')
            ->join('hr_employees as employee', 'employee.id', '=', 'summary.employee_id')
            ->whereBetween('summary.work_date', [$from->toDateString(), $to->toDateString()])
            ->when($employeeId, fn ($query) => $query->where('summary.employee_id', $employeeId))
            ->orderByDesc('summary.work_date')->orderBy('employee.full_name')->orderBy('summary.id')
            ->paginate(15, ['summary.*', 'employee.full_name', 'employee.employee_number'], 'summary_page')->withQueryString();

        return view('human_resources::timekeeping.index', [
            'from' => $from, 'to' => $to,
            'employees' => $employees->getModel()->newQuery()->withTrashed()->orderBy('full_name')->get(['id', 'full_name', 'employee_number']),
            'entries' => $query->orderByDesc('entries.occurred_at')->orderByDesc('entries.id')->paginate(30)->withQueryString(),
            'batches' => $this->recentBatches(),
            'summary' => $summary,
            'shifts' => $shiftAccess ? $this->shifts($request, $from, $to, $employeeId) : null,
            'sites' => $shiftAccess ? $sites->orderBy('name')->all(['id', 'name']) : collect(),
            'statuses' => TimekeepingShiftMatch::STATUSES,
        ]);
    }

    private function shifts(Request $request, CarbonImmutable $from, CarbonImmutable $to, ?int $employeeId): LengthAwarePaginator
    {
        $filters = $request->validate([
            'work_site_id' => ['nullable', 'integer', 'exists:hr_work_sites,id'],
            'punch_status' => ['nullable', Rule::in([...TimekeepingShiftMatch::STATUSES, 'pending'])],
        ]);
        $matcher = app(TimekeepingShiftMatch::class);
        $query = app(ShiftAssignmentRepository::class)->getModel()->newQuery()
            ->with(['workSite:id,name', 'employee' => fn ($query) => $query->withTrashed()->select('id', 'full_name', 'employee_number')])
            ->whereIn('status', ['confirmed', 'completed', 'absence'])
            ->where('starts_at', '>=', $from)
            ->where('starts_at', '<', $to->addDay())
            ->where('ends_at', '<=', Carbon::now(config('app.timezone'))->subMinutes(TimekeepingShiftMatch::TOLERANCE))
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->when($filters['work_site_id'] ?? null, fn ($query, $site) => $query->where('work_site_id', $site))
            ->orderByDesc('starts_at')->orderByDesc('id');
        $status = $filters['punch_status'] ?? null;
        $perPage = 15;

        if (! $status) {
            $page = $query->paginate($perPage, ['*'], 'shift_page')->withQueryString();
            $marks = $matcher->forShifts($page->getCollection());

            return $page->through(fn ($shift) => $matcher->match($shift, $marks));
        }

        $pageNumber = LengthAwarePaginator::resolveCurrentPage('shift_page');
        $offset = ($pageNumber - 1) * $perPage;
        $rows = [];
        $total = 0;

        $query->chunk(200, function ($shifts) use ($matcher, $status, $offset, $perPage, &$rows, &$total) {
            $marks = $matcher->forShifts($shifts);

            foreach ($shifts as $shift) {
                $row = $matcher->match($shift, $marks);
                if ($status === 'pending' ? $row['result'] === 'matched' : $row['result'] !== $status) {
                    continue;
                }

                if ($total >= $offset && count($rows) < $perPage) {
                    $rows[] = $row;
                }
                $total++;
            }
        });

        return (new LengthAwarePaginator($rows, $total, $perPage, $pageNumber, [
            'path' => $request->url(), 'pageName' => 'shift_page',
        ]))->withQueryString();
    }

    private function recentBatches(): Collection
    {
        return DB::table('hr_timekeeping_batches as batch')->select('batch.*')
            ->selectRaw('COALESCE(first_mark_at, (SELECT MIN(occurred_at) FROM hr_timekeeping_entries WHERE batch_id = batch.id)) AS period_start')
            ->selectRaw('COALESCE(last_mark_at, (SELECT MAX(occurred_at) FROM hr_timekeeping_entries WHERE batch_id = batch.id)) AS period_end')
            ->latest('batch.id')->limit(5)->get();
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeScope();
        [$query] = $this->filteredEntries($request);

        return response()->streamDownload(function () use ($query) {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['matricula', 'colaborador', 'data_hora', 'origem'], ';', '"', '');

            foreach ($query->lazyById(500, 'entries.id', 'id') as $entry) {
                fputcsv($stream, [
                    $this->csvCell($entry->employee_number),
                    $this->csvCell($entry->full_name),
                    CarbonImmutable::parse($entry->occurred_at, 'UTC')->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    $entry->source,
                ], ';', '"', '');
            }

            fclose($stream);
        }, 'marcacoes-de-ponto.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    /**
     * @return array{Builder, CarbonImmutable, CarbonImmutable}
     */
    private function filteredEntries(Request $request): array
    {
        $filters = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:hr_employees,id'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $to = CarbonImmutable::parse($filters['to'] ?? today()->toDateString(), config('app.timezone'));
        $from = isset($filters['from'])
            ? CarbonImmutable::parse($filters['from'], config('app.timezone'))
            : $to->startOfMonth();

        if (! isset($filters['to']) && $from->gt($to)) {
            $to = $from;
        }
        $query = DB::table('hr_timekeeping_entries as entries')->join('hr_employees as employee', 'employee.id', '=', 'entries.employee_id')
            ->where('entries.occurred_at', '>=', $from->utc()->format('Y-m-d H:i:s'))
            ->where('entries.occurred_at', '<', $to->addDay()->utc()->format('Y-m-d H:i:s'))
            ->select('entries.*', 'employee.full_name', 'employee.employee_number');

        if (! empty($filters['employee_id'])) {
            $query->where('entries.employee_id', $filters['employee_id']);
        }

        return [$query, $from, $to];
    }

    private function csvCell(string $value): string
    {
        return preg_match('/^(?:[\t\r\n]|[\s\x{FEFF}]*[=+\-@＝＋－＠])/u', $value) ? "'".$value : $value;
    }

    public function template(): StreamedResponse
    {
        $this->authorizeScope();
        abort_unless(bouncer()->hasPermission('timekeeping'), 401);

        return response()->streamDownload(function () {
            echo "employee_code;occurred_at;external_id\r\n";
        }, 'modelo-marcacoes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, TimekeepingImporter $importer): RedirectResponse
    {
        $this->authorizeScope();
        abort_unless(bouncer()->hasPermission('timekeeping'), 401);

        $request->validate(['file' => ['required', 'file', 'extensions:csv', 'max:5120']]);
        $rows = $importer->readCsv($request->file('file')->getPathname());
        $result = $importer->import($rows, 'alterdata_csv', auth()->guard('user')->id());

        return redirect()->route('admin.human_resources.timekeeping.index')
            ->with('success', trans('human_resources::workforce.imported', $result));
    }

    private function authorizeScope(): void
    {
        abort_unless(auth()->guard('user')->user()->view_permission === 'global', 401);
    }
}
