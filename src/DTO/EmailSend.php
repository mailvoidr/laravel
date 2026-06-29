<?php

namespace Mailvoidr\Laravel\DTO;

final readonly class EmailSend
{
    /**
     * @param  list<string>  $to
     */
    public function __construct(
        public bool $success,
        public int $id,
        public string $messageId,
        public string $status,
        public ?string $from,
        public array $to,
        public ?string $subject,
        public ?string $error,
        public ?string $queuedAt,
        public ?string $sentAt,
        public ?string $bouncedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) ($data['success'] ?? true),
            id: (int) $data['id'],
            messageId: (string) $data['message_id'],
            status: (string) $data['status'],
            from: isset($data['from']) ? (string) $data['from'] : null,
            to: array_values(array_map('strval', (array) ($data['to'] ?? []))),
            subject: isset($data['subject']) ? (string) $data['subject'] : null,
            error: isset($data['error']) ? (string) $data['error'] : null,
            queuedAt: isset($data['queued_at']) ? (string) $data['queued_at'] : null,
            sentAt: isset($data['sent_at']) ? (string) $data['sent_at'] : null,
            bouncedAt: isset($data['bounced_at']) ? (string) $data['bounced_at'] : null,
        );
    }
}
