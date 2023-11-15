<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class GetResponseOtherServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:get-response-other-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get response from other service';
    private $connection;
    private $channel;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->connection = $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_LOGIN'),
            env('RABBITMQ_PASSWORD'),
            env('RABBITMQ_VHOST')
        );
        $this->channel = $this->connection->channel();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->channel->exchange_declare(env('RABBITMQ_EXCHANGE'), 'direct', false, false, false);

        $this->channel->queue_declare(env('RABBITMQ_QUEUE'), false, true, false, false);
        $this->channel->queue_bind(env('RABBITMQ_QUEUE'), env('RABBITMQ_EXCHANGE'), env('RABBITMQ_ROUTING_KEY'));
    }
}
