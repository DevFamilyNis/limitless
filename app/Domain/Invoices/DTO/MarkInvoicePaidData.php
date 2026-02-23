<?php

declare(strict_types=1);

namespace App\Domain\Invoices\DTO;

final class MarkInvoicePaidData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $invoiceId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (int) $data['invoice_id']);
    }
}
