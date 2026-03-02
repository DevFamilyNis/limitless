<?php

use App\Actions\Invoices\GenerateInvoiceNumber;
use App\Livewire\Invoices\Form;
use App\Livewire\Invoices\Index;
use App\Models\BillingPeriod;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('invoices page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertOk();
});

test('invoices are visible to another user in shared workspace', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $owner->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Shared Invoice Client',
        'is_active' => true,
    ]);

    Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 10,
        'invoice_number' => '010/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 1000,
        'total' => 1000,
    ]);

    $this->actingAs($anotherUser)
        ->get(route('invoices.index'))
        ->assertOk()
        ->assertSee('010/'.$year);
});

test('create invoice page is displayed with previewed invoice number fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('invoices.create'))
        ->assertOk()
        ->assertSee('Nova faktura')
        ->assertSee('Broj fakture')
        ->assertSee('Godina')
        ->assertSee('Sekvenca');
});

test('invoice number generator preview and execute return next sequence for year', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Generator Klijent',
        'is_active' => true,
    ]);

    Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 1,
        'invoice_number' => '001/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 1000,
        'total' => 1000,
    ]);

    $generator = app(GenerateInvoiceNumber::class);

    $preview = $generator->preview($year);
    $generated = $generator->execute($year);

    expect($preview['invoice_year'])->toBe($year);
    expect($preview['invoice_seq'])->toBe(2);
    expect($preview['invoice_number'])->toBe('002/'.$year);

    expect($generated['invoice_year'])->toBe($year);
    expect($generated['invoice_seq'])->toBe(2);
    expect($generated['invoice_number'])->toBe('002/'.$year);
});

test('changing client loads client price list as invoice items', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Cenovnik Klijent',
        'is_active' => true,
    ]);

    $projectOne = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'DEV',
        'name' => 'Development',
        'is_active' => true,
    ]);

    $projectTwo = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'QA',
        'name' => 'Quality Assurance',
        'is_active' => true,
    ]);

    ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $projectOne->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 10000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);

    ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $projectTwo->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 5000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class)
        ->set('clientId', (string) $client->id)
        ->assertSet('items.0.projectId', (string) $projectOne->id)
        ->assertSet('items.0.unitPrice', '10000.00')
        ->assertSet('items.1.projectId', (string) $projectTwo->id)
        ->assertSet('items.1.unitPrice', '5000.00')
        ->assertSet('total', '15000.00');
});

test('user can create invoice with multiple services and total is sum of items', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Invoice Client',
        'is_active' => true,
    ]);

    $projectOne = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'CONS',
        'name' => 'Consulting',
        'is_active' => true,
    ]);

    $projectTwo = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'SUP',
        'name' => 'Support',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class)
        ->set('clientId', (string) $client->id)
        ->set('statusId', (string) $draftStatusId)
        ->set('issueDate', now()->subMonthNoOverflow()->startOfMonth()->toDateString())
        ->set('issueDateTo', now()->subMonthNoOverflow()->endOfMonth()->toDateString())
        ->set('dueDate', now()->startOfMonth()->addDays(14)->toDateString())
        ->set('items', [
            [
                'id' => null,
                'projectId' => (string) $projectOne->id,
                'clientProjectRateId' => '',
                'description' => 'Consulting',
                'quantity' => '1.00',
                'unitPrice' => '12000.00',
                'amount' => '12000.00',
            ],
            [
                'id' => null,
                'projectId' => (string) $projectTwo->id,
                'clientProjectRateId' => '',
                'description' => 'Support',
                'quantity' => '2.00',
                'unitPrice' => '3000.00',
                'amount' => '6000.00',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('invoices.index', absolute: false));

    $invoice = Invoice::query()
        ->where('client_id', $client->id)
        ->first();

    expect($invoice)->not->toBeNull();
    expect($invoice->invoice_year)->toBe((int) now()->year);
    expect($invoice->invoice_seq)->toBe(1);
    expect($invoice->invoice_number)->toBe('001/'.now()->year);
    expect($invoice->issue_date_to?->toDateString())->toBe(now()->subMonthNoOverflow()->endOfMonth()->toDateString());

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'status_id' => $draftStatusId,
        'subtotal' => '18000.00',
        'total' => '18000.00',
    ]);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $invoice->id,
        'project_id' => $projectOne->id,
        'amount' => '12000.00',
    ]);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $invoice->id,
        'project_id' => $projectTwo->id,
        'amount' => '6000.00',
    ]);
});

test('user can search invoices', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $clientOne = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Alpha Client',
        'is_active' => true,
    ]);

    $clientTwo = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Beta Client',
        'is_active' => true,
    ]);

    Invoice::query()->create([
        'client_id' => $clientOne->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 1,
        'invoice_number' => '001/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 1000,
        'total' => 1000,
    ]);

    Invoice::query()->create([
        'client_id' => $clientTwo->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 2,
        'invoice_number' => '002/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 2000,
        'total' => 2000,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('search', '001/'.$year)
        ->assertSee('001/'.$year)
        ->assertDontSee('002/'.$year);
});

test('user can update invoice while keeping generated number fields', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $paidStatusId = InvoiceStatus::query()->where('key', 'paid')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Update Client',
        'is_active' => true,
    ]);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'UPD',
        'name' => 'Update Service',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 11,
        'invoice_number' => '011/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 1000,
        'total' => 1000,
    ]);

    $invoice->items()->create([
        'project_id' => $project->id,
        'client_project_rate_id' => null,
        'description' => 'Update Service',
        'quantity' => '1.00',
        'unit_price' => '1000.00',
        'amount' => '1000.00',
    ]);

    Livewire::actingAs($user)->test(Form::class, ['invoice' => $invoice])
        ->set('statusId', (string) $paidStatusId)
        ->set('items.0.quantity', '2.00')
        ->set('items.0.unitPrice', '1000.00')
        ->call('save')
        ->assertRedirect(route('invoices.index', absolute: false));

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'status_id' => $paidStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 11,
        'invoice_number' => '011/'.$year,
        'subtotal' => '2000.00',
        'total' => '2000.00',
    ]);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $invoice->id,
        'project_id' => $project->id,
        'quantity' => '2.00',
        'amount' => '2000.00',
    ]);
});

test('user can mark invoice as paid from list', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $paidStatusId = InvoiceStatus::query()->where('key', 'paid')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Paid Client',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 3,
        'invoice_number' => '003/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 3000,
        'total' => 3000,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('markAsPaid', $invoice->id);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'status_id' => $paidStatusId,
    ]);
});

test('user can delete invoice', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Delete Client',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 9,
        'invoice_number' => '009/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 900,
        'total' => 900,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteInvoice', $invoice->id);

    $this->assertDatabaseMissing('invoices', [
        'id' => $invoice->id,
    ]);
});
