<?php

use Illuminate\Support\Facades\Route;

test('password confirmation routes are not exposed', function () {
    expect(Route::has('password.confirm'))->toBeFalse();
});
