<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RequestController extends Controller
{
    private $connection;
    private $channel;

    public function __construct()
    {
        $this->connection = $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_LOGIN'),
            env('RABBITMQ_PASSWORD'),
            env('RABBITMQ_VHOST')
        );
        $this->channel = $this->connection->channel();
    }

    public function requestDataUser() {
        
        $this->channel->queue_declare("service_b_queue", false, false, false, false);
        $correlationId = uniqid();
        
        $msg = new AMQPMessage($correlationId);
        $msg->set('reply_to', "service_a_response_queue");
        // $msg->set('correlation_id', $correlationId);
        $this->channel->basic_publish($msg, '', 'service_b_queue');
        // dd($msg);

        // $this->channel->exchange_declare(env('RABBITMQ_EXCHANGE'), 'direct', false, false, false);
        // $this->channel->queue_declare(env('RABBITMQ_QUEUE'), false, true, false, false);
        // $this->channel->basic_publish($msg, env('RABBITMQ_EXCHANGE'), env('RABBITMQ_ROUTING_KEY'));

        // callback
        // $this->channel->queue_declare("callback-queue", false, false, true, false);
        // $this->channel->queue_bind(env('RABBITMQ_QUEUE'), env('RABBITMQ_EXCHANGE'), env('RABBITMQ_ROUTING_KEY'));

        // $this->channel->basic_consume(env('RABBITMQ_QUEUE'), '', false, false, false, false, function($res) {
        //     return response()->json([
        //         'status' => 'success',
        //         'data' => json_decode($res->body)
        //     ]);
        // });
        $response = $this->waitForResponse($this->channel, $correlationId);

        $this->channel->close();
        $this->connection->close();

        return response()->json(['message' => 'Response from Service B', 'data' => $response]);
    }

    public function waitForResponse($channel, $correlationId) {
        $channel->queue_declare("service_a_response_queue", false, false, false, false);
        $response = null;

        $callback = function ($msg) use ($correlationId, &$response) {
            if ($msg->get('correlation_id') == $correlationId) {
                $response = json_decode($msg->body);
            }
        };

        $channel->basic_consume('service_a_response_queue', '', false, true, false, false, $callback);

        while (empty($response)) {
            $channel->wait();
        }

        return $response;
    }
}
