<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Categories\Form as CategoryForm;
use App\Livewire\Categories\Index as CategoryIndex;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// These are transaction/expense/income categories (App\Livewire\Categories),
// NOT Settings\IssueCategories which were guarded in a prior commit.
// Category views use Flux UI. Direct component instantiation avoids view rendering.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── CANNOT: user without manage-categories ──────────────────────────────────

test('user without manage-categories cannot save category through form component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Category::query()->count();

    expect(fn () => (new CategoryForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(Category::query()->count())->toBe($initialCount);
});

test('user without manage-categories cannot delete category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');
    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Test kategorija',
    ]);

    expect(fn () => (new CategoryIndex)->deleteCategory($category->id))
        ->toThrow(AuthorizationException::class);

    expect(Category::find($category->id))->not()->toBeNull();
});

// ─── CAN: user with manage-categories ────────────────────────────────────────

test('user with manage-categories can save category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageCategories->value);
    $this->actingAs($user);

    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $component = new CategoryForm;
    $component->categoryTypeId = (string) $expenseTypeId;
    $component->name = 'Nova kategorija';

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — category is already saved
    }

    expect(Category::query()->where('name', 'Nova kategorija')->exists())->toBeTrue();
});

test('user with manage-categories can delete category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageCategories->value);
    $this->actingAs($user);

    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');
    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Za brisanje',
    ]);

    (new CategoryIndex)->deleteCategory($category->id);

    expect(Category::find($category->id))->toBeNull();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can delete category via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');
    $category = Category::query()->create([
        'user_id' => $superAdmin->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Super Admin kategorija',
    ]);

    (new CategoryIndex)->deleteCategory($category->id);

    expect(Category::find($category->id))->toBeNull();
});
