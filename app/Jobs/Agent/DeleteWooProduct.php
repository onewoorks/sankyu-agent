<?php

namespace App\Jobs\Agent;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Woo\ProductsController;

class DeleteWooProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $product_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product_id){
        $this->product_id = $product_id;
        echo $product_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        $woo = new ProductsController;
        $woo->deleteProduct($this->product_id);
    }
}
