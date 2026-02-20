<?php

use Illuminate\Support\Facades\Route;

test('email verification routes are not exposed', function () {
    expect(Route::has('verification.notice'))->toBeFalse();
    expect(Route::has('verification.verify'))->toBeFalse();
    expect(Route::has('verification.send'))->toBeFalse();
});
