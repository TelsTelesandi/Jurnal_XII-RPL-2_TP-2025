<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForumOnlineController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $cut = now()->subMinutes(5);

        // Prioritas 1: kolom users.last_seen_at
        if (Schema::hasColumn('users', 'last_seen_at')) {
            $count = (int) DB::table('users')->where('last_seen_at', '>=', $cut)->count();
            return response()->json(['count' => $count]);
        }

        // Prioritas 2: user yang kirim pesan forum dalam 5 menit terakhir
        if (Schema::hasTable('forum_messages')) {
            $count = (int) DB::table('forum_messages')
                ->when(Schema::hasColumn('forum_messages', 'is_deleted'), fn($q) => $q->where('is_deleted', false))
                ->where('created_at', '>=', $cut)
                ->distinct('user_id')->count('user_id');

            return response()->json(['count' => $count]);
        }

        return response()->json(['count' => 0]);
    }
}
