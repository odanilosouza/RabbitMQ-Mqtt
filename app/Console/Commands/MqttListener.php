<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Events\LocationCreatedEvent;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MqttListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mqtt-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */


    public function handle()
{
    echo " [*] Waiting for messages. To exit press CTRL+C\n";
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    $channel->queue_declare('hello', false, false, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";
    $callback = function ($msg) {
        echo sprintf(" [x] Received %s", $msg->body);
    };
      
    $channel->basic_consume('hello', '', false, true, false, false, $callback);
      
    }
}
