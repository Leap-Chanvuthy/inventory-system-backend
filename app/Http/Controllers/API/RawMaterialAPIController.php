<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class RawMaterialAPIController extends Controller
{

    private function allBuilder() : QueryBuilder {
        return QueryBuilder::for(RawMaterial::class)
           -> allowedIncludes(['suppliers', 'products'])
           -> allowedFilters([
              AllowedFilter::exact('id'),
              AllowedFilter::exact('name'),
              AllowedFilter::exact('quantity'),
              AllowedFilter::exact('unit_price'),
              AllowedFilter::exact('total_value'),
              AllowedFilter::exact('minimum_stock_level'),
              AllowedFilter::exact('unit'),
              AllowedFilter::exact('package_size'),
           ])
           -> allowedSorts('created_at' , 'quantity' , 'package_size' , 'total_value' , 'minimun_stock_level')
           -> defaultSort('-created_at');

    }


    public function index () {
       $raw_materials =  $this -> allBuilder() -> paginate(10);
       
       if (!$raw_materials){
        return response() -> json(['message' => 'No raw materials found'] , 400);
       }

       return response() -> json(['message' => $raw_materials] , 200);
    }

    public function store(Request $request){

    }

    public function update (Request $request , $id){

    }

    public function destroy($id){

    }


}
