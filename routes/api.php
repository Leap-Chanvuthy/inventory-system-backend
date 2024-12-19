<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\API\CurrencyAPIController;
use App\Http\Controllers\API\CustomerAPIController;
use App\Http\Controllers\API\CustomerCategoryAPIController;
use App\Http\Controllers\API\ProductAPIController;
use App\Http\Controllers\API\ProductCategoryAPIController;
use App\Http\Controllers\API\ProfileSettingAPIController;
use App\Http\Controllers\API\PurchaseInvoiceAPIController;
use App\Http\Controllers\API\RawMaterialAPIController;
use App\Http\Controllers\API\RawMaterialCetegoryAPIController;
use App\Http\Controllers\API\SaleOrderAPIController;
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
Route::post('password/send-otp', [AuthAPIController::class, 'sendOtp']);
Route::post('password/reset', [AuthAPIController::class, 'reset']);
Route::get('/password/reset/{token}', function ($token) {
    return response()->json(['token' => $token]);
})->name('password.reset');

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthAPIController::class, 'logout']);
    Route::post('refresh', [AuthAPIController::class, 'refresh']);

    Route::post('/change-password' , [ProfileSettingAPIController::class , 'changePassword']);
    Route::post('/profile-picture/update' , [ProfileSettingAPIController::class , 'uploadProfilePicture']);
    Route::post('/profile/update' , [ProfileSettingAPIController::class , 'updateProfileInfo']);
});


// Users
Route::middleware(['auth:api' , 'Admin'  ])->group(function () {
    Route::get('users' , [UserAPIController::class, 'index']);
    Route::get('user/{id}' , [UserAPIController::class , 'show']);
    Route::post('users' , [UserAPIController::class , 'store']);
    Route::patch('user/{id}', [UserAPIController::class , 'update']);
    Route::delete('user/{id}', [UserAPIController::class , 'destroy']);
    Route::get('users/role-counts' ,  [UserAPIController::class , 'getUserRoleCount']);
});




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
Route::get('/suppliers/stats/top-suppliers', [SupplierAPIController::class, 'topSuppliers']);
Route::get('/suppliers/stats/grouped-by-date', [SupplierAPIController::class, 'suppliersGroupedByDay']);
Route::post('/suppliers/import', [SupplierAPIController::class, 'import'])->name('suppliers.import');
Route::get('/suppliers/export', [SupplierAPIController::class, 'export'])->name('suppliers.export');




// raw materials
Route::get('/raw-materials' , [RawMaterialAPIController::class , 'index']);
Route::get('/raw-materials/trashed' , [RawMaterialAPIController::class , 'trashed']);
Route::patch('/raw-materials/recover/{id}' , [RawMaterialAPIController::class , 'recover']);
Route::get('/raw-material/{id}',[RawMaterialAPIController::class, 'show']);
Route::get('/raw-materials/no-invoice' , [RawMaterialAPIController::class , 'getRawMaterialsWithoutInvoice']);
Route::get('/raw-materials/no-supplier' , [RawMaterialAPIController::class , 'getRawMaterialsWithoutSupplier']);
Route::post('/raw-materials' , [RawMaterialAPIController::class, 'store']);
Route::patch('/raw-material/{id}' , [RawMaterialAPIController::class, 'update']);
Route::delete('/raw-material/{id}' , [RawMaterialAPIController::class , 'destroy']);
Route::get('raw-materials/export', [RawMaterialAPIController::class, 'export']);
Route::post('raw-materials/import', [RawMaterialAPIController::class, 'import']);

// Raw Material Category
Route::get('raw-material-categories', [RawMaterialCetegoryAPIController::class , 'index']);
Route::get('raw-material-category/{id}', [RawMaterialCetegoryAPIController::class , 'show']);
Route::get('non-paginate/raw-material-categories', [RawMaterialCetegoryAPIController::class , 'getWithoutPagination']);
Route::post('raw-material-category/create', [RawMaterialCetegoryAPIController::class , 'store']);
Route::patch('raw-material-category/update/{id}', [RawMaterialCetegoryAPIController::class , 'update']);
Route::delete('raw-material-category/delete/{id}', [RawMaterialCetegoryAPIController::class , 'delete']);


