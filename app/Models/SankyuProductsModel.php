<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class SankyuProductsModel extends Model
{
    protected $table = 'sankyu_products';
    use HasFactory;

    public static function copyToCheckTable(){
        $query = "INSERT INTO sankyu_products_check SELECT * FROM sankyu_products";
        DB::connection()->insert($query);
    }

    public static function productsTableCompare(){
        $query = "SELECT ref_table, no_tag, stock_name, product_id, berat, mutu, upah, panjang, lebar, diameter, cakuk, size
        FROM (
            SELECT 'new' as ref_table, t1.no_tag, t1.stock_name, t1.product_id, t1.berat, t1.mutu, t1.upah, t1.panjang, t1.lebar, t1.diameter, t1.cakuk, t1.size
            FROM sankyu_products t1
            UNION ALL
            SELECT 'past' as ref_table, t2.no_tag, t2.stock_name, t2.product_id, t2.berat, t2.mutu, t2.upah, t2.panjang, t2.lebar, t2.diameter, t2.cakuk, t2.size
            FROM sankyu_products_check t2
        ) t
        GROUP BY stock_name, product_id, berat, mutu, upah, panjang, lebar, diameter, cakuk, size
        HAVING COUNT(*) = 1
        ORDER BY product_id";
        $result = DB::connection()->select($query);
        return $result;
    }

    public static function updateProduct($payload){
        $query = "UPDATE sankyu_products SET
        status = 'CHANGED'
        WHERE no_tag = '$payload->no_tag' 
        AND product_id = '$payload->product_id'";
        DB::connection()->update($query);
    }
}
