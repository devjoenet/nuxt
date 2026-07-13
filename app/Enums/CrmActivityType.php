<?php

declare(strict_types=1);

namespace App\Enums;

enum CrmActivityType: string
{
    case PublicIntakeCreated = 'public_intake_created';
    case LeadCreated = 'lead_created';
    case LeadUpdated = 'lead_updated';
    case NoteAdded = 'note_added';
    case TaskCompleted = 'task_completed';
    case LeadConverted = 'lead_converted';
}
