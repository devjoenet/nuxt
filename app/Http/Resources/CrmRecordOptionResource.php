<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrmRecordOptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'label' => match (true) {
                $this->resource instanceof Contact => "{$this->resource->name} — {$this->resource->email}",
                $this->resource instanceof Organization => $this->resource->website === null
                    ? $this->resource->name
                    : "{$this->resource->name} — {$this->resource->website}",
                default => 'Unknown record',
            },
            'value' => $this->resource->uuid,
        ];
    }
}
