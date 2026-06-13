<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_banned) {
            return response()->json([
                'message' => 'Your account has been banned.',
                'reason' => $request->user()->ban_reason,
            ], 403);
        }

        return $next($request);
    }
}
