<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (setting('maintenance_mode') == 1) {
            $user = $request->user();

            // ❌ kalau belum login atau bukan admin
            if (!$user || $user->role_id !== 1) {
                // ✅ kecualikan semua URL /admin/*
                if (!$request->is('admin/*')) {
                    return response()->view('maintenance');
                }
            }
        }

        return $next($request);
    }
}
