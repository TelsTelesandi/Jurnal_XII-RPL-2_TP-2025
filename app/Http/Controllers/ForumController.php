<?php

// app/Http/Controllers/ForumController.php

namespace App\Http\Controllers;

use App\Models\ForumMessage;
use App\Models\ForumSetting;
use App\Models\ForumBan;
use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\User;
use App\Models\MessageReaction;
use App\Models\ForumReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\StringHelper;

class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $settings = ForumSetting::current() ?? ForumSetting::first();

        if (!$settings) {
            $settings = ForumSetting::create([
                'is_open' => true,
                'allow_attachments' => true,
                'allow_polls' => true,
                'allow_voice' => true,
                // Tambahkan field lain sesuai schema
            ]);
        }
        return view('components.forum-widget', compact('settings'));
    }

   public function getMessages(Request $request): JsonResponse
{
    $user = Auth::user();

    if ($user->isBannedFromForum()) {
        $ban = ForumBan::where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        return response()->json([
            'status' => 'error',
            'message' => 'Anda telah di-ban dari forum',
            'ban' => $ban ? $this->banPayload($ban) : null,
            'data' => []
        ], 403);
    }

    // Hilangkan pengecekan forum ditutup
    // $settings = ForumSetting::current();
    // if (!$settings->is_open && !$user->canModerateForum()) {
    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Forum sedang ditutup',
    //         'data' => []
    //     ], 403);
    // }

    $messages = ForumMessage::with([
        'user',
        'replyTo.user',
        'poll',
        'reactions:id,message_id,user_id,emoji',
    ])
        ->active()
        ->orderBy('created_at', 'asc')
        ->limit(50)
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $messages->map(function ($message) use ($user) {
            return $this->formatMessage($message, $user);
        })
    ]);
}

