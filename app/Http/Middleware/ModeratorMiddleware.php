<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ModeratorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();


        if (!in_array($user->role_id, [1,2, 3])) {
            abort(403, 'Akses ditolak. Hanya moderator/admin yang diizinkan.');
        }

        return $next($request);
    }
}