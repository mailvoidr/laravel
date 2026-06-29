<?php

namespace Mailvoidr\Laravel\Transport;

use Mailvoidr\Laravel\Client\MailvoidrClient;
use Mailvoidr\Laravel\Exceptions\MailvoidrException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

class MailvoidrTransport extends AbstractTransport
{
    public function __construct(
        private readonly MailvoidrClient $client,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $envelope = $message->getEnvelope();

        $to = $this->emailAddresses($this->getRecipients($email, $envelope));

        if ($to === []) {
            throw new TransportException('Mailvoidr transport requires at least one "to" recipient.');
        }

        $payload = [
            'to' => $to,
            'subject' => (string) $email->getSubject(),
        ];

        $from = $this->resolveFromAddress($envelope);

        if ($from !== null) {
            $payload['from'] = $from;
        }

        $cc = $this->emailAddresses($email->getCc());

        if ($cc !== []) {
            $payload['cc'] = $cc;
        }

        $bcc = $this->emailAddresses($email->getBcc());

        if ($bcc !== []) {
            $payload['bcc'] = $bcc;
        }

        $replyTo = $this->firstEmailAddress($email->getReplyTo());

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        if (($html = $email->getHtmlBody()) !== null) {
            $payload['html'] = $html;
        }

        if (($text = $email->getTextBody()) !== null) {
            $payload['text'] = $text;
        }

        if (! isset($payload['html']) && ! isset($payload['text'])) {
            $payload['text'] = $email->getSubject() ?: ' ';
        }

        if ($email->getHtmlBody() !== null) {
            $payload['track_opens'] = true;
            $payload['track_clicks'] = true;
        }

        try {
            $result = $this->client->send($payload);
        } catch (MailvoidrException $exception) {
            throw new TransportException($exception->getMessage(), $exception->statusCode, $exception);
        }

        $email->getHeaders()->addHeader('X-Mailvoidr-Send-ID', (string) $result->id);
    }

    /**
     * @param  Address[]  $addresses
     * @return list<string>
     */
    private function emailAddresses(array $addresses): array
    {
        return array_values(array_filter(array_map(
            fn (Address $address) => $address->getAddress(),
            $addresses,
        )));
    }

    /**
     * @param  Address[]  $addresses
     */
    private function firstEmailAddress(array $addresses): ?string
    {
        return $addresses === [] ? null : $addresses[0]->getAddress();
    }

    private function resolveFromAddress(Envelope $envelope): ?string
    {
        $sender = $envelope->getSender()->getAddress();
        $appFrom = (string) config('mail.from.address', '');

        if ($sender === '' || $sender === $appFrom) {
            return null;
        }

        return $sender;
    }

    /**
     * @return Address[]
     */
    private function getRecipients(Email $email, Envelope $envelope): array
    {
        return array_filter(
            $envelope->getRecipients(),
            fn (Address $address) => ! in_array($address, array_merge($email->getCc(), $email->getBcc()), true),
        );
    }

    public function __toString(): string
    {
        return 'mailvoidr';
    }
}
