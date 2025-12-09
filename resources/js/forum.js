import { initEmojiPicker } from './ui/emoji.js';
// Fetch emoji data
let emojiData = {};
fetch('./data/emojis.json')
    .then(response => response.json())
    .then(data => {
        emojiData = data;
        initEmojiPicker(emojiData);
    })
    .catch(error => console.error('Error loading emoji data:', error));

document.addEventListener("DOMContentLoaded", () => {
    const forumWidget = document.getElementById("forum-widget");
    const openBtn = document.getElementById("open-forum");
    const closeBtn = document.getElementById("close-forum");

    const chatBox = document.getElementById("chat-box");
    const sendBtn = document.getElementById("send-button");
    const messageInput = document.getElementById("message-input");

    const attachmentToggle = document.getElementById("attachment-toggle");
    const attachmentMenu = document.getElementById("attachment-menu");
    const attachmentPreview = document.getElementById("attachment-preview");
    const attachmentInfo = document.getElementById("attachment-info");

    const iconVoice = document.getElementById("icon-voice");
    const iconSend = document.getElementById("icon-send");

    const replyPreview = document.getElementById("reply-preview");
    const cancelReplyBtn = document.getElementById("cancel-reply");

    const voiceRecording = document.getElementById("voice-recording");
    const recordingTime = document.getElementById("recording-time");
    const contactModal = document.getElementById("contact-modal");
    const locationModal = document.getElementById("location-modal");

    const contextMenu = document.getElementById("context-menu");
    const contextReply = document.getElementById("context-reply");
    const contextDelete = document.getElementById("context-delete");
    const contextReport = document.getElementById("context-report");
    
    const reportModal = document.getElementById("report-modal");

    const onlineEl = document.getElementById("online-count");
    const input = document.getElementById("message-input");
    const maxHeight = 160;

    input.addEventListener("input", () => {
        input.style.height = "auto";
        const newHeight = Math.min(input.scrollHeight, maxHeight);
        input.style.height = newHeight + "px";

        if (input.scrollHeight > maxHeight) {
            input.classList.remove("overflow-y-hidden");
            input.classList.add("overflow-y-auto");
        } else {
            input.classList.remove("overflow-y-auto");
            input.classList.add("overflow-y-hidden");
        }
    });

    let onlineNow = 0;
    let onlineInterval = null;
    let heartbeatInterval = null;
    let presenceChannelName = "presence-forum";
    let presenceJoined = false;
    let messagesMap = new Map();
    let recordingStream;

    // Reaction set
    const REACTION_SET = ["👍", "❤️", "😂", "😮", "😢", "🙏"];

    // Reaction handling
    async function reactToMessage(messageId, emoji) {
        try {
            const res = await fetch(`/forum/messages/${messageId}/react`, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                },
                body: JSON.stringify({ emoji }),
            });
            const data = await res.json();
            if (data.status === "success") {
                setReaction(messageId, emoji);
            } else {
                showError(data.message || "Gagal memberi reaksi");
            }
        } catch (e) {
            console.error(e);
            showError("Gagal memberi reaksi");
        }
    }

    function showError(message) {
        alert(message);
        console.warn("showError:", message);
    }

    function setReaction(messageId, emoji) {
        // Implement UI update logic for reactions
        console.log(`Setting reaction ${emoji} for message ${messageId}`);
    }

    chatBox.addEventListener("click", (e) => {
        const chip = e.target.closest(".reaction-chip");
        if (chip) reactToMessage(chip.dataset.messageId, chip.dataset.emoji);
    });

    // === Moderation row (Kick / Ban) di context menu ===
    async function fetchBanAware(url, options = {}) {
        const res = await fetch(url, {
            credentials: "same-origin",
            ...options,
        });
        if (res.status === 403) {
            let data = null;
            try {
                data = await res.json();
            } catch {}
            if (data?.ban) {
                const b = data.ban;
                const msg =
                    b.type === "permanent"
                        ? `Anda diban permanen. Alasan: ${b.reason ?? "-"}`
                        : `Anda diban (${b.type}). Sisa: ${formatDuration(
                              b.remaining_seconds
                          )}. Berakhir: ${b.expires_at_local}`;
                alert(msg); // ganti dengan modal UI kamu
                // optional: redirect ke halaman login/home
                location.href = "/";
            } else if (data?.message) {
                alert(data.message);
            }
        }
        return res;
    }

    function showBanDialog(ban) {
        const txt =
            ban.type === "permanent"
                ? "Anda diban permanen dari forum."
                : `Anda diban sementara. Sisa waktu: ${formatDuration(
                      ban.remaining_seconds
                  )} (berakhir ${ban.expires_at_local}).`;
        alert(txt); // ganti dengan modal cantikmu
    }

    function formatRemaining(sec) {
        if (sec == null) return "permanent";
        const h = Math.floor(sec / 3600),
            m = Math.floor((sec % 3600) / 60),
            s = sec % 60;
        return [h, m, s].map((v) => String(v).padStart(2, "0")).join(":");
    }

    let moderationRow = null;
    function ensureModerationRow() {
        let row = document.getElementById("moderation-row");
        if (!row) {
            row = document.createElement("div");
            row.id = "moderation-row";
            // wrapper + garis atas biar terpisah dari menu utama
            row.className = "hidden border-t border-gray-200 mt-2 pt-2";

            const btnBase =
                "mod-btn px-3 py-1.5 rounded-2xl text-sm font-medium " +
                "bg-gray-100 hover:bg-gray-200 focus:outline-none " +
                "focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 " +
                "disabled:opacity-60 disabled:cursor-not-allowed";

            row.innerHTML = `
      <div class="flex flex-wrap items-center gap-2">
        <button class="${btnBase}" data-action="kick" aria-label="Kick 5 minutes">
          <span class="font-medium">Kick</span>
          <span class="text-xs opacity-70 ml-1">(5m)</span>
        </button>

        <button class="${btnBase}" data-action="ban1h" aria-label="Ban 1 hour">
          <span class="font-medium">Ban</span>
          <span class="text-xs opacity-70 ml-1">1h</span>
        </button>

        <button class="${btnBase}" data-action="ban24h" aria-label="Ban 24 hours">
          <span class="font-medium">Ban</span>
          <span class="text-xs opacity-70 ml-1">24h</span>
        </button>

        <button class="${btnBase}" data-action="banperm" aria-label="Ban permanent">
          <span class="font-medium">Ban</span>
          <span class="text-xs opacity-70 ml-1">Permanent</span>
        </button>

        <button class="${btnBase}" data-action="unban" aria-label="Unban user">
          <span class="font-medium">Unban</span>
        </button>
      </div>
    `;

            // taruh di dalam contextMenu kamu
            contextMenu.appendChild(row);
        }

        // pastikan tombol tetap punya kelas dasar bila sudah ada sebelumnya
        row.querySelectorAll(".mod-btn").forEach((b) => {
            b.classList.add(
                "px-3",
                "py-1.5",
                "rounded-2xl",
                "text-sm",
                "font-medium",
                "bg-gray-100",
                "hover:bg-gray-200",
                "focus:outline-none",
                "focus:ring-2",
                "focus:ring-emerald-500",
                "focus:ring-offset-2",
                "disabled:opacity-60",
                "disabled:cursor-not-allowed"
            );
        });

        return row;
    }

    let reactionsRow = null;
    function ensureReactionsRow() {
        if (reactionsRow) return reactionsRow;
        reactionsRow = document.createElement("div");
        reactionsRow.id = "context-reactions";
        reactionsRow.className =
            "px-2 pt-2 pb-2 border-b flex items-center gap-1";
        reactionsRow.innerHTML = REACTION_SET.map(
            (emo) =>
                `<button type="button" class="px-2 py-1 text-xl leading-none hover:bg-gray-100 rounded reaction-choose" data-emoji="${emo}">${emo}</button>`
        ).join("");
        contextMenu.prepend(reactionsRow);
        return reactionsRow;
    }

    // agregasi reaksi -> [{emoji, count, reacted_by_me}]
    function aggregateReactions(msg) {
        const raw = msg.reactions || msg.reactions_raw || [];
        const map = new Map();
        for (const r of raw) {
            const key = r.emoji || r.reaction || r.symbol;
            if (!key) continue;
            const item = map.get(key) || {
                emoji: key,
                count: 0,
                reacted_by_me: false,
            };
            item.count++;
            if (
                currentUser &&
                (r.user_id === currentUser.id || r.user?.id === currentUser.id)
            )
                item.reacted_by_me = true;
            map.set(key, item);
        }
        return Array.from(map.values());
    }

    // delegasi klik chip reaksi (toggle on/off)
    chatBox?.addEventListener("click", (e) => {
        const chip = e.target.closest(".reaction-chip");
        if (chip) {
            const mid = chip.dataset.messageId;
            const emo = chip.dataset.emoji;
            reactToMessage(mid, emo);
        }
    });

    function setOnline(n) {
        onlineNow = Math.max(0, n | 0);
        if (onlineEl) onlineEl.textContent = `online ${onlineNow}`;
    }

    // --- Jika pakai Laravel Echo (presence channel) ---
    function startEchoPresence() {
        if (!window.Echo || presenceJoined) return false;
        try {
            Echo.join(presenceChannelName)
                .here((users) => setOnline(users.length))
                .joining(() => setOnline(onlineNow + 1))
                .leaving(() => setOnline(onlineNow - 1))
                .error((err) => console.warn("Echo presence error:", err));
            presenceJoined = true;
            return true;
        } catch (e) {
            console.warn("Echo join gagal, fallback ke polling", e);
            return false;
        }
    }
    function stopEchoPresence() {
        if (!window.Echo || !presenceJoined) return;
        try {
            Echo.leave(presenceChannelName);
        } catch (e) {}
        presenceJoined = false;
    }

    // --- Fallback polling (butuh endpoint sederhana di backend) ---
    async function refreshOnline() {
        try {
            const res = await fetch("/forum/online-count", {
                credentials: "same-origin",
                headers: { Accept: "application/json" },
            });
            if (!res.ok) throw 0;
            const data = await res.json();
            setOnline(data.count ?? 0);
        } catch (e) {
            // diamkan jika gagal
        }
    }
    async function sendHeartbeat() {
        try {
            await fetchBanAware("/forum/online-heartbeat", {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                    Accept: "application/json",
                },
            });
        } catch (e) {}
    }
// ===== Poll Modal =====
 // ===== Poll Modal =====
const pollModal = document.getElementById('poll-modal');
const pollQ = document.getElementById('poll-q');
const pollOptsEl = document.getElementById('poll-opts');
const pollMulti = document.getElementById('poll-multi');

document.getElementById('poll-multi').addEventListener('change', function () {
    const pollMode = document.getElementById('poll-mode');
    pollMode.textContent = this.checked ? 'Pilih satu atau lebih' : 'Pilih satu';
});

function openPollModal() {
    pollQ.value = "";
    pollOptsEl.innerHTML = "";
    addOptRow(); addOptRow(); // Minimal 2 opsi
    pollMulti.checked = true;

    pollModal.classList.remove('hidden');
    pollModal.classList.add('flex');
    setTimeout(() => pollQ.focus(), 0);
}

function closePollModal() {
    pollModal.classList.add('hidden');
    pollModal.classList.remove('flex');
}

