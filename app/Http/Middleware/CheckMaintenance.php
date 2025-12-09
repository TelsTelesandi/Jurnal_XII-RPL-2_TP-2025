<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        // bypass untuk admin
        if (auth()->check() && auth()->user()->role_id == 1) {
            return $next($request);
        }

        // cek DB setting
        $maintenance = Setting::where('key', 'maintenance_mode')->value('value');

        if ($maintenance) {
            return response()->view('maintenance'); // tampilkan view custom
        }

        return $next($request);
    }
}
