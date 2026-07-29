<?php

declare(strict_types=1);

namespace App\Enums;

enum DevelopmentNoteAudience: string
{
    case Participants = 'participants';
    case DevelopmentTeam = 'development_team';
}
