<?php

namespace Mailvoidr\Laravel\Client;

use Illuminate\Support\Facades\Http;
use Mailvoidr\Laravel\DTO\EmailSend;
use Mailvoidr\Laravel\DTO\SendEmailResponse;
use Mailvoidr\Laravel\Exceptions\MailvoidrException;
use Mailvoidr\Laravel\MailvoidrDefaults;

class MailvoidrClient
{
    public function __construct(
        private readonly string $apiKey,
    ) {}

    /**
     * Queue an email for delivery.
     *
     * @param  array{
     *     from?: string,
     *     to: list<string>,
     *     subject: string,
     *     html?: string|null,
     *     text?: string|null,
     *     cc?: list<string>,
     *     bcc?: list<string>,
     *     reply_to?: string|null,
     *     track_opens?: bool,
     *     track_clicks?: bool
     * }  $payload
     */
    public function send(array $payload): SendEmailResponse
    {
        $payload['from'] ??= MailvoidrDefaults::FROM;

        $response = $this->request()->post(MailvoidrDefaults::BASE_URL.'/mail/send', $payload);

        if ($response->successful()) {
            return SendEmailResponse::fromArray($response->json());
        }

        throw $this->exceptionFromResponse($response->status(), $response->json());
    }

    public function getSend(int|string $id): EmailSend
    {
        $response = $this->request()->get(MailvoidrDefaults::BASE_URL."/mail/sends/{$id}");

        if ($response->successful()) {
            return EmailSend::fromArray($response->json());
        }

        throw $this->exceptionFromResponse($response->status(), $response->json());
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        if ($this->apiKey === '') {
            throw new MailvoidrException('MAILVOIDR_API_KEY is not configured.', 0);
        }

        return Http::timeout(MailvoidrDefaults::TIMEOUT)
            ->acceptJson()
            ->withToken($this->apiKey);
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    private function exceptionFromResponse(int $status, ?array $body): MailvoidrException
    {
        $message = is_array($body) ? (string) ($body['error'] ?? 'Mailvoidr API request failed.') : 'Mailvoidr API request failed.';
        $errors = is_array($body['errors'] ?? null) ? $body['errors'] : [];

        return new MailvoidrException($message, $status, $errors);
    }
}
