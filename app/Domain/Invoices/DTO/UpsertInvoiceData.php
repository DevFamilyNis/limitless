<?php

declare(strict_types=1);

namespace App\Domain\Invoices\DTO;

final class UpsertInvoiceData
{
    /**
     * @param  array<int, array{projectId:string,clientProjectRateId:string,description:string,quantity:string,unitPrice:string,amount:string}>  $items
     */
    public function __construct(
        public readonly int $userId,
        public readonly ?int $invoiceId,
        public readonly int $clientId,
        public readonly int $statusId,
        public readonly string $issueDate,
        public readonly ?string $dueDate,
        public readonly float $total,
        public readonly ?string $note,
        public readonly array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        $note = trim((string) ($data['note'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            invoiceId: isset($data['invoice_id']) ? (int) $data['invoice_id'] : null,
            clientId: (int) $data['client_id'],
            statusId: (int) $data['status_id'],
            issueDate: (string) $data['issue_date'],
            dueDate: isset($data['due_date']) && $data['due_date'] !== '' ? (string) $data['due_date'] : null,
            total: (float) $data['total'],
            note: $note !== '' ? $note : null,
            items: $data['items'] ?? [],
        );
    }
}