function addOptRow(value = "") {
    const rows = pollOptsEl.querySelectorAll('input[data-opt]');
    if (rows.length >= 10) {
        showError("Maksimum 10 opsi diperbolehkan.");
        return;
    }
    const id = `opt-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
    const row = document.createElement('div');
    row.className = "flex items-center gap-2";
    row.innerHTML = `
        <input type="text" data-opt class="flex-1 border rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="+ Add option" value="${value}">
        <button type="button" aria-label="Remove" class="shrink-0 text-gray-400 hover:text-red-600">✕</button>
    `;
    row.querySelector('button').onclick = () => {
        if (rows.length <= 2) return;
        row.remove();
    };
    pollOptsEl.appendChild(row);
}

document.getElementById('btn-poll')?.addEventListener('click', openPollModal);
document.getElementById('poll-add')?.addEventListener('click', () => addOptRow());
document.getElementById('poll-cancel')?.addEventListener('click', closePollModal);

document.getElementById('poll-send')?.addEventListener('click', async () => {
    const q = (pollQ.value || "").trim();
    const opts = [...pollOptsEl.querySelectorAll('input[data-opt]')]
        .map(i => i.value.trim()).filter(Boolean);
    if (!q) return showError("Pertanyaan wajib diisi");
    if (opts.length < 2) return showError("Minimal 2 opsi diperlukan");
    if (opts.length > 10) return showError("Maksimum 10 opsi diperbolehkan.");

    try {
        const res = await fetch("/forum/polls", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ question: q, options: opts, multiple_choice: !!pollMulti.checked }),
            credentials: "same-origin",
            redirect: "manual",
        });

        const raw = await res.text();
        let data = null;
        try { data = JSON.parse(raw); } catch (_e) {
            throw new Error(`Server ${res.status}. Body: ${raw.slice(0, 120)}…`);
        }

        if (!res.ok || data?.status !== "success") {
            throw new Error(data?.message || `Gagal membuat poll (HTTP ${res.status})`);
        }

        closePollModal();
        attachmentMenu?.classList.add("hidden");
        showSuccess("Poll berhasil dibuat!");
        loadMessages?.();
    } catch (e) {
        console.error(e);
        showError(e.message || "Gagal membuat poll");
    }
});
  function addOptRow(value="") {
    const id = `opt-${Date.now()}-${Math.random().toString(36).slice(2,7)}`;
    const row = document.createElement('div');
    row.className = "flex items-center gap-2";
    row.innerHTML = `
      <input type="text" data-opt class="flex-1 border rounded-lg px-3 py-2 text-sm
             focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="+ Add option" value="${value}">
      <button type="button" aria-label="Remove" class="shrink-0 text-gray-400 hover:text-red-600">✕</button>
    `;
    row.querySelector('button').onclick = () => {
      // jangan biarkan kurang dari 2 opsi
      if (pollOptsEl.querySelectorAll('input[data-opt]').length <= 2) return;
      row.remove();
    };
    pollOptsEl.appendChild(row);
  }

  // === Vote Poll ===
  
  window.votePoll = async function (pollId) {
    const wrap = document.getElementById(`poll_${pollId}`);
    if (!wrap) {
        console.error("Poll wrapper not found for ID:", pollId);
        return;
    }

    const inputs = [...wrap.querySelectorAll('input[name="poll_' + pollId + '"]')];
    const option_indexes = inputs
        .filter(i => i.checked)
        .map(i => parseInt(i.value))
        .filter(n => Number.isFinite(n));

    const prevVotes = JSON.parse(wrap.dataset.userVotes || "[]");

    console.log("Voting for poll", pollId, "with options", option_indexes, "prev", prevVotes);

    try {
        const res = await fetch(`/forum/polls/${pollId}/vote`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ option_indexes, prev_votes: prevVotes }),
            credentials: "same-origin",
            redirect: "manual",
        });

        const data = await res.json();

        if (!res.ok || data?.status !== "success") {
            throw new Error(data?.message || `Vote gagal (HTTP ${res.status})`);
        }

        wrap.dataset.userVotes = JSON.stringify(data.user_votes || []);
        inputs.forEach(i => {
            i.checked = (data.user_votes || []).includes(parseInt(i.value));
        });

        showSuccess("Vote diperbarui!");
        loadMessages?.();
    } catch (e) {
        console.error(e);
        showError(e.message || "Vote gagal");
    }
};

  // === Online Tracking ===
  function startPolling() {
    refreshOnline();
    onlineInterval = setInterval(refreshOnline, 15000); // 15s
    sendHeartbeat();
    heartbeatInterval = setInterval(sendHeartbeat, 25000); // 25s
  }
  function stopPolling() {
    if (onlineInterval) {
      clearInterval(onlineInterval);
      onlineInterval = null;
    }
    if (heartbeatInterval) {
      clearInterval(heartbeatInterval);
      heartbeatInterval = null;
    }
  }
  function startOnlineTracking() {
    const ok = startEchoPresence();
    if (!ok) startPolling();
  }
  function stopOnlineTracking() {
    stopEchoPresence();
    stopPolling();
    setOnline(0);
  }
    function roleIsModOrAdmin(u) {
        const r = Number(u?.role_id ?? u?.roleId ?? 0);
        return r === 1 || r === 3 || !!u?.is_admin || !!u?.is_moderator;
    }

    function showContextMenu(event, messageId, userId) {
        event.preventDefault();
        if (!currentUser) {
            showError("Harap login");
            return;
        }

        const fromMe = currentUser.id === userId;
        const iCanModerate = roleIsModOrAdmin(currentUser);

        const itemEl = document.getElementById(`msg-${messageId}`);
        const senderIsAdmin = itemEl?.dataset.senderAdmin === "1";
        const senderIsMod = itemEl?.dataset.senderMod === "1";

        const canDelete = fromMe || (iCanModerate && !senderIsAdmin);
        const canModerateTarget =
            iCanModerate && !fromMe && !senderIsAdmin && !senderIsMod;

        // 1) Tampilkan menu "offscreen" agar bisa diukur tanpa flicker
        contextMenu.classList.remove("hidden");
        contextMenu.classList.add("show");
        contextMenu.style.position = "fixed";
        contextMenu.style.visibility = "hidden";
        contextMenu.style.left = "-9999px";
        contextMenu.style.top = "-9999px";

        // 2) SUSUN ISI MENU (finalkan DOM) — baru ukur setelah ini

        // Reactions
        ensureReactionsRow();
        reactionsRow.querySelectorAll(".reaction-choose").forEach((btn) => {
            btn.onclick = () => {
                reactToMessage(messageId, btn.dataset.emoji);
                contextMenu.classList.add("hidden");
                contextMenu.classList.remove("show");
            };
        });

        // Reply
        contextReply.onclick = () => {
            const messageEl = document.getElementById(`msg-${messageId}`);
            const messageText = messageEl.querySelector("p")?.textContent || "";
            const userName =
                messageEl.querySelector(".text-emerald-700, .text-blue-700")
                    ?.textContent || "User";
            setReplyTo(messageId, userName, messageText);
            contextMenu.classList.add("hidden");
            contextMenu.classList.remove("show");
        };

        // Delete (show/hide + binding)
        if (canDelete) {
            contextDelete.classList.remove("hidden");
            contextDelete.onclick = () => {
                deleteMessage(messageId);
                contextMenu.classList.add("hidden");
                contextMenu.classList.remove("show");
            };
        } else {
            contextDelete.classList.add("hidden");
            contextDelete.onclick = null;
        }

        // Report (show/hide + binding) - hanya untuk user biasa, tidak bisa report diri sendiri atau admin/mod
        if (contextReport) {
            const canReport = !fromMe && !iCanModerate && !senderIsAdmin && !senderIsMod;
            if (canReport) {
                contextReport.classList.remove("hidden");
                contextReport.onclick = () => {
                    showReportModal(messageId, userId);
                    contextMenu.classList.add("hidden");
                    contextMenu.classList.remove("show");
                };
            } else {
                contextReport.classList.add("hidden");
                contextReport.onclick = null;
            }
        }

        // Moderation bar (show/hide + sekali binding)
        const modBar = ensureModerationRow();
        if (canModerateTarget) {
            modBar.classList.remove("hidden");
            if (!modBar.__bound) {
                modBar.__bound = true;
                const mapAction = {
                    kick: () => moderateUser(userId, "kick"),
                    ban1h: () => moderateUser(userId, "ban", 1),
                    ban24h: () => moderateUser(userId, "ban", 24),
                    banperm: () => moderateUser(userId, "ban", "permanent"),
                    unban: () => moderateUser(userId, "unban"),
                };
                modBar.addEventListener("click", async (ev) => {
                    const btn = ev.target.closest(".mod-btn");
                    if (!btn) return;
                    const run = mapAction[btn.dataset.action];
                    if (!run) {
                        showError("Tombol moderasi tidak dikenali.");
                        return;
                    }
                    ev.preventDefault();
                    const prevHTML = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = "Memproses…";
                    try {
                        await run();
                        contextMenu.classList.add("hidden");
                        contextMenu.classList.remove("show");
                    } catch (e) {
                        console.error(e);
                        showError(e?.message || "Aksi moderasi gagal");
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = prevHTML;
                    }
                });
            }
        } else {
            modBar.classList.add("hidden");
        }

        // 3) Ukur & posisikan SETELAH DOM final (pakai rAF untuk memastikan layout settle)
        requestAnimationFrame(() => {
            const menuRect = contextMenu.getBoundingClientRect();
            const wRect = forumWidget.getBoundingClientRect();
            const PAD = 12;

            let x = wRect.right - menuRect.width - PAD;
            let y = wRect.top + wRect.height / 2 - menuRect.height / 2;

            const minX = wRect.left + PAD;
            const maxX = wRect.right - menuRect.width - PAD;
            const minY = wRect.top + PAD;
            const maxY = wRect.bottom - menuRect.height - PAD;

            x = Math.max(minX, Math.min(x, maxX));
            y = Math.max(minY, Math.min(y, maxY));

            contextMenu.style.left = `${x}px`;
            contextMenu.style.top = `${y}px`;
            contextMenu.style.visibility = "visible";
        });
    }

    // Tutup saat klik di luar
    document.addEventListener("click", (e) => {
        if (!contextMenu.contains(e.target)) {
            contextMenu.classList.add("hidden");
            contextMenu.classList.remove("show");
        }
    });

    // Listener klik kanan di item pesan (sudah OK)
    chatBox.addEventListener("contextmenu", (event) => {
        const messageItem = event.target.closest(".msg-item");
        if (messageItem) {
            const messageId = parseInt(messageItem.dataset.messageId, 10);
            const userId = parseInt(messageItem.dataset.userId, 10);
            showContextMenu(event, messageId, userId);
        }
    });

const VN = { current: null, map: new Map() };

const SVG_PLAY  = `
<svg xmlns="http://www.w3.org/2000/svg" 
     class="w-6 h-6 fill-current" viewBox="0 0 24 24">
  <path d="M8 5v14l11-7z"/>
</svg>`;

const SVG_PAUSE = `
<svg xmlns="http://www.w3.org/2000/svg" 
     class="w-6 h-6 fill-current" viewBox="0 0 24 24">
  <path d="M6 5h4v14H6zM14 5h4v14h-4z"/>
</svg>`;

function fmtTime(s=0){
  s = Math.max(0, Math.floor(s));
  const m = Math.floor(s/60);
  const ss = String(s%60).padStart(2,"0");
  return `${m}:${ss}`;
}

function ensureAudio(wrapper){
  const id  = wrapper.dataset.audioId;
  const src = wrapper.dataset.src;
  if (VN.map.has(id)) return VN.map.get(id);

  const audio = new Audio(src);
  audio.preload = "metadata";

  const btn  = wrapper.querySelector(".vn-play");
  const seek = wrapper.querySelector(".vn-seek");
  const cur  = wrapper.querySelector(".vn-current");
  const bars = wrapper.querySelectorAll(".vn-wave span");

  // set ikon awal
  btn.innerHTML = SVG_PLAY;

  audio.addEventListener("loadedmetadata", () => {
    cur.textContent = fmtTime(audio.duration);
  });
  audio.addEventListener("timeupdate", () => {
    const p = (audio.currentTime / (audio.duration || 1)) * 100;
    if (seek) seek.value = isFinite(p) ? p : 0;
    cur.textContent = fmtTime(audio.currentTime);
    // animasi bar sederhana
    const activeBars = Math.floor((p/100) * bars.length);
    bars.forEach((bar,i)=> bar.style.height = (i < activeBars ? 12 : 6) + "px");
  });
  audio.addEventListener("ended", () => {
    btn.innerHTML = SVG_PLAY;
    if (seek) seek.value = 0;
    cur.textContent = fmtTime(audio.duration);
    bars.forEach(bar=>bar.style.height="6px");
    if (VN.current === audio) VN.current = null;
  });

  VN.map.set(id, { audio, btn, seek, cur, bars });
  return VN.map.get(id);
}

function pauseCurrent(){
  if (VN.current && !VN.current.paused){
    VN.current.pause();
    VN.map.forEach(({audio, btn})=>{
      if (audio === VN.current) btn.innerHTML = SVG_PLAY;
    });
    VN.current = null;
  }
}

// CLICK
chatBox.addEventListener("click",(e)=>{
  const wrap = e.target.closest(".vn-player");
  if(!wrap) return;

  // kalau klik tombol play, hentikan propagasi ke handler lain
  const playBtn = e.target.closest(".vn-play");
  if(!playBtn) return;               // biar handler lain di luar VN tetap jalan
  e.preventDefault();
  e.stopPropagation();

  const {audio, btn} = ensureAudio(wrap);

  if(audio.paused){
    pauseCurrent();
    audio.play();
    btn.innerHTML = SVG_PAUSE;
    VN.current = audio;
  }else{
    audio.pause();
    btn.innerHTML = SVG_PLAY;
    VN.current = null;
  }
});

// RANGE INPUT
chatBox.addEventListener("input",(e)=>{
  const seek = e.target.closest(".vn-seek");
  if(!seek) return;
  // cegah bentrok dengan handler input global lain
  e.stopPropagation();

  const wrap = e.target.closest(".vn-player");
  const {audio, cur} = ensureAudio(wrap);
  const pct = Number(seek.value)/100;
  if(isFinite(audio.duration)){
    audio.currentTime = pct*audio.duration;
    cur.textContent = fmtTime(audio.currentTime);
  }
});

// (opsional) hentikan mousedown pada tombol agar tidak memicu drag/selection global
chatBox.addEventListener("mousedown",(e)=>{
  if (e.target.closest(".vn-play")) {
    e.stopPropagation();
  }
});


    // State management
    let currentUser = null;
    let replyToMessage = null;
    let isForumOpen = false;
    let currentAttachment = null;
    let mediaRecorder = null;

    let recordingTimer = null;

    let isFocusing = false; // lagi fokus ke pesan hasil jump
    let focusTimer = null;

    let recElapsedMs = 0;  // ⏱ total durasi rekaman (akumulatif)
    let shouldSaveRecording = false; 
// ===== CAMERA (live preview) =====
let camModal, camVideo, camCanvas, camCtx, camPlayback, camStream = null;
let camUseBack = true;
let camMode = "video";             // "photo" | "video"
let camRecorder = null;
let camChunks = [];
let camVideoBlob = null;           // Blob video (webm/mp4)
let camPhotoBlob = null;           // Blob foto (jpeg)
let recTimer = null, recSec = 0;
let recCanvas = null, recCtx = null, recStream = null, recRAF = 0;

const IS_DESKTOP = !/Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
let isRecording = false;
let autoSendAfterStop = false;
// === WA-style hold-to-record & gestures ===
let holdStart = null;
let startX = 0, startY = 0;
let locked = false;
let canceled = false;
const CANCEL_THRESHOLD = 80; // px geser kiri = batal
const LOCK_THRESHOLD   = 60; // px geser atas = lock
const MIN_SEND_MS      = 350; // minimal durasi agar auto-send

function resetHoldFlags() {
  holdStart = null; locked = false; canceled = false;
  document.getElementById('lock-indicator')?.classList.add('hidden');
}
sendBtn.addEventListener('pointerdown', (e) => {
  if (IS_DESKTOP) return;                          // ⛔ desktop: abaikan gesture
  if (e.pointerType !== 'touch') return;           // ⛔ non-touch
  if (sendBtn.dataset.mode !== 'voice') return;    // hanya mode mic
  e.preventDefault();
  resetHoldFlags();
  holdStart = Date.now();
  startX = e.clientX; startY = e.clientY;
  startVoiceRecording();
  attachmentMenu?.classList.add('hidden');
});

sendBtn.addEventListener('pointermove', (e) => {
  if (IS_DESKTOP || e.pointerType !== 'touch') return;
  if (!isRecording || holdStart == null) return;
  const dx = e.clientX - startX;
  const dy = e.clientY - startY;

  if (dx < -CANCEL_THRESHOLD && !locked) {
    canceled = true;
    cancelVoiceRecording();
    resetHoldFlags();
  }
  if (-dy > LOCK_THRESHOLD && !locked) {
    locked = true;
    document.getElementById('lock-indicator')?.classList.remove('hidden');
    try { sendBtn.releasePointerCapture(e.pointerId); } catch {}
  }
});

window.addEventListener('pointerup', (e) => {
  if (IS_DESKTOP || e.pointerType !== 'touch') return;
  if (!isRecording) return;
  if (locked) return;
  const dur = Date.now() - (holdStart || Date.now());
  autoSendAfterStop = !canceled && dur >= MIN_SEND_MS;
  stopVoiceRecording();
  resetHoldFlags();
});

// === Waveform ===
let audioCtx = null, analyser = null, waveRAF = 0;

function startWaveform(stream){
  try {
    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const src = audioCtx.createMediaStreamSource(stream);
    analyser = audioCtx.createAnalyser();
    analyser.fftSize = 1024;
    src.connect(analyser);

    const cvs = document.getElementById('recording-wave');
    const ctx = cvs?.getContext('2d');
    if (!cvs || !ctx) return;

    function draw(){
      const w = cvs.width  || cvs.clientWidth;
      const h = cvs.height || 28;
      if (cvs.width !== w) cvs.width = w;
      if (cvs.height!== h) cvs.height= h;

      const buf = new Uint8Array(analyser.frequencyBinCount);
      analyser.getByteTimeDomainData(buf);

      ctx.clearRect(0,0,w,h);
      ctx.beginPath();
      for (let x=0; x<w; x++){
        const i = Math.floor(x / w * buf.length);
        const y = (buf[i] / 255) * h;
        x===0 ? ctx.moveTo(x,y) : ctx.lineTo(x,y);
      }
      ctx.lineWidth = 2;
      ctx.strokeStyle = '#16a34a';
      ctx.stroke();

      waveRAF = requestAnimationFrame(draw);
    }
    cancelAnimationFrame(waveRAF);
    draw();
  } catch {}
}

function stopWaveform(){
  cancelAnimationFrame(waveRAF); waveRAF = 0;
  try { audioCtx?.close(); } catch {}
  audioCtx = null; analyser = null;
}


function lockCompose(lock) {
  if (!messageInput) return;
  if (messageInput.isContentEditable) {
    // simpan state awal ke dataset biar bisa dibalikin
    if (!messageInput.dataset._orig_ce) {
      messageInput.dataset._orig_ce = messageInput.getAttribute("contenteditable") || "true";
    }
    messageInput.setAttribute("contenteditable", lock ? "false" : (messageInput.dataset._orig_ce || "true"));
  } else {
    messageInput.readOnly = !!lock;
    messageInput.disabled = !!lock;
  }
  messageInput.classList.toggle("opacity-60", !!lock);
}


// ---------- INIT ----------
function initCameraDom() {
  camModal    = document.getElementById("camera-modal");
  camVideo    = document.getElementById("camera-video");
  camCanvas   = document.getElementById("camera-canvas");
  camPlayback = document.getElementById("camera-playback");
  if (camCanvas && !camCtx) camCtx = camCanvas.getContext("2d");

  // header buttons
  document.getElementById("camera-close")?.addEventListener("click", closeCameraModal);
  document.getElementById("camera-switch")?.addEventListener("click", switchCamera);
  document.getElementById("camera-mode-toggle")?.addEventListener("click", toggleCamMode);

  // controls
  document.getElementById("camera-capture")?.addEventListener("click", capturePhoto);
  document.getElementById("camera-record")?.addEventListener("click", startVideoRecording);
  document.getElementById("camera-stop")?.addEventListener("click", stopVideoRecording);
  document.getElementById("camera-retake")?.addEventListener("click", retake);
  document.getElementById("camera-use")?.addEventListener("click", useCaptured);

  // buka modal dari tombol camera di attachment bar
  document.getElementById("btn-camera")?.addEventListener("click", openCameraModal);

  // kalau panel forum ditutup, pastikan kamera mati juga
  document.getElementById("close-forum")?.addEventListener("click", () => {
    if (!camModal) return;
    camModal.classList.add("hidden"); camModal.classList.remove("flex");
    stopCamera();
  });

  // hide Flip di desktop
  if (IS_DESKTOP) document.getElementById("camera-switch")?.classList.add("hidden");
}
initCameraDom();

// ---------- OPEN/CLOSE ----------
async function openCameraModal() {
  try {
    await startCamera();
    camMode = camMode || "video";
    updateModeUI();
    camModal.classList.remove("hidden");
    camModal.classList.add("flex");
    showStage("live");
  } catch (e) {
    console.warn("getUserMedia gagal, fallback:", e);
    document.getElementById("camera-input")?.click();
  }
}

function closeCameraModal() {
  camModal.classList.add("hidden");
  camModal.classList.remove("flex");
  stopRecTimer();
  stopCamera();
  if (camRecorder && camRecorder.state === "recording") {
    try { camRecorder.stop(); } catch {}
  }
  // Extra cleanup untuk memastikan tidak ada stream leftover
  if (recStream) {
    recStream.getTracks().forEach(t => t.stop());
    recStream = null;
  }
  cancelAnimationFrame(recRAF);
  recRAF = 0;
  if (camPlayback.src) {
    URL.revokeObjectURL(camPlayback.src);
    camPlayback.src = '';
    camPlayback.load();
  }
  camChunks = [];
  camVideoBlob = null;
  camPhotoBlob = null;
  camRecorder = null;
}

// ---------- CAMERA STREAM ----------
async function startCamera() {
  await stopCamera();

  const constraints = {
    video: { facingMode: camUseBack ? { exact: "environment" } : "user" },
    audio: camMode === "video"
  };

  try {
    camStream = await navigator.mediaDevices.getUserMedia(constraints);
  } catch {
    camStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: camMode === "video" });
  }

  camVideo.srcObject = camStream;
  camVideo.playsInline = true;
  camVideo.muted = true;
  try { await camVideo.play(); } catch {}

  // --- atur mirror berdasarkan facingMode ---
  const vTrack = camStream.getVideoTracks?.()[0];
  const facing = vTrack?.getSettings?.().facingMode || (camUseBack ? "environment" : "user");
  // untuk front camera, set scaleX(-1) supaya TIDAK mirror (kanan tetap kanan)
  camVideo.style.transform = facing === "user" ? "scaleX(-1)" : "none";

  camPhotoBlob = null; camVideoBlob = null; camChunks = [];
  showStage("live");
}

async function stopCamera() {
  try { camStream?.getTracks().forEach(t => t.stop()); } catch {}
  if (camVideo) {
    try { camVideo.pause(); } catch {}
    camVideo.srcObject = null;
    camVideo.removeAttribute("src");
    camVideo.load?.();
  }
  camStream = null;
}

async function switchCamera() {
  if (IS_DESKTOP) return;
  camUseBack = !camUseBack;
  await startCamera();
}
function isCamRecording() {
  return !!(camRecorder && camRecorder.state === "recording");
}
function setCamControlsEnabled(enabled) {
  const btnMode = document.getElementById("camera-mode-toggle");
  const btnFlip = document.getElementById("camera-switch");
  [btnMode, btnFlip].forEach(btn => {
    if (!btn) return;
    btn.disabled = !enabled;
    btn.classList.toggle("opacity-50", !enabled);
    btn.classList.toggle("pointer-events-none", !enabled);
  });
}

// ---------- MODE ----------
function toggleCamMode() {
  if (isCamRecording()) {
    showError?.("Hentikan perekaman dulu untuk ganti ke Foto.");
    return;
  }
  camMode = camMode === "photo" ? "video" : "photo";
  updateModeUI();
  startCamera(); // restart (audio on/off)
}


function updateModeUI() {
  const btnMode = document.getElementById("camera-mode-toggle");
  const btnCap  = document.getElementById("camera-capture");
  const btnRec  = document.getElementById("camera-record");
  const btnStop = document.getElementById("camera-stop");
  const btnUse  = document.getElementById("camera-use");
  const btnRt   = document.getElementById("camera-retake");

  btnMode.textContent = "Mode: " + (camMode === "photo" ? "Foto" : "Video");

  // state awal
  btnCap.classList.toggle("hidden", camMode !== "photo");
  btnRec.classList.toggle("hidden", camMode !== "video");
  btnStop.classList.add("hidden");
  btnUse.classList.add("hidden");
  btnRt.classList.add("hidden");

  camCanvas.classList.add("hidden");
  camPlayback.classList.add("hidden");
  camVideo.classList.remove("hidden");
}

// ---------- PHOTO ----------
function capturePhoto() {
  const vw = camVideo.videoWidth  || 1280;
  const vh = camVideo.videoHeight || 720;
  camCanvas.width = vw; camCanvas.height = vh;

  const unmirrorPreview = camVideo.style.transform.includes("scaleX(-1)");
  const ctx = camCanvas.getContext("2d");
  ctx.setTransform(1,0,0,1,0,0);

  if (unmirrorPreview) {
    // kalau preview di-flip, gambar ke canvas juga di-flip agar hasilnya sama
    ctx.save();
    ctx.translate(vw, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(camVideo, 0, 0, vw, vh);
    ctx.restore();
  } else {
    ctx.drawImage(camVideo, 0, 0, vw, vh);
  }

  camCanvas.toBlob((b) => {
    camPhotoBlob = b;
    showStage("review-photo");
    stopCamera(); // Stop kamera setelah capture untuk fix bug
  }, "image/jpeg", 0.92);
}

// ---------- VIDEO ----------
function pickMime() {
  const list = [
    "video/mp4;codecs=avc1.42E01E,mp4a.40.2", // Safari/iOS jika bisa
    "video/webm;codecs=vp9,opus",
    "video/webm;codecs=vp8,opus",
    "video/webm"
  ];
  for (const t of list) {
    if (window.MediaRecorder?.isTypeSupported?.(t)) return t;
  }
  return "";
}

function startVideoRecording() {
  if (!camStream) return;

  // --- siapkan canvas perekaman (menangani mirror) ---
  const vw = camVideo.videoWidth  || 1280;
  const vh = camVideo.videoHeight || 720;
  if (!recCanvas) recCanvas = document.createElement('canvas');
  recCanvas.width = vw; recCanvas.height = vh;
  recCtx = recCanvas.getContext('2d');

  // apakah preview di-flip? (front camera)
  const unmirrorPreview = camVideo.style.transform.includes("scaleX(-1)");

  // loop gambar frame video -> canvas
  const draw = () => {
    recCtx.setTransform(1,0,0,1,0,0);
    if (unmirrorPreview) {
      recCtx.save();
      recCtx.translate(vw, 0);
      recCtx.scale(-1, 1);
      recCtx.drawImage(camVideo, 0, 0, vw, vh);
      recCtx.restore();
    } else {
      recCtx.drawImage(camVideo, 0, 0, vw, vh);
    }
    recRAF = requestAnimationFrame(draw);
  };
  draw();

  // ambil video stream dari canvas + gabungkan audio asli kamera
  const canvasVideo = recCanvas.captureStream(30); // 30fps
  const audioTracks = camStream.getAudioTracks ? camStream.getAudioTracks() : [];
  recStream = new MediaStream([ canvasVideo.getVideoTracks()[0], ...audioTracks ]);

  // --- recorder pada recStream (bukan camStream) ---
  const mime = pickMime();
  try {
    camRecorder = mime ? new MediaRecorder(recStream, { mimeType: mime }) : new MediaRecorder(recStream);
  } catch (e) {
    alert("Browser Anda belum mendukung perekaman video.");
    cancelAnimationFrame(recRAF);
    canvasVideo.getTracks().forEach(t=>t.stop());
    return;
  }

  camChunks = []; camVideoBlob = null;

  camRecorder.ondataavailable = (e) => { if (e.data?.size) camChunks.push(e.data); };
  camRecorder.onstop = () => {
    stopRecTimer();

    // hentikan loop & stream sementara
    cancelAnimationFrame(recRAF);
    recRAF = 0;
    canvasVideo.getTracks().forEach(t=>t.stop());
    if (recStream) recStream.getTracks().forEach(t=>t.stop());

    camVideoBlob = new Blob(camChunks, { type: camRecorder.mimeType || mime || "video/webm" });
    const url = URL.createObjectURL(camVideoBlob);
    camPlayback.src = url; camPlayback.load();
    showStage("review-video");
    stopCamera(); // Stop kamera setelah recording selesai untuk fix bug
  };

  camRecorder.start(250);
  startRecTimer();
  showStage("recording");

  // limit 60s
  camRecorder.__timer = setTimeout(() => {
    if (camRecorder?.state === "recording") stopVideoRecording();
  }, 60000);
}

function stopVideoRecording() {
  if (!camRecorder) return;
  try { camRecorder.stop(); } catch {}
  if (camRecorder.__timer) { clearTimeout(camRecorder.__timer); camRecorder.__timer = null; }
}

// ---------- STAGES ----------
function showStage(stage) {
  const btnCap  = document.getElementById("camera-capture");
  const btnRec  = document.getElementById("camera-record");
  const btnStop = document.getElementById("camera-stop");
  const btnUse  = document.getElementById("camera-use");
  const btnRt   = document.getElementById("camera-retake");
  const badge   = document.getElementById("rec-badge");
  const timer   = document.getElementById("rec-timer");

  if (stage === "live") {
    setCamControlsEnabled(true);
    btnCap.classList.toggle("hidden", camMode !== "photo");
    btnRec.classList.toggle("hidden", camMode !== "video");
    btnStop.classList.add("hidden"); btnUse.classList.add("hidden"); btnRt.classList.add("hidden");
    badge.classList.add("hidden");   timer.classList.add("hidden");
    camCanvas.classList.add("hidden"); camPlayback.classList.add("hidden"); camVideo.classList.remove("hidden");
  } else if (stage === "recording") {
    setCamControlsEnabled(false);
    btnCap.classList.add("hidden"); btnRec.classList.add("hidden"); btnStop.classList.remove("hidden");
    btnUse.classList.add("hidden");  btnRt.classList.add("hidden");
    badge.classList.remove("hidden"); timer.classList.remove("hidden");
    camCanvas.classList.add("hidden"); camPlayback.classList.add("hidden"); camVideo.classList.remove("hidden");
  } else if (stage === "review-photo") {
    setCamControlsEnabled(true);
    btnCap.classList.add("hidden"); btnRec.classList.add("hidden"); btnStop.classList.add("hidden");
    btnUse.classList.remove("hidden"); btnRt.classList.remove("hidden");
    badge.classList.add("hidden"); timer.classList.add("hidden");
    camVideo.classList.add("hidden"); camPlayback.classList.add("hidden"); camCanvas.classList.remove("hidden");
  } else if (stage === "review-video") {
    setCamControlsEnabled(true);
    btnCap.classList.add("hidden"); btnRec.classList.add("hidden"); btnStop.classList.add("hidden");
    btnUse.classList.remove("hidden"); btnRt.classList.remove("hidden");
    badge.classList.add("hidden"); timer.classList.add("hidden");
    camVideo.classList.add("hidden"); camCanvas.classList.add("hidden"); camPlayback.classList.remove("hidden");
  }
}


function retake() {
  camPhotoBlob = null; camVideoBlob = null; camChunks = [];
  camPlayback.src = ""; camPlayback.load();
  showStage("live");
  startCamera();
}

function useCaptured() {
  let file = null;
  if (camMode === "photo" && camPhotoBlob) {
    file = new File([camPhotoBlob], `photo-${Date.now()}.jpg`, { type: "image/jpeg" });
    currentAttachment = { file, type: "image", name: file.name, size: file.size };
  } else if (camMode === "video" && camVideoBlob) {
    const ext = camVideoBlob.type.includes("mp4") ? "mp4" : "webm";
    file = new File([camVideoBlob], `video-${Date.now()}.${ext}`, { type: camVideoBlob.type });
    currentAttachment = { file, type: "video", name: file.name, size: file.size };
  }
  if (file) {
    showAttachmentPreview?.();
    updateSendButton?.();
  }
  closeCameraModal();
}

// ---------- TIMER ----------
function startRecTimer(){
  const t = document.getElementById("rec-timer");
  recSec = 0;
  t.textContent = "0:00";
  recTimer = setInterval(() => {
    recSec++;
    const m = Math.floor(recSec/60);
    const s = String(recSec%60).padStart(2,"0");
    t.textContent = `${m}:${s}`;
  }, 1000);
}
function stopRecTimer(){ clearInterval(recTimer); recTimer = null; }
document.addEventListener("visibilitychange", () => {
  if (document.hidden) stopCamera();
});

    function isNearBottom(el, threshold = 80) {
        return el.scrollHeight - el.scrollTop - el.clientHeight < threshold;
    }

    window.jumpToMessage = async function (messageId) {
        const container = document.getElementById("chat-box");
        let target = document.getElementById(`msg-${messageId}`);

        // Mark fokus supaya loadMessages nggak memaksa scroll
        isFocusing = true;
        clearTimeout(focusTimer);
        focusTimer = setTimeout(() => {
            isFocusing = false;
        }, 12000); // lepas fokus setelah 12 detik

        if (!container) return;

        // Kalau target belum ada (mis. di luar batch 50), muat ulang lebih banyak
        if (!target) {
            await loadMessages(200); // muat 200 pesan
            target = document.getElementById(`msg-${messageId}`);
            if (!target) {
                showError("Pesan yang dituju tidak tersedia di riwayat ini.");
                isFocusing = false;
                return;
            }
        }

        // Scroll supaya target DI TENGAH
        target.scrollIntoView({
            behavior: "smooth",
            block: "center",
            inline: "nearest",
        });

        // Highlight kilat
        target.classList.add("jump-highlight");
        setTimeout(() => target.classList.remove("jump-highlight"), 1400);
    };

    // === Get Current User ===
    async function ensureCurrentUser() {
        try {
            const response = await fetch("/api/user", {
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.content,
                },
            });
            if (response.ok) {
                currentUser = await response.json();
            }
        } catch (error) {
            console.error("Error getting current user:", error);
        }

        if (!currentUser) await getCurrentUser();
        return !!currentUser;
    }

    if (openBtn && forumWidget) {
        openBtn.addEventListener("click", async () => {
            const ok = await ensureCurrentUser();
            if (!ok) {
                showError("Harap login terlebih dahulu");
                return;
            }
        });
    }

    // === Open & Close Widget ===
    if (openBtn && forumWidget) {
        openBtn.addEventListener("click", () => {
            if (isForumOpen) {
                forumWidget.classList.add("hidden");
                forumWidget.classList.remove("flex");
                isForumOpen = false;
                stopOnlineTracking();
            } else {
                forumWidget.classList.remove("hidden");
                forumWidget.classList.add("flex");
                isForumOpen = true;
                startOnlineTracking();
                loadMessages();
            }
        });
    }

    if (closeBtn && forumWidget) {
        closeBtn.addEventListener("click", () => {
            forumWidget.classList.add("hidden");
            forumWidget.classList.remove("flex");
            isForumOpen = false;
        });
    }
    // ====== ACCESS STATE ======
    const ACCESS = {
        AUTH_GUEST: "auth_guest",
        AUTH_OK: "auth_ok",
        MOD_OK: "mod_ok",
        MOD_KICKED: "mod_kicked",
        MOD_BANNED: "mod_banned",
    };

    let accessTimer = null; // untuk countdown

    function ensureAccessOverlay() {
        let ov = document.getElementById("access-overlay");
        if (ov) return ov;

        ov = document.createElement("div");
        ov.id = "access-overlay";
        ov.className = `
    hidden fixed inset-0 z-50
    bg-white/70 backdrop-blur-sm
    flex items-center justify-center
  `;
        ov.innerHTML = `
    <div class="max-w-md w-[92%] rounded-2xl shadow-xl bg-white border border-gray-200 p-5 text-center">
      <div id="access-icon" class="mx-auto mb-3 w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
        <svg class="w-6 h-6 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01M5.07 19h13.86A2 2 0 0021 17.13L13.93 4.87a2 2 0 00-3.86 0L3 17.13A2 2 0 005.07 19z"/>
        </svg>
      </div>
      <h3 id="access-title" class="text-lg font-semibold mb-1">Akses terkunci</h3>
      <p id="access-desc" class="text-sm text-gray-600 mb-4">Silakan login untuk melanjutkan.</p>
      <div id="access-cta" class="flex items-center justify-center gap-2"></div>
    </div>
  `;
        document.body.appendChild(ov);
        return ov;
    }

    function blurLock(enable) {
        // elemen-elemen utama aplikasi
        forumWidget.classList.toggle("pointer-events-none", !!enable);
        forumWidget.classList.toggle("blur-[1.5px]", !!enable);
        // izinkan scroll container luar supaya overlay bisa klik
    }

    function formatCountdown(ms) {
        if (ms <= 0) return "sebentar lagi…";
        const s = Math.floor(ms / 1000);
        const hh = Math.floor(s / 3600);
        const mm = Math.floor((s % 3600) / 60);
        const ss = s % 60;
        if (hh > 0) return `${hh}j ${mm}m ${ss}d`;
        if (mm > 0) return `${mm}m ${ss}d`;
        return `${ss}d`;
    }

    /**
     * state: {
     *   auth: "auth_guest" | "auth_ok",
     *   moderation: "mod_ok" | "mod_kicked" | "mod_banned",
     *   until?: number|Date|null,  // kapan berakhir (ms epoch)
     *   reason?: string
     * }
     */
    function applyAccessState(state) {
        const ov = ensureAccessOverlay();
        const title = ov.querySelector("#access-title");
        const desc = ov.querySelector("#access-desc");
        const cta = ov.querySelector("#access-cta");
        const icon = ov.querySelector("#access-icon");

        // bersihkan timer lama
        if (accessTimer) {
            clearInterval(accessTimer);
            accessTimer = null;
        }

        // default: tidak terkunci
        if (
            state.auth === ACCESS.AUTH_OK &&
            state.moderation === ACCESS.MOD_OK
        ) {
            ov.classList.add("hidden");
            blurLock(false);
            return;
        }

        // terkunci
        ov.classList.remove("hidden");
        blurLock(true);

        // reset CTA
        cta.innerHTML = "";

        // Belum login
        if (state.auth === ACCESS.AUTH_GUEST) {
            title.textContent = "Akses terkunci";
            desc.textContent = "Silakan login untuk membuka forum.";
            const btn = document.createElement("button");
            btn.className =
                "px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700";
            btn.textContent = "Login";
            btn.onclick = () => {
                window.location.href =
                    "/login?next=" +
                    encodeURIComponent(location.pathname + location.search);
            };
            cta.appendChild(btn);
            icon.className =
                "mx-auto mb-3 w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center";
            return;
        }

        // Kicked (sementara)
        if (state.moderation === ACCESS.MOD_KICKED) {
            title.textContent = "Sementara dikeluarkan (Kick)";
            const until = state.until ? new Date(state.until).getTime() : null;
            const reason = state.reason
                ? `Alasan: ${state.reason}`
                : "Tunggu sebentar sebelum mencoba lagi.";
            const cd = document.createElement("div");
            cd.id = "kick-countdown";
            cd.className = "mt-2 font-semibold text-gray-800";
            desc.innerHTML = `${reason}`;
            cta.appendChild(cd);
            icon.className =
                "mx-auto mb-3 w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center";

            const update = () => {
                const now = Date.now();
                const remain = (until ?? now) - now;
                if (remain <= 0) {
                    cd.textContent = "Selesai. Muat ulang…";
                    setTimeout(() => location.reload(), 800);
                } else {
                    cd.textContent = "Sisa waktu: " + formatCountdown(remain);
                }
            };
            update();
            accessTimer = setInterval(update, 1000);
            return;
        }

        // Banned
        if (state.moderation === ACCESS.MOD_BANNED) {
            title.textContent = "Akun diblokir";
            const until = state.until ? new Date(state.until).getTime() : null;
            const reason = state.reason
                ? `<span class="block">Alasan: ${state.reason}</span>`
                : "";
            const when = until
                ? `Hingga: ${new Date(until).toLocaleString()}`
                : "Durasi: permanen";
            desc.innerHTML = `${reason}${when}`;
            const help = document.createElement("a");
            help.href = "/appeal";
            help.className =
                "px-4 py-2 rounded-xl bg-gray-900 text-white font-medium hover:bg-gray-800";
            help.textContent = "Ajukan banding";
            cta.appendChild(help);
            icon.className =
                "mx-auto mb-3 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center";
            return;
        }
    }

    // === Load Messages ===
    async function loadMessages(limit = 50) {
        try {
            const wasNearBottom = isNearBottom(chatBox);
            showRefresh();

            const response = await fetchBanAware(
                `/forum/messages?limit=${limit}`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    credentials: "same-origin",
                }
            );

            // ==== AUTH / MODERATION GATE ====
            // 401: belum login
            if (response.status === 401) {
                applyAccessState({
                    auth: ACCESS.AUTH_GUEST,
                    moderation: ACCESS.MOD_OK,
                });
                ensureContainers();
                messagesListEl.innerHTML = `<p class="text-gray-600 text-sm text-center">Login diperlukan.</p>`;
                return;
            }

            // 403/423: moderation (tergantung backend-mu, sesuaikan)
            // - 403 banned (permanent / time-bound)
            // - 423 locked/kicked (sementara)
            if (response.status === 403 || response.status === 423) {
                let payload = null;
                try {
                    payload = await response.json();
                } catch {}
                const mod = payload?.moderation || {};
                // contoh bentuk: { state: "banned"|"kicked", until: epochMs|null, reason: "..." }
                if (mod.state === "kicked") {
                    applyAccessState({
                        auth: ACCESS.AUTH_OK,
                        moderation: ACCESS.MOD_KICKED,
                        until: mod.until ?? null,
                        reason: mod.reason ?? null,
                    });
                } else {
                    applyAccessState({
                        auth: ACCESS.AUTH_OK,
                        moderation: ACCESS.MOD_BANNED,
                        until: mod.until ?? null,
                        reason: mod.reason ?? null,
                    });
                }
                ensureContainers();
                messagesListEl.innerHTML = `<p class="text-gray-600 text-sm text-center">Akses dibatasi.</p>`;
                return;
            }

            // jika lolos: akses normal
            applyAccessState({
                auth: ACCESS.AUTH_OK,
                moderation: ACCESS.MOD_OK,
            });

            // ==== lanjut proses normal ====
            let result = null;
            try {
                result = await response.json();
            } catch {
                result = null;
            }

            ensureContainers();
            messagesListEl.innerHTML = "";

            if (!response.ok) {
                const msg = result?.message || `HTTP ${response.status}`;
                messagesListEl.innerHTML = `<p class="text-red-500 text-sm text-center">${msg}</p>`;
                return;
            }

            const raw = Array.isArray(result) ? result : result?.data ?? [];
            if (!raw || raw.length === 0) {
                messagesListEl.innerHTML = `<p class="text-gray-500 text-sm text-center">Belum ada pesan...</p>`;
                return;
            }

            const messagesAsc = [...raw].sort(
                (a, b) => new Date(a.created_at) - new Date(b.created_at)
            );

           let lastKey = null;
for (const msg of messagesAsc) {
  messagesMap.set(msg.id, msg);
  const key = getDateKey(msg.created_at);
  if (key !== lastKey) {
    const label = dateLabelForKey(key);
    const bgColor = isTodayKey(key)
      ? "bg-green-100 text-green-800"
      : isYesterdayKey(key)
      ? "bg-blue-100 text-blue-800"
      : "bg-gray-200 text-gray-700";

    messagesListEl.insertAdjacentHTML(
      "beforeend",
      `<div class="flex justify-center my-4">
         <span class="inline-flex items-center justify-center ${bgColor}
                      px-4 py-1.5 rounded-full text-[11px] font-medium tracking-wide shadow mb-3
                      min-w-[140px] text-center whitespace-nowrap">
           ${label}
         </span>
       </div>`
    );
    lastKey = key;
  }


                try {
                    messagesListEl.insertAdjacentHTML(
                        "beforeend",
                        renderMessage(msg)
                    );
                } catch (e) {
                    console.error("renderMessage error:", e, msg);
                    const safeName = msg?.user?.name ?? "User";
                    const safeText = msg?.message ?? "";
                    const isOwn =
                        currentUser && msg?.user?.id === currentUser.id;
                    messagesListEl.insertAdjacentHTML(
                        "beforeend",
                        `<div class="mb-2 flex ${
                            isOwn ? "justify-end" : "justify-start"
                        }">
            <div class="relative max-w-[75%] px-3 py-2 rounded-2xl shadow-sm border text-sm
              ${
                  isOwn
                      ? "bg-green-100 border-green-200 rounded-br-none chat-bubble chat-bubble--right"
                      : "bg-white border-gray-200 rounded-bl-none chat-bubble chat-bubble--left"
              }">
              <div class="text-sm font-medium mb-1 text-emerald-700">${escapeHtml(
                  safeName
              )}</div>
              <p class="text-[13px] leading-relaxed">${escapeHtml(safeText)}</p>
              <div class="mt-1 flex justify-end">
                <span class="text-[10px] text-gray-500">${formatTime(
                    msg?.created_at ?? Date.now()
                )}</span>
              </div>
            </div>
          </div>`
                    );
                }
            }

            if (!isFocusing && wasNearBottom) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        } catch (err) {
            console.error("Error loadMessages:", err);
            ensureContainers();
            messagesListEl.innerHTML = `<p class="text-red-500 text-sm text-center">Gagal memuat pesan</p>`;
        } finally {
            hideRefresh();
            isInitialLoad = false;
        }
    }

    function setReaction(messageId, emoji) {
        const id = Number(messageId);
        const msg = messagesMap.get(id) || { id, reactions: [] };
        msg.reactions = msg.reactions || [];

        // hanya satu reaksi per user → ganti emoji
        const me = currentUser?.id;
        msg.reactions = msg.reactions.filter((r) => r.user_id !== me);
        msg.reactions.push({ emoji, user_id: me });
        messagesMap.set(id, msg);

        const root = document.getElementById(`msg-${id}`);
        if (!root) return;

        const bubble = root.querySelector(".chat-bubble");
        const isOwn = bubble?.classList.contains("chat-bubble--right"); // sesuai template
        const wrap = root.querySelector(".reactions-float");
        if (wrap) {
            wrap.innerHTML = renderReactionsHTML(msg, isOwn ? "right" : "left");

            // klik ikon → ganti emoji ke yang diklik
            if (!wrap.__bound) {
                wrap.__bound = true;
                wrap.addEventListener("click", (e) => {
                    const btn = e.target.closest(".reaction-circle");
                    if (!btn) return;
                    setReaction(id, btn.getAttribute("data-emoji"));
                });
            }
        }

        // pastikan tidak ada elemen toast lama
        const toast = root.querySelector(".reaction-toast");
        if (toast) toast.remove();
    }

    // hitung apakah konten teksnya pendek (agar bubble dikasih min-width)
    function isShortMessage(msg) {
        const text = (msg.text || msg.body || msg.message || "").trim();
        return text.length > 0 && text.length <= 12;
    }
    // ringkas per emoji & tandai apakah milik kita
    function summarizeReactions(msg) {
        const map = new Map();
        (msg.reactions || []).forEach((r) => {
            if (!map.has(r.emoji)) map.set(r.emoji, new Set());
            map.get(r.emoji).add(r.user_id);
        });
        return [...map.entries()].map(([emoji, users]) => ({
            emoji,
            isMine: users.has(currentUser?.id),
        }));
    }

    function renderReactionsHTML(msg, side = "right") {
        const items = summarizeReactions(msg);
        if (!items.length) return "";
        const d = 36; // diameter
        const neg =
            side === "right" ? "margin-left:-10px;" : "margin-right:-10px;";
        return items
            .map(
                (it, i) => `
    <button data-emoji="${it.emoji}" class="reaction-circle"
      style="
        position:relative;width:${d}px;height:${d}px;
        display:inline-flex;align-items:center;justify-content:center;
        border-radius:9999px;background:#fff;border:1px solid #e5e7eb;
        box-shadow:0 2px 10px rgba(0,0,0,.08);
        font-size:18px;line-height:1;cursor:pointer;user-select:none;
        ${i > 0 ? neg : ""}
        ${it.isMine ? "" : ""}
      "
      title="${it.emoji}">
      <span>${it.emoji}</span>
    </button>
  `
            )
            .join("");
    }

    // === Render Message (updated for WA-like styling with smaller time font) ===
    function renderMessage(msg) {
        const isOwn =
            !!currentUser && String(msg.user?.id) === String(currentUser.id);
        const canModerate =
            currentUser &&
            (currentUser.role_id === 1 || currentUser.role_id === 3);

        // — detect role robust —
        const asBool = (v) =>
            v === true || v === 1 || v === "1" || v === "true";
        const roleId = Number(msg.user?.role_id ?? msg.user?.roleId ?? 0);
        const isAdminSender =
            asBool(msg.user?.is_admin ?? msg.user?.isAdmin) || roleId === 1;
        const isModSender =
            asBool(msg.user?.is_moderator ?? msg.user?.isModerator) ||
            roleId === 3;
        const ag = aggregateReactions(msg);
        const topEmoji = ag.length ? ag[0].emoji : "＋";
        // — name + badges —
        const nameLine = msg.user
            ? `<div class="text-sm font-medium mb-1 ${
                  isModSender ? "text-blue-700" : "text-emerald-700"
              } flex items-center">
         ${escapeHtml(msg.user.name)}
         ${
             isAdminSender
                 ? '<span class="ml-1 text-[10px] px-1 rounded bg-red-500 text-white">Admin</span>'
                 : ""
         }
         ${
             isModSender
                 ? '<span class="ml-1 text-[10px] px-1 rounded bg-blue-500 text-white">Mod</span>'
                 : ""
         }
       </div>`
            : "";

        // Reply preview
        let replySection = "";
        if (msg.reply_to) {
            replySection = `
                <button type="button"
                        onclick="jumpToMessage(${msg.reply_to.id})"
                        class="block mb-2 text-left px-2 py-1 text-xs rounded bg-gray-50 border-l-2 border-gray-300 text-gray-600 hover:bg-gray-100">
                    <span class="font-medium">${escapeHtml(
                        msg.reply_to.user_name
                    )}:</span>
                    <span class="truncate">${escapeHtml(
                        msg.reply_to.message
                    )}</span>
                </button>`;
        }
        // ==== Doc card helpers (namespaced, biar gak bentrok) ====
        (function () {
            if (window.DocCard) return; // jangan redeclare
            const NS = (window.DocCard = {});

            NS.bytesHuman = function (b = 0) {
                if (!b) return "0 B";
                const u = ["B", "KB", "MB", "GB"];
                let i = 0;
                while (b >= 1024 && i < u.length - 1) {
                    b /= 1024;
                    i++;
                }
                return `${b.toFixed(b < 10 && i ? 1 : 0)} ${u[i]}`;
            };

            NS.extFrom = function (meta = {}, url = "") {
                if (meta.filename?.includes("."))
                    return meta.filename.split(".").pop().toLowerCase();
                if (meta.mime?.includes("/"))
                    return meta.mime.split("/")[1].toLowerCase();
                try {
                    const p = new URL(url, location.href).pathname;
                    const n = decodeURIComponent(
                        (p.split("/").pop() || "").split("?")[0]
                    );
                    if (n.includes("."))
                        return n.split(".").pop().toLowerCase();
                } catch {}
                return "";
            };

            NS.typePretty = function (ext) {
                const map = {
                    pdf: "PDF Document",
                    doc: "Microsoft Word Document",
                    docx: "Microsoft Word Document",
                    xls: "Microsoft Excel Worksheet",
                    xlsx: "Microsoft Excel Worksheet",
                    ppt: "Microsoft PowerPoint Presentation",
                    pptx: "Microsoft PowerPoint Presentation",
                    csv: "CSV",
                    txt: "Text File",
                    md: "Markdown",
                };
                return map[ext] || (ext ? ext.toUpperCase() + " File" : "File");
            };

            NS.badge = (ext) => (ext || "FILE").toUpperCase();

            // Ikon “rasa Office” pakai SVG (ringan & tanpa dependensi)
            NS.ICONS = {
                ppt: `<img src="/icons/powerpoint.png" class="w-9 h-9" alt="PowerPoint">`,
                doc: `<img src="/icons/word.png" class="w-9 h-9" alt="Word">`,
                xls: `<img src="/icons/excel.png" class="w-9 h-9" alt="Excel">`,
                pdf: `<img src="/icons/pdf.png" class="w-9 h-9" alt="PDF">`,
                file: `<img src="/icons/file.png" class="w-9 h-9" alt="File">`,
            };

            NS.iconFor = function (ext) {
                if (["doc", "docx"].includes(ext)) return NS.ICONS.doc;
                if (["xls", "xlsx", "csv"].includes(ext)) return NS.ICONS.xls;
                if (["ppt", "pptx"].includes(ext)) return NS.ICONS.ppt;
                if (ext === "pdf") return NS.ICONS.pdf;
                if (["zip", "rar", "7z"].includes(ext)) return NS.ICONS.zip;
                return NS.ICONS.file;
            };

            NS.absoluteUrl = (u) => {
                try {
                    return new URL(u, location.href).href;
                } catch {
                    return u;
                }
            };

            // ====== OPEN: coba launch native app (Windows), kalau gagal fallback ke viewer web ======
            NS.openHref = function (url, meta = {}) {
                const abs = NS.absoluteUrl(url);
                const ext = NS.extFrom(meta, url);

                // bisa dirender browser langsung
                if (
                    [
                        "jpg",
                        "jpeg",
                        "png",
                        "gif",
                        "webp",
                        "svg",
                        "pdf",
                        "mp4",
                        "webm",
                        "mp3",
                        "wav",
                        "txt",
                    ].includes(ext)
                ) {
                    return abs;
                }

                // 1) skema Windows Office (jika terpasang) — efeknya buka aplikasi
                if (["doc", "docx"].includes(ext))
                    return `ms-word:ofe|u|${abs}`;
                if (["xls", "xlsx", "csv"].includes(ext))
                    return `ms-excel:ofe|u|${abs}`;
                if (["ppt", "pptx"].includes(ext))
                    return `ms-powerpoint:ofe|u|${abs}`;

                // 2) fallback web viewer Office (tidak download)
                if (
                    ["doc", "docx", "xls", "xlsx", "ppt", "pptx"].includes(ext)
                ) {
                    return (
                        "https://view.officeapps.live.com/op/view.aspx?src=" +
                        encodeURIComponent(abs)
                    );
                }

                // 3) tipe lain → biarkan browser (mungkin download)
                return abs;
            };

            // Klik “Open” yang mencoba ms- scheme dahulu, lalu fallback viewer web (agar cross OS)
            NS.handleOpenClick = function (ev, url, meta = {}) {
                const abs = NS.absoluteUrl(url);
                const ext = NS.extFrom(meta, url);
                const msUri = NS.openHref(url, meta);

                // kalau msUri sudah berupa https viewer/abs, biar default <a> jalan
                if (msUri.startsWith("http")) return;

                // coba launch app native
                ev.preventDefault();
                const wnd = window.open(msUri, "_self"); // gunakan _self agar skema dipanggil
                // jika OS tak kenal skema → fallback ke viewer web setelah 700ms
                setTimeout(() => {
                    const isOffice = [
                        "doc",
                        "docx",
                        "xls",
                        "xlsx",
                        "ppt",
                        "pptx",
                    ].includes(ext);
                    const viewer = isOffice
                        ? "https://view.officeapps.live.com/op/view.aspx?src=" +
                          encodeURIComponent(abs)
                        : abs;
                    window.open(viewer, "_blank");
                }, 700);
            };
        })();

        // Konten pesan
        let messageContent = "";
        switch (msg.message_type) {
       // === di switch (msg.message_type) ===
  case "poll": {
    const isOwn = msg.sender === "me";
    const p = msg.poll || {};
    const opts = Array.isArray(p.options) ? p.options.slice(0, 10) : [];
    const counts = Array.isArray(p.vote_counts) ? p.vote_counts.slice(0, 10) : Array(opts.length).fill(0);
    const total = p.total_votes || counts.reduce((a, b) => a + b, 0);
    const userVotes = Array.isArray(p.user_votes) ? p.user_votes : [];
    const isMulti = !!p.multiple_choice;

    const rows = opts.map((text, idx) => {
        const votes = counts[idx] || 0;
        const pct = total > 0 ? Math.round((votes / total) * 100) : 0;
        const checked = userVotes.includes(idx);
        const inputType = isMulti ? "checkbox" : "radio";

        return `
            <label class="cursor-pointer select-none flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg">
                <input type="${inputType}" 
                       name="poll_${p.id}" 
                       value="${idx}" 
                       class="hidden peer" 
                       ${checked ? "checked" : ""} 
                       onchange="votePoll(${p.id})" />
                <span class="relative inline-flex items-center justify-center w-5 h-5">
                    <span class="absolute inset-0 rounded-full border-2
                                ${checked ? 'border-emerald-600 bg-emerald-600' : 'border-gray-300 bg-white'}"></span>
                    <svg class="relative w-3 h-3 text-white ${checked ? 'opacity-100' : 'opacity-0'} transition-opacity"
                         viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 16.2 4.8 12 3.4 13.4 9 19 21 7 19.6 5.6z"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center text-sm">
                        <span class="truncate">${escapeHtml(text)}</span>
                        <span class="text-gray-500">${votes}</span>
                    </div>
                    <div class="mt-1 h-1.5 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full bg-emerald-600" style="width: ${pct}%"></div>
                    </div>
                </div>
            </label>
        `;
    }).join("");

    messageContent = `
    <div id="poll_${p.id}" data-poll-id="${p.id}" data-multi="${isMulti ? 1 : 0}"
         data-user-votes="${JSON.stringify(userVotes)}"
         class="${isOwn ? 'ml-auto' : ''} max-w-[480px]">
        <div>
            <div class="text-sm font-medium text-gray-700">${escapeHtml(p.question || "")}</div>
            <div class="text-xs text-gray-500 mt-1">${isMulti ? "Pilih satu atau lebih" : "Pilih satu"}</div>
            <div class="mt-3 space-y-2">${rows}</div>
        </div>
    </div>
