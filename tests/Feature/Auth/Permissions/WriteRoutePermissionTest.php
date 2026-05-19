<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Models\Category;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\Invoice;
use App\Models\Issue;
use App\Models\Lead;
use App\Models\Project;
use App\Models\TaxYear;
use App\Models\User;

// Write routes (create/edit) are guarded with can:manage-* middleware.
// "Cannot" tests use a user with no role (zero permissions).
// SubstituteBindings (web middleware group) runs BEFORE can:, so edit route tests
// must use real model IDs — a fake ID gives 404 before the permission check fires.
// "Can" and super-admin positive tests check status != 403 (view rendering not under test).

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── CANNOT: user with no role ────────────────────────────────────────────────

test('user without manage-leads cannot access leads write routes', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    $this->actingAs($user)->get(route('leads.create'))->assertForbidden();
    $this->actingAs($user)->get(route('leads.edit', $lead))->assertForbidden();
});

test('user without manage-clients cannot access clients write routes', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create();

    $this->actingAs($user)->get(route('clients.create'))->assertForbidden();
    $this->actingAs($user)->get(route('clients.edit', $client))->assertForbidden();
});

test('user without manage-projects cannot access projects write routes', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)->get(route('projects.create'))->assertForbidden();
    $this->actingAs($user)->get(route('projects.edit', $project))->assertForbidden();
});

test('user without manage-clients cannot access client-project-rates write routes', function () {
    $user = User::factory()->create();
    $rate = ClientProjectRate::factory()->create();

    $this->actingAs($user)->get(route('client-project-rates.create'))->assertForbidden();
    $this->actingAs($user)->get(route('client-project-rates.edit', $rate))->assertForbidden();
});

test('user without manage-invoices cannot access invoices write routes', function () {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create();

    $this->actingAs($user)->get(route('invoices.create'))->assertForbidden();
    $this->actingAs($user)->get(route('invoices.edit', $invoice))->assertForbidden();
});

test('user without manage-categories cannot access categories write routes', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)->get(route('categories.create'))->assertForbidden();
    $this->actingAs($user)->get(route('categories.edit', $category))->assertForbidden();
});

test('user without manage-tax-years cannot access tax-years write routes', function () {
    $user = User::factory()->create();
    $taxYear = TaxYear::factory()->create();

    $this->actingAs($user)->get(route('tax-years.create'))->assertForbidden();
    $this->actingAs($user)->get(route('tax-years.edit', $taxYear))->assertForbidden();
});

test('user without manage-issues cannot access issues write routes', function () {
    $user = User::factory()->create();
    $issue = Issue::factory()->create();

    $this->actingAs($user)->get(route('issues.create'))->assertForbidden();
    $this->actingAs($user)->get(route('issues.edit', $issue))->assertForbidden();
});

// ─── CAN: user with matching permission ───────────────────────────────────────

test('user with manage-leads can access leads create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageLeads->value);

    $response = $this->actingAs($user)->get(route('leads.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-clients can access clients create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);

    $response = $this->actingAs($user)->get(route('clients.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-projects can access projects create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageProjects->value);

    $response = $this->actingAs($user)->get(route('projects.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-clients can access client-project-rates create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);

    $response = $this->actingAs($user)->get(route('client-project-rates.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-invoices can access invoices create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageInvoices->value);

    $response = $this->actingAs($user)->get(route('invoices.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-categories can access categories create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageCategories->value);

    $response = $this->actingAs($user)->get(route('categories.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-tax-years can access tax-years create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageTaxYears->value);

    $response = $this->actingAs($user)->get(route('tax-years.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-issues can access issues create route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageIssues->value);

    $response = $this->actingAs($user)->get(route('issues.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

// ─── SUPER-ADMIN: Gate::before bypass ─────────────────────────────────────────

test('super-admin can access invoices create route via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    $response = $this->actingAs($superAdmin)->get(route('invoices.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('super-admin can access clients create route via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    $response = $this->actingAs($superAdmin)->get(route('clients.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('super-admin can access issues create route via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    $response = $this->actingAs($superAdmin)->get(route('issues.create'));
    expect($response->getStatusCode())->not()->toBe(403);
});
