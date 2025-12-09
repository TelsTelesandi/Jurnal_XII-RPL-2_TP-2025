let emojiOpen = false;
let activeCat = "recent";

function showError(message) {
    alert(message);
    console.warn("showError:", message);
}

const RECENT_KEY = "wa_emoji_recent";

function loadRecent() {
    try {
        return JSON.parse(localStorage.getItem(RECENT_KEY) || "[]");
    } catch (e) {
        console.error("Error loading recent emojis:", e);
        return [];
    }
}

function saveRecent(emojis) {
    try {
        localStorage.setItem(RECENT_KEY, JSON.stringify(emojis.slice(0, 30)));
    } catch (e) {
        console.error("Error saving recent emojis:", e);
    }
}

function bumpRecent(emoji) {
    const arr = loadRecent().filter((e) => e !== emoji);
    arr.unshift(emoji);
    saveRecent(arr);
}

function buildEmojiPanel(emojiBtn, emojiData) {
    let panel = document.getElementById("emoji-panel");
    if (!panel) {
        console.log("Creating new emoji-panel");
        panel = document.createElement("div");
        panel.id = "emoji-panel";
        panel.className = "hidden fixed bg-white border rounded-2xl shadow-lg w-40 flex flex-col z-50";
        panel.innerHTML = `
            <div class="p-2 border-b">
                <input id="emoji-search" type="text" placeholder="Cari emoji..." class="w-full px-2 py-1 text-sm border rounded focus:outline-none focus:ring" />
            </div>
            <div id="emoji-grid" class="flex-1 overflow-y-auto px-2 py-1 text-xl leading-none"></div>
            <div id="emoji-tabs" class="flex items-center justify-around border-t py-1 text-lg"></div>
        `;
        document.body.appendChild(panel);
        panel.style.width = "28rem";
        panel.style.maxWidth = "28vw";
        const MOBILE_BREAKPOINT = 640;
        const MOBILE_SIDE_MARGIN = 8;

        function applyResponsiveSize() {
            const vp = window.visualViewport;
            const vw = vp ? vp.width : window.innerWidth;
            if (vw <= MOBILE_BREAKPOINT) {
                panel.style.width = `calc(100vw - ${MOBILE_SIDE_MARGIN * 2}px)`;
                panel.style.maxWidth = `calc(100vw - ${MOBILE_SIDE_MARGIN * 2}px)`;
                panel.dataset.mobile = "1";
                panel.dataset.sideMargin = String(MOBILE_SIDE_MARGIN);
            } else {
                panel.style.width = "28rem";
                panel.style.maxWidth = "28vw";
                delete panel.dataset.mobile;
                delete panel.dataset.sideMargin;
            }
        }

        applyResponsiveSize();
        window.addEventListener("resize", applyResponsiveSize, { passive: true });

        const grid = panel.querySelector("#emoji-grid");
        if (!grid.dataset.listenerAttached) {
            grid.addEventListener("click", (e) => {
                const btn = e.target.closest(".emoji-btn");
                if (!btn) return;
                insertEmoji(btn.dataset.emoji);
            });
            grid.dataset.listenerAttached = "1";
        }

        const tabs = panel.querySelector("#emoji-tabs");
        tabs.innerHTML = emojiData.EMOJI_CATS.map(
            (c) => `<button class="px-2 py-1 hover:bg-gray-100 rounded" data-cat="${c.key}" title="${c.label}">${c.icon}</button>`
        ).join("");

        tabs.addEventListener("click", (e) => {
            const btn = e.target.closest("button[data-cat]");
            if (!btn) return;
            activeCat = btn.dataset.cat;
            renderEmojiGrid(emojiData);
        });

        const searchInput = panel.querySelector("#emoji-search");
        searchInput?.addEventListener("input", () => renderEmojiGrid(emojiData, searchInput.value));
    }
    return panel;
}

