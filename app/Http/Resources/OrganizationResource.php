<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin Organization */
class OrganizationResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'website' => $this->website,
            'archived_at' => $this->archived_at?->toISOString(),
            'client_since_at' => $this->client_since_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'contacts_count' => $this->when(isset($this->contacts_count), $this->contacts_count),
            'leads_count' => $this->when(isset($this->leads_count), $this->leads_count),
            'owner' => $this->owner ? ['id' => $this->owner->id, 'name' => $this->owner->name] : null,
            'contacts' => $this->whenLoaded('contacts', fn (): array => $this->contacts->map(fn (Contact $contact): array => [
                'id' => $contact->uuid,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'owner' => $contact->owner ? ['id' => $contact->owner->id, 'name' => $contact->owner->name] : null,
            ])->all()),
        ];
    }
}
