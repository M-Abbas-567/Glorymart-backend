<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'status' => 'ok',
        'message' => 'Backend is running. Use /api/* endpoints.',
    ]);
});

Route::fallback(function () {
    return response()->json([
        'status' => 'not_found',
        'message' => 'This is backend server. Use /api/* endpoints or open frontend at http://127.0.0.1:5173',
    ], 404);
});
