<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/vendors', [VendorController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);

    Route::get('/vendor/dashboard', [VendorController::class, 'dashboard']);
    Route::get('/vendor/products', [VendorController::class, 'myProducts']);
    Route::get('/vendor/orders', [VendorController::class, 'myOrders']);

    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/categories', [AdminController::class, 'categories']);
    Route::post('/admin/categories', [AdminController::class, 'storeCategory']);
    Route::put('/admin/categories/{id}', [AdminController::class, 'updateCategory']);
    Route::delete('/admin/categories/{id}', [AdminController::class, 'destroyCategory']);
    Route::post('/admin/vendors/{id}/approve', [AdminController::class, 'approve']);
    Route::post('/admin/vendors/{id}/reject', [AdminController::class, 'reject']);
});
