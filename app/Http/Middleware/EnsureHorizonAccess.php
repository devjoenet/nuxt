<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Models\User;
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
            $user instanceof User && $user->is_active && $user->canAny([
                Permission::JobsView->value,
                Permission::JobsManage->value,
            ]),
            403,
        );

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        abort_unless($user->can(Permission::JobsManage->value), 403);

        $response = $next($request);

        if ($response->isSuccessful()) {
            activity()
                ->useLog('operations')
                ->causedBy($user)
                ->withProperties([
                    'method' => $request->method(),
                    'path' => $request->path(),
                ])
                ->log('Horizon action executed.');
        }

        return $response;
    }
}
