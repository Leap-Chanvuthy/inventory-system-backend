<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\API\InventoryAPIController;
use App\Http\Controllers\API\SupplierAPIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthAPIController::class, 'register']);
Route::post('login', [AuthAPIController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthAPIController::class, 'logout']);
    Route::post('refresh', [AuthAPIController::class, 'refresh']);
    Route::post('/change-password' , [AuthAPIController::class , 'changePassword']);
});

// Inventory
Route::middleware(['auth:api', 'checkIfAdmin'])->group(function () {
    Route::get('inventories', [InventoryAPIController::class, 'getInventory']);
});

// Supplier
Route::get('/suppliers' , [SupplierAPIController::class , 'index']);
Route::get('/supplier/{id}' , [SupplierAPIController::class, 'show']);
Route::post('/supplier' , [SupplierAPIController::class , 'store']);
Route::patch('/supplier/{id}', [SupplierAPIController::class , 'update']);
Route::delete('/supplier/{id}' , [SupplierAPIController::class , 'destroy']);
Route::post('/suppliers/import', [SupplierAPIController::class, 'import'])->name('suppliers.import')->middleware(['auth:api' , 'checkIfAdmin']);
Route::get('/suppliers/export', [SupplierAPIController::class, 'export'])->name('suppliers.export');