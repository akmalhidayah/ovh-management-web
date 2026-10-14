<?php

namespace App\Services\Integrations;

use RuntimeException;

class WomsOrganizationException extends RuntimeException
{
    public function __construct(
        public readonly string $category,
        string $safeMessage
    ) {
        parent::__construct($safeMessage);
    }
}
