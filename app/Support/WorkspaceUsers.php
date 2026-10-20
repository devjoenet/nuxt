<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use LogicException;

/** Host Eloquent adapter; the portable identity contract lives in Modules. */
final class WorkspaceUsers
{
    public static function morphClass(): string
    {
        return (new (self::model()))->getMorphClass();
    }

    /** @return class-string<User> */
    public static function model(): string
    {
        $model = config('auth.providers.users.model');
        if (! is_string($model) || ! is_a($model, User::class, true)) {
            throw new LogicException('The configured workspace user must extend the host User and implement WorkspaceUser.');
        }

        return $model;
    }
}