function positionEmojiPanel(anchorEl, panel) {
    const GAP = 6;
    const r = anchorEl.getBoundingClientRect();
    if (!r || (r.width === 0 && r.height === 0)) return;

    const vp = window.visualViewport;
    const vw = vp ? vp.width : document.documentElement.clientWidth;
    const vh = vp ? vp.height : document.documentElement.clientHeight;

    const prevVis = panel.style.visibility;
    const prevDisp = panel.style.display;
    const wasHidden = getComputedStyle(panel).display === "none";
    if (wasHidden) {
        panel.style.visibility = "hidden";
        panel.style.display = "flex";
    }

    const pw = panel.offsetWidth || 448;
    const spaceBelow = vh - r.bottom - GAP;
    const spaceAbove = r.top - GAP;
    const placeBelow = (spaceBelow >= 300) || (spaceBelow >= spaceAbove);
    const available = Math.max(0, placeBelow ? spaceBelow : spaceAbove);

    const MOBILE = panel.dataset.mobile === "1" || vw <= 640;
    const MOBILE_HEIGHT_RATIO = 0.20;
    const DESKTOP_HEIGHT_RATIO = 0.55;
    const SIDE_MARGIN = MOBILE ? parseInt(panel.dataset.sideMargin || "8", 10) : GAP;
    const V_OFFSET = MOBILE ? -8 : -30;

    const HARD_CAP = 480;
    const ratioCap = Math.round(vh * (MOBILE ? MOBILE_HEIGHT_RATIO : DESKTOP_HEIGHT_RATIO));
    const MIN_H = MOBILE ? 180 : 240;
    const maxH = Math.max(MIN_H, Math.min(HARD_CAP, ratioCap, available));

    panel.style.height = "auto";
    panel.style.maxHeight = `${Math.floor(maxH)}px`;
    const ph = panel.offsetHeight || Math.min(panel.scrollHeight, maxH);

    let top = placeBelow ? (r.bottom + GAP) : (r.top - ph - GAP);
    top += V_OFFSET;

    let left = MOBILE ? Math.round((vw - pw) / 2) : r.left;

    if (left + pw > vw - SIDE_MARGIN) left = vw - pw - SIDE_MARGIN;
    if (left < SIDE_MARGIN) left = SIDE_MARGIN;
    if (top + ph > vh - GAP) top = vh - ph - GAP;
    if (top < GAP) top = GAP;

    Object.assign(panel.style, {
        position: "fixed",
        top: `${Math.round(top)}px`,
        left: `${Math.round(left)}px`,
        right: "auto",
        bottom: "auto",
        zIndex: 1000
    });

    if (wasHidden) {
        panel.style.visibility = prevVis;
        panel.style.display = prevDisp;
    }
}

function renderEmojiGrid(emojiData, keyword = "") {
    const grid = document.querySelector("#emoji-grid");
    if (!grid) {
        console.error("emoji-grid element not found!");
        return;
    }

    const q = (keyword || "").trim().toLowerCase();

    function collectAllEmojis() {
        const out = [];
        for (const [k, set] of Object.entries(emojiData.EMOJI_SET)) {
            if (!set) continue;
            if (typeof set === "string") out.push(...[...set]);
            else if (Array.isArray(set)) out.push(...set);
        }
        return Array.from(new Set(out));
    }

    let emojis = [];
    if (q) {
        emojis = collectAllEmojis().filter(
            (e) => (emojiData.EMOJI_KEYWORDS[e] || "").toLowerCase().includes(q) || e.includes(q)
        );
    } else {
        if (activeCat === "recent") {
            emojis = loadRecent();
        } else {
            const set = emojiData.EMOJI_SET[activeCat];
            if (typeof set === "string") emojis = [...set];
            else if (Array.isArray(set)) emojis = set;
        }
    }

    if (!emojis || emojis.length === 0) {
        grid.innerHTML = `<p class="text-gray-400 text-sm p-3">Tidak ada emoji</p>`;
        return;
    }

    grid.innerHTML = emojis.map(
        (e) => `<button type="button" class="p-1 hover:bg-gray-100 rounded emoji-btn" data-emoji="${e}">${e}</button>`
    ).join("");
}

