<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;

class ProductAPIController extends Controller
{
    public function getInventory (){
        $inventories = PurchaseInvoice::with('purchaseInvoiceDetails.rawMaterial')->get();
        return response()->json(['inventories' => $inventories],200);
    }
}
