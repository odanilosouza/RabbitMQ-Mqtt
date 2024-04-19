<?php

namespace App\Events;

use PhpMqtt\Client\Facades\MQTT;
use App\Events\LocationCreatedEvent;

class MqttEvent{

    function __construct(){
        $server   = 'localhost';
        $port     = 1883;
        $clientId = 'test-subscriber';
    
        $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
        $mqtt->connect();
        $mqtt->subscribe('teste/teste', function ($topic, $message, $retained, $matchedWildcards) {
            echo sprintf("Received message on topic [%s]: %s\n", $topic, $message);
            new LocationCreatedEvent($message);
    
        }, 0);
    
    
        $mqtt->loop(true);
        $mqtt->disconnect();
    }
}