`;

    break;
}

            case "location": {
    const isMine = msg.sender === "me";
    const { lat, lng } = getLocationFromMsg(msg);
    const hasCoord = Number.isFinite(lat) && Number.isFinite(lng);

    // Google Maps: iframe untuk preview, anchor overlay untuk klik
    const z = 16;
    const embed = hasCoord ? `https://www.google.com/maps?q=${lat},${lng}&z=${z}&output=embed` : "#";
    const link  = hasCoord ? `https://www.google.com/maps?q=${lat},${lng}&z=${z}` : "#";

    messageContent = `
        <div class="relative overflow-hidden rounded-2xl ring-1 ring-black/5 shadow-sm max-w-[360px]
                    ${isMine ? "ml-auto" : ""}">
        <iframe
            src="${embed}"
            class="block w-full h-[200px] pointer-events-none"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            aria-hidden="true">
        </iframe>
        <a href="${link}" target="_blank" rel="noopener"
            class="absolute inset-0" aria-label="Buka di Google Maps"></a>
        </div>
    `;
    break;
    }


       case "contact": {
  const isMine = msg.sender === "me";
  const { name = "", phone = "" } = getContactFromMsg(msg); // ambil dari attachment_data/data (support JSON string)
  const cleaned = phone.replace(/[^\d+]/g, "");             // biarkan +, buang selain digit

  messageContent = `
    <div class="px-3 py-2 rounded-2xl border max-w-[320px]
                ${isMine ? "bg-emerald-50 ml-auto border-emerald-100" : "bg-white border-gray-200"}">
      <div class="min-w-0">
        ${name ? `<div class="text-sm font-semibold truncate">${escapeHtml(name)}</div>` : ""}
        ${
          cleaned
            ? `<a href="tel:${cleaned}" class="block text-xs text-gray-600 hover:underline truncate">${escapeHtml(phone)}</a>`
            : ""
        }
      </div>
    </div>
  `;
  break;
}

            case "text":
                messageContent = `<p class="text-[13px] leading-relaxed">${escapeHtml(
                    msg.message || ""
                )}</p>`;
                break;
           case "image":
  messageContent = `
    <div>
      <img
        src="${msg.attachment_url}"
        class="max-w-[220px] rounded-lg cursor-pointer"
        onclick="window.open('${msg.attachment_url}', '_blank')"
      >
    </div>`;
  break;
   case "video": {
  const url = msg.attachment_url || "";
  const ext = (url.split("?")[0].split(".").pop() || "").toLowerCase();
  const mime = ext === "mp4" ? "video/mp4" : ext === "webm" ? "video/webm" : "video/mp4";
  messageContent = `
    <div class="relative">
      <video controls playsinline preload="metadata" class="max-w-[220px] rounded-lg" src="${url}">
        <source src="${url}" type="${mime}">
      </video>
    </div>`;
  break;
}
            case "document": {
                const url = msg.attachment_url || "";

                // nama file
                let name = msg?.metadata?.filename || "";
                if (!name && url) {
                    try {
                        const p = new URL(url, location.href).pathname;
                        name = decodeURIComponent(
                            (p.split("/").pop() || "").split("?")[0]
                        );
                    } catch {
                        name = url.split("/").pop() || "Dokumen";
                    }
                }
                if (!name) name = "Dokumen";

                const ext = DocCard.extFrom(msg?.metadata, url);
                const pretty = DocCard.typePretty(ext); // contoh: Microsoft PowerPoint Presentation
                const badge = DocCard.badge(ext); // contoh: PPTX
                const size = msg?.metadata?.size
                    ? DocCard.bytesHuman(+msg.metadata.size)
                    : "";

                const openHref = DocCard.openHref(url, msg?.metadata); // bisa ms- scheme / viewer / raw

                messageContent = `
    <div class="rounded-2xl border border-gray-200 bg-white/90">
      <div class="flex items-start gap-3 px-3 pt-3">
        ${DocCard.iconFor(ext)}
        <div class="min-w-0 flex-1">
          <div class="text-[15px] font-semibold truncate">${escapeHtml(
              name
          )}</div>
          <div class="text-[12px] text-gray-500">${[size, pretty]
              .filter(Boolean)
              .join(", ")}</div>
        </div>
        <span class="ml-2 text-[11px] px-2 py-0.5 rounded bg-gray-100 border text-gray-600">${badge}</span>
      </div>

      <div class="grid grid-cols-2 gap-2 px-3 pb-3 pt-3">
        <a href="${openHref}"
           class="text-center px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm"
           onclick="DocCard.handleOpenClick(event, '${url.replace(
               /'/g,
               "\\'"
           )}', ${JSON.stringify(msg?.metadata || {})})">
           Open
        </a>
        <a href="${url}" download
           class="text-center px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">
           Save as…
        </a>
      </div>
    </div>`;
                break;
            }
   case "voice": {
  const audioId = `vn-${msg.id}`;
  const src = msg.attachment_url || "";
  const isMine = msg.sender === "me";

  messageContent = `
    <div class="vn-player flex items-center gap-3 px-3 py-2 rounded-2xl max-w-[360px]
                ${isMine ? "bg-emerald-100 ml-auto" : "bg-white"}"
                data-audio-id="${audioId}" data-src="${src}">

      <!-- Hidden preloaded audio -->
      <audio id="${audioId}" src="${src}" preload="auto"></audio>

      <!-- Play -->
      <button type="button"
        class="vn-play shrink-0 inline-flex items-center justify-center w-6 h-6 text-green-600 hover:text-green-700"
        aria-label="Play/Pause">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 block" viewBox="0 0 24 24" fill="currentColor">
          <path d="M8 5v14l11-7z"/>
        </svg>
      </button>

      <!-- Slider -->
      <div class="flex-1 min-w-0 h-6 flex items-center">
        <input type="range"
          class="vn-seek w-full h-1 bg-gray-300 rounded-full accent-emerald-600 m-0 p-0 focus:outline-none focus:ring-0"
          min="0" max="100" step="0.1" value="0">
      </div>

      <!-- Timer -->
      <span class="vn-current shrink-0 text-[12px] leading-none tabular-nums text-gray-600">0:00</span>
    </div>
  `;
  break;
}


            default:
                messageContent = `<p class="text-[13px] leading-relaxed">${escapeHtml(
                    msg.message || ""
                )}</p>`;
                const reactionsHTML = renderReactionsHTML(msg); // <= TAMBAH BARIS INI
        }

        // Tidak ada tombol aksi di sini, akan ditangani oleh klik kanan
        const short =
            (msg.text || msg.body || msg.message || "").trim().length <= 12;
        const minW = short ? "min-width:clamp(14ch,42vw,260px);" : "";

        return `
<div class="mb-2 flex ${
            isOwn ? "justify-end" : "justify-start"
        } msg-item relative"
     id="msg-${msg.id}" data-message-id="${msg.id}" data-user-id="${
            msg.user.id
        }"
     data-sender-admin="${isAdminSender ? 1 : 0}" data-sender-mod="${
            isModSender ? 1 : 0
        }">

  <div class="relative max-w-[75%] px-3 py-2 rounded-2xl shadow-sm border text-sm
              ${
                  isOwn
                      ? "bg-green-100 border-green-200 rounded-br-none chat-bubble chat-bubble--right"
                      : "bg-white border-gray-200 rounded-bl-none chat-bubble chat-bubble--left"
              }"
       style="${minW}">
    ${replySection}
    ${nameLine}
    ${messageContent}

    <div class="mt-1 flex justify-between items-center">
      <span class="text-[10px] text-gray-500">${formatTime(
          msg.created_at
      )}</span>
    </div>

    <!-- REACTIONS: selalu pojok kanan bawah -->
    <div class="reactions-float"
         style="
           position:absolute;
           right:-12px;
           bottom:-12px;
           display:flex;align-items:center;z-index:3;">
      ${renderReactionsHTML(msg, "right")}
    </div>
  </div>
</div>`;
    }

    function showReactionToast(messageId, emoji) {
        const el = document.getElementById(`rt-${messageId}`);
        if (!el) return;
        el.textContent = emoji;
        el.classList.add("show");
        clearTimeout(el._rtTimer);
        el._rtTimer = setTimeout(() => el.classList.remove("show"), 1600);
    }
