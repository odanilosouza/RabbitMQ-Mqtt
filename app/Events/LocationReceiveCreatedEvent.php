<?php

namespace App\Events;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class LocationReceiveCreatedEvent{

    function __construct(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
    
        $channel->queue_declare('hello', false, false, false, false);
    
        $callback = function ($msg) {
            echo sprintf(" [x] Received %s", $msg->body);
        };
          
        $channel->basic_consume('hello', '', false, true, false, false, $callback);
          
    }
}