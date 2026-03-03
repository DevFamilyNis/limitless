<?php

use App\Domain\Transactions\Actions\UpsertTransactionAction;
use App\Domain\Transactions\DTO\UpsertTransactionData;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\User;

test('upsert transaction action forces faktura category for fiscal document', function () {
    $user = User::factory()->create();
    $incomeTypeId = CategoryType::query()->where('key', 'income')->value('id');
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $expenseCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Trošak test',
    ]);

    $transaction = app(UpsertTransactionAction::class)->execute(
        UpsertTransactionData::fromArray([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'client_id' => null,
            'document_type' => 'fiscal',
            'invoice_id' => null,
            'date' => now()->toDateString(),
            'amount' => 2200,
            'title' => 'Fiskalni prihod',
            'note' => null,
        ])
    );

    $fakturaCategory = Category::query()->where('category_type_id', $incomeTypeId)
        ->where('name', 'Faktura')
        ->first();

    expect($fakturaCategory)->not->toBeNull();
    expect($transaction->category_id)->toBe($fakturaCategory->id);
});

test('upsert transaction action forces faktura category for invoice document', function () {
    $user = User::factory()->create();
    $incomeTypeId = CategoryType::query()->where('key', 'income')->value('id');
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $expenseCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Pogrešna kategorija',
    ]);

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Test klijent',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 44,
        'invoice_number' => '044/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal' => 4400,
        'total' => 4400,
    ]);

    $transaction = app(UpsertTransactionAction::class)->execute(
        UpsertTransactionData::fromArray([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'client_id' => $client->id,
            'document_type' => 'invoice',
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 4400,
            'title' => 'Naplata fakture',
            'note' => null,
        ])
    );

    $fakturaCategory = Category::query()->where('category_type_id', $incomeTypeId)
        ->where('name', 'Faktura')
        ->first();

    expect($fakturaCategory)->not->toBeNull();
    expect($transaction->category_id)->toBe($fakturaCategory->id);
    expect($transaction->invoice_id)->toBe($invoice->id);
});