function pickLocation(msg){
  // ambil sumber data yang mungkin
  let src = msg?.attachment_data ?? msg?.data ?? msg?.metadata ?? null;
  if (typeof src === "string") { try { src = JSON.parse(src); } catch { src = null; } }

  const lat = Number(src?.latitude ?? src?.lat ?? msg?.latitude ?? msg?.lat);
  const lng = Number(src?.longitude ?? src?.lng ?? src?.lon ?? msg?.longitude ?? msg?.lng);
  const name = (src?.name || src?.label || "").toString().trim();
  const address = (src?.address || "").toString().trim();

  return { lat, lng, name, address };
}

const mapsLink = (lat, lng, name="") => {
  const q = `${lat},${lng}`;
  const label = name ? ` (${encodeURIComponent(name)})` : "";
  // link universal (Android/iOS/desktop aman)
  return `https://www.google.com/maps?q=${q}${label}`;
};

const osmStatic = (lat, lng, zoom=15, w=360, h=180) =>
  `https://staticmap.openstreetmap.de/staticmap.php?center=${lat},${lng}&zoom=${zoom}&size=${w}x${h}&maptype=mapnik&markers=${lat},${lng},red-pushpin`;

function getLocationFromMsg(msg){
  let src = msg?.attachment_data ?? msg?.data ?? msg?.metadata ?? null;
  if (typeof src === "string") { try { src = JSON.parse(src); } catch { src = null; } }
  const lat = Number(src?.latitude ?? src?.lat);
  const lng = Number(src?.longitude ?? src?.lng ?? src?.lon);
  return { lat, lng };
}

    // === ENHANCED: Send Message with Attachment Support ===
