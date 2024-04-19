<?php

use Illuminate\Support\Facades\Route;
use PhpAmqpLib\Connection\AMQPStreamConnection;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/send2', function () {
  
    echo " [*] Waiting for messages.";
    
});





