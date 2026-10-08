<?php

namespace App\Http\Middleware;

use App\Support\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        if (! Permission::allows($request->user(), $ability)) {
            return response()->json(['message' => 'You do not have permission for this action.'], 403);
        }

        return $next($request);
    }
}
