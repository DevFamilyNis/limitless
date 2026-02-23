<?php

declare(strict_types=1);

namespace App\Domain\ClientProjectRates\DTO;

final class UpsertClientProjectRateData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $rateId,
        public readonly int $clientId,
        public readonly int $projectId,
        public readonly int $billingPeriodId,
        public readonly float $priceAmount,
        public readonly string $currency,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            rateId: isset($data['rate_id']) ? (int) $data['rate_id'] : null,
            clientId: (int) $data['client_id'],
            projectId: (int) $data['project_id'],
            billingPeriodId: (int) $data['billing_period_id'],
            priceAmount: (float) $data['price_amount'],
            currency: strtoupper(trim((string) $data['currency'])),
        );
    }
}
