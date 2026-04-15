<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\PaymentCallbackController;

// Endpoint Publik untuk Pembeli


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/products', [CatalogController::class, 'index']);
Route::get('/products/{slug}', [CatalogController::class, 'show']);
Route::post('/midtrans-callback', [PaymentCallbackController::class, 'receive']);

Route::name('api.')->group(function () {
    Route::apiResource('cart', CartController::class);
});