function getContactFromMsg(msg) {
  // 1) ambil kandidat sumber data
  let src =
    msg?.attachment_data ??
    msg?.data ??
    msg?.attachment ??
    msg?.metadata ??
    msg?.payload ??
    null;

  // 2) kalau berupa string JSON → parse
  if (typeof src === "string") {
    try { src = JSON.parse(src); } catch { src = null; }
  }

  // 3) fallback: kadang server menaruh di message sebagai JSON string
  if (!src && typeof msg?.message === "string" && msg.message.trim().startsWith("{")) {
    try { src = JSON.parse(msg.message); } catch {}
  }

  // 4) normalisasi output
  const name  = (src?.name  ?? msg?.name  ?? "").toString().trim();
  const phone = (src?.phone ?? msg?.phone ?? "").toString().trim();
  return { name, phone };
}

function telHref(num) {
  const cleaned = (num || "").replace(/[^\d+]/g, "");
  return cleaned ? `tel:${cleaned}` : "#";
}

    // Kirim pesan
 function getMessageType() {
  if (!currentAttachment) return "text";
  switch (currentAttachment.type) {
    case "image":    return "image";
    case "document": return "document";
    case "voice":    return "voice";
    case "contact":  return "contact";
    case "location": return "location";
    case "video":    return "video";     
    default:         return "text";
  }
}

    async function sendMessage() {
        const message = getMessageText().trim();
        if (!message && !currentAttachment) return;

        const formData = new FormData();

        // wajib: tipe pesan
        const msgType = getMessageType();
        formData.append("message_type", msgType);

        if (message) formData.append("message", message);
        if (replyToMessage) formData.append("reply_to_id", replyToMessage.id);

        // kirim attachment jika ada
        if (currentAttachment) {
            if (
                ["image", "document", "voice", "video"].includes(currentAttachment.type)
            ) {
                const f = currentAttachment.file;
                formData.append(
                    "attachment",
                    f,
                    f.name || `file-${Date.now()}`
                );
            } else {
                // contact / location sebagai JSON string
                formData.append(
                    "attachment_data",
                    JSON.stringify(currentAttachment.data)
                );
            }
        }

        try {
            const response = await fetch("/forum/messages", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
                body: formData,
                credentials: "same-origin",
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch {
                result = { message: text };
            }

            if (!response.ok || result?.status === "error") {
                const errs = result?.errors;
                let msg = result?.message || "Data tidak valid";
                if (errs && typeof errs === "object") {
                    const k = Object.keys(errs)[0];
                    if (k && errs[k]?.[0]) msg = errs[k][0];
                }
                return showError(msg);
            }

            setMessageText("");
            removeAttachment();
            cancelReply?.();
            resetButtonState();
            loadMessages?.();
            showSuccess("Pesan berhasil dikirim");
        } catch (err) {
            console.error("Error sendMessage:", err);
            showError("Gagal mengirim pesan");
        }
    }

    // Helper universal: ambil isi pesan (contentEditable atau input)
    function getMessageText() {
        return messageInput.isContentEditable
            ? messageInput.innerText
            : messageInput.value;
    }

    // Helper universal: set isi pesan
    function setMessageText(text) {
        if (messageInput.isContentEditable) {
            messageInput.innerText = text;
        } else {
            messageInput.value = text;
        }
    }

    // Reset tombol kirim ke mode "voice"
    function resetButtonState() {
        iconVoice.classList.remove("hidden");
        iconSend.classList.add("hidden");
        sendBtn.dataset.mode = "voice";
    }

    // Update tampilan tombol kirim
    function updateSendButton() {
  // 🚩 Selama rekaman, paksa tombol jadi SEND
  if (isRecording) {
    iconVoice.classList.add("hidden");
    iconSend.classList.remove("hidden");
    sendBtn.dataset.mode = "send";
    return;
  }

  const hasText = getMessageText().trim().length > 0;
  const hasAttachment = currentAttachment !== null;

  if (hasText || hasAttachment) {
    iconVoice.classList.add("hidden");
    iconSend.classList.remove("hidden");
    sendBtn.dataset.mode = "send";
  } else {
    resetButtonState();
  }
}

    /*******  8366f8dd-8532-4053-9a4e-5a5ae1b34440  *******/

    if (messageInput && sendBtn) {
        messageInput.addEventListener("input", updateSendButton);

        messageInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                if (sendBtn.dataset.mode === "send") {
                    sendMessage();
                }
            }
        });

      sendBtn.addEventListener("click", () => {
  const mode = sendBtn.dataset.mode;

  // DESKTOP: sekali klik mulai, klik lagi stop & auto-send
  if (IS_DESKTOP) {
    if (!isRecording && mode === "voice") {
      // start
      startVoiceRecording();
      return;
    }
    if (isRecording) {
      // stop & auto-send
      autoSendAfterStop = true;
      stopVoiceRecording();
      return;
    }
    // tidak rekam → kirim pesan teks/attachment biasa
    if (mode === "send") sendMessage();
    return;
  }

  // MOBILE (tetap seperti kemarin)
  if (isRecording && mode === "send") {
    autoSendAfterStop = true;
    stopVoiceRecording();
    return;
  }
  if (mode === "send") sendMessage(); else startVoiceRecording();
});

    }
    function enterRecordingUI(){
  // kunci compose dan timpa UI
  lockCompose(true);
  forumWidget?.classList.add("is-recording");
  voiceRecording?.classList.remove("hidden");
}

