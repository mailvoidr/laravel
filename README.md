# Mailvoidr Laravel SDK

Official Laravel client for the [Mailvoidr](https://mailvoidr.com) transactional email API. Supports Laravel 10–13.

Use this when SMTP port `587` is unavailable — the HTTP API delivers the same way.

## Install

```bash
composer require mailvoidr/laravel
```

## Configure

Only your API key is required. Base URL, default sender (`hello@mailvoidr.com`), and timeout are built in.

```env
MAIL_MAILER=mailvoidr
MAILVOIDR_API_KEY=mvdr_live_your_key_here
```

Add a mailer in `config/mail.php`:

```php
'mailvoidr' => [
    'transport' => 'mailvoidr',
],
```

That's it — `Mail::`, Mailables, and Notifications all route through Mailvoidr.

## Laravel Mail (recommended)

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

Mail::to('riya@example.com')->send(new WelcomeMail());
```

```php
// Notification
$user->notify(new OrderShipped($order));
```

Default sender is `hello@mailvoidr.com`. Override per mailable:

```php
return $this->from('hello@mail.yourdomain.com', 'Acme')
    ->subject('Welcome')
    ->view('emails.welcome');
```

## Direct API client

```php
use Mailvoidr\Laravel\Facades\Mailvoidr;

$response = Mailvoidr::send([
    'to' => ['riya@example.com'],
    'subject' => 'Welcome to Acme',
    'html' => '<h1>Hey Riya</h1>',
]);

$send = Mailvoidr::getSend($response->id);
```

Inject the client:

```php
use Mailvoidr\Laravel\Client\MailvoidrClient;

public function __construct(private MailvoidrClient $mailvoidr) {}
```

## Errors

`MailvoidrException` is thrown on `402` (plan limit), `403` (live sending off), `422` (validation), and other non-success responses. The mail transport wraps these as `TransportException`.
