<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contact;
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
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'organization_id' => $this->organization_id,
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'leads_count' => $this->when(isset($this->leads_count), $this->leads_count),
            'organization' => $this->organization ? [
                'id' => $this->organization->uuid,
                'name' => $this->organization->name,
            ] : null,
            'owner' => $this->owner ? ['id' => $this->owner->id, 'name' => $this->owner->name] : null,
        ];
    }
}
