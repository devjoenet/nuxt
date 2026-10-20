<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\DevelopmentTestRunStatus;
use App\Models\DevelopmentTestRun;
use App\Models\User;
use App\Modules\ModuleRegistry;
use App\Modules\PermissionDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

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
            'has_pending_development_tests' => DevelopmentTestRun::query()
                ->whereBelongsTo($this->resource, 'tester')
                ->where('status', DevelopmentTestRunStatus::Pending)
                ->exists(),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
