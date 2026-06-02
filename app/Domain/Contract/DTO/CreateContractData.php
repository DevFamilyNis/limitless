<?php

declare(strict_types=1);

namespace App\Domain\Contract\DTO;

use App\Domain\Contract\Enums\ContractType;
use Illuminate\Http\UploadedFile;

final class CreateContractData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $clientId,
        public readonly ?int $parentId,
        public readonly ContractType $type,
        public readonly string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $note,
        public readonly ?UploadedFile $pdfFile,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            clientId: (int) $data['client_id'],
            parentId: isset($data['parent_id']) && $data['parent_id'] !== '' ? (int) $data['parent_id'] : null,
            type: ContractType::from((string) $data['type']),
            startDate: (string) $data['start_date'],
            endDate: isset($data['end_date']) && $data['end_date'] !== '' ? (string) $data['end_date'] : null,
            note: self::nullableString($data['note'] ?? null),
            pdfFile: $data['pdf_file'] ?? null,
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $result = trim((string) $value);

        return $result !== '' ? $result : null;
    }
}
