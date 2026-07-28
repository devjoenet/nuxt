<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin Contact */
class ContactResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        $organization = $this->relationLoaded('organization') ? $this->getRelation('organization') : null;
        $user = $request->user();

        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'organization_id' => $this->organization_id,
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'leads_count' => $this->when(isset($this->leads_count), $this->leads_count),
            'organization' => $organization instanceof Organization && $user instanceof User && $organization->isVisibleTo($user) ? [
                'id' => $organization->uuid,
                'name' => $organization->name,
            ] : null,
            'owner' => $this->owner ? ['id' => $this->owner->id, 'name' => $this->owner->name] : null,
        ];
    }
}
