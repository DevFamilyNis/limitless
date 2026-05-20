<?php

use App\Domain\Dashboard\Queries\DashboardMetricsQuery;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Transaction;
use App\Models\User;

// Pravilo: Dashboard prikazuje ukupne metrike firme, nije scopeovan po user_id.
// Više korisnika radi u istoj firmi i svi vide iste metrike.

test('dashboard income metric includes transactions from all users in the company', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $incomeTypeId = CategoryType::query()->where('key', 'income')->value('id');
    $year = (int) now()->year;
    $month = (int) now()->month;

    $categoryA = Category::query()->create([
        'user_id' => $userA->id,
        'category_type_id' => $incomeTypeId,
        'name' => 'Income A',
    ]);

    $categoryB = Category::query()->create([
        'user_id' => $userB->id,
        'category_type_id' => $incomeTypeId,
        'name' => 'Income B',
    ]);

    Transaction::query()->create([
        'user_id' => $userA->id,
        'category_id' => $categoryA->id,
        'date' => now()->toDateString(),
        'amount' => 10000,
        'currency' => 'RSD',
        'title' => 'Prihod korisnik A',
    ]);

    Transaction::query()->create([
        'user_id' => $userB->id,
        'category_id' => $categoryB->id,
        'date' => now()->toDateString(),
        'amount' => 5000,
        'currency' => 'RSD',
        'title' => 'Prihod korisnik B',
    ]);

    // Dashboard se poziva sa bilo kojim userId — vraća ukupno firme
    $metrics = app(DashboardMetricsQuery::class)->execute($userA->id);

    expect((float) $metrics['incomeThisMonth'])->toBe(15000.0);
});

test('dashboard open invoices count includes invoices from all users', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $year = (int) now()->year;

    $clientA = Client::query()->create([
        'user_id' => $userA->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Client A',
        'is_active' => true,
    ]);

    $clientB = Client::query()->create([
        'user_id' => $userB->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Client B',
        'is_active' => true,
    ]);

    Invoice::query()->create([
        'client_id' => $clientA->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 1,
        'invoice_number' => '001/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal' => 3000,
        'total' => 3000,
    ]);

    Invoice::query()->create([
        'client_id' => $clientB->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 2,
        'invoice_number' => '002/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal' => 4000,
        'total' => 4000,
    ]);

    // Dashboard je company-wide, prikazuje obe fakture bez obzira ko je logovan
    $metricsAsUserA = app(DashboardMetricsQuery::class)->execute($userA->id);
    $metricsAsUserB = app(DashboardMetricsQuery::class)->execute($userB->id);

    expect($metricsAsUserA['openInvoicesCount'])->toBe(2);
    expect($metricsAsUserB['openInvoicesCount'])->toBe(2);
    expect((float) $metricsAsUserA['openInvoicesAmount'])->toBe(7000.0);
});

test('dashboard metrics are not filtered by the requesting user id', function () {
    $requestingUser = User::factory()->create();
    $otherUser = User::factory()->create();

    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $otherUser->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Trošak',
    ]);

    Transaction::query()->create([
        'user_id' => $otherUser->id,
        'category_id' => $category->id,
        'date' => now()->toDateString(),
        'amount' => 2000,
        'currency' => 'RSD',
        'title' => 'Trošak drugog korisnika',
    ]);

    $metrics = app(DashboardMetricsQuery::class)->execute($requestingUser->id);

    // Trošak drugog korisnika se vidi na dashboardu firme
    expect((float) $metrics['expenseThisMonth'])->toBe(2000.0);
});
