<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\DevelopmentNoteAudience;
use App\Models\DevelopmentTaskNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin DevelopmentTaskNote */
class DevelopmentTaskNoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        $author = $this->relationLoaded('author') ? $this->getRelation('author') : null;

        return [
            'id' => $this->uuid,
            'body' => $this->body,
            'audience' => $this->audience->value,
            'audience_label' => $this->audience === DevelopmentNoteAudience::Participants
                ? 'Participants'
                : 'Development team',
            'author' => $author instanceof User ? $author->name : 'Former user',
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
