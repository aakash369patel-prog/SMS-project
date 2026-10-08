<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isParent()) {
            return response()->json(['message' => 'Parent access required.'], 403);
        }

        return $next($request);
    }
}
