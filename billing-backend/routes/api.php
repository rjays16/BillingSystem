<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group. Enjoy building your API!
|
*/

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::apiResource('invoices', InvoiceController::class)->middleware('auth:sanctum');

Route::prefix('invoices')->middleware('auth:sanctum')->group(function () {
    Route::get('/status/{status}', [InvoiceController::class, 'byStatus']);
});

Route::apiResource('organizations', OrganizationController::class)->middleware('auth:sanctum');

Route::apiResource('vendors', VendorController::class)->middleware('auth:sanctum');

Route::apiResource('users', UserController::class)->middleware('auth:sanctum');