<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\IntakeData;
use SoutheastCode\Crm\Leads\Models\Lead;

class SubmitPublicIntake
{
    public function __construct(
        private readonly CreateLeadFromIntake $createLead,
    ) {}

    public function handle(IntakeData $data): Lead
    {
        return $this->createLead->handle($data);
    }
}
