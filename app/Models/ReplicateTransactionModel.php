<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class ReplicateTransactionModel extends Model
{
    protected $table = 'replicate_transactions';
    use HasFactory;

    public static function getTransactionId($id){
        $query = "SELECT * FROM replicate_transactions WHERE id = $id";
        return collect(DB::connection()->select($query))->first();
    }

}
