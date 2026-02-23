<?php

use App\Domain\Categories\Actions\UpsertCategoryAction;
use App\Domain\Categories\DTO\UpsertCategoryData;
use App\Models\CategoryType;
use App\Models\User;

test('upsert category action creates and updates user category', function () {
    $user = User::factory()->create();
    $typeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = app(UpsertCategoryAction::class)->execute(
        UpsertCategoryData::fromArray([
            'user_id' => $user->id,
            'category_type_id' => $typeId,
            'name' => 'Cloud',
        ])
    );

    expect($category->user_id)->toBe($user->id);
    expect($category->name)->toBe('Cloud');

    $updated = app(UpsertCategoryAction::class)->execute(
        UpsertCategoryData::fromArray([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'category_type_id' => $typeId,
            'name' => 'Cloud Updated',
        ])
    );

    expect($updated->id)->toBe($category->id);
    expect($updated->name)->toBe('Cloud Updated');
});
