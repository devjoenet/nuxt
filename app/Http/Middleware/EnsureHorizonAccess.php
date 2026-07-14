<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHorizonAccess
{
    /** @param Closure(Request): (Response) $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user?->is_active === true && $user->canAny([
                Permission::JobsView->value,
                Permission::JobsManage->value,
            ]),
            403,
        );

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        abort_unless($user->can(Permission::JobsManage->value), 403);

        return $next($request);
    }
}
