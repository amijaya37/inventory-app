<?php

namespace App\Domain\Inventory\DTOs;

use App\Domain\Inventory\DTOs\Concerns\BuildsTransactionData;

final readonly class GoodsIssueData
{
    use BuildsTransactionData;

    public function __construct(public string $issueDate, public int $sourceLocationId, public ?string $purpose, public ?string $recipient, public array $items) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(issueDate: (string) $data['issue_date'], sourceLocationId: (int) $data['source_location_id'], purpose: $data['purpose'] ?? null, recipient: $data['recipient'] ?? null, items: self::normalizeItems($data));
    }
}
