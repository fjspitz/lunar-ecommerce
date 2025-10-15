<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])
            ->middleware('ability:customer:list');
        Route::get('/{customer}', [CustomerController::class, 'show'])
            ->middleware('ability:customer:get');
        Route::post('/', [CustomerController::class, 'create'])
            ->middleware('ability:customer:create');
        Route::post('/group', [CustomerGroupController::class, 'create'])
            ->middleware('ability:customer:group:create');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])
            ->middleware('ability:customer:delete');
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'create'])
            ->middleware('ability:product:create');
        Route::patch('/{product}', [ProductController::class, 'update'])
            ->middleware('ability:product:update');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [ProductController::class, 'categories']);
    });

    Route::prefix('brands')->group(function () {
        Route::get('/', [ProductController::class, 'brands']);
    });

    Route::prefix('pickups')->group(function () {
        Route::get('/', [PickupController::class, 'index']);
    });

    Route::prefix('carts')->group(function () {
        Route::post('/', [CartController::class, 'create']);
        Route::get('/{cart}', [CartController::class, 'show']);
        Route::post('/{cart}/clear', [CartController::class, 'clear']);
        Route::post('/{cart}/lines', [CartController::class, 'addLine']);
        Route::post('/{cart}/checkout', [CartController::class, 'checkout']);
        Route::post('/{cart}/confirm', [CartController::class, 'confirm']);
        Route::delete('/{cart}', [CartController::class, 'remove']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/{order}/transaction', [OrderController::class, 'transaction']);
    });

    Route::prefix('search')->group(function () {
        Route::get('/products', [SearchController::class, 'show']);
    });
});
