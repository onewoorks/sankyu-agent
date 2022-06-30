<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductModel extends Model
{
    use HasFactory;

    public static function getCurrentProducts(){
        $query = "SELECT * FROM sankyu_products";
        $result = DB::connection()->select($query);
        return $result;
    }

    public static function getAllStockActive(){
        $query = "SELECT
        id as product_id,
        remarks as stock_name,
        no_siri_Produk as no_tag,
        coalesce(Berat,0) as berat,
        kod_Purity as mutu,
        coalesce(Upah_Jualan,0) as upah,
        Dulang as dulang,
        dimension_Panjang as panjang,
        dimension_Lebar as lebar,
        dimension_Dia as diameter,
        dimension_Saiz as size,
        StatusItem
        FROM data_database 
        WHERE 
        StatusItem = 10 AND 
        remarks IS NOT NULL
        AND remarks NOT LIKE 'TEMPAHAN%'";
        return DB::connection('sankyu')->select($query);
    }
}
