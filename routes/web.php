<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AdminForumController;

use App\Models\ForumMessage;
use App\Models\MessageReaction;

Route::view('/bantuan', 'sections.bantuan')->name('bantuan');


/*
|--------------------------------------------------------------------------
| Guest (publik)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/layanan', fn() => view('index', ['selected' => 'foto_udara_lidar']))->name('layanan.index');
Route::get('/layanan/{kategori}', function (string $kategori) {
    $allowed = ['foto_udara_lidar', 'tematik', 'software_development', 'survey', 'training', 'data_software_provider'];
    abort_unless(in_array($kategori, $allowed, true), 404);
    return view('index', ['selected' => $kategori]);
})->name('layanan.show');

/*
|--------------------------------------------------------------------------
| Blog publik
|--------------------------------------------------------------------------
*/
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/list', [BlogController::class, 'list'])->name('list');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
    Route::post('/{post}/comment', [BlogController::class, 'storeComment'])->middleware('auth')->name('comment.store');
    Route::post('/{post}/like', [BlogController::class, 'toggleLike'])->middleware('auth')->name('like');
    Route::post('/comments/{comment}/report', [BlogController::class, 'reportComment'])->middleware('auth')->name('comment.report');
});

/*
|--------------------------------------------------------------------------
| User Auth (Login/Logout)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard User (redirect setelah login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/dashboard', function () {
    // kalau mau view terpisah, ganti ke resources/views/dashboard.blade.php
    return view('index');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| User Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
    Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
});

/*
|--------------------------------------------------------------------------
| Forum (user login, tidak dibanned)
|--------------------------------------------------------------------------
*/
Route::get('/cek-blasp', function () {
    dd(config('blasp.extra_words'));
});

Route::middleware(['auth', 'forum.not_banned'])->group(function () {
    Route::get('/api/user', fn() => response()->json(auth()->user()));

    Route::get('/forum/status', [ForumController::class, 'status'])->name('forum.status');

    // online heartbeat
    Route::post('/forum/online-heartbeat', function () {
        $key = 'forum:online:users';
        $list = Cache::get($key, []);
        $id = Auth::id() ?: request()->ip();
        $list[$id] = now()->timestamp;
        Cache::put($key, $list, 120);
        return response()->json(['ok' => true]);
    });

    // hitung online
    Route::get('/forum/online-count', function () {
        $key = 'forum:online:users';
        $list = Cache::get($key, []);
        $now = now()->timestamp;
        $list = array_filter($list, fn($ts) => ($now - $ts) < 60);
        Cache::put($key, $list, 120);
        return response()->json(['count' => count($list)]);
    });

    Route::prefix('forum')->group(function () {
        Route::get('/messages', [ForumController::class, 'getMessages']);
        Route::post('/messages', [ForumController::class, 'sendMessage']);
        Route::post('/polls', [ForumController::class, 'createPoll']);
        Route::post('/polls/{poll}/vote', [ForumController::class, 'votePoll']);
        Route::delete('/messages/{message}', [ForumController::class, 'deleteMessage']);
        Route::post('/report', [ForumController::class, 'reportUser'])->name('forum.report');

        Route::prefix('moderate')->group(function () {
            Route::post('/users/{user}/kick', [ForumController::class, 'kickUser']);
            Route::post('/users/{user}/ban', [ForumController::class, 'banUser']);
            Route::post('/users/{user}/unban', [ForumController::class, 'unbanUser']);
            Route::post('/toggle', [ForumController::class, 'toggleForum']);
        });
    });
});

// Toggle reaction (auth)
Route::post('/forum/messages/{message}/react', function (ForumMessage $message, Request $req) {
    $user = Auth::user();
    $emoji = (string) $req->input('emoji');
    if ($emoji === '') {
        return response()->json(['status' => 'error', 'message' => 'Emoji wajib'], 422);
    }

    $existing = MessageReaction::where([
        'message_id' => $message->id,
        'user_id' => $user->id,
        'emoji' => $emoji,
    ])->first();

    if ($existing) {
        $existing->delete();
    } else {
        try {
            MessageReaction::create(['message_id' => $message->id, 'user_id' => $user->id, 'emoji' => $emoji]);
        } catch (\Illuminate\Database\QueryException $e) {
            // ignore duplicate
        }
    }

    $reactions = MessageReaction::where('message_id', $message->id)->get(['emoji', 'user_id']);
    return response()->json(['status' => 'success', 'reactions' => $reactions]);
})->middleware('auth')->name('messages.react');

/*
|--------------------------------------------------------------------------
| Admin Auth (login/logout)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| Admin Panel (protected)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'admin.check'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::get('users/{user}/reports', [UserController::class, 'reports'])->name('users.reports');
    Route::post('users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');
    Route::resource('blog', AdminBlogController::class);
    Route::post('comments/clear', [CommentController::class, 'clear'])->name('comments.clear');
    Route::resource('comments', CommentController::class)->only(['index', 'destroy']);



    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/forum', [AdminForumController::class, 'index'])->name('forum.index');
    Route::prefix('forum')->as('forum.')->group(function () {
        Route::get('/stats', [AdminForumController::class, 'stats'])->name('stats');
        Route::post('/toggle', [AdminForumController::class, 'toggle'])->name('toggle');
        Route::post('/clear', [AdminForumController::class, 'clear'])->name('clear');
        Route::get('/banned', [AdminForumController::class, 'banned'])->name('banned');
        Route::post('/unban/{user}', [AdminForumController::class, 'unban'])->name('unban');
        Route::get('/moderator-logs', [AdminForumController::class, 'logs'])->name('logs');
        Route::get('/reports', [AdminForumController::class, 'reports'])->name('reports');
        Route::post('/settings', [AdminForumController::class, 'settings'])->name('settings');
        Route::post('/broadcast', [AdminForumController::class, 'broadcast'])->name('broadcast');
        Route::get('/export', [AdminForumController::class, 'export'])->name('export');
        Route::get('/history', [AdminForumController::class, 'history'])->name('history');
        Route::get('/top-users', [AdminForumController::class, 'topUsers'])->name('topUsers');
        Route::get('/latest-polls', [AdminForumController::class, 'latestPolls'])->name('latestPolls');
    });
});
