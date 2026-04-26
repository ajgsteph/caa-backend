<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureArtistIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'Compte suspendu.'], 403);
        }

        return $next($request);
    }
}
