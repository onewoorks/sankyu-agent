<?php

namespace App\Http\Controllers\Woo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductsController extends Controller{

    public function deleteProduct($product_id){}

    public function addNewProduct($product_id){
        return $product_id;
    }
}
