<?php

declare(strict_types=1);

use App\Enums\AppSettingKey;
use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Models\AppSetting;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Invoice views use Flux UI. Direct component instantiation avoids view rendering
// while still exercising the full authorization + domain logic path.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeInvoice(int $clientUserId): Invoice
{
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $clientUserId,
        'client_type_id' => $personTypeId,
        'display_name' => 'Test Client',
        'is_active' => true,
    ]);

    return Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => fake()->unique()->numberBetween(100, 999),
        'invoice_number' => fake()->unique()->numerify('###/').$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 5000,
        'total' => 5000,
    ]);
}

// ─── CANNOT: user without manage-invoices ────────────────────────────────────

test('user without manage-invoices cannot mark invoice as paid', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $invoice = makeInvoice($user->id);
    $originalStatusId = $invoice->status_id;

    expect(fn () => (new InvoiceIndex)->markAsPaid($invoice->id))
        ->toThrow(AuthorizationException::class);

    expect(Invoice::find($invoice->id)?->status_id)->toBe($originalStatusId);
    expect(Transaction::query()->where('invoice_id', $invoice->id)->exists())->toBeFalse();
});

test('user without manage-invoices cannot delete invoice', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $invoice = makeInvoice($user->id);

    expect(fn () => (new InvoiceIndex)->deleteInvoice($invoice->id))
        ->toThrow(AuthorizationException::class);

    expect(Invoice::find($invoice->id))->not()->toBeNull();
});

test('user without manage-invoices cannot save invoice through form component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Invoice::query()->count();

    expect(fn () => (new InvoiceForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(Invoice::query()->count())->toBe($initialCount);
});

// ─── CAN: user with manage-invoices ──────────────────────────────────────────

test('user with manage-invoices can mark invoice as paid', function () {
    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageInvoices->value);
    $this->actingAs($user);

    $paidStatusId = InvoiceStatus::query()->where('key', 'paid')->value('id');
    $invoice = makeInvoice($signer->id);

    (new InvoiceIndex)->markAsPaid($invoice->id);

    expect(Invoice::find($invoice->id)?->status_id)->toBe($paidStatusId);
    expect(Transaction::query()->where('invoice_id', $invoice->id)->exists())->toBeTrue();
});

test('user with manage-invoices can delete invoice', function () {
    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageInvoices->value);
    $this->actingAs($user);

    $invoice = makeInvoice($user->id);

    (new InvoiceIndex)->deleteInvoice($invoice->id);

    expect(Invoice::find($invoice->id))->toBeNull();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can delete invoice via gate bypass', function () {
    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $invoice = makeInvoice($superAdmin->id);

    (new InvoiceIndex)->deleteInvoice($invoice->id);

    expect(Invoice::find($invoice->id))->toBeNull();
});
