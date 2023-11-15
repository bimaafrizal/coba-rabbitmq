<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('rabbitmq', function ($app) {
            $config = config('rabbitmq');

            return new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['login'],
                $config['password'],
                $config['vhost']
            );
        });
    }
}