function exitRecordingUI(){
  lockCompose(false);
  forumWidget?.classList.remove("is-recording");
  voiceRecording?.classList.add("hidden");
}


    // === NEW: Attachment Handling ===
    function selectFile(type) {
        const inputMap = {
            document: "document-input",
            image: "image-input",

            camera: "camera-input",
        };

        const input = document.getElementById(inputMap[type]);
        if (input) {
            input.click();
        }
        attachmentMenu.classList.add("hidden");
    }

    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (file.size > 10 * 1024 * 1024) {
            showError("File terlalu besar. Maksimal 10MB.");
            return;
        }

        currentAttachment = {
            file: file,
            type: getFileType(file),
            name: file.name,
            size: file.size,
        };

        showAttachmentPreview();
        updateSendButton();
    }

   function getFileType(file) {
  if (file.type.startsWith("image/")) return "image";
  if (file.type.startsWith("audio/")) return "voice";
  if (file.type.startsWith("video/")) return "video"; 
  return "document";
}


    function showAttachmentPreview() {
        if (!currentAttachment || !attachmentPreview || !attachmentInfo) return;

        const iconMap = {
            image: "🖼️",
            document: "📄",
            voice: "🎵",
            video: "🎬",  
            contact: "👤",
            location: "📍",
        };

        let content = "";
        if (currentAttachment.file) {
            content = `
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">${
                            iconMap[currentAttachment.type] || "📎"
                        }</span>
                        <div>
                            <p class="text-sm font-medium">${
                                currentAttachment.name
                            }</p>
                            <p class="text-xs text-gray-500">${formatFileSize(
                                currentAttachment.size
                            )}</p>
                        </div>
                    </div>
                `;
        } else if (currentAttachment.type === "contact") {
            content = `
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">👤</span>
                        <div>
                            <p class="text-sm font-medium">${currentAttachment.data.name}</p>
                            <p class="text-xs text-gray-500">${currentAttachment.data.phone}</p>
                        </div>
                    </div>
                `;
        } else if (currentAttachment.type === "location") {
            content = `
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">📍</span>
                        <div>
                            <p class="text-sm font-medium">Location</p>
                            <p class="text-xs text-gray-500">${currentAttachment.data.latitude.toFixed(
                                6
                            )}, ${currentAttachment.data.longitude.toFixed(
                6
            )}</p>
                        </div>
                    </div>
                `;
        }

        attachmentInfo.innerHTML = content;
        attachmentPreview.classList.remove("hidden");
    }

    function removeAttachment() {
        currentAttachment = null;
        if (attachmentPreview) {
            attachmentPreview.classList.add("hidden");
        }

        const inputs = ["document-input", "image-input", "camera-input"];
        inputs.forEach((id) => {
            const input = document.getElementById(id);
            if (input) input.value = "";
        });

        updateSendButton();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }

    // === NEW: Voice Recording ===
   async function startVoiceRecording() {
  if (isRecording) return;
  isRecording = true;
  autoSendAfterStop = false;
  shouldSaveRecording = false;   // default: jangan simpan dulu

  lockCompose(true);

  // tombol send jadi sama dengan chat biasa
  iconVoice?.classList.add("hidden");
  iconSend?.classList.remove("hidden");
  sendBtn.dataset.mode = "send";

  try {
    recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });

    enterRecordingUI?.();
    showVoiceRecording();
    startWaveform(recordingStream);
    startRecordingTimer();

    const mime = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
      ? 'audio/webm;codecs=opus' : 'audio/webm';

    mediaRecorder = new MediaRecorder(recordingStream, { mimeType: mime });
    const mediaChunks = [];

    mediaRecorder.ondataavailable = (e) => { if (e.data?.size) mediaChunks.push(e.data); };

   mediaRecorder.onstop = () => {
  if (shouldSaveRecording) {
    // hanya buat file kalau bukan cancel
    const recordingBlob = new Blob(mediaChunks, { type: "audio/webm" });
    const file = new File([recordingBlob], `voice-${Date.now()}.webm`, { type: "audio/webm" });
    currentAttachment = { file, type: "voice", name: file.name, size: file.size };
    showAttachmentPreview?.();
  } else {
    currentAttachment = null; // pastikan tidak tersimpan
  }

  // cleanup UI
  stopWaveform();
  if (recordingTimer) { 
    clearInterval(recordingTimer); 
    recordingTimer = null; 
  }
  hideVoiceRecording();
  exitRecordingUI?.(); // pastikan UI header/footer juga tertutup
  isRecording = false;
  lockCompose(false);
  updateSendButton?.();

  try { 
    recordingStream?.getTracks().forEach(t => t.stop()); 
  } catch {}
  recordingStream = null;

  if (autoSendAfterStop && shouldSaveRecording) {
    autoSendAfterStop = false;
    sendMessage?.();
  }
};

