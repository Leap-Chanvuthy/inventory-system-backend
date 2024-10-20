<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\API\CurrencyAPIController;
use App\Http\Controllers\API\ProductAPIController;
use App\Http\Controllers\API\ProfileSettingAPIController;
use App\Http\Controllers\API\PurchaseInvoiceAPIController;
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

    Route::post('/change-password' , [ProfileSettingAPIController::class , 'changePassword']);
    Route::post('/profile-picture/update' , [ProfileSettingAPIController::class , 'uploadProfilePicture']);
    Route::post('/profile/update' , [ProfileSettingAPIController::class , 'updateProfileInfo']);
});


Route::get('users' , [UserAPIController::class, 'index']);
Route::get('user/{id}' , [UserAPIController::class , 'show']);
Route::post('users' , [UserAPIController::class , 'store']);
Route::patch('user/{id}', [UserAPIController::class , 'update']);
Route::delete('user/{id}', [UserAPIController::class , 'destroy']);
Route::get('users/role-counts' ,  [UserAPIController::class , 'getUserRoleCount']);




// Product
Route::middleware(['auth:api', 'checkIfAdmin'])->group(function () {
    Route::get('inventories', [ProductAPIController::class, 'getInventory']);
});

Route::post('/product', [ProductAPIController::class, 'store']);
Route::put('/product/{id}', [ProductAPIController::class, 'update']);



// currency 
Route::get('/currencies' , [CurrencyAPIController::class , 'index']);
Route::get('/currency/{id}' , [CurrencyAPIController::class, 'show']);
Route::post('/currency' , [CurrencyAPIController::class , 'store']);
Route::patch('/currency/{id}', [CurrencyAPIController::class , 'update']);
Route::delete('/currency/{id}' , [CurrencyAPIController::class , 'destroy']);




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
Route::get('/raw-materials/trashed' , [RawMaterialAPIController::class , 'trashed']);
Route::patch('/raw-materials/recover/{id}' , [RawMaterialAPIController::class , 'recover']);
Route::get('/raw-material/{id}',[RawMaterialAPIController::class, 'show']);
Route::post('/raw-materials' , [RawMaterialAPIController::class, 'store']);
Route::patch('/raw-material/{id}' , [RawMaterialAPIController::class, 'update']);
Route::delete('/raw-material/{id}' , [RawMaterialAPIController::class , 'destroy']);
Route::get('raw-materials/export', [RawMaterialAPIController::class, 'export']);
Route::post('raw-materials/import', [RawMaterialAPIController::class, 'import']);



// Purchase Invoice
Route::get('/purchase-invoices' , [PurchaseInvoiceAPIController::class , 'index']);
Route::get('purchase-invoice/{id}' , [PurchaseInvoiceAPIController::class , 'show']);
Route::post('/purchase-invoice' , [PurchaseInvoiceAPIController::class, 'store']);
Route::patch('/purchase-invoice/{id}' , [PurchaseInvoiceAPIController::class, 'update']);
Route::patch('/purchase-invoice/restore/{id}' , [PurchaseInvoiceAPIController::class, 'restore']);
Route::delete('/purchase-invoice/{id}' , [PurchaseInvoiceAPIController::class, 'destroy']);