function insertEmoji(emoji) {
    const input = document.getElementById("message-input");
    if (!input) {
        console.error("message-input element not found!");
        showError("Input pesan tidak ditemukan");
        return;
    }

    console.log("Inserting emoji:", emoji, "isContentEditable:", input.isContentEditable, "tagName:", input.tagName);

    try {
        input.focus();
        if (input.isContentEditable) {
            const sel = window.getSelection();
            if (!sel.rangeCount) {
                console.warn("No active selection, appending emoji");
                input.innerText += emoji;
            } else {
                const range = sel.getRangeAt(0);
                range.deleteContents();
                range.insertNode(document.createTextNode(emoji));
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        } else {
            const start = input.selectionStart || 0;
            const end = input.selectionEnd || 0;
            const text = input.value;
            input.value = text.slice(0, start) + emoji + text.slice(end);
            input.selectionStart = input.selectionEnd = start + emoji.length;
        }

        input.dispatchEvent(new Event("input", { bubbles: true }));
        console.log("After insert, content:", input.value);
        bumpRecent(emoji);
    } catch (e) {
        console.error("Error inserting emoji:", e);
        showError("Gagal menyisipkan emoji");
    }
}

function showEmojiPanel(anchorEl, emojiData) {
    const panel = buildEmojiPanel(anchorEl, emojiData);
    panel.classList.remove("hidden");
    panel.classList.add("flex");
    panel.style.visibility = "hidden";

    activeCat = "recent";
    const s = document.getElementById("emoji-search");
    if (s) s.value = "";
    renderEmojiGrid(emojiData);

    requestAnimationFrame(() => {
        positionEmojiPanel(anchorEl, panel);
        panel.style.visibility = "";
        emojiOpen = true;
        anchorEl.setAttribute("aria-expanded", "true");
    });
}

function hideEmojiPanel() {
    const panel = document.getElementById("emoji-panel");
    if (!panel) return;
    panel.classList.remove("flex");
    panel.classList.add("hidden");
    emojiOpen = false;
    const emojiBtn = document.getElementById("emoji-toggle");
    if (emojiBtn) emojiBtn.setAttribute("aria-expanded", "false");
}

export function initEmojiPicker(emojiData) {
    const chatContainer = document.querySelector("#chat-messages");
    if (chatContainer) {
        chatContainer.addEventListener("scroll", () => {
            if (!emojiOpen) return;
            const panel = document.getElementById("emoji-panel");
            if (panel) positionEmojiPanel(document.getElementById("emoji-toggle"), panel);
        }, { passive: true });
    }

    const emojiBtn = document.getElementById("emoji-toggle");
    if (!emojiBtn) {
        console.error("emoji-toggle element not found!");
        return;
    }

    emojiBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        console.log("Toggling emoji panel, current state:", emojiOpen);
        emojiOpen ? hideEmojiPanel() : showEmojiPanel(e.currentTarget, emojiData);
    });

    document.addEventListener("pointerdown", (e) => {
        if (!emojiOpen) return;
        const panel = document.getElementById("emoji-panel");
        if (panel?.contains(e.target) || emojiBtn.contains(e.target)) return;
        hideEmojiPanel();
    }, true);

    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && emojiOpen) hideEmojiPanel();
    });

    window.addEvent嚴Listener("scroll", () => {
        if (!emojiOpen) return;
        const panel = document.getElementById("emoji-panel");
        if (panel) positionEmojiPanel(emojiBtn, panel);
    }, { passive: true });

    window.addEventListener("resize", () => {
        if (!emojiOpen) return;
        const panel = document.getElementById("emoji-panel");
        if (panel) positionEmojiPanel(emojiBtn, panel);
    });
}