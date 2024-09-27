<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\API\ProductAPIController;
use App\Http\Controllers\API\RawMaterialAPIController;
use App\Http\Controllers\API\SupplierAPIController;
use App\Http\Controllers\API\UserAPIController;

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


Route::get('users' , [UserAPIController::class, 'index']);
Route::get('users/{id}' , [UserAPIController::class , 'show']);
Route::post('users' , [UserAPIController::class , 'store']);
Route::patch('user/{id}', [UserAPIController::class , 'update']);
Route::delete('user/{id}', [UserAPIController::class , 'destroy']);
Route::get('users/role-counts' ,  [UserAPIController::class , 'getUserRoleCount']);




// Inventory
Route::middleware(['auth:api', 'checkIfAdmin'])->group(function () {
    Route::get('inventories', [ProductAPIController::class, 'getInventory']);
});

Route::get('inventory', [ProductAPIController::class, 'getInventory']);

// Supplier
Route::get('/suppliers' , [SupplierAPIController::class , 'index']);
Route::get('/supplier/{id}' , [SupplierAPIController::class, 'show']);
Route::post('/supplier' , [SupplierAPIController::class , 'store']);
Route::patch('/supplier/{id}', [SupplierAPIController::class , 'update']);
Route::delete('/supplier/{id}' , [SupplierAPIController::class , 'destroy']);
Route::get('/suppliers/stats', [SupplierAPIController::class, 'getSupplierStats']);
Route::post('/suppliers/import', [SupplierAPIController::class, 'import'])->name('suppliers.import');
Route::get('/suppliers/export', [SupplierAPIController::class, 'export'])->name('suppliers.export');




// raw materials
Route::get('/raw-materials' , [RawMaterialAPIController::class , 'index']);
Route::get('/raw-material/{id}',[RawMaterialAPIController::class, 'show']);
Route::post('/raw-materials' , [RawMaterialAPIController::class, 'store']);
Route::patch('/raw-materials/{id}' , [RawMaterialAPIController::class, 'update']);
Route::delete('/raw-materials/{id}' , [RawMaterialAPIController::class , 'destroy']);
Route::get('raw-materials/export', [RawMaterialAPIController::class, 'export']);
Route::post('raw-materials/import', [RawMaterialAPIController::class, 'import']);