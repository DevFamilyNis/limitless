<?php

use App\Domain\TaxYears\Actions\EnsureCurrentTaxYearAction;
use App\Domain\TaxYears\Actions\UpsertTaxYearAction;
use App\Domain\TaxYears\DTO\EnsureCurrentTaxYearData;
use App\Domain\TaxYears\DTO\UpsertTaxYearData;
use App\Models\User;

test('ensure current tax year action creates default thresholds only once', function () {
    $user = User::factory()->create();

    $first = app(EnsureCurrentTaxYearAction::class)->execute(
        EnsureCurrentTaxYearData::fromArray(['user_id' => $user->id])
    );

    $second = app(EnsureCurrentTaxYearAction::class)->execute(
        EnsureCurrentTaxYearData::fromArray(['user_id' => $user->id])
    );

    expect($first->id)->toBe($second->id);
    expect((float) $first->first_threshold_amount)->toBe(6000000.0);
    expect((float) $first->second_threshold_amount)->toBe(8000000.0);
});

test('upsert tax year action updates thresholds', function () {
    $user = User::factory()->create();

    $taxYear = app(UpsertTaxYearAction::class)->execute(
        UpsertTaxYearData::fromArray([
            'user_id' => $user->id,
            'year' => 2028,
            'first_threshold_amount' => 7000000,
            'second_threshold_amount' => 10000000,
        ])
    );

    $updated = app(UpsertTaxYearAction::class)->execute(
        UpsertTaxYearData::fromArray([
            'user_id' => $user->id,
            'tax_year_id' => $taxYear->id,
            'year' => 2028,
            'first_threshold_amount' => 7500000,
            'second_threshold_amount' => 11000000,
        ])
    );

    expect((float) $updated->first_threshold_amount)->toBe(7500000.0);
    expect((float) $updated->second_threshold_amount)->toBe(11000000.0);
});
