<?php

use Illuminate\Support\Facades\Route;

test('registration routes are not exposed', function () {
    expect(Route::has('register'))->toBeFalse();
    expect(Route::has('register.store'))->toBeFalse();
});
