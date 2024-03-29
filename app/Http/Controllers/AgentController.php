<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductModel;
use App\Models\ReplicateTransactionModel;
use App\Models\SankyuProductsModel;
use App\Models\SankyuProductsCheckModel;
use App\Jobs\Agent\DeleteWooProduct;
use App\Jobs\Agent\AddProduct;
use App\Jobs\Agent\CompareSyncProduct;

class AgentController extends Controller
{

    public static function syncFlow(){
        $replicate = self::replicateSankyuData();
        self::deleteWooProducts($replicate->to_be_deleted);
        self::addNewProducts($replicate->to_be_added);
    }

    public static function getReplication($id){
        $replicate = ReplicateTransactionModel::getTransactionId($id);
        return json_encode($replicate);
    }

    public static function syncReplicateTransaction($id){
        $replicate = ReplicateTransactionModel::getTransactionId($id);
        if(!empty($replicate)){
            if(!empty($replicate->online_to_delete)){
                $to_delete = json_decode($replicate->online_to_delete);
                self::deleteWooProducts($to_delete);
            }
            if(!empty($replicate->online_to_add)){
                $to_add = json_decode($replicate->online_to_add);
                self::addNewProducts($to_add);
            }
        } else {
            echo '| No data to add                |' . PHP_EOL;
            
        }

        
        
    }

    private static function productUpdateCheck(){

    }

    private static function deleteWooProducts($product_ids){
        foreach($product_ids as $id){
            dispatch(new DeleteWooProduct($id));
        }
    }

    private static function addNewProducts($product_ids){
        foreach($product_ids as $id){
            dispatch(new AddProduct($id));
        }
    }

    private static function replicateSankyuData(){
        $current_products   = ProductModel::getCurrentProducts();
        $sankyu_products    = ProductModel::getAllStockActive();
        $product_ids        = self::product_ids($current_products);
        $compare            = self::compareProducts($current_products, $sankyu_products);
        $data = [
            'total_online'      => count($current_products),
            'total_offline'     => count($sankyu_products),
            'online_to_delete'  => json_encode($compare->online_to_delete),
            'online_to_add'     => json_encode($compare->online_to_add), 
            'total_last_online' => count(ProductModel::getUploadToWoo())
        ];
        
        if(count($compare->online_to_delete) > 0 || count($compare->online_to_add) > 0){
            ReplicateTransactionModel::insert($data);
        } 
        SankyuProductsCheckModel::truncate();
        SankyuProductsModel::copyToCheckTable();
        SankyuProductsModel::truncate();
        $stock_chunked = array_chunk($sankyu_products,100);
        foreach($stock_chunked as $stocks){
            self::createNewSankyuProduct($stocks);
        }
        self::checkProductDataChanges();

        return (object) [
            'to_be_deleted' => $compare->online_to_delete,
            'to_be_added' => $compare->online_to_add
        ];
    }

    private static function refCakuk($id){
        $name = '';
        switch($id){
            case 1: $name = 'CANGKUK S'; break;
            case 2: $name = 'CANGKUK KOTAK'; break;
            case 3: $name = 'CANGKUK PANDORA'; break;
            case 4: $name = 'CANGKUK J'; break;
            case 5: $name = 'CANGKUK LOBSTER'; break;
            case 6: $name = 'CANGKUK W'; break;
            case 7: $name = 'CANGKUK T'; break;
            default: $name = null; break;
        }
        return $name;

    }

    private static function cleanStokName($stock_name){
        $stock_name = str_replace(array("\r", "\n"), ' ', $stock_name);
        $stock_name = preg_replace('/\s+/', ' ', $stock_name);
        return trim($stock_name);
    }

    private static function createNewSankyuProduct($stocks){
        $data = [];
        foreach($stocks as $stock){
            $data[] = [
                'product_id'    => $stock->product_id,
                'stock_name'    => self::cleanStokName($stock->stock_name),
                'no_tag'        => $stock->no_tag,
                'berat'         => $stock->berat,
                'mutu'          => $stock->mutu,
                'upah'          => $stock->upah,
                'dulang'        => $stock->dulang,
                'panjang'       => (float) $stock->panjang,
                'lebar'         => (float) $stock->lebar,
                'diameter'      => (float) $stock->diameter,
                'size'          => (float) $stock->size,
                'cakuk'         => self::refCakuk($stock->cakuk)
            ];
        }
        SankyuProductsModel::insert($data);
    }

    private static function checkProductDataChanges(){
        $products = SankyuProductsModel::productsTableCompare();
        $to_update = 0;
        foreach($products as $product){
            if(strtolower($product->ref_table == 'new')){
                SankyuProductsModel::updateProduct($product);
                $to_update++;
            }
        }
        if($to_update > 0){
            dispatch(new CompareSyncProduct());
        }
    }

    private static function product_ids($payloads){
        $product_ids = []; 
        foreach($payloads as $payload){
            $product_ids[] = $payload->product_id;
        }
        return $product_ids;
    }


    private static function compareProducts($web, $offline){
        $online_product_ids     = self::product_ids($web);
        $sankyu_product_ids     = self::product_ids($offline);
        $max_online_product_id  = (!empty($online_product_ids)) ? $online_product_ids[count($online_product_ids)-1] : 0;
        $product_diff_online    = array_diff($online_product_ids, $sankyu_product_ids);
        $product_diff_offline   = array_diff($sankyu_product_ids, $online_product_ids);
        $online_to_delete       = [];
        $online_to_add          = [];
        foreach($product_diff_online as $diff){
            if($diff <= $max_online_product_id){
                $online_to_delete[] = $diff;
            }
        }
        foreach($product_diff_offline as $diff){
            if($diff > $max_online_product_id){
                $online_to_add[] = $diff;
            }
        }

        return (object) [
            'online_to_delete' => $online_to_delete,
            'online_to_add' => $online_to_add
        ];
    }
}
