<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductAPIController extends Controller
{
    protected $productRepository;


    public function __construct( ProductRepositoryInterface $productRepository )
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        return $this-> productRepository->all();
    }

}
