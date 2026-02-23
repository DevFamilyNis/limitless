<?php

declare(strict_types=1);

namespace App\Domain\Clients\DTO;

final class UpsertClientData
{
    /**
     * @param  array<int, array{id:int|null,full_name:string,email:string,phone:string,position:string,is_primary:bool,note:string}>  $contacts
     */
    public function __construct(
        public readonly int $userId,
        public readonly ?int $clientId,
        public readonly int $clientTypeId,
        public readonly string $displayName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $note,
        public readonly ?string $pib,
        public readonly ?string $mb,
        public readonly ?string $bankAccount,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly array $contacts,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            clientId: isset($data['client_id']) ? (int) $data['client_id'] : null,
            clientTypeId: (int) $data['client_type_id'],
            displayName: trim((string) $data['display_name']),
            email: self::nullableString($data['email'] ?? null),
            phone: self::nullableString($data['phone'] ?? null),
            address: self::nullableString($data['address'] ?? null),
            note: self::nullableString($data['note'] ?? null),
            pib: self::nullableString($data['pib'] ?? null),
            mb: self::nullableString($data['mb'] ?? null),
            bankAccount: self::nullableString($data['bank_account'] ?? null),
            firstName: self::nullableString($data['first_name'] ?? null),
            lastName: self::nullableString($data['last_name'] ?? null),
            contacts: $data['contacts'] ?? [],
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $result = trim((string) $value);

        return $result !== '' ? $result : null;
    }
}
