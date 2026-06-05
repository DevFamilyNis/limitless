<?php

declare(strict_types=1);

namespace App\Domain\LeadCampaigns\Exceptions;

use RuntimeException;

final class LeadCampaignHasLeadsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot delete a campaign that has leads assigned to it.');
    }
}
