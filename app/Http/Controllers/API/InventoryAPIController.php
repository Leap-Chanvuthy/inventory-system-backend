<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InventoryAPIController extends Controller
{
    public function getInventory (){
        $inventories = Supplier::with('inventories')->get();
        return response()->json(['inventories' => $inventories],200);
    }
}
