<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $systemAdminId = \App\Models\User::where('role_id', 1)->value('id') ?? 1;
        // 🔄 Sinkronisasi retro-aktif: Pastikan user lama yang sudah punya 3 laporan otomatis ter-ban
        $allUsers = User::all();
        foreach ($allUsers as $u) {
            if ($u->getTotalReportsCount() >= 3 && !$u->isBannedFromForum()) {
                \App\Models\ForumBan::create([
                    'user_id' => $u->id,
                    'banned_by' => $systemAdminId,
                    'reason' => 'Auto-ban: Telah dilaporkan lebih dari 3 kali oleh pengguna lain.',
                    'is_active' => true
                ]);
            }
        }

        $query = User::with('activeBan');

        // Jika ada pencarian
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->paginate(10);

        return view('admin.users.index', [
            'users' => $users,
            'totalUsers' => User::count(),
            'activeUsers' => User::whereNotNull('email_verified_at')->count(),
            'pendingUsers' => User::whereNull('email_verified_at')->count(),
            'bannedUsers' => User::has('activeBan')->count(),
        ]);
    }


    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|integer', // kalau ada kolom role_id
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan');
    }


    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|integer',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }


    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function unban(User $user)
    {
        if ($user->activeBan) {
            $user->activeBan()->update([
                'is_active' => false,
                'expires_at' => now(),
            ]);
            
            // Hapus report komentar agar tidak otomatis ter-banned lagi
            \App\Models\BlogCommentReport::whereHas('comment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->delete();

            // Hapus juga report forum agar hitungan tereset total
            \App\Models\ForumReport::where('target_user_id', $user->id)->delete();
            
        }

        return redirect()->back()->with('success', 'Status blokir (ban) pengguna berhasil dicabut. Laporan telah di-reset menjadi 0.');
    }

    public function reports(User $user)
    {
        $forumReports = \App\Models\ForumReport::with(['reporter:id,name', 'message:id,message,user_id'])
            ->where('target_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => 'f_' . $r->id,
                    'type' => 'forum',
                    'reason' => $r->reason,
                    'notes' => $r->notes,
                    'created_at' => optional($r->created_at)->toIso8601String(),
                    'reporter' => $r->reporter?->name ?? 'User',
                    'message' => $r->message ? \Illuminate\Support\Str::limit((string) $r->message->message, 140) : null,
                ];
            });

        $blogReports = \App\Models\BlogCommentReport::with(['reporter:id,name', 'comment' => function($q) {
                $q->withTrashed();
            }])
            ->whereHas('comment', function ($q) use ($user) {
                $q->withTrashed()->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => 'b_' . $r->id,
                    'type' => 'blog',
                    'reason' => $r->reason,
                    'notes' => 'Laporan dari Komentar Artikel',
                    'created_at' => optional($r->created_at)->toIso8601String(),
                    'reporter' => $r->reporter?->name ?? 'User',
                    'message' => $r->comment ? \Illuminate\Support\Str::limit((string) $r->comment->isi, 140) : null,
                ];
            });

        $reports = $forumReports->concat($blogReports)
            ->sortByDesc('created_at')
            ->values();

        return response()->json(['status' => 'success', 'data' => $reports]);
    }
}
