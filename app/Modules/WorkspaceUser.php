<?php

declare(strict_types=1);

namespace App\Modules;

use Illuminate\Contracts\Auth\Authenticatable;

interface WorkspaceUser extends Authenticatable
{
    public function workspaceId(): int;

    public function workspaceUuid(): string;

    public function workspaceName(): string;

    public function workspaceEmail(): string;

    public function workspaceActive(): bool;

    public function workspaceCan(string $ability, mixed $arguments = []): bool;

    /** @param list<string> $roles */
    public function workspaceHasAnyRole(array $roles): bool;
}
