<?php

namespace Mailvoidr\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Mailvoidr\Laravel\Client\MailvoidrClient;
use Mailvoidr\Laravel\DTO\EmailSend;
use Mailvoidr\Laravel\DTO\SendEmailResponse;

/**
 * @method static SendEmailResponse send(array $payload)
 * @method static EmailSend getSend(int|string $id)
 *
 * @see MailvoidrClient
 */
class Mailvoidr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MailvoidrClient::class;
    }
}