mediaRecorder.start(250);
} catch (err) {
  console.error(err);
  // cleanup jika gagal
  isRecording = false;
  autoSendAfterStop = false;
  stopWaveform();
  if (recordingTimer) { 
    clearInterval(recordingTimer); 
    recordingTimer = null; 
  }
  hideVoiceRecording();
  exitRecordingUI?.();
  lockCompose(false);
  try { 
    recordingStream?.getTracks().forEach(t => t.stop()); 
  } catch {}
  recordingStream = null;
  updateSendButton?.();
  showError?.("Tidak dapat mengakses mikrofon. Pastikan izin sudah diberikan.");
}

}

// ================== STOP ==================
function stopVoiceRecording() {
  shouldSaveRecording = true; // simpan
  try { if (mediaRecorder?.state !== "inactive") mediaRecorder.stop(); } catch {}
}

// ================== CANCEL ==================
function cancelVoiceRecording() {
  shouldSaveRecording = false; // buang
  try { if (mediaRecorder?.state !== "inactive") mediaRecorder.stop(); } catch {}

  try { recordingStream?.getTracks().forEach(t => t.stop()); } catch {}
  recordingStream = null;

  isRecording = false;
  autoSendAfterStop = false;
  currentAttachment = null;

  stopWaveform();
  if (recordingTimer) { clearInterval(recordingTimer); recordingTimer = null; }
  hideVoiceRecording();
  exitRecordingUI?.();
  lockCompose(false);
  updateSendButton?.(); // balikkan tombol jadi mic
}


function showVoiceRecording() {
  const el = document.getElementById('voice-recording');
  if (!el) return;
  el.classList.remove('hidden');

  // reset elapsed
  recElapsedMs = 0;

  // pastikan canvas terlihat
  document.getElementById('recording-wave')?.classList.remove('hidden');

  // state tombol (pause terlihat, resume tersembunyi)
  document.getElementById('btn-voice-pause')?.classList.remove('hidden');
  document.getElementById('btn-voice-resume')?.classList.add('hidden');
}

function hideVoiceRecording() {
  const el = document.getElementById('voice-recording');
  if (el) el.classList.add('hidden');

  // matikan timer & animasi
  if (recordingTimer) { clearInterval(recordingTimer); recordingTimer = null; }
  stopWaveform();

  // reset UI supaya nggak “nyangkut”
  const t = document.getElementById('recording-time');
  if (t) t.textContent = '00:00';
  const wave = document.getElementById('recording-wave');
  if (wave && wave.getContext) wave.getContext('2d')?.clearRect(0, 0, wave.width, wave.height);

  // kembalikan state tombol
  document.getElementById('btn-voice-pause')?.classList.remove('hidden');
  document.getElementById('btn-voice-resume')?.classList.add('hidden');
}

function startRecordingTimer() {
  if (!recordingTime) return;
  if (recordingTimer) clearInterval(recordingTimer);
  recordingTimer = setInterval(() => {
    recElapsedMs += 250;                            // ⬅️ akumulasi 250ms
    const minutes = Math.floor(recElapsedMs / 60000);
    const seconds = Math.floor((recElapsedMs % 60000) / 1000);
    recordingTime.textContent = `${minutes.toString().padStart(2,"0")}:${seconds.toString().padStart(2,"0")}`;
    const dots = document.getElementById('vr-dots');
    if (dots) dots.style.backgroundPositionX = `${(performance.now()/14)%100}%`;
  }, 250);
}
/*******  c5e4eb62-5e48-45e1-a288-eac153162bed  *******/




    // === NEW: Attachment Button Events ===
    document
        .getElementById("btn-document")
        ?.addEventListener("click", () => selectFile("document"));
    document
        .getElementById("btn-image")
        ?.addEventListener("click", () => selectFile("image"));
 
    document
        .getElementById("btn-contact")
        ?.addEventListener("click", showContactModal);
    document
        .getElementById("btn-location")
        ?.addEventListener("click", showLocationModal);

    // Hidden file inputs
    document
        .getElementById("document-input")
        ?.addEventListener("change", handleFileSelect);
    document
        .getElementById("image-input")
        ?.addEventListener("change", handleFileSelect);
    document
        .getElementById("camera-input")
        ?.addEventListener("change", handleFileSelect);


   // Voice recording controls (SAMAKAN DENGAN HTML)
document.getElementById('btn-voice-discard')?.addEventListener('click', () => {
  cancelVoiceRecording(); // stop + bersih + kembalikan UI
});
const btnPause  = document.getElementById('btn-voice-pause');
const btnResume = document.getElementById('btn-voice-resume');

btnPause?.addEventListener('click', () => {
  if (mediaRecorder?.state === 'recording') {
    try { mediaRecorder.pause(); } catch {}
    // hentikan animasi & timer
    stopWaveform();
    if (recordingTimer) { clearInterval(recordingTimer); recordingTimer = null; }
    btnPause.classList.add('hidden');
    btnResume?.classList.remove('hidden');
  }
});

btnResume?.addEventListener('click', () => {
  if (mediaRecorder?.state === 'paused') {
    try { mediaRecorder.resume(); } catch {}
    // lanjutkan animasi & timer dari recElapsedMs yg sudah ada
    startWaveform(recordingStream);
    startRecordingTimer();
    btnResume.classList.add('hidden');
    btnPause?.classList.remove('hidden');
  }
});


