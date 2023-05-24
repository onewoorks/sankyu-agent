<?php

namespace App\Http\Controllers\Woo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class ProductsController extends Controller{

    public function deleteProduct($product_id){
        Log::info("Submit delete job to queue!");
    }

    public function addNewProduct($product_id){
        Log::info("Submit add job to queue!");
        return $product_id;
    }

    
}
