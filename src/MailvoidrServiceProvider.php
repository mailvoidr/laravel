<?php

namespace Mailvoidr\Laravel;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Mailvoidr\Laravel\Client\MailvoidrClient;
use Mailvoidr\Laravel\Transport\MailvoidrTransport;

class MailvoidrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mailvoidr.php', 'mailvoidr');

        $this->app->singleton(MailvoidrClient::class, function ($app) {
            $config = $app['config']['mailvoidr'];

            return new MailvoidrClient(
                apiKey: (string) $config['api_key'],
            );
        });

        $this->app->alias(MailvoidrClient::class, 'mailvoidr');
    }

    public function boot(): void
    {
        config([
            'mail.mailers.mailvoidr' => [
                'transport' => 'mailvoidr',
            ],
        ]);

        Mail::extend('mailvoidr', function () {
            return new MailvoidrTransport($this->app->make(MailvoidrClient::class));
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mailvoidr.php' => config_path('mailvoidr.php'),
            ], 'mailvoidr-config');
        }
    }
}
