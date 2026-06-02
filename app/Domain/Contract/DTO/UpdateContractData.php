<?php

declare(strict_types=1);

namespace App\Domain\Contract\DTO;

use Illuminate\Http\UploadedFile;

final class UpdateContractData
{
    public function __construct(
        public readonly int $contractId,
        public readonly int $userId,
        public readonly string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $note,
        public readonly ?UploadedFile $pdfFile,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contractId: (int) $data['contract_id'],
            userId: (int) $data['user_id'],
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
