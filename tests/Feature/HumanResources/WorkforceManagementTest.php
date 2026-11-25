<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Contact\Models\Organization;
use Webkul\HumanResources\Contracts\TimekeepingProvider;
use Webkul\HumanResources\Models\Employee;
use Webkul\HumanResources\Models\EmployeeDocument;
use Webkul\HumanResources\Models\ShiftAssignment;
use Webkul\HumanResources\Models\WorkSite;
use Webkul\HumanResources\Services\TimekeepingImporter;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

uses(DatabaseTransactions::class);

function workforceEmployee(): Employee
{
    return Employee::query()->create([
        'employee_number' => fake()->unique()->numerify('HR-########'),
        'full_name' => 'Colaborador de teste',
        'status' => 'active',
        'alterdata_employee_id' => fake()->unique()->numerify('000########'),
    ]);
}

function workforceUser(array $permissions, string $scope = 'global'): User
{
    $role = Role::query()->create([
        'name' => fake()->uuid(),
        'description' => 'Workforce test',
        'permission_type' => 'custom',
        'permissions' => $permissions,
    ]);

    return User::query()->create([
        'name' => 'Workforce test',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('test-secret'),
        'status' => 1,
        'view_permission' => $scope,
        'role_id' => $role->id,
    ]);
}

it('reports missing payroll documents for the selected month to authorized HR users', function () {
    $missing = workforceEmployee();
    $missing->update(['full_name' => 'RH-AAA-Pendente']);
    $complete = workforceEmployee();
    $complete->update(['full_name' => 'RH-AAA-Completo']);
    $complete->documents()->create([
        'type' => 'payslip', 'name' => 'Contracheque', 'competence' => '2026-11-01',
        'path' => 'test.pdf', 'original_name' => 'test.pdf', 'mime_type' => 'application/pdf', 'size' => 10,
    ]);

    $url = route('admin.human_resources.document_center.missing', ['type' => 'payslip', 'competence' => '2026-11']);
    $this->actingAs(workforceUser(['document-center', 'human-resources.document-center']), 'user')
        ->get($url)->assertOk()->assertSee($missing->full_name)->assertDontSee($complete->full_name);
    $this->actingAs(workforceUser(['document-center', 'human-resources.shared-documents']), 'user')
        ->get($url)->assertUnauthorized();
    $this->actingAs(workforceUser(['document-center', 'human-resources.document-center'], 'individual'), 'user')
        ->get($url)->assertUnauthorized();
});

it('notifies only authorized HR users once per day without including document identities', function () {
    workforceEmployee()->documents()->create([
        'type' => 'identity', 'name' => 'Sensitive document name', 'expires_at' => today()->addDay(),
        'path' => 'test.pdf', 'original_name' => 'test.pdf', 'mime_type' => 'application/pdf', 'size' => 10,
    ]);

    $allowed = workforceUser(['document-center', 'human-resources.document-center']);
    $sharedOnly = workforceUser(['document-center', 'human-resources.shared-documents']);
    $individual = workforceUser(['document-center', 'human-resources.document-center'], 'individual');

    $this->artisan('hr:remind-document-expirations')->assertSuccessful();
    $this->artisan('hr:remind-document-expirations')->assertSuccessful();

    $notification = DB::table('notifications')->where('user_id', $allowed->id)->where('action', 'expiry_reminder')->sole();
    expect($notification->title)->not->toContain('Sensitive document name');
    expect($notification->route)->toContain('expiry=soon');
    expect(DB::table('notifications')->whereIn('user_id', [$sharedOnly->id, $individual->id])->exists())->toBeFalse();
});

