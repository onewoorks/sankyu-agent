<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\AgentController;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('replicate_product', function(){
    echo '----------------------------------' . PHP_EOL;
    echo '| Execute Product Syncronization |' . PHP_EOL;
    echo '| Prosessing.....                |' . PHP_EOL;
    AgentController::syncFlow();
    echo '| Completed......                |' . PHP_EOL;
    echo '----------------------------------' . PHP_EOL;
});

Artisan::command('synclast {id}', function(){
    $id = $this->argument('id');
    echo '----------------------------------' . PHP_EOL;
    echo '| Manual sync last product       |' . PHP_EOL;
    echo '| Prosessing.....                |' . PHP_EOL;
    AgentController::syncReplicateTransaction($id);
    echo '| Completed......                |' . PHP_EOL;
    echo '----------------------------------' . PHP_EOL;
});