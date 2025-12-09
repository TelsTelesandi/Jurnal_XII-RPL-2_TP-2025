/* public/js/forum-guard.js */
(function () {
  "use strict";

  const $$ = (sel) => (typeof sel === "string" ? document.querySelector(sel) : sel);
  const onCap = (el, ev, fn) => el && el.addEventListener(ev, fn, { capture: true });

  // daftar selector bawaan sesuai ID di halaman kamu
  const SEL = {
    msg: "#message-input",
    send: "#send-button",
    attachToggle: "#attachment-toggle",
    btn: {
      image: "#btn-image",
      document: "#btn-document",
      contact: "#btn-contact",
      location: "#btn-location",
      camera: "#btn-camera",
      poll: "#btn-poll",
      voiceIcon: "#icon-voice",
    },
    input: {
      image: "#image-input",
      document: "#document-input",
      camera: "#camera-input",
    },
  };

  function guardAttachment(e) {
    if (!window.ForumPolicy) return;
    if (!ForumPolicy.guardAttachmentAction()) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return false;
    }
    return true;
  }
  function guardVoice(e) {
    if (!window.ForumPolicy) return;
    if (!ForumPolicy.guardVoiceAction()) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return false;
    }
    return true;
  }
  function guardPoll(e) {
    if (!window.ForumPolicy) return;
    if (!ForumPolicy.guardPollAction()) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return false;
    }
    return true;
  }

  async function boot() {
    // pastikan ForumPolicy sudah ada
    if (!window.ForumPolicy) {
      console.warn("[forum-guard] ForumPolicy belum dimuat. Pastikan /js/forum-policy.js di-include lebih dulu.");
      return;
    }

    // inisialisasi + auto lock UI
    await ForumPolicy.init({
      statusUrl: "/forum/status",
      selectors: {
        messageInput: SEL.msg,
        sendButton: SEL.send,
        attachmentToggle: SEL.attachToggle,
        buttons: {
          image: SEL.btn.image,
          document: SEL.btn.document,
          contact: SEL.btn.contact,
          location: SEL.btn.location,
          camera: SEL.btn.camera,
          poll: SEL.btn.poll,
          voiceIcon: SEL.btn.voiceIcon,
        },
      },
      showError: (m) => toastError(m),
    });

    // ==== INTERCEPT (capturing) ====

    // attachment triggers
    [SEL.btn.image, SEL.btn.document, SEL.btn.contact, SEL.btn.location, SEL.btn.camera, SEL.attachToggle]
      .map($$)
      .forEach((el) => onCap(el, "click", guardAttachment));

    // file inputs (kalau user langsung pilih file)
    [SEL.input.image, SEL.input.document, SEL.input.camera]
      .map($$)
      .forEach((el) => onCap(el, "change", guardAttachment));

    // poll open
    onCap($$(SEL.btn.poll), "click", guardPoll);

    // voice: blokir saat user mencoba mulai rekaman
    const sendBtn = $$(SEL.send);
    onCap(sendBtn, "pointerdown", (e) => {
      // beberapa device mulai rekaman di pointerdown
      const mode = sendBtn?.dataset?.mode || "";
      if (mode === "voice") guardVoice(e);
    });
    onCap(sendBtn, "click", (e) => {
      // desktop kamu mulai rekaman di click
      const mode = sendBtn?.dataset?.mode || "";
      if (mode === "voice") guardVoice(e);
    });

    // ketika policy berubah → re-apply lock
    window.addEventListener("forum:policy:updated", () => {
      try { ForumPolicy.applyLocks(); } catch {}
    });

    // DOM berubah (mis. widget show/hide) → re-apply lock ringan
    const mo = new MutationObserver(() => {
      try { ForumPolicy.applyLocks(); } catch {}
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }

  // notifikasi ringan (fallback ke alert kalau container tidak ada)
  function toastError(msg) {
    try {
      const n = document.createElement("div");
      n.className =
        "fixed top-4 right-4 z-[9999] bg-red-500 text-white text-sm px-3 py-2 rounded-lg shadow";
      n.textContent = msg || "Aksi tidak diizinkan.";
      document.body.appendChild(n);
      setTimeout(() => n.remove(), 2600);
    } catch {
      alert(msg || "Aksi tidak diizinkan.");
    }
  }

  document.addEventListener("DOMContentLoaded", boot);
})();
