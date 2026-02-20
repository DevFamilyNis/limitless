<?php

use Illuminate\Support\Facades\Route;

test('password reset routes are not exposed', function () {
    expect(Route::has('password.request'))->toBeFalse();
    expect(Route::has('password.email'))->toBeFalse();
    expect(Route::has('password.reset'))->toBeFalse();
    expect(Route::has('password.update'))->toBeFalse();
});
