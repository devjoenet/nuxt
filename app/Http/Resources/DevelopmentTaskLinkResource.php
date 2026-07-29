<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DevelopmentTaskLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin DevelopmentTaskLink */
class DevelopmentTaskLinkResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        $addedBy = $this->relationLoaded('addedBy') ? $this->getRelation('addedBy') : null;

        return [
            'id' => $this->uuid,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'label' => $this->label,
            'url' => $this->url,
            'added_by' => $addedBy instanceof User ? $addedBy->name : 'Former user',
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
