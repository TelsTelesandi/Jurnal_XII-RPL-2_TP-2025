<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumMessage;
use App\Models\ForumSetting;
use App\Models\ForumBan;
use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Events\ForumSettingsUpdated;
class AdminForumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin.check']);
    }

    public function index()
    {
        $settings = ForumSetting::current();

        // --- Konversi batas lampiran ke MB
        $mbVal = $settings?->max_attachment_kb
            ? (int) ($settings->max_attachment_kb / 1024)
            : '';

        // --- Konversi durasi poll (menit → nilai & unit)
        $pdVal = null;
        $pdUnit = 'minutes';
        if ($settings?->default_poll_duration_minutes) {
            $minutes = (int) $settings->default_poll_duration_minutes;

            if ($minutes % (60 * 24) === 0) {
                $pdVal = (int) ($minutes / (60 * 24));
                $pdUnit = 'days';
            } elseif ($minutes % 60 === 0) {
                $pdVal = (int) ($minutes / 60);
                $pdUnit = 'hours';
            } else {
                $pdVal = $minutes;
                $pdUnit = 'minutes';
            }
        }

        return view('admin.forum.index', compact('settings', 'mbVal', 'pdVal', 'pdUnit'));
    }



    public function stats(): JsonResponse
    {
        // Counts
        $totalMessages = ForumMessage::where('is_deleted', false)->count();
        $messagesToday = ForumMessage::where('is_deleted', false)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $totalUsers = User::count();
        $bannedUsers = ForumBan::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->distinct('user_id')
            ->count('user_id');

        $pollsCount = ForumPoll::whereHas('message', fn($q) => $q->where('is_deleted', false))->count();
        $activePolls = ForumPoll::whereHas('message', fn($q) => $q->where('is_deleted', false))
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();

        $voiceCount = ForumMessage::where('is_deleted', false)
            ->whereIn('message_type', ['voice', 'audio'])
            ->count();

        $attachmentsCount = ForumMessage::where('is_deleted', false)
            ->whereIn('message_type', ['image', 'document', 'video', 'file', 'attachment'])
            ->count();

        // 7 Days
        $days7 = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->toDateString());

        $perDay7 = $this->getDailyCounts(ForumMessage::where('is_deleted', false), 6, 'created_at');
        $perDay7Del = $this->getDailyCounts(ForumMessage::where('is_deleted', true), 6, 'created_at');
        $perDay7Voice = $this->getDailyCounts(
            ForumMessage::where('is_deleted', false)->whereIn('message_type', ['voice', 'audio']),
            6,
            'created_at'
        );
        $perDay7Attach = $this->getDailyCounts(
            ForumMessage::where('is_deleted', false)->whereIn('message_type', ['image', 'document', 'video', 'file', 'attachment']),
            6,
            'created_at'
        );
        $perDay7Poll = $this->getDailyCounts(
            ForumPoll::whereHas('message', fn($q) => $q->where('is_deleted', false)),
            6,
            'created_at'
        );

        $perDay7Articles = $this->getBlogDailyCounts('blog_posts', 6, 'created_at');
        $perDay7Comments = $this->getBlogDailyCounts('blog_comments', 6, 'created_at');

        $last7_labels = $days7->map(fn($d) => date('d M', strtotime($d)))->values();
        $last7_series = $days7->map(fn($d) => (int) ($perDay7[$d] ?? 0))->values();
        $last7_deleted_series = $days7->map(fn($d) => (int) ($perDay7Del[$d] ?? 0))->values();
        $last7_voice_series = $days7->map(fn($d) => (int) ($perDay7Voice[$d] ?? 0))->values();
        $last7_attach_series = $days7->map(fn($d) => (int) ($perDay7Attach[$d] ?? 0))->values();
        $last7_poll_series = $days7->map(fn($d) => (int) ($perDay7Poll[$d] ?? 0))->values();
        $last7_articles_series = $days7->map(fn($d) => (int) ($perDay7Articles[$d] ?? 0))->values();
        $last7_comments_series = $days7->map(fn($d) => (int) ($perDay7Comments[$d] ?? 0))->values();

        // 30 Days
        $days30 = collect(range(29, 0))->map(fn($i) => now()->subDays($i)->toDateString());

        $perDay30 = $this->getDailyCounts(ForumMessage::where('is_deleted', false), 29, 'created_at');
        $perDay30Del = $this->getDailyCounts(ForumMessage::where('is_deleted', true), 29, 'created_at');
        $perDay30Voice = $this->getDailyCounts(
            ForumMessage::where('is_deleted', false)->whereIn('message_type', ['voice', 'audio']),
            29,
            'created_at'
        );
        $perDay30Attach = $this->getDailyCounts(
            ForumMessage::where('is_deleted', false)->whereIn('message_type', ['image', 'document', 'video', 'file', 'attachment']),
            29,
            'created_at'
        );
        $perDay30Poll = $this->getDailyCounts(
            ForumPoll::whereHas('message', fn($q) => $q->where('is_deleted', false)),
            29,
            'created_at'
        );

        $perDay30Articles = $this->getBlogDailyCounts('blog_posts', 29, 'created_at');
        $perDay30Comments = $this->getBlogDailyCounts('blog_comments', 29, 'created_at');

        $last30_labels = $days30->map(fn($d) => date('d M', strtotime($d)))->values();
        $last30_series = $days30->map(fn($d) => (int) ($perDay30[$d] ?? 0))->values();
        $last30_deleted_series = $days30->map(fn($d) => (int) ($perDay30Del[$d] ?? 0))->values();
        $last30_voice_series = $days30->map(fn($d) => (int) ($perDay30Voice[$d] ?? 0))->values();
        $last30_attach_series = $days30->map(fn($d) => (int) ($perDay30Attach[$d] ?? 0))->values();
        $last30_poll_series = $days30->map(fn($d) => (int) ($perDay30Poll[$d] ?? 0))->values();
        $last30_articles_series = $days30->map(fn($d) => (int) ($perDay30Articles[$d] ?? 0))->values();
        $last30_comments_series = $days30->map(fn($d) => (int) ($perDay30Comments[$d] ?? 0))->values();

        // 12 Months
        $months = collect(range(11, 0))->map(fn($i) => now()->subMonths($i)->startOfMonth());
        $fromMonth = $months->first()->toDateString();

        $perMonth = $this->getMonthlyCounts(ForumMessage::where('is_deleted', false), $fromMonth, 'created_at');
        $perMonthDel = $this->getMonthlyCounts(ForumMessage::where('is_deleted', true), $fromMonth, 'created_at');
        $perMonthVoice = $this->getMonthlyCounts(
            ForumMessage::where('is_deleted', false)->whereIn('message_type', ['voice', 'audio']),
            $fromMonth,
            'created_at'
        );
        $perMonthAttach = $this->getMonthlyCounts(
            ForumMessage::where('is_deleted', false)->whereIn('message_type', ['image', 'document', 'video', 'file', 'attachment']),
            $fromMonth,
            'created_at'
        );
        $perMonthPoll = $this->getMonthlyCounts(
            ForumPoll::whereHas('message', fn($q) => $q->where('is_deleted', false)),
            $fromMonth,
            'created_at'
        );

        $perMonthArticles = $this->getBlogMonthlyCounts('blog_posts', $fromMonth, 'created_at');
        $perMonthComments = $this->getBlogMonthlyCounts('blog_comments', $fromMonth, 'created_at');

        $last365_labels = $months->map(fn($m) => $m->translatedFormat('M'))->values();
        $last365_series = $months->map(fn($m) => (int) ($perMonth[$m->format('Y-m-01')] ?? 0))->values();
        $last365_deleted_series = $months->map(fn($m) => (int) ($perMonthDel[$m->format('Y-m-01')] ?? 0))->values();
        $last365_voice_series = $months->map(fn($m) => (int) ($perMonthVoice[$m->format('Y-m-01')] ?? 0))->values();
        $last365_attach_series = $months->map(fn($m) => (int) ($perMonthAttach[$m->format('Y-m-01')] ?? 0))->values();
        $last365_poll_series = $months->map(fn($m) => (int) ($perMonthPoll[$m->format('Y-m-01')] ?? 0))->values();
        $last365_articles_series = $months->map(fn($m) => (int) ($perMonthArticles[$m->format('Y-m-01')] ?? 0))->values();
        $last365_comments_series = $months->map(fn($m) => (int) ($perMonthComments[$m->format('Y-m-01')] ?? 0))->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_messages' => (int) $totalMessages,
                'messages_today' => (int) $messagesToday,
                'total_users' => (int) $totalUsers,
                'banned_users' => (int) $bannedUsers,

                'polls_count' => (int) $pollsCount,
                'active_polls' => (int) $activePolls,
                'voice_count' => (int) $voiceCount,
                'attachments_count' => (int) $attachmentsCount,

                // 7 days
                'last7_labels' => $last7_labels,
                'last7_series' => $last7_series,
                'last7_deleted_series' => $last7_deleted_series,
                'last7_voice_series' => $last7_voice_series,
                'last7_attach_series' => $last7_attach_series,
                'last7_poll_series' => $last7_poll_series,
                'last7_articles_series' => $last7_articles_series,
                'last7_comments_series' => $last7_comments_series,

                // 30 days
                'last30_labels' => $last30_labels,
                'last30_series' => $last30_series,
                'last30_deleted_series' => $last30_deleted_series,
                'last30_voice_series' => $last30_voice_series,
                'last30_attach_series' => $last30_attach_series,
                'last30_poll_series' => $last30_poll_series,
                'last30_articles_series' => $last30_articles_series,
                'last30_comments_series' => $last30_comments_series,

                // 12 months
                'last365_labels' => $last365_labels,
                'last365_series' => $last365_series,
                'last365_deleted_series' => $last365_deleted_series,
                'last365_voice_series' => $last365_voice_series,
                'last365_attach_series' => $last365_attach_series,
                'last365_poll_series' => $last365_poll_series,
                'last365_articles_series' => $last365_articles_series,
                'last365_comments_series' => $last365_comments_series,

                'updated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function topUsers(): JsonResponse
    {
        $rows = ForumMessage::select('user_id', DB::raw('COUNT(*) as cnt'))
            ->where('is_deleted', false)
            ->groupBy('user_id')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        $userIds = $rows->pluck('user_id')->all();
        $users = User::whereIn('id', $userIds)->get(['id', 'name']);
        $activeBans = ForumBan::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->pluck('user_id')
            ->flip();

        $list = $rows->map(function ($r) use ($users, $activeBans) {
            $u = $users->firstWhere('id', $r->user_id);
            return [
                'name' => $u?->name ?? 'User #' . $r->user_id,
                'count' => (int) $r->cnt,
                'banned' => $activeBans->has($r->user_id),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $list]);
    }

    public function latestPolls(): JsonResponse
    {
        $polls = ForumPoll::with('message:id,is_deleted,created_at,message')
            ->whereHas('message', fn($q) => $q->where('is_deleted', false))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pollIds = $polls->pluck('id')->all();
        $voteCounts = ForumPollVote::select('poll_id', DB::raw('COUNT(*) as c'))
            ->whereIn('poll_id', $pollIds)
            ->groupBy('poll_id')
            ->pluck('c', 'poll_id');

        $data = $polls->map(function ($p) use ($voteCounts) {
            $optionsCount = is_array($p->options ?? null) ? count($p->options) : ($p->options_count ?? null);
            return [
                'question' => $p->message?->message ?? ($p->question ?? 'Pertanyaan'),
                'options_count' => $optionsCount,
                'votes_count' => (int) ($voteCounts[$p->id] ?? 0),
                'created_at' => optional($p->created_at)->toIso8601String(),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function banned(): JsonResponse
    {
        $bans = ForumBan::with(['user:id,name,email'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ban) {
                return [
                    'id' => $ban->id,
                    'user' => [
                        'id' => $ban->user->id,
                        'name' => $ban->user->name,
                        'email' => $ban->user->email,
                    ],
                    'ban_type' => $ban->ban_type,
                    'reason' => $ban->reason,
                    'expires_at' => optional($ban->expires_at)->toIso8601String(),
                    'created_at' => $ban->created_at->toIso8601String(),
                ];
            });

        return response()->json(['status' => 'success', 'data' => $bans]);
    }

    public function unban(User $user): JsonResponse
    {
        ForumBan::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        if (class_exists(\App\Events\ForumBanRevoked::class)) {
            event(new \App\Events\ForumBanRevoked($user->id));
        }

        return response()->json(['status' => 'success', 'message' => 'User unbanned']);
    }

    public function logs(): JsonResponse
    {
        $deletedMessages = ForumMessage::with(['user:id,name', 'deletedBy:id,name'])
            ->where('is_deleted', true)
            ->orderBy('deleted_at', 'desc')
            ->limit(40)
            ->get()
            ->map(function ($m) {
                return [
                    'type' => 'message_deleted',
                    'message' => Str::limit((string) $m->message, 120),
                    'user' => $m->user?->name,
                    'deleted_by' => $m->deletedBy?->name,
                    'created_at' => optional($m->deleted_at ?? $m->updated_at ?? $m->created_at)->toIso8601String(),
                ];
            });

        $banLogs = ForumBan::with(['user:id,name', 'bannedBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(40)
            ->get()
            ->map(function ($b) {
                return [
                    'type' => 'user_banned',
                    'user' => $b->user?->name,
                    'banned_by' => $b->bannedBy?->name,
                    'ban_type' => $b->ban_type,
                    'reason' => $b->reason,
                    'created_at' => $b->created_at->toIso8601String(),
                ];
            });

        $logs = $deletedMessages->merge($banLogs)
            ->sortByDesc('created_at')
            ->values()
            ->all();

        return response()->json(['status' => 'success', 'data' => $logs]);
    }

    public function reports(): JsonResponse
    {
        $forumReports = \App\Models\ForumReport::with([
                'reporter:id,name',
                'target:id,name',
                'message:id,message,user_id'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => 'f_' . $r->id,
                    'type' => 'forum',
                    'reason' => $r->reason,
                    'notes' => $r->notes,
                    'created_at' => optional($r->created_at)->toIso8601String(),
                    'reporter' => [
                        'id' => $r->reporter?->id,
                        'name' => $r->reporter?->name,
                    ],
                    'target' => [
                        'id' => $r->target?->id,
                        'name' => $r->target?->name,
                    ],
                    'message' => $r->message ? [
                        'id' => $r->message->id,
                        'text' => \Illuminate\Support\Str::limit((string) $r->message->message, 140),
                        'user_id' => $r->message->user_id,
                    ] : null,
                ];
            });

        $blogReports = \App\Models\BlogCommentReport::with([
                'reporter:id,name',
                'comment' => function($q) {
                    $q->withTrashed()->with('user:id,name');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => 'b_' . $r->id,
                    'type' => 'blog',
                    'reason' => $r->reason,
                    'notes' => 'Laporan dari Komentar Artikel',
                    'created_at' => optional($r->created_at)->toIso8601String(),
                    'reporter' => [
                        'id' => $r->reporter?->id,
                        'name' => $r->reporter?->name,
                    ],
                    'target' => [
                        'id' => $r->comment?->user?->id,
                        'name' => $r->comment?->user?->name,
                    ],
                    'message' => $r->comment ? [
                        'id' => $r->comment->id,
                        'text' => \Illuminate\Support\Str::limit((string) $r->comment->isi, 140),
                        'user_id' => $r->comment->user_id,
                    ] : null,
                ];
            });

        $reports = $forumReports->concat($blogReports)
            ->sortByDesc('created_at')
            ->take(100)
            ->values();

        return response()->json(['status' => 'success', 'data' => $reports]);
    }

    public function toggle(Request $request)
    {
        $s = ForumSetting::first() ?? new ForumSetting();

        $mode = $request->boolean('open', null);
        if ($mode === null) {
            $s->is_open = !(bool) $s->is_open;
        } else {
            $s->is_open = $mode;
        }

        $s->save();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'is_open' => (bool) $s->is_open,
                'label' => $s->is_open ? 'Tutup Forum' : 'Buka Forum',
                'chip' => $s->is_open ? 'Terbuka' : 'Tertutup',
            ]);
        }

        return back()->with('success', 'Status forum diperbarui.');
    }

    public function clear(Request $request)
    {
        ForumMessage::query()->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'message' => 'Semua pesan berhasil dihapus.']);
        }

        return back()->with('success', 'Semua pesan berhasil dihapus.');
    }

    public function settings(Request $request)
    {
        $request->validate([
            'allow_attachments' => 'nullable|boolean',
            'allow_polls' => 'nullable|boolean',
            'allow_voice' => 'nullable|boolean',
            'max_attachment_mb' => 'nullable|integer|min:0',
            'poll_duration_value' => 'nullable|integer|min:1',
            'poll_duration_unit' => 'nullable|in:minutes,hours,days',
            'max_users' => 'nullable|integer|min:0',
        ]);

        $s = ForumSetting::first() ?? new ForumSetting();

        $s->allow_attachments = $request->boolean('allow_attachments');
        $s->allow_polls = $request->boolean('allow_polls');
        $s->allow_voice = $request->boolean('allow_voice');

        $mb = $request->input('max_attachment_mb');
        $s->max_attachment_kb = ($mb === null || $mb === '' || (int) $mb === 0) ? null : (int) $mb * 1024;

        $val = (int) $request->input('poll_duration_value');
        $unit = $request->input('poll_duration_unit');
        if ($val && $unit) {
            $s->default_poll_duration_minutes = match ($unit) {
                'days' => $val * 60 * 24,
                'hours' => $val * 60,
                default => $val,
            };
        } else {
            $s->default_poll_duration_minutes = null;
        }

        $maxUsers = $request->input('max_users');
        $s->max_users = ($maxUsers === null || $maxUsers === '' || (int) $maxUsers === 0) ? null : (int) $maxUsers;

        $s->save();
        Cache::forget('forum:settings'); // pastikan cache dihapus

        // 🔔 Tambahkan broadcast event
        broadcast(new ForumSettingsUpdated($s))->toOthers();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'message' => 'Pengaturan tersimpan.']);
        }

        return back()->with('success', 'Pengaturan tersimpan.');
    }

    public function broadcast(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'message_type' => 'nullable|in:text,announcement',
        ]);

        $type = $data['message_type'] ?? 'text';
        if ($type === 'announcement') {
            $type = 'text';
        }

        $msg = ForumMessage::create([
            'user_id' => Auth::id(),
            'message' => $data['message'],
            'message_type' => $type,
            'metadata' => [
                'is_broadcast' => true,
                'broadcast_at' => now()->toIso8601String(),
            ],
        ]);

        $msg->load(['user', 'replyTo.user', 'poll', 'reactions']);

        if (class_exists(\App\Events\ForumMessageCreated::class)) {
            event(new \App\Events\ForumMessageCreated([
                'id' => $msg->id,
                'message' => $msg->message,
                'message_type' => $msg->message_type,
                'metadata' => $msg->metadata,
                'attachment_url' => method_exists($msg, 'getAttachmentUrlAttribute') ? $msg->attachment_url : null,
                'user' => [
                    'id' => $msg->user->id,
                    'name' => $msg->user->name,
                    'role_id' => $msg->user->role_id,
                ],
                'reply_to' => null,
                'created_at' => $msg->created_at->toIso8601String(),
                'formatted_time' => method_exists($msg, 'getFormattedCreatedAtAttribute')
                    ? $msg->formatted_created_at : $msg->created_at->format('H:i'),
                'can_delete' => true,
                'reactions' => [],
            ]));
        }

        return response()->json(['status' => 'success', 'message' => 'Broadcast sent']);
    }

    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'include_deleted' => 'nullable|boolean',
        ]);

        $q = ForumMessage::with(['user:id,name', 'replyTo.user:id,name']);

        if (!$request->boolean('include_deleted')) {
            $q->where('is_deleted', false);
        }
        if ($request->filled('start_date')) {
            $q->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $q->whereDate('created_at', '<=', $request->end_date);
        }

        $messages = $q->orderBy('created_at', 'asc')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'user' => $m->user?->name,
                'message' => $m->message,
                'type' => $m->message_type,
                'reply_to' => $m->replyTo ? ($m->replyTo->user?->name . ': ' . Str::limit((string) $m->replyTo->message, 60)) : null,
                'created_at' => $m->created_at->format('Y-m-d H:i:s'),
                'is_deleted' => (bool) $m->is_deleted,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'messages' => $messages,
                'total_count' => $messages->count(),
                'export_date' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $q = $request->input('q');
        $type = $request->input('type'); // text|poll|attachment|voice|action
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $inclDel = $request->boolean('include_deleted');
        $onlyDel = $request->boolean('only_deleted');
        $cursor = $request->input('cursor');
        $perPage = 20;

        $query = ForumMessage::with(['user:id,name,profile_photo_path'])
            ->when($q, function ($x) use ($q) {
                $x->where(function ($y) use ($q) {
                    $y->where('message', 'like', "%{$q}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($type, function ($x) use ($type) {
                if ($type === 'attachment') {
                    $x->whereIn('message_type', ['image', 'document', 'video', 'file', 'attachment']);
                } elseif ($type === 'poll') {
                    $x->whereIn('message_type', ['poll', 'polling', 'vote']);
                } elseif ($type === 'voice') {
                    $x->whereIn('message_type', ['voice', 'audio']);
                } elseif ($type === 'text') {
                    $x->where('message_type', 'text');
                } elseif ($type === 'action') {
                    $x->whereRaw('1=0'); // kosong
                }
            })
            ->when($onlyDel, fn($x) => $x->where('is_deleted', true))
            ->when(!$onlyDel && !$inclDel, fn($x) => $x->where('is_deleted', false))
            ->when($start, fn($x) => $x->whereDate('created_at', '>=', $start))
            ->when($end, fn($x) => $x->whereDate('created_at', '<=', $end))
            ->orderByDesc('id');

        $items = $query->when($cursor, fn($x) => $x->where('id', '<', (int) $cursor))
            ->limit($perPage + 1)
            ->get();

        $nextCursor = null;
        if ($items->count() > $perPage) {
            $nextCursor = $items[$perPage - 1]->id;
            $items = $items->take($perPage);
        }

        $mapped = $items->map(function ($m) {
            $rawType = (string) $m->message_type;

            if (in_array($rawType, ['image', 'document', 'video', 'file', 'attachment'])) {
                $type = 'attachment';
            } elseif (in_array($rawType, ['poll', 'polling', 'vote'])) {
                $type = 'poll';
            } elseif (in_array($rawType, ['voice', 'audio'])) {
                $type = 'voice';
            } else {
                $type = $rawType ?: 'text';
            }

            $avatar = $m->user?->profile_photo_path
                ? url('/storage/' . ltrim($m->user->profile_photo_path, '/'))
                : null;

            $attachmentPath = $m->attachment ? ltrim($m->attachment, '/') : null;
            $attachmentUrl = $attachmentPath ? url('/storage/' . $attachmentPath) : null;
            $attachmentName = $m->metadata['filename'] ?? ($attachmentPath ? basename($attachmentPath) : null);

            $meta = is_array($m->metadata) ? $m->metadata : [];
            $location = [
                'name' => $meta['location_name'] ?? $meta['place_name'] ?? null,
                'lat' => $meta['lat'] ?? null,
                'lng' => $meta['lng'] ?? null,
            ];

            return [
                'id' => $m->id,
                'user' => ['name' => $m->user?->name, 'avatar' => $avatar],
                'type' => $type,
                'raw_type' => $rawType,
                'content' => $m->message,
                'attachment_url' => $attachmentUrl,
                'attachment_name' => $attachmentName,
                'location' => $location,
                'created_at' => optional($m->created_at)->toIso8601String(),
                'deleted' => (bool) $m->is_deleted,
            ];
        });

        return response()->json([
            'data' => $mapped,
            'next_cursor' => $nextCursor,
        ]);
    }

    private function getDailyCounts($query, int $daysBack, string $dateColumn): array
    {
        return $query->select(DB::raw('DATE(' . $dateColumn . ') d'), DB::raw('COUNT(*) c'))
            ->whereDate($dateColumn, '>=', now()->subDays($daysBack)->toDateString())
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();
    }

    private function getMonthlyCounts($query, string $fromDate, string $dateColumn): array
    {
        return $query->select(DB::raw('DATE_FORMAT(' . $dateColumn . ', "%Y-%m-01") m'), DB::raw('COUNT(*) c'))
            ->whereDate($dateColumn, '>=', $fromDate)
            ->groupBy('m')
            ->pluck('c', 'm')
            ->toArray();
    }

    private function getBlogDailyCounts(string $table, int $daysBack, string $dateColumn): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)->select(DB::raw('DATE(' . $dateColumn . ') d'), DB::raw('COUNT(*) c'))
            ->whereDate($dateColumn, '>=', now()->subDays($daysBack)->toDateString())
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();
    }

    private function getBlogMonthlyCounts(string $table, string $fromDate, string $dateColumn): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)->select(DB::raw('DATE_FORMAT(' . $dateColumn . ', "%Y-%m-01") m'), DB::raw('COUNT(*) c'))
            ->whereDate($dateColumn, '>=', $fromDate)
            ->groupBy('m')
            ->pluck('c', 'm')
            ->toArray();
    }
}
