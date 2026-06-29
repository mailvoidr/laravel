<?php

namespace Mailvoidr\Laravel\DTO;

final readonly class SendEmailResponse
{
    /**
     * @param  array{used: int, limit: ?int, remaining: ?int}|null  $emailUsage
     */
    public function __construct(
        public bool $success,
        public int $id,
        public string $messageId,
        public string $status,
        public ?array $emailUsage = null,
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
            emailUsage: isset($data['email_usage']) ? (array) $data['email_usage'] : null,
        );
    }
}
