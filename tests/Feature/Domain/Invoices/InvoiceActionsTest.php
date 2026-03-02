<?php

use App\Domain\Invoices\Actions\MarkInvoicePaidAction;
use App\Domain\Invoices\Actions\UpsertInvoiceAction;
use App\Domain\Invoices\DTO\MarkInvoicePaidData;
use App\Domain\Invoices\DTO\UpsertInvoiceData;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\User;

test('upsert invoice action creates and updates invoice with items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $sentStatusId = InvoiceStatus::query()->where('key', 'sent')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Invoice Domain Client',
        'is_active' => true,
    ]);

    $projectOne = Project::factory()->create(['user_id' => $user->id]);
    $projectTwo = Project::factory()->create(['user_id' => $user->id]);

    $created = app(UpsertInvoiceAction::class)->execute(
        UpsertInvoiceData::fromArray([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status_id' => $draftStatusId,
            'issue_date' => now()->toDateString(),
            'issue_date_to' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'total' => 15000,
            'note' => 'Prva verzija',
            'items' => [
                [
                    'projectId' => (string) $projectOne->id,
                    'clientProjectRateId' => '',
                    'description' => 'Service one',
                    'quantity' => '1.00',
                    'unitPrice' => '10000.00',
                    'amount' => '10000.00',
                ],
                [
                    'projectId' => (string) $projectTwo->id,
                    'clientProjectRateId' => '',
                    'description' => 'Service two',
                    'quantity' => '1.00',
                    'unitPrice' => '5000.00',
                    'amount' => '5000.00',
                ],
            ],
        ])
    );

    expect($created->invoice_number)->toBe('001/'.now()->year);
    expect($created->issue_date_to?->toDateString())->toBe(now()->toDateString());
    expect($created->items)->toHaveCount(2);

    $updated = app(UpsertInvoiceAction::class)->execute(
        UpsertInvoiceData::fromArray([
            'user_id' => $user->id,
            'invoice_id' => $created->id,
            'client_id' => $client->id,
            'status_id' => $sentStatusId,
            'issue_date' => now()->toDateString(),
            'issue_date_to' => now()->toDateString(),
            'due_date' => now()->addDays(20)->toDateString(),
            'total' => 7000,
            'note' => 'Izmena',
            'items' => [
                [
                    'projectId' => (string) $projectTwo->id,
                    'clientProjectRateId' => '',
                    'description' => 'Service two updated',
                    'quantity' => '1.00',
                    'unitPrice' => '7000.00',
                    'amount' => '7000.00',
                ],
            ],
        ])
    );

    expect($updated->id)->toBe($created->id);
    expect($updated->invoice_number)->toBe($created->invoice_number);
    expect($updated->issue_date_to?->toDateString())->toBe(now()->toDateString());
    expect((float) $updated->total)->toBe(7000.0);
    expect($updated->items)->toHaveCount(1);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $created->id,
        'project_id' => $projectTwo->id,
        'amount' => '7000.00',
    ]);
});

test('mark invoice paid action updates status to paid', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $paidStatusId = InvoiceStatus::query()->where('key', 'paid')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Paid Client',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 1,
        'invoice_number' => '001/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'due_date' => now()->addDays(15)->toDateString(),
        'subtotal' => 1000,
        'total' => 1000,
    ]);

    app(MarkInvoicePaidAction::class)->execute(
        MarkInvoicePaidData::fromArray([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
        ])
    );

    $invoice->refresh();

    expect($invoice->status_id)->toBe($paidStatusId);
});

test('person invoice does not consume company invoice counter', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $companyClient = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Company Client',
        'is_active' => true,
    ]);

    $personClient = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Person Client',
        'is_active' => true,
    ]);

    $project = Project::factory()->create(['user_id' => $user->id]);

    Invoice::query()->create([
        'client_id' => $companyClient->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 20,
        'invoice_number' => '020/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'due_date' => now()->addDays(15)->toDateString(),
        'subtotal' => 1000,
        'total' => 1000,
    ]);

    $personInvoice = app(UpsertInvoiceAction::class)->execute(
        UpsertInvoiceData::fromArray([
            'user_id' => $user->id,
            'client_id' => $personClient->id,
            'status_id' => $draftStatusId,
            'issue_date' => now()->toDateString(),
            'issue_date_to' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'total' => 3000,
            'note' => null,
            'items' => [
                [
                    'projectId' => (string) $project->id,
                    'clientProjectRateId' => '',
                    'description' => 'Fiscal item',
                    'quantity' => '1.00',
                    'unitPrice' => '3000.00',
                    'amount' => '3000.00',
                ],
            ],
        ])
    );

    expect($personInvoice->invoice_year)->toBe(0);
    expect($personInvoice->invoice_number)->toStartWith('FIZ-');

    $companyInvoice = app(UpsertInvoiceAction::class)->execute(
        UpsertInvoiceData::fromArray([
            'user_id' => $user->id,
            'client_id' => $companyClient->id,
            'status_id' => $draftStatusId,
            'issue_date' => now()->toDateString(),
            'issue_date_to' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'total' => 5000,
            'note' => null,
            'items' => [
                [
                    'projectId' => (string) $project->id,
                    'clientProjectRateId' => '',
                    'description' => 'Company item',
                    'quantity' => '1.00',
                    'unitPrice' => '5000.00',
                    'amount' => '5000.00',
                ],
            ],
        ])
    );

    expect($companyInvoice->invoice_number)->toBe('021/'.$year);
});
