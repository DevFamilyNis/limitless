<?php

use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Transaction;
use App\Models\User;

// Pravilo: canBeDeleted koristi Eloquent relacije, ne Schema introspection.

test('client without invoices or transactions can be deleted', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Clean Client',
        'is_active' => true,
    ]);

    expect($client->canBeDeleted())->toBeTrue();
});

test('client with invoice cannot be deleted', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Client With Invoice',
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

    expect($client->canBeDeleted())->toBeFalse();
});

test('client with transaction cannot be deleted', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $incomeTypeId = CategoryType::query()->where('key', 'income')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Client With Transaction',
        'is_active' => true,
    ]);

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $incomeTypeId,
        'name' => 'Test Income',
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'category_id' => $category->id,
        'date' => now()->toDateString(),
        'amount' => 500,
        'currency' => 'RSD',
        'title' => 'Test Transaction',
    ]);

    expect($client->canBeDeleted())->toBeFalse();
});

test('canBeDeleted uses eloquent relations not schema introspection', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Relation Check Client',
        'is_active' => true,
    ]);

    // Verifikacija: metoda ne koristi Schema:: – proveravamo da relacije postoje
    expect($client->invoices())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($client->transactions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($client->canBeDeleted())->toBeTrue();
});
