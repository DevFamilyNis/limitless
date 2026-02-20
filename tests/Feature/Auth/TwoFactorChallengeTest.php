<?php

use Illuminate\Support\Facades\Route;

test('two factor routes are not exposed', function () {
    expect(Route::has('two-factor.login'))->toBeFalse();
    expect(Route::has('two-factor.login.store'))->toBeFalse();
    expect(Route::has('two-factor.enable'))->toBeFalse();
});