document.getElementById('btn-voice-resume')?.addEventListener('click', () => {
  if (mediaRecorder?.state === 'paused') {
    try { mediaRecorder.resume(); } catch {}
    // toggle UI
    document.getElementById('btn-voice-resume')?.classList.add('hidden');
    document.getElementById('btn-voice-pause')?.classList.remove('hidden');
  }
});
document.getElementById('btn-voice-send')?.addEventListener('click', () => {
  autoSendAfterStop = true;   // agar langsung kirim setelah stop
  stopVoiceRecording();       // hentikan dan trigger onstop
});


    // Attachment remove
    document
        .getElementById("remove-attachment")
        ?.addEventListener("click", removeAttachment);

    // === NEW: Contact Modal ===
    function showContactModal() {
        if (contactModal) {
            contactModal.classList.remove("hidden");
            contactModal.classList.add("flex");
        }
        attachmentMenu.classList.add("hidden");
    }

    function hideContactModal() {
        if (contactModal) {
            contactModal.classList.add("hidden");
            contactModal.classList.remove("flex");
            document.getElementById("contact-form")?.reset();
        }
    }

    document
        .getElementById("contact-form")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();

            const name = document.getElementById("contact-name")?.value.trim();
            const phone = document
                .getElementById("contact-phone")
                ?.value.trim();

            if (!name || !phone) {
                showError("Nama dan nomor telepon harus diisi");
                return;
            }

            currentAttachment = {
                type: "contact",
                data: { name, phone },
            };

            hideContactModal();
            showAttachmentPreview();
            updateSendButton();
        });

    document
        .getElementById("cancel-contact")
        ?.addEventListener("click", hideContactModal);

    // === NEW: Report Modal ===
    function showReportModal(messageId, userId) {
        if (!reportModal) {
            showError("Modal report tidak ditemukan");
            return;
        }

        // Set data untuk report
        reportModal.dataset.messageId = messageId;
        reportModal.dataset.userId = userId;

        // Reset form
        const reportForm = document.getElementById("report-form");
        if (reportForm) {
            reportForm.reset();
        }

        // Tampilkan modal
        reportModal.classList.remove("hidden");
        reportModal.classList.add("flex");
    }

    function hideReportModal() {
        if (reportModal) {
            reportModal.classList.add("hidden");
            reportModal.classList.remove("flex");
            const reportForm = document.getElementById("report-form");
            if (reportForm) {
                reportForm.reset();
            }
        }
    }

    // Submit report
    async function submitReport(event) {
        event.preventDefault();
        
        if (!reportModal) return;

        const messageId = reportModal.dataset.messageId;
        const userId = reportModal.dataset.userId;
        const reason = document.getElementById("report-reason")?.value?.trim();
        const notes = document.getElementById("report-notes")?.value?.trim();

        if (!reason) {
            showError("Pilih alasan report");
            return;
        }

        if (reason.length > 50) {
            showError("Alasan terlalu panjang (maksimal 50 karakter)");
            return;
        }

        if (notes && notes.length > 1000) {
            showError("Catatan terlalu panjang (maksimal 1000 karakter)");
            return;
        }

        try {
            const response = await fetchBanAware("/forum/report", {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                    "Accept": "application/json",
                },
                body: JSON.stringify({
                    target_user_id: parseInt(userId),
                    message_id: messageId ? parseInt(messageId) : null,
                    reason: reason,
                    notes: notes || null,
                }),
            });

            const data = await response.json();

            if (!response.ok || data?.status !== "success") {
                throw new Error(data?.message || "Gagal mengirim report");
            }

            showSuccess(data?.message || "Report berhasil dikirim");
            hideReportModal();
        } catch (error) {
            console.error("Error submitting report:", error);
            showError(error?.message || "Gagal mengirim report");
        }
    }

    // Event listeners untuk report modal
    document.getElementById("report-form")?.addEventListener("submit", submitReport);
    document.getElementById("cancel-report")?.addEventListener("click", hideReportModal);
    document.getElementById("report-modal-close")?.addEventListener("click", hideReportModal);

    // === NEW: Location Modal ===
    function showLocationModal() {
        if (locationModal) {
            locationModal.classList.remove("hidden");
            locationModal.classList.add("flex");
        }
        attachmentMenu.classList.add("hidden");
    }

    function hideLocationModal() {
        if (locationModal) {
            locationModal.classList.add("hidden");
            locationModal.classList.remove("flex");
            const latInput = document.getElementById("location-lat");
            const lngInput = document.getElementById("location-lng");
            if (latInput) latInput.value = "";
            if (lngInput) lngInput.value = "";
        }
    }

    document
        .getElementById("current-location")
        ?.addEventListener("click", () => {
            if (!navigator.geolocation) {
                showError("Geolocation tidak didukung browser ini");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    const latInput = document.getElementById("location-lat");
                    const lngInput = document.getElementById("location-lng");
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;
                },
                (error) => {
                    console.error("Geolocation error:", error);
                    showError(
                        "Tidak dapat mendapatkan lokasi. Pastikan izin lokasi telah diberikan."
                    );
                }
            );
        });

    document.getElementById("share-location")?.addEventListener("click", () => {
        const latInput = document.getElementById("location-lat");
        const lngInput = document.getElementById("location-lng");

        if (!latInput || !lngInput) return;

        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!lat || !lng) {
            showError("Koordinat latitude dan longitude harus diisi");
            return;
        }

        currentAttachment = {
            type: "location",
            data: { latitude: lat, longitude: lng },
        };

        hideLocationModal();
        showAttachmentPreview();
        updateSendButton();
    });

    document
        .getElementById("cancel-location")
        ?.addEventListener("click", hideLocationModal);

    

    // === Reply Functionality (keeping your existing structure) ===
    window.setReplyTo = function (messageId, userName, messagePreview) {
        replyToMessage = { id: messageId, userName, messagePreview };

        const replyContent = document.getElementById("reply-content");
        if (replyContent && replyPreview) {
            replyContent.textContent = `${userName}: ${messagePreview}`;
            replyPreview.classList.remove("hidden");
        }

        messageInput.focus();
    };

    function cancelReply() {
        replyToMessage = null;
        if (replyPreview) {
            replyPreview.classList.add("hidden");
        }
    }

    if (cancelReplyBtn) {
        cancelReplyBtn.addEventListener("click", cancelReply);
    }

    // === Delete Message (keeping your existing structure) ===
    window.deleteMessage = async function (messageId) {
        if (!confirm("Yakin ingin menghapus pesan ini?")) {
            return;
        }

        try {
            const response = await fetch(`/forum/messages/${messageId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
            });

            const result = await response.json();

            if (result.status === "success") {
                loadMessages();
                showSuccess("Pesan berhasil dihapus");
            } else {
                showError(result.message);
            }
        } catch (error) {
            console.error("Error deleting message:", error);
            showError("Gagal menghapus pesan");
        }
    };

    // === User Moderation (keeping your existing structure) ===
    window.showUserActions = function (userId, userName) {
        const actions = [
            {
                text: "Kick User (5 min)",
                action: () => moderateUser(userId, "kick"),
            },
            {
                text: "Ban User (1 hour)",
                action: () => moderateUser(userId, "ban", 1),
            },
            {
                text: "Ban User (1 day)",
                action: () => moderateUser(userId, "ban", 24),
            },
            {
                text: "Ban User (Permanent)",
                action: () => moderateUser(userId, "ban", "permanent"),
            },
            { text: "Unban User", action: () => moderateUser(userId, "unban") },
        ];

        const actionList = actions
            .map((action, index) => `${index + 1}. ${action.text}`)
            .join("\n");

        const choice = prompt(
            `Moderate User: ${userName}\n\n${actionList}\n\nPilih nomor (1-${actions.length}):`
        );

        if (choice && !isNaN(choice)) {
            const selectedAction = actions[parseInt(choice) - 1];
            if (selectedAction) {
                selectedAction.action();
            }
        }
    };
    function getCookie(name) {
        return document.cookie
            .split("; ")
            .find((r) => r.startsWith(name + "="))
            ?.split("=")[1];
    }

    async function moderateUser(userId, action, durationHours) {
        const csrfMeta =
            document.querySelector('meta[name="csrf-token"]')?.content || "";
        const xsrf = decodeURIComponent(getCookie("XSRF-TOKEN") || "");

        const url =
            action === "kick"
                ? `/forum/moderate/users/${userId}/kick`
                : action === "ban"
                ? `/forum/moderate/users/${userId}/ban`
                : `/forum/moderate/users/${userId}/unban`;

        // bentuk body sesuai validasi controller
        let body = null;
        if (action === "ban") {
            body =
                durationHours === "permanent"
                    ? { ban_type: "permanent" }
                    : {
                          ban_type: "temporary",
                          duration_hours: Number(durationHours || 1),
                      };
        }

        // sertakan _token (fallback) + header CSRF
        const bodyObj = body
            ? { ...body, _token: csrfMeta }
            : { _token: csrfMeta };

        const res = await fetchBanAware(url, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfMeta,
                "X-XSRF-TOKEN": xsrf,
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify(bodyObj),
        });

        let data = null;
        try {
            data = await res.json();
        } catch {}

        if (!res.ok) {
            // tampilkan alasan yang jelas
            if (res.status === 422 && data?.errors) {
                const first = Object.values(data.errors)[0]?.[0];
                throw new Error(first || data?.message || "Validasi gagal");
            }
            throw new Error(
                data?.message || `Moderasi gagal (HTTP ${res.status})`
            );
        }

        showSuccess(data?.message || "OK");
        await loadMessages();
    }


    // === Toggle Attachment Menu (keeping your existing structure) ===
    if (attachmentToggle && attachmentMenu) {
        attachmentToggle.addEventListener("click", () => {
            attachmentMenu.classList.toggle("hidden");
        });
    }

    // === Utility Functions (enhanced notifications) ===
    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
    // === Time & Date helpers (WA-like) ===
    function formatTime(ts) {
        const d = new Date(ts);
        return d.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
        });
    }
    
    function getDateKey(input) {
        const d = new Date(input);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        return `${y}-${m}-${day}`;
    }

    function isTodayKey(key) {
        return key === getDateKey(Date.now());
    }

/*************  ✨ Windsurf Command 🌟  *************/
    /**
     * Checks if the given key represents yesterday's date.
     * @param {string} key - the date key in the format "YYYY-MM-DD"
     * @returns {boolean} true if the key represents yesterday's date, false otherwise
     */
    function isYesterdayKey(key) {
        // get yesterday's date
        const y = new Date();
        y.setDate(y.getDate() - 1);

        // compare the given key with yesterday's date
        return key === getDateKey(y);
    }
/*******  458af3f9-c643-4350-80bb-ae5cec403da0  *******/

    function daysAgoKey(key) {
        const today = new Date();
        const [y, m, d] = key.split("-").map(Number);
        const date = new Date(y, m - 1, d);
        const diffTime = today - date;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    }

    function dateLabelForKey(key) {
        if (isTodayKey(key)) return "Hari ini";
        if (isYesterdayKey(key)) return "Kemarin";
        const daysAgo = daysAgoKey(key);
        if (daysAgo >= 2 && daysAgo <= 7) return `${daysAgo} hari lalu`;
        const [y, m, d] = key.split("-").map(Number);
        const dt = new Date(y, m - 1, d);
        return dt.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    // === Container & refresh helpers ===
    let isInitialLoad = true;
    let refreshEl = null;
    let messagesListEl = null;

    function ensureContainers() {
        // wadah daftar pesan agar refresh bar tidak ikut dihapus
        if (!messagesListEl) {
            const existing = document.getElementById("messages-list");
            if (existing) {
                messagesListEl = existing;
            } else {
                messagesListEl = document.createElement("div");
                messagesListEl.id = "messages-list";
                // kosongkan chatBox & sisipkan container
                chatBox.innerHTML = "";
                chatBox.appendChild(messagesListEl);
            }
        }
        // indikator refresh (sticky di atas)
        if (!refreshEl) {
            refreshEl = document.createElement("div");
            refreshEl.id = "refresh-indicator";
            refreshEl.className = "sticky top-0 z-10 flex justify-center";
            refreshEl.style.display = "none";
            refreshEl.innerHTML = `
        <span class="mt-2 inline-flex items-center gap-2 px-3 py-1 text-xs bg-white/90 backdrop-blur rounded-full shadow">
            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="9" stroke-width="2" class="opacity-25"></circle>
            <path d="M21 12a9 9 0 00-9-9" stroke-width="2" class="opacity-80"></path>
            </svg>
            Menyegarkan…
        </span>`;
            chatBox.prepend(refreshEl);
        }
    }
    function showRefresh() {
        ensureContainers();
        refreshEl.style.display = "flex";
    }
    function hideRefresh() {
        if (refreshEl) refreshEl.style.display = "none";
    }

function showToast(message, type = "success") {
  const container = document.getElementById("toast-container");
  if (!container) return alert(message);

  const bg =
    type === "error"
      ? "bg-red-500"
      : type === "warn"
      ? "bg-yellow-500"
      : "bg-green-600";
  const icon =
    type === "error"
      ? "⚠️"
      : type === "warn"
      ? "⚡"
      : "✅";

  const toast = document.createElement("div");
  toast.className = `${bg} text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 animate-fadeIn pointer-events-auto`;
  toast.innerHTML = `
    <span class="text-lg">${icon}</span>
    <span class="text-sm flex-1">${message}</span>
    <button class="text-white/80 hover:text-white font-bold" onclick="this.parentElement.remove()">✕</button>
  `;
  container.appendChild(toast);

  setTimeout(() => toast.remove(), 4000);
}

function showError(msg) { showToast(msg, "error"); }
function showSuccess(msg) { showToast(msg, "success"); }
function showWarn(msg) { showToast(msg, "warn"); }

    // === Initialize (keeping your existing structure) ===
    getCurrentUser();

    // Auto refresh messages every 10 seconds (only if forum is open)
    setInterval(() => {
        if (isForumOpen) {
            loadMessages();
        }
    }, 10000);

    loadMessages();
});
