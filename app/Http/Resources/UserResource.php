<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;
use SoutheastCode\Modules\ModuleRegistry;
use SoutheastCode\Modules\PermissionDefinition;
use SoutheastCode\Modules\WorkspaceComposition;

/** @mixin User */
class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'roles' => $this->getRoleNames()->values()->all(),
            'can' => collect(app(ModuleRegistry::class)->permissions())->mapWithKeys(fn (PermissionDefinition $permission): array => [
                $permission->key => $this->can($permission->key),
            ])->all(),
            ...app(WorkspaceComposition::class)->userContext($this->resource),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