it('requires a payroll month and audits replacement and download of a private document', function () {
    Storage::fake('local');
    $employee = workforceEmployee();
    $this->actingAs(getDefaultAdmin(), 'user');
    $this->get(route('admin.human_resources.document_center.create'))->assertOk();
    $store = route('admin.human_resources.document_center.store');
    $payload = ['employee_id' => $employee->id, 'type' => 'payslip', 'name' => 'Contracheque de teste'];

    $this->post($store, $payload + ['file' => UploadedFile::fake()->create('old.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors('competence');

    $this->post($store, $payload + ['competence' => '2026-11', 'file' => UploadedFile::fake()->create('old.pdf', 10, 'application/pdf')])
        ->assertRedirect();

    $document = $employee->documents()->sole();
    $previous = $document->path;
    Storage::disk('local')->assertExists($previous);

    $this->get(route('admin.human_resources.document_center.index', ['competence' => '2026-11']))
        ->assertOk()->assertSee($document->name);

    $this->put(route('admin.human_resources.document_center.update', $document->id), [
        'type' => 'payslip', 'competence' => '2026-11', 'name' => $document->name,
        'file' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'),
    ])->assertRedirect();

    Storage::disk('local')->assertMissing($previous);
    Storage::disk('local')->assertExists($document->refresh()->path);
    $this->get(route('admin.human_resources.document_center.download', $document->id))->assertDownload('new.pdf');
    $this->get(route('admin.human_resources.document_center.show', $document->id))->assertOk()->assertSee('new.pdf');

    foreach (['created', 'replaced', 'downloaded'] as $action) {
        $this->assertDatabaseHas('hr_document_audits', ['document_id' => $document->id, 'action' => $action]);
    }
});

it('stores a shared file once and isolates it from employee document permissions', function () {
    Storage::fake('local');
    $this->actingAs(getDefaultAdmin(), 'user');
    $first = Organization::query()->create(['name' => 'Empresa Teste A']);
    $second = Organization::query()->create(['name' => 'Empresa Teste B']);
    $store = route('admin.human_resources.document_center.store_shared');
    $this->get(route('admin.human_resources.document_center.create_shared'))->assertOk();

    $this->post($store, ['type' => 'solidarity_term', 'name' => 'Termo de teste', 'file' => UploadedFile::fake()->create('termo.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors(['organizations', 'contracts']);

    $this->post($store, [
        'type' => 'solidarity_term', 'name' => 'Termo de teste',
        'organizations' => [$first->id, $second->id],
        'file' => UploadedFile::fake()->create('termo.pdf', 10, 'application/pdf'),
    ])->assertRedirect();

    $document = EmployeeDocument::query()->whereNull('employee_id')->where('name', 'Termo de teste')->sole();
    expect($document->organizations()->count())->toBe(2);
    expect(Storage::disk('local')->allFiles())->toBe([$document->path]);
    $this->get(route('admin.human_resources.document_center.edit', $document->id))->assertOk()->assertSee('Empresa Teste A');
    $this->get(route('admin.human_resources.document_center.index', ['category' => 'employee']))->assertOk()->assertDontSee($document->name);

    $this->actingAs(workforceUser(['document-center', 'human-resources.document-center']), 'user');
    $this->get(route('admin.human_resources.document_center.index'))->assertOk()->assertDontSee($document->name);
    $this->get(route('admin.human_resources.document_center.download', $document->id))->assertUnauthorized();
    $this->post($store)->assertUnauthorized();

    $this->actingAs(workforceUser(['document-center', 'human-resources.shared-documents']), 'user');
    $this->get(route('admin.human_resources.document_center.index'))->assertOk()->assertSee($document->name);
    $this->get(route('admin.human_resources.document_center.download', $document->id))->assertDownload('termo.pdf');
    $this->delete(route('admin.human_resources.document_center.delete', $document->id))->assertUnauthorized();
});

it('keeps legacy documents private from employee viewers and scoped managers', function () {
    Storage::fake('local');
    $employee = workforceEmployee();
    $this->actingAs(getDefaultAdmin(), 'user')->post(route('admin.human_resources.employee_documents.store', $employee->id), [
        'name' => 'Arquivo restrito', 'type' => 'identity', 'file' => UploadedFile::fake()->create('rg.pdf', 10, 'application/pdf'),
    ])->assertRedirect();
    $document = $employee->documents()->sole();
    $url = route('admin.human_resources.employee_documents.download', [$employee->id, $document->id]);
    $this->actingAs(workforceUser(['human-resources.employees.view']), 'user');
    $this->get(route('admin.human_resources.employees.show', $employee->id))->assertOk()->assertDontSee($document->name);
    $this->get($url)->assertUnauthorized();

    $this->actingAs(workforceUser(['human-resources.employees.documents'], 'individual'), 'user');
    $this->get($url)->assertUnauthorized();
});

it('imports linked Alterdata marks without duplicating them and rejects incomplete batches', function () {
    $employee = workforceEmployee();
    $this->actingAs(workforceUser(['timekeeping', 'timekeeping.import']), 'user');
    $url = route('admin.human_resources.timekeeping.import');
    $csv = "employee_code;occurred_at;external_id\n{$employee->alterdata_employee_id};2026-11-13 08:00:00;test-mark-1\n";

    for ($attempt = 0; $attempt < 2; $attempt++) {
        $this->post($url, ['file' => UploadedFile::fake()->createWithContent('marks.csv', $csv)])->assertRedirect();
    }

    expect(DB::table('hr_timekeeping_entries')->where('employee_id', $employee->id)->count())->toBe(1);
    $this->assertDatabaseHas('hr_timekeeping_entries', [
        'employee_id' => $employee->id, 'occurred_at' => '2026-11-13 11:00:00', 'source' => 'alterdata_csv',
    ]);
    $this->assertDatabaseHas('hr_timekeeping_batches', [
        'source' => 'alterdata_csv', 'received' => 1, 'inserted' => 0,
        'first_mark_at' => '2026-11-13 11:00:00', 'last_mark_at' => '2026-11-13 11:00:00',
    ]);

    $bad = $csv."{$employee->alterdata_employee_id};2026-11-13 09:00:00;test-mark-2\nmissing;2026-11-13 09:01:00;test-mark-3\n";
    $this->post($url, ['file' => UploadedFile::fake()->createWithContent('marks.csv', $bad)])->assertSessionHasErrors('file');
    $this->assertDatabaseMissing('hr_timekeeping_entries', ['external_id' => 'test-mark-2']);

    $this->get(route('admin.human_resources.timekeeping.index', ['employee_id' => $employee->id, 'from' => '2026-11-13', 'to' => '2026-11-13']))
        ->assertOk()->assertSee('13/11/2026 08:00:00');

    $export = $this->get(route('admin.human_resources.timekeeping.export', ['employee_id' => $employee->id, 'from' => '2026-11-13', 'to' => '2026-11-13']));
    $export->assertDownload('marcacoes-de-ponto.csv');
    expect($export->streamedContent())->toContain($employee->employee_number, '2026-11-13 08:00:00');
});

it('uses the same validated import path for an API provider and refuses to run without one', function () {
    $this->artisan('hr:sync-alterdata-timekeeping', ['from' => '2026-11-13', 'to' => '2026-11-13'])->assertFailed();
    $employee = workforceEmployee();
    $provider = Mockery::mock(TimekeepingProvider::class);
    $provider->shouldReceive('entries')->once()->andReturn([[
        'employee_code' => $employee->alterdata_employee_id,
        'occurred_at' => '2026-11-13T08:00:00-03:00',
        'external_id' => 'api-test-mark',
    ]]);
    $this->app->instance(TimekeepingProvider::class, $provider);

    $this->artisan('hr:sync-alterdata-timekeeping', ['from' => '2026-11-13', 'to' => '2026-11-13'])->assertSuccessful();
    $this->assertDatabaseHas('hr_timekeeping_entries', ['employee_id' => $employee->id, 'external_id' => 'api-test-mark', 'source' => 'alterdata_api']);
});

it('reviews overnight shifts by their start date and filters pending punches', function () {
    $employee = workforceEmployee();
    $site = WorkSite::create(['code' => fake()->uuid(), 'name' => 'Posto para conferência', 'status' => 'active']);
    $shift = ShiftAssignment::create([
        'employee_id' => $employee->id,
        'work_site_id' => $site->id,
        'starts_at' => '2026-11-13 19:00:00',
        'ends_at' => '2026-11-14 07:00:00',
        'shift_type' => 'night',
        'status' => 'completed',
    ]);
    $importer = app(TimekeepingImporter::class);
    $importer->import([['employee_code' => $employee->alterdata_employee_id, 'occurred_at' => '2026-11-13 18:57:00']], 'alterdata_csv');
    $filters = [
        'employee_id' => $employee->id, 'work_site_id' => $site->id, 'from' => '2026-11-13', 'to' => '2026-11-13',
    ];
    $this->travelTo(Carbon::parse('2026-11-14 10:00:00'));
    $this->actingAs(workforceUser(['timekeeping', 'human-resources.work-sites', 'human-resources.shifts']), 'user')
        ->get(route('admin.human_resources.timekeeping.index', $filters + ['punch_status' => 'pending']))
        ->assertOk()
        ->assertViewHas('shifts', fn ($shifts) => $shifts->total() === 1 && $shifts->first()['result'] === 'missing_end');

    $importer->import([['employee_code' => $employee->alterdata_employee_id, 'occurred_at' => '2026-11-14 07:05:00']], 'alterdata_csv');
    $this->get(route('admin.human_resources.timekeeping.index', $filters + ['punch_status' => 'pending']))
        ->assertOk()->assertViewHas('shifts', fn ($shifts) => $shifts->total() === 0);
    $this->get(route('admin.human_resources.timekeeping.index', $filters))
        ->assertOk()->assertViewHas('shifts', fn ($shifts) => $shifts->first()['shift']->is($shift)
            && $shifts->first()['result'] === 'matched'
            && $shifts->first()['exit']->time->format('d/m H:i') === '14/11 07:05');
});

it('enforces separate timekeeping read and import permissions', function () {
    $this->actingAs(workforceUser(['timekeeping']), 'user');
    $this->get(route('admin.human_resources.timekeeping.index'))->assertOk()
        ->assertDontSee(route('admin.human_resources.timekeeping.import'));
    $this->post(route('admin.human_resources.timekeeping.import'))->assertUnauthorized();

    $this->actingAs(workforceUser(['timekeeping', 'timekeeping.import'], 'individual'), 'user');
    $this->get(route('admin.human_resources.timekeeping.index'))->assertUnauthorized();
    $this->get(route('admin.human_resources.timekeeping.export'))->assertUnauthorized();
    $this->post(route('admin.human_resources.timekeeping.import'))->assertUnauthorized();
});
