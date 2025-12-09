/* public/js/forum-policy.js */
(function (global) {
  "use strict";

  const DEFAULT_FLAGS = {
    // Forum sekarang selalu dianggap terbuka
    is_open: true,
    is_moderator: false,
    is_admin: false,
    is_banned: false,
    allow_attachments: true,
    allow_polls: true,
    allow_voice: true,
  };

  const STATE = {
    flags: { ...DEFAULT_FLAGS },
    statusUrl: "/forum/status",
    showError: (msg) => alert(msg || "Aksi tidak diizinkan"),
    selectors: {
      messageInput: null,
      sendButton: null,
      attachmentToggle: null,
      buttons: {
        image: null,
        document: null,
        contact: null,
        location: null,
        camera: null,
        poll: null,
        voiceIcon: null,
      },
    },
    cacheForMs: 10_000,
    _cachedAt: 0,
  };

  // ---------- utils ----------
  const qs = (sel) => (typeof sel === "string" ? document.querySelector(sel) : sel);
  const toggleHidden = (el, hide) => {
    if (!el) return;
    el.classList.toggle("hidden", !!hide);
  };
  const setDisabled = (el, dis) => {
    if (!el) return;
    el.toggleAttribute("disabled", !!dis);
    el.classList.toggle("opacity-60", !!dis);
    el.classList.toggle("pointer-events-none", !!dis);
  };
  const dispatchUpdated = () => {
    try {
      window.dispatchEvent(new CustomEvent("forum:policy:updated", { detail: { ...STATE.flags } }));
    } catch {}
  };

  const isPrivileged = () => !!(STATE.flags.is_admin || STATE.flags.is_moderator);
  const canUseAttachments = () => STATE.flags.allow_attachments || isPrivileged();
  const canUseVoice = () => (STATE.flags.allow_voice && STATE.flags.allow_attachments) || isPrivileged();
  const canUsePolls = () => STATE.flags.allow_polls || isPrivileged();

  // 🚀 Forum selalu terbuka untuk semua user
  const isForumOpenForMe = () => true;

  // ---------- core ----------
  async function refresh(force = false) {
    const now = Date.now();
    if (!force && now - STATE._cachedAt < STATE.cacheForMs) {
      applyLocks();
      return { ...STATE.flags };
    }

    try {
      const res = await fetch(STATE.statusUrl, {
        headers: { "Accept": "application/json" },
        credentials: "same-origin",
      });
      if (res.ok) {
        const json = await res.json();
        const data = json?.data || {};
        STATE.flags = { ...DEFAULT_FLAGS, ...data, is_open: true }; // paksa is_open tetap true
        STATE._cachedAt = now;
      }
    } catch (e) {
      console.warn("[ForumPolicy] refresh failed:", e);
    }

    applyLocks();
    dispatchUpdated();
    return { ...STATE.flags };
  }

  function applyLocks() {
    const s = STATE.selectors;

    const msgInput = qs(s.messageInput);
    const sendBtn = qs(s.sendButton);
    const attachTgl = qs(s.attachmentToggle);

    // ❌ Hilangkan semua check forum closed
    setDisabled(msgInput, false);
    setDisabled(sendBtn, false);
    toggleHidden(attachTgl, false);

    // Attachment permissions
    const disallowAttach = !canUseAttachments();
    const b = s.buttons || {};
    toggleHidden(qs(b.image), disallowAttach);
    toggleHidden(qs(b.document), disallowAttach);
    toggleHidden(qs(b.contact), disallowAttach);
    toggleHidden(qs(b.location), disallowAttach);
    toggleHidden(qs(b.camera), disallowAttach);
    toggleHidden(qs(s.attachmentToggle), disallowAttach);

    // Voice
    const disallowVoice = !canUseVoice();
    toggleHidden(qs(b.voiceIcon), disallowVoice);

    // Polls
    const disallowPolls = !canUsePolls();
    toggleHidden(qs(b.poll), disallowPolls);
  }

  // ---------- guards ----------
  function guardAttachmentAction() {
    if (!canUseAttachments()) {
      STATE.showError("Lampiran dimatikan oleh admin.");
      return false;
    }
    return true;
  }

  function guardVoiceAction() {
    if (!canUseVoice()) {
      STATE.showError("Voice message dimatikan oleh admin.");
      return false;
    }
    return true;
  }

  function guardPollAction() {
    if (!canUsePolls()) {
      STATE.showError("Poll dimatikan oleh admin.");
      return false;
    }
    return true;
  }

  // ---------- public API ----------
  const ForumPolicy = {
    init(opts = {}) {
      STATE.statusUrl = opts.statusUrl || STATE.statusUrl;
      STATE.showError = typeof opts.showError === "function" ? opts.showError : STATE.showError;
      if (opts.selectors) {
        STATE.selectors = {
          ...STATE.selectors,
          ...opts.selectors,
          buttons: { ...(STATE.selectors.buttons || {}), ...(opts.selectors.buttons || {}) },
        };
      }

      document.addEventListener("visibilitychange", () => {
        if (!document.hidden) refresh(false);
      });

      return refresh(true);
    },

    refresh,
    applyLocks,

    // getters
    getFlags() { return { ...STATE.flags }; },
    isPrivileged,
    canUseAttachments,
    canUseVoice,
    canUsePolls,
    isForumOpenForMe,

    // guards
    guardAttachmentAction,
    guardVoiceAction,
    guardPollAction,
  };

  global.ForumPolicy = ForumPolicy;
  if (typeof module !== "undefined" && module.exports) {
    module.exports = ForumPolicy;
  }
})(typeof window !== "undefined" ? window : globalThis);