public function sendMessage(Request $request): JsonResponse
{
    $user = Auth::user();

    if ($user->isBannedFromForum()) {
        return response()->json(['status' => 'error', 'message' => 'Anda telah di-ban dari forum'], 403);
    }

    $settings = ForumSetting::current()->fresh();
    $type = $request->input('message_type');
    $nonStaff = !$user->canModerateForum();

    // ❌ Hilangkan pengecekan forum tutup
    // if (!$settings->is_open && $nonStaff) {
    //     return response()->json(['status' => 'error', 'message' => 'Forum sedang ditutup'], 403);
    // }

    $attachmentTypes = ['image', 'document', 'file', 'attachment', 'voice', 'video'];

    if ($nonStaff && in_array($type, $attachmentTypes, true) && !$settings->allow_attachments) {
        return response()->json(['status' => 'error', 'message' => 'Attachment tidak diizinkan'], 403);
    }
    if ($nonStaff && $type === 'poll' && !$settings->allow_polls) {
        return response()->json(['status' => 'error', 'message' => 'Poll tidak diizinkan'], 403);
    }
    if ($nonStaff && $type === 'voice' && !$settings->allow_voice) {
        return response()->json(['status' => 'error', 'message' => 'Voice note tidak diizinkan'], 403);
    }

    $validator = Validator::make($request->all(), [
        'message' => 'nullable|string|max:1000',
        'message_type' => 'required|in:text,image,document,file,attachment,voice,contact,location,poll,video',
        'attachment' => 'nullable|file|max:10240',
        'reply_to_id' => 'nullable|exists:forum_messages,id',
        'metadata' => 'nullable|array',
        'poll_question' => 'required_if:message_type,poll|string|max:255',
        'poll_options' => 'required_if:message_type,poll|array|min:2|max:10',
        'poll_options.*' => 'string|max:100',
        'poll_multiple_choice' => 'boolean',
        'poll_expires_at' => 'nullable|date|after:now',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Data tidak valid',
            'errors' => $validator->errors()
        ], 422);
    }

    $attachmentPath = null;
    $metaInput = $request->input('metadata', $request->input('attachment_data', []));
    $meta = is_string($metaInput)
        ? (json_last_error() === JSON_ERROR_NONE ? (array) json_decode($metaInput, true) : [])
        : (array) $metaInput;

    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $folder = match ($type) {
            'image' => 'forum/images',
            'voice' => 'forum/voices',
            'video' => 'forum/videos',
            default => 'forum/docs',
        };
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $attachmentPath = $file->storeAs($folder, $filename, 'public');

        $meta = array_merge($meta, [
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    $censoredMessage = StringHelper::censorProfanity($request->input('message'));
    $message = ForumMessage::create([
        'user_id' => $user->id,
        'message' => $censoredMessage,
        'message_type' => $type,
        'attachment' => $attachmentPath,
        'reply_to_id' => $request->input('reply_to_id'),
        'metadata' => $meta,
    ]);

    if ($type === 'poll') {
        $pollQuestion = StringHelper::censorProfanity($request->input('poll_question'));
        $pollOptions = array_map(function ($opt) { return StringHelper::censorProfanity($opt); }, (array) $request->input('poll_options', []));
        ForumPoll::create([
            'message_id' => $message->id,
            'question' => $pollQuestion,
            'options' => array_values($pollOptions),
            'multiple_choice' => (bool) $request->boolean('poll_multiple_choice'),
            'expires_at' => $request->filled('poll_expires_at')
                ? Carbon::parse($request->input('poll_expires_at'))
                : null,
        ]);
    }

    $message->load(['user', 'replyTo.user', 'poll', 'reactions']);
    event(new \App\Events\ForumMessageCreated($this->formatMessage($message, $user)));

    return response()->json([
        'status' => 'success',
        'message' => 'Pesan berhasil dikirim',
        'data' => $this->formatMessage($message, $user),
    ]);
}


    public function createPoll(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Ban & forum tutup
        if ($user->isBannedFromForum()) {
            return response()->json(['status' => 'error', 'message' => 'Anda telah di-ban dari forum'], 403);
        }

        $settings = ForumSetting::current();
        if (!$settings->is_open && !$user->canModerateForum()) {
            return response()->json(['status' => 'error', 'message' => 'Forum sedang ditutup'], 403);
        }
        if (!$settings->allow_polls && !$user->canModerateForum()) {
            return response()->json(['status' => 'error', 'message' => 'Poll tidak diizinkan'], 403);
        }

        // Validasi
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:100'],
            'multiple_choice' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        return DB::transaction(function () use ($user, $data) {
            $msg = ForumMessage::create([
                'user_id' => $user->id,
                'message' => null,
                'message_type' => 'poll',
                'metadata' => [],
            ]);

            ForumPoll::create([
                'message_id' => $msg->id,
                'question' => $data['question'],
                'options' => array_values($data['options']),
                'multiple_choice' => (bool) ($data['multiple_choice'] ?? false),
                'expires_at' => isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
            ]);

            $msg->load(['user', 'replyTo.user', 'poll', 'reactions']);

            event(new \App\Events\ForumPollCreated($this->formatMessage($msg, $user)));

            return response()->json([
                'status' => 'success',
                'message' => 'Poll berhasil dibuat',
                'data' => $this->formatMessage($msg, $user),
            ]);
        });
    }

    public function deleteMessage(ForumMessage $message, Request $request): JsonResponse
    {
        $user = $request->user();

        // Otorisasi: pakai policy/method jika ada, fallback ke owner/admin
        $allowed = method_exists($message, 'canBeDeletedBy')
            ? (bool) $message->canBeDeletedBy($user)
            : ($message->user_id === $user->id || (property_exists($user, 'role_id') && (int) $user->role_id === 1));

        if (!$allowed) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan'], 403);
        }

        // Soft delete ala flag kolom
        $message->is_deleted = true;
        if ($message->isFillable('deleted_at') || Schema::hasColumn($message->getTable(), 'deleted_at')) {
            $message->deleted_at = now();
        }
        if ($message->isFillable('deleted_by') || Schema::hasColumn($message->getTable(), 'deleted_by')) {
            $message->deleted_by = $user->id;
        }
        $message->save();

        // (Opsional) broadcast event ke UI real-time
        if (class_exists(\App\Events\ForumMessageDeleted::class)) {
            event(new \App\Events\ForumMessageDeleted($message->id));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan dihapus',
            'data' => ['id' => $message->id],
        ]);
    }

    public function votePoll(Request $request, ForumPoll $poll): JsonResponse
    {
        $user = Auth::user();

        if ($user->isBannedFromForum()) {
            return response()->json(['status' => 'error', 'message' => 'Anda telah di-ban dari forum'], 403);
        }
        if (method_exists($poll, 'isExpired') && $poll->isExpired()) {
            return response()->json(['status' => 'error', 'message' => 'Poll sudah berakhir'], 422);
        }

        $maxIndex = max(0, min(9, (count($poll->options ?? []) - 1)));

        $validated = $request->validate([
            'option_indexes' => ['sometimes', 'array', 'max:10'],
            'option_indexes.*' => ['integer', 'min:0', 'max:' . $maxIndex],
            'prev_votes' => ['sometimes', 'array'],
            'prev_votes.*' => ['integer', 'min:0', 'max:' . $maxIndex],
        ]);

        $indexes = array_values($validated['option_indexes'] ?? []);
        $prevVotes = array_values($validated['prev_votes'] ?? []);

        if (!$poll->multiple_choice && !empty($indexes)) {
            $indexes = [$indexes[0]]; // Single choice
        }

        return DB::transaction(function () use ($poll, $user, $indexes, $prevVotes) {
            if ($poll->multiple_choice) {
                $toRemove = array_diff($prevVotes, $indexes);
                if (!empty($toRemove)) {
                    ForumPollVote::where('poll_id', $poll->id)
                        ->where('user_id', $user->id)
                        ->whereIn('option_index', $toRemove)
                        ->delete();
                }
            } else {
                ForumPollVote::where('poll_id', $poll->id)
                    ->where('user_id', $user->id)
                    ->delete();
            }

            $already = ForumPollVote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->pluck('option_index')
                ->all();

            $toInsert = array_diff($indexes, $already);
            if (!empty($toInsert)) {
                $rows = [];
                foreach ($toInsert as $idx) {
                    $rows[] = [
                        'poll_id' => $poll->id,
                        'user_id' => $user->id,
                        'option_index' => (int) $idx,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ForumPollVote::insert($rows);
            }

            $poll->refresh();
            $message = $poll->message()->with(['user', 'replyTo.user', 'poll', 'reactions'])->first();

            // Hitung ulang
            $voteCounts = array_fill(0, count($poll->options ?? []), 0);
            $votes = ForumPollVote::where('poll_id', $poll->id)->get()->groupBy('option_index');
            foreach ($votes as $i => $group) {
                $voteCounts[(int) $i] = $group->count();
            }
            $totalVotes = array_sum($voteCounts);

            $userVotes = ForumPollVote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->pluck('option_index')
                ->all();

            event(new \App\Events\ForumPollVoted(
                $poll->id,
                array_map('intval', $voteCounts),
                (int) $totalVotes
            ));

            return response()->json([
                'status' => 'success',
                'message' => 'Vote diperbarui',
                'data' => $this->formatMessage($message, $user),
                'user_votes' => $userVotes,
                'vote_counts' => $voteCounts,
                'total_votes' => $totalVotes,
            ]);
        });
    }

    public function reportUser(Request $request): JsonResponse
    {
        $reporter = Auth::user();
        if (!$reporter) {
            return response()->json(['status' => 'error', 'message' => 'Harap masuk terlebih dahulu'], 401);
        }

        $repRole = (int) ($reporter->role_id ?? 0);
        if ($repRole === 1 || $repRole === 3 || ($reporter->is_admin ?? false) || ($reporter->is_moderator ?? false)) {
            return response()->json(['status' => 'error', 'message' => 'Hanya user biasa yang dapat melaporkan'], 403);
        }

        $validated = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'message_id' => ['nullable', 'integer', 'exists:forum_messages,id'],
            'reason' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ((int) $validated['target_user_id'] === (int) $reporter->id) {
            return response()->json(['status' => 'error', 'message' => 'Tidak dapat melaporkan diri sendiri'], 422);
        }

        $target = User::find((int) $validated['target_user_id']);
        $tarRole = (int) ($target->role_id ?? 0);
        if ($tarRole === 1 || $tarRole === 3 || ($target->is_admin ?? false) || ($target->is_moderator ?? false)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak dapat melaporkan admin atau moderator'], 422);
        }

        $cleanNotes = \App\Helpers\StringHelper::censorProfanity($validated['notes'] ?? null);
        $report = ForumReport::create([
            'reporter_id' => (int) $reporter->id,
            'target_user_id' => (int) $validated['target_user_id'],
            'message_id' => isset($validated['message_id']) ? (int) $validated['message_id'] : null,
            'reason' => (string) $validated['reason'],
            'notes' => $cleanNotes,
            'ip' => (string) request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Laporan berhasil dikirim', 'data' => [
            'id' => $report->id,
            'reporter_id' => $report->reporter_id,
            'target_user_id' => $report->target_user_id,
            'message_id' => $report->message_id,
            'reason' => $report->reason,
            'notes' => $report->notes,
        ]]);
    }

    /**
     * Ban a user from the forum.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function banUser(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->canModerateForum()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan untuk mem-ban user'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['status' => 'error', 'message' => 'Tidak bisa mem-ban diri sendiri'], 422);
        }

        // Ambil input
        $banType = $request->input('ban_type');
        $duration = $request->input('duration', 1);  // Default 1 if not set
        $duration = (int) $duration;  // Ensure integer
        $reason = $request->input('reason', 'No reason provided');  // Default kalau kosong

        // Handle preset durations (misal dari UI tombol: '1h' → 1/24 hari, '24h' → 1 hari, dll.)
        // Note: Duration in days, min 1 day for temporary. For hours, approximate to days (e.g., 1h -> 1 day min)
        $presetDurations = [
            'kick-5m' => null,  // Kick uses minutes, not days
            'ban-1h' => 1,      // Approximate 1h to 1 day (min)
            'ban-24h' => 1,     // 24h = 1 day
            'ban-permanent' => null,  // Permanent: no duration
        ];
        $preset = $request->input('preset');  // Opsional: UI kirim 'ban-1h' sebagai preset
        if (isset($presetDurations[$preset])) {
            $duration = $presetDurations[$preset];  // Use preset value (null for kick/permanent)
            if ($duration !== null) {
                $duration = max(1, $duration);  // Ensure min 1 for temporary
            }
            if (str_contains($preset, 'permanent')) {
                $banType = 'permanent';
            } elseif (str_contains($preset, 'kick')) {
                $banType = 'kick';
            } else {
                $banType = 'temporary';
            }
            $reason = $request->input('reason', "Banned via preset: {$preset}");  // Default reason
        }

        // Fallback ban_type kalau kosong
        $banType = $banType ?? 'temporary';

        // Ensure duration min 1 for temporary
        if ($banType === 'temporary' && $duration < 1) {
            $duration = 1;
        }

        // Validator (update rule untuk handle default)
        $validator = Validator::make([
            'ban_type' => $banType,
            'duration' => $duration,
            'reason' => $reason,
        ], [
            'ban_type' => 'required|in:temporary,permanent,kick',
            'duration' => 'required_if:ban_type,temporary|integer|min:1|max:365',  // Required only for temporary, min 1 day
            'reason' => 'string|max:500',  // Optional now, but max length
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($user, $banType, $duration, $reason, $currentUser) {
            // Hapus ban sebelumnya jika ada (set inactive)
            ForumBan::where('user_id', $user->id)->update(['is_active' => false]);

            $expiresAt = null;
            if ($banType === 'temporary') {
                $expiresAt = now()->addDays($duration);
            } elseif ($banType === 'kick') {
                $expiresAt = now()->addMinutes(5);  // Fixed 5 min for kick
            }

            $ban = ForumBan::create([
                'user_id' => $user->id,
                'banned_by' => $currentUser->id,
                'ban_type' => $banType,
                'reason' => $reason,
                'expires_at' => $expiresAt,
                'is_active' => true,  // Ensure active = true
            ]);

            // Jika kick, force logout
            if ($banType === 'kick') {
                $this->forceLogoutUser($user);
            }

            // (Opsional) broadcast event
            if (class_exists(\App\Events\ForumUserBanned::class)) {
                event(new \App\Events\ForumUserBanned($ban));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil di-ban',
                'data' => $this->banPayload($ban),
            ]);
        });
    }

    /**
     * Kick a user from the forum (temporary ban + logout).
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function kickUser(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->canModerateForum()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan untuk kick user'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['status' => 'error', 'message' => 'Tidak bisa kick diri sendiri'], 422);
        }

        $reason = $request->input('reason', 'Kicked from forum');

        return DB::transaction(function () use ($user, $reason, $currentUser) {
            // Hapus ban sebelumnya
            ForumBan::where('user_id', $user->id)->update(['is_active' => false]);

            // Buat ban kick (temporary dengan durasi pendek, misal 5 menit)
            $ban = ForumBan::create([
                'user_id' => $user->id,
                'banned_by' => $currentUser->id,
                'ban_type' => 'kick',
                'reason' => $reason,
                'expires_at' => now()->addMinutes(5),
                'is_active' => true,
            ]);

            // Force logout
            $this->forceLogoutUser($user);

            // (Opsional) broadcast event
            if (class_exists(\App\Events\ForumUserKicked::class)) {
                event(new \App\Events\ForumUserKicked($ban));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil di-kick',
                'data' => $this->banPayload($ban),
            ]);
        });
    }

    /**
     * Unban a user from the forum.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function unbanUser(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->canModerateForum()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan untuk unban user'], 403);
        }

        return DB::transaction(function () use ($user, $currentUser) {
            $updated = ForumBan::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    // Tambahkan kolom unbanned_by dan unbanned_at jika ada di schema
                    // 'unbanned_by' => $currentUser->id,
                    // 'unbanned_at' => now(),
                ]);

            if ($updated === 0) {
                return response()->json(['status' => 'error', 'message' => 'User tidak di-ban'], 404);
            }

            // (Opsional) broadcast event
            if (class_exists(\App\Events\ForumUserUnbanned::class)) {
                event(new \App\Events\ForumUserUnbanned($user->id));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil di-unban',
                'data' => ['user_id' => $user->id],
            ]);
        });
    }

    /**
     * Toggle forum open/closed status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleForum(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->canModerateForum()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan'], 403);
        }

        $settings = ForumSetting::current() ?? ForumSetting::firstOrCreate([]);

        $settings->is_open = !$settings->is_open;
        $settings->save();

        // (Opsional) broadcast event
        if (class_exists(\App\Events\ForumToggled::class)) {
            event(new \App\Events\ForumToggled($settings->is_open));
        }

        return response()->json([
            'status' => 'success',
            'message' => $settings->is_open ? 'Forum dibuka' : 'Forum ditutup',
            'data' => ['is_open' => $settings->is_open],
        ]);
    }

    /**
     * Get forum status.
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        $settings = ForumSetting::current();

        $isBanned = $user->isBannedFromForum();
        $ban = null;
        if ($isBanned) {
            $ban = ForumBan::where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->first();
        }

        return response()->json([
            'is_open' => $settings?->is_open ?? true,
            'is_banned' => $isBanned,
            'ban' => $ban ? $this->banPayload($ban) : null,
            'can_moderate' => $user->canModerateForum(),
        ]);
    }

    /**
     * Force logout user.
     *
     * Jika session driver = database maka hapus semua sesi user tsb.
     * Jika pakai Sanctum untuk API maka revoke token.
     *
     * @param  User  $user
     * @return void
     */
    protected function forceLogoutUser(User $user): void
    {
        // Jika session driver = database → hapus semua sesi user tsb
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }

        // Jika pakai Sanctum untuk API → revoke token
        if (class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
            \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();
        }
    }

    private function banPayload(ForumBan $ban): array
    {
        $type = 'temporary';  // Default
        if ($ban->ban_type === 'permanent') {
            $type = 'permanent';
        } elseif ($ban->ban_type === 'kick') {
            $type = 'kick';
        }

        return [
            'type' => $type,
            'reason' => $ban->reason,
            'expires_at' => $ban->expires_at?->toISOString(),
            'expires_at_local' => $ban->expires_at?->format('Y-m-d H:i:s'),
            'remaining_seconds' => $ban->expires_at
                ? now()->diffInSeconds($ban->expires_at, false) : null,
        ];
    }

    private function formatMessage(ForumMessage $message, ?User $currentUser = null): array
    {
        $currentUser = $currentUser ?: Auth::user();

        // URL lampiran (pakai accessor kalau ada; fallback ke disk public)
        $attachmentUrl = null;
        if (method_exists($message, 'getAttachmentUrlAttribute')) {
            $attachmentUrl = $message->attachment_url;
        } elseif ($message->attachment) {
            $attachmentUrl = Storage::disk('public')->url($message->attachment);
        }

        $data = [
            'id' => $message->id,
            'message' => $message->message,
            'message_type' => $message->message_type,
            'metadata' => (array) $message->metadata,
            'attachment_url' => $attachmentUrl,

            'user' => [
                'id' => $message->user?->id,
                'name' => $message->user?->name,
                'role_id' => $message->user?->role_id,
                'is_admin' => (bool) ($message->user && method_exists($message->user, 'isAdmin') ? $message->user->isAdmin() : false),
                'is_moderator' => (bool) ($message->user && method_exists($message->user, 'isModerator') ? $message->user->isModerator() : false),
            ],

            'reply_to' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'message' => Str::limit((string) $message->replyTo->message, 50),
                'user_name' => $message->replyTo->user?->name,
            ] : null,

            'created_at' => optional($message->created_at)->toISOString(),
            'formatted_time' => method_exists($message, 'getFormattedCreatedAtAttribute')
                ? $message->formatted_created_at
                : optional($message->created_at)->format('Y-m-d H:i:s'),

            'can_delete' => $currentUser ? (bool) ($message->canBeDeletedBy($currentUser) ?? false) : false,
        ];

        // Reactions
        $reactions = $message->relationLoaded('reactions')
            ? $message->reactions
            : MessageReaction::where('message_id', $message->id)->get(['emoji', 'user_id']);

        $data['reactions'] = collect($reactions)->map(fn($r) => [
            'emoji' => $r->emoji,
            'user_id' => $r->user_id,
        ])->values();

        // Poll (jika ada)
        if ($message->poll) {
            $poll = $message->poll;
            $data['poll'] = [
                'id' => $poll->id,
                'question' => $poll->question,
                'options' => $poll->options,
                'multiple_choice' => (bool) $poll->multiple_choice,
                'expires_at' => optional($poll->expires_at)->toISOString(),
                'is_expired' => method_exists($poll, 'isExpired') ? (bool) $poll->isExpired() : false,
                'vote_counts' => $poll->vote_counts ?? [],
                'total_votes' => (int) ($poll->total_votes ?? 0),
                'user_votes' => ($currentUser && method_exists($poll, 'getUserVotes')) ? (array) $poll->getUserVotes($currentUser) : [],
                'has_voted' => ($currentUser && method_exists($poll, 'hasUserVoted')) ? (bool) $poll->hasUserVoted($currentUser) : false,
            ];
        }

        return $data;
    }
}
