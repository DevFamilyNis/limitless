<?php

use App\Domain\Invoices\Actions\MarkInvoicePaidAction;
use App\Domain\Invoices\DTO\MarkInvoicePaidData;
use App\Livewire\Dashboard\DashboardPage;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\User;
use Livewire\Livewire;

test('dashboard page is displayed for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Prihod ove godine')
        ->assertSee('Neto ovaj mesec')
        ->assertSee(__('messages.text.sentInvoices'));
});

test('dashboard uses fallback paucal thresholds when tax year is missing', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DashboardPage::class)
        ->assertSee('6.000.000,00')
        ->assertSee('8.000.000,00');
});

test('dashboard financial stats include invoice after marking it paid', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Dashboard Client',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 12,
        'invoice_number' => '012/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'due_date' => now()->addDays(10)->toDateString(),
        'subtotal' => 12500,
        'total' => 12500,
    ]);

    app(MarkInvoicePaidAction::class)->execute(
        MarkInvoicePaidData::fromArray([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
        ])
    );

    Livewire::actingAs($user)
        ->test(DashboardPage::class)
        ->assertSee('12.500,00 RSD');
});
