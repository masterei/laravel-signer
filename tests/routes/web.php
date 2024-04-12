<?php

use Illuminate\Support\Facades\Route;

Route::get('subscribe/{user}', function () {
    return response()->status();
})->name('subscribe')->middleware('auth,signer');

Route::get('test', function () {
    return response()
        ->setStatusCode(200)
        ->json(['message' => 'success']);

})->name('test.absolute')->middleware('signer');