// Product Category
Route::get('product-categories', [ProductCategoryAPIController::class , 'index']);
Route::get('product-category/{id}', [ProductCategoryAPIController::class , 'show']);
Route::get('product-categories/all', [ProductCategoryAPIController::class , 'getWithoutPagination']);
Route::post('product-category/create', [ProductCategoryAPIController::class , 'store']);
Route::patch('product-category/update/{id}', [ProductCategoryAPIController::class , 'update']);
Route::delete('product-category/delete/{id}', [ProductCategoryAPIController::class , 'delete']);



// Purchase Invoice
Route::get('/purchase-invoices' , [PurchaseInvoiceAPIController::class , 'index']);
Route::get('/purchase-invoices/trashed' , [PurchaseInvoiceAPIController::class , 'trashed']);
Route::get('purchase-invoice/{id}' , [PurchaseInvoiceAPIController::class , 'show']);
Route::post('/purchase-invoice' , [PurchaseInvoiceAPIController::class, 'store']);
Route::patch('/purchase-invoice/{id}' , [PurchaseInvoiceAPIController::class, 'update']);
Route::patch('/purchase-invoice/recover/{id}' , [PurchaseInvoiceAPIController::class, 'restore']);
Route::delete('/purchase-invoice/{id}' , [PurchaseInvoiceAPIController::class, 'destroy']);
Route::get('/purchase-invoices/export' , [PurchaseInvoiceAPIController::class, 'export']);



// Product
Route::get('/products' , [ProductAPIController::class , 'index']);
Route::get('/products/trashed' , [ProductAPIController::class , 'trashed']);
Route::post('/product' , [ProductAPIController::class , 'store']);
Route::get('/product/{id}' , [ProductAPIController::class , 'show'] );
Route::patch('/product/{id}' , [ProductAPIController::class , 'update']);
Route::delete('/product/{id}' , [ProductAPIController::class , 'destroy']);
Route::patch('/product/recover/{id}' , [ProductAPIController::class , 'recover']);
Route::get('/products/export', [ProductAPIController::class, 'export']);




// Customer Category
Route::get('customer-categories', [CustomerCategoryAPIController::class , 'index']);
Route::get('customer-category/{id}', [CustomerCategoryAPIController::class , 'show']);
Route::get('customer-categories/all', [CustomerCategoryAPIController::class , 'getWithoutPagination']);
Route::post('customer-category/create', [CustomerCategoryAPIController::class , 'store']);
Route::patch('customer-category/update/{id}', [CustomerCategoryAPIController::class , 'update']);
Route::delete('customer-category/delete/{id}', [CustomerCategoryAPIController::class , 'delete']);


// Customer
Route::get('/customers', [CustomerAPIController::class , 'index']);
Route::get('/customers/trashed', [CustomerAPIController::class , 'trashed']);
Route::get('/customer/{id}' , [CustomerAPIController::class, 'show']);
Route::post('/customer' , [CustomerAPIController::class , 'store']);
Route::patch('/customer/{id}' , [CustomerAPIController::class , 'update']);
Route::patch('/customer/recover/{id}' , [CustomerAPIController::class , 'recover']);
Route::delete('/customer/{id}' , [CustomerAPIController::class , 'destroy']);
Route::get('/customers/export' , [CustomerAPIController::class , 'export']);


// Sale Order
Route::get('/sale-orders' , [SaleOrderAPIController::class , 'index'] );
Route::post('/sale-order' , [SaleOrderAPIController::class , 'store']);
Route::patch('/sale-order/{id}' , [SaleOrderAPIController::class , 'update']);
