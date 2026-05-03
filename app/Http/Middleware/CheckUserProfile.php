<?php
// app/Http/Middleware/CheckUserProfile.php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CheckUserProfile
{
    public function handle(Request $request, Closure $next, ...$profiles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Admin sempre tem acesso
        if ($request->user()->hasRole('admin')) {
            return $next($request);
        }

        $userProfiles = $request->user()->roles->pluck('name')->toArray();

        if (!array_intersect($userProfiles, $profiles)) {
            abort(403, 'Acesso não autorizado. Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
