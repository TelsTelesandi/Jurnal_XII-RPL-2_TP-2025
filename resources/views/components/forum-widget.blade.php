
{{-- resources/views/components/forum-widget.blade.php --}}
<div>
    <!-- Floating Open Button (WA style) -->
    <div class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 group">
  <button id="open-forum"
      class="bg-[#25D366] hover:bg-[#1ec257] text-white p-3 sm:p-4 rounded-full shadow-xl 
             hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 relative">
      <svg class="h-5 w-5 sm:h-6 sm:w-6 transition-transform duration-300 group-hover:scale-110" 
           fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
      </svg>
  </button>

  <!-- Tooltip -->
  <span class="absolute bottom-full right-1/2 translate-x-1/2 mb-2 
               px-3 py-1 text-xs text-white bg-gray-800 rounded-lg shadow 
               opacity-0 group-hover:opacity-100 transition duration-200 whitespace-nowrap">
      Contact Us RKA
  </span>
</div>
    <!-- Forum Widget -->
    <div id="forum-widget"
        class="fixed inset-x-3 bottom-20 top-20 sm:bottom-20 sm:top-auto sm:right-6 sm:left-auto
         sm:w-[28rem] md:w-[32rem] lg:w-[30rem] 
         sm:h-[640px] md:h-[720px] lg:h-[650px]
         bg-white rounded-2xl sm:rounded-2xl shadow-2xl border hidden flex-col z-50
         max-h-[calc(100vh-160px)] sm:max-h-[calc(100vh-96px)] overflow-hidden">

        <!-- Header (WA green) -->
        <div class="bg-[#075E54] text-white px-3 sm:px-4 py-3 flex justify-between items-center flex-shrink-0">
            <div class="flex items-center gap-3">
                <!-- avatar placeholder -->
                <div class="w-8 h-8 rounded-full bg-white/20 grid place-items-center text-sm font-semibold">FD</div>
                <div>
                    <div class="font-semibold text-sm sm:text-base leading-tight">Contact Us</div>
                    <!-- di header -->
                    <div class="text-xs text-white/80 leading-tight" id="online-count">online 0</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="close-forum" class="text-white/90 hover:text-white rounded-full p-1">
                    ✕
                </button>
            </div>
        </div>

        <!-- Chat Messages (WA paper background) -->
        <div id="chat-box" class="flex-1 p-3 sm:p-4 overflow-y-auto bg-[#ECE5DD] relative"
            style="scrollbar-width: thin; overscroll-behavior: contain;">
            <!-- background pattern -->
            <div class="pointer-events-none absolute inset-0 opacity-[.25]"
                style="background-image: radial-gradient(rgba(7,94,84,.15) 1px, transparent 1px);
                        background-size: 14px 14px;">
            </div>

            <!-- loader -->
            <p class="relative text-gray-600 text-xs sm:text-sm text-center">Loading messages...</p>
        </div>

        <!-- Reply Preview -->
        <div id="reply-preview"
            class="hidden bg-[#e7f3ff] border-l-4 border-blue-400 px-3 py-2 sm:py-2.5 mx-3 sm:mx-4 rounded">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="text-[11px] text-blue-600 font-medium tracking-wide uppercase">Membalas</p>
                    <p class="text-xs sm:text-sm text-gray-800" id="reply-content"></p>
                </div>
                <button id="cancel-reply" class="text-gray-400 hover:text-gray-600 ml-2">✕</button>
            </div>
        </div>

        <!-- Attachment Preview -->
        <div id="attachment-preview" class="hidden bg-white p-2 sm:p-3 mx-3 sm:mx-4 border rounded">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2" id="attachment-info"></div>
                <button id="remove-attachment" class="text-red-500 hover:text-red-700 font-medium">✕</button>
            </div>
        </div>

   <!-- RECORDING BAR -->
<div id="voice-recording" class="hidden w-full px-3 py-2 rounded-xl border bg-white shadow items-center gap-3">
  <!-- kiri: hapus -->
  <button id="btn-voice-discard" class="p-2 rounded-lg hover:bg-gray-100" title="Batalkan">
    🗑️
  </button>

  <!-- timer -->
  <div class="min-w-[42px] text-sm font-semibold tabular-nums" id="recording-time">00:00</div>

  <!-- garis putus-putus / waveform tipis -->
  <div class="flex-1 flex items-center">
    <canvas id="recording-wave" class="h-[28px] w-full"></canvas>
  </div>

  <!-- jeda / lanjut -->
 <button id="btn-voice-pause" 
  class="text-green-600 hover:text-green-700" 
  title="Jeda">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-current" viewBox="0 0 24 24">
    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/> <!-- Pause bars -->
  </svg>
</button>

<button id="btn-voice-resume" 
  class="hidden text-green-600 hover:text-green-700" 
  title="Lanjut">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-current" viewBox="0 0 24 24">
    <path d="M8 5v14l11-7z"/> <!-- Play triangle -->
  </svg>
</button>


 <button id="btn-voice-send"
  class="ml-1 px-3 py-1.5 rounded-xl text-white font-medium"
  style="background:#16a34a;" title="Kirim">
  ➤
</button>
</div>

        <!-- Input Area (WA-style row) -->
        <div class="bg-[#ECE5DD] p-2  shadow-[inset_0_4px_6px_rgba(0,0,0,0.15)]">
            <!-- Attachment menu -->
            <div id="attachment-menu" class="hidden bg-white rounded-lg px-2 py-2 border mx-1 max-h-24 overflow-y-auto">
                <div class="grid grid-cols-5 sm:grid-cols-6 gap-1.5 sm:gap-2 text-center">
                    <button type="button" id="btn-document"
                        class="flex flex-col items-center p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-blue-600 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-[11px]">Doc</span>
                    </button>
                    <button type="button" id="btn-image"
                        class="flex flex-col items-center p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-green-600 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-[11px]">Image</span>
                    </button>
                    <button type="button" id="btn-camera"
                        class="flex flex-col items-center p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-purple-600 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-[11px]">Camera</span>
                    </button>

                    <button type="button" id="btn-contact"
                        class="flex flex-col items-center p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-orange-600 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[11px]">Contact</span>
                    </button>
                    <button type="button" id="btn-location"
                        class="flex flex-col items-center p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-red-600 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-[11px]">Location</span>
                    </button>
                    <button type="button" id="btn-poll"
                        class="flex flex-col items-center p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-indigo-600 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="text-[11px]">Poll</span>
                    </button>
                </div>
            </div>

            <!-- Message row -->
           <div id="compose-row" class="flex items-center gap-2 p-1.5 sm:p-2">
                <!-- Emoji -->
                <button type="button" id="emoji-toggle" aria-controls="emoji-panel" aria-expanded="false"
                    class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-white/70 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14.828 14.828A4 4 0 019.172 14.828M9 9h.01M15 9h.01M12 22a10 10 0 100-20 10 10 0 000 20z" />
                    </svg>
                </button>

                <!-- Attachment -->
                <button type="button" id="attachment-toggle"
                    class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-white/70 transition-colors">
                    <svg class="w-5 h-5 rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.172 7l-6.586 6.586a2 2 0 002.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </button>

                <!-- Input -->
             <textarea id="message-input"
  rows="1"
  class="flex-1 bg-white border border-gray-300 rounded-xl px-4 py-2 sm:py-2.5 text-sm
         focus:outline-none focus:ring-0 focus:border-gray-300 placeholder:text-gray-400 shadow
         resize-none overflow-y-hidden max-h-40"
  placeholder="Tulis pesan"></textarea>

                <!-- Send / Voice (auto toggled by JS) -->
                <button type="button" id="send-button"
                    class="bg-[#25D366] text-white p-2.5 sm:p-3 rounded-full hover:bg-[#1ec257] transition-colors flex items-center justify-center min-w-[44px] min-h-[44px] sm:min-w-[48px] sm:min-h-[48px] flex-shrink-0"
                    data-mode="voice">
                    <!-- Mic -->
                    <svg id="icon-voice" class="w-5 h-5 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                    <!-- Send -->
                    <svg id="icon-send" class="w-5 h-5 sm:w-5 sm:h-5 hidden" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        </div>

     <!-- Hidden Inputs -->
<input type="file" id="document-input" class="hidden" accept=".pdf,.doc,.docx,.txt,.xlsx,.pptx">
<input type="file" id="image-input" class="hidden" accept="image/*">
<!-- Fallback kamera (boleh foto ATAU video) -->
<input type="file" id="camera-input" class="hidden" accept="image/*,video/*">

<!-- Camera Modal -->
<div id="camera-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/70">
  <div class="bg-white w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl">

    <!-- Header -->
<div class="px-4 py-3 border-b flex items-center justify-between bg-gray-50 rounded-t-xl">
  <!-- Kiri: Judul / Label -->
  <div>
    <h3 class="text-gray-700 font-semibold text-sm uppercase tracking-wide">Kontrol Kamera</h3>
  </div>

  <!-- Kanan: Tombol Kontrol -->
  <div class="flex items-center gap-3">
    <button id="camera-switch"
      class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-white border border-gray-300 text-gray-700 hover:bg-blue-50 hover:border-blue-400 transition-all text-sm shadow-sm">
      🔄 Flip
    </button>
    
    <button id="camera-mode-toggle"
      class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-white border border-gray-300 text-gray-700 hover:bg-blue-50 hover:border-blue-400 transition-all text-sm shadow-sm">
      🎥 Mode: Video
    </button>
    
    <button id="camera-close"
      class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 border border-red-200 hover:bg-red-100 hover:border-red-400 text-red-600 transition-all shadow-sm">
      ✕
    </button>
  </div>
</div>


    <!-- Stage -->
    <div class="relative bg-black">
      <!-- LIVE preview -->
      <video id="camera-video" autoplay playsinline muted
             class="w-full aspect-[4/3] object-contain"></video>

      <!-- PHOTO review -->
      <canvas id="camera-canvas" class="hidden w-full"></canvas>

      <!-- VIDEO review -->
      <video id="camera-playback" playsinline controls
             class="hidden w-full aspect-[4/3] object-contain"></video>

      <!-- Recording badge + timer -->
      <div id="rec-badge" class="hidden absolute top-2 left-2  items-center gap-2
                   px-2 py-1 rounded-full bg-red-600/90 text-white text-xs">
        <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
        <span>REC</span>
      </div>
      <div id="rec-timer" class="hidden absolute bottom-2 left-1/2 -translate-x-1/2
                   text-white text-sm bg-black/40 px-2 py-1 rounded">0:00</div>
    </div>

    <!-- Controls -->
   <!-- Camera Controls (Minimalist, Clean Design) -->
<div class="p-4 bg-white/70 backdrop-blur-md border-t flex items-center justify-center gap-8 rounded-b-xl">

  <!-- Retake -->

<!-- Retake (Ulangi) -->
<button id="camera-retake"
  class="hidden w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-blue-600 transition-all shadow-sm">
  <i class="fa-solid fa-rotate-right text-xl"></i>
</button>




  <!-- Capture Photo -->
  <button id="camera-capture"
    class="hidden w-16 h-16 flex items-center justify-center rounded-full bg-white border-4 border-emerald-600 hover:border-emerald-700 transition-all shadow-md active:scale-95">
    <!-- Icon: Camera -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3 7h4l2-3h6l2 3h4a1 1 0 011 1v11a1 1 0 01-1 1H3a1 1 0 01-1-1V8a1 1 0 011-1z" />
      <circle cx="12" cy="13" r="3" />
    </svg>
  </button>

  <!-- Record Video -->
  <button id="camera-record"
    class="w-16 h-16 flex items-center justify-center rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition-all active:scale-95">
    <!-- Icon: Circle Record -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="6" />
    </svg>
  </button>

  <!-- Stop Recording -->
  <button id="camera-stop"
    class="hidden w-16 h-16 flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md transition-all active:scale-95">
    <!-- Icon: Stop -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
      <rect x="8" y="8" width="8" height="8" rx="1" />
    </svg>
  </button>

  <!-- Use -->
  <button id="camera-use"
    class="hidden w-12 h-12 flex items-center justify-center rounded-full bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm">
    <!-- Icon: Check -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
  </button>

</div>

  </div>
</div>


        <!-- Contact Modal -->
        <div id="contact-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-sm mx-4 shadow-2xl">
                <h3 class="text-base sm:text-lg font-semibold mb-4">Share Contact</h3>
                <form id="contact-form">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input type="text" id="contact-name" class="w-full border rounded px-3 py-2 text-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Phone</label>
                        <input type="tel" id="contact-phone" class="w-full border rounded px-3 py-2 text-sm"
                            required>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" id="cancel-contact"
                            class="px-3 sm:px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">Cancel</button>
                        <button type="submit"
                            class="px-3 sm:px-4 py-2 bg-[#25D366] text-white rounded hover:bg-[#1ec257] text-sm">Share</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Location Modal -->
        <div id="location-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-sm mx-4 shadow-2xl">
                <h3 class="text-base sm:text-lg font-semibold mb-4">Share Location</h3>
                <div class="mb-4">
                    <button id="current-location"
                        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 mb-3 text-sm">Use Current
                        Location</button>
                    <div class="text-center text-gray-500 text-sm mb-3">or</div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Latitude</label>
                        <input type="number" id="location-lat" step="any"
                            class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Longitude</label>
                        <input type="number" id="location-lng" step="any"
                            class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancel-location"
                        class="px-3 sm:px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">Cancel</button>
                    <button type="button" id="share-location"
                        class="px-3 sm:px-4 py-2 bg-[#25D366] text-white rounded hover:bg-[#1ec257] text-sm">Share</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Poll Modal -->
<div id="poll-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[60] p-4"
     aria-labelledby="poll-modal-title" aria-hidden="true">
  <div class="bg-white rounded-2xl w-full max-w-md sm:max-w-lg shadow-xl overflow-hidden transform transition-all duration-300"
       role="dialog">
    
    <!-- Header -->
    <div class="px-5 py-4 border-b bg-gray-50">
      <h3 id="poll-modal-title" class="text-xl font-semibold text-gray-900">Buat Poll</h3>
      <div id="poll-mode" class="text-sm text-gray-500 mt-1">
        Select one <!-- Akan diperbarui via JS -->
      </div>
    </div>

    <!-- Body -->
    <div class="p-5 space-y-5 max-h-[400px] overflow-y-auto"> <!-- Tambah max-height dan scroll -->
      <!-- Pertanyaan -->
      <div>
        <label for="poll-q" class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
        <input id="poll-q" type="text"
               class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
               placeholder="Masukkan pertanyaan poll" aria-required="true" />
      </div>

      <!-- Opsi -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Opsi</label>
        <div id="poll-opts" class="space-y-3">
          <!-- Opsi akan ditambahkan via JS -->
        </div>
        <button id="poll-add" type="button"
                class="mt-3 w-full text-left text-emerald-600 text-sm px-3 py-2 border border-emerald-200 rounded-lg hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
          + Tambah opsi
        </button>
      </div>

      <!-- Multiple Answers -->
      <label class="flex items-center gap-3 cursor-pointer select-none">
        <input id="poll-multi" type="checkbox" class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500 transition-colors">
        <span class="text-sm text-gray-700">Izinkan jawaban ganda</span>
      </label>
    </div>

    <!-- Footer -->
    <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-3">
      <button id="poll-cancel" type="button" class="px-4 py-2 text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-300 rounded"
              aria-label="Batal membuat poll">Batal</button>
      <button id="poll-send" type="button" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
              aria-label="Buat poll">Buat</button>
    </div>
  </div>
</div>


    <!-- Context Menu (WA style + custom font for Reply/Delete) -->
    <div id="context-menu" class="hidden absolute bg-white shadow-lg rounded-lg py-2 z-[60] border border-gray-200">
        <button id="context-reply"
            class="wa-menu-font flex items-center w-full px-4 py-2 text-sm text-gray-800 hover:bg-[#E7F3FF] hover:text-[#0B57D0]">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10l-4-4m0 8l4-4H3" />
            </svg>
            Balas
        </button>
        <button id="context-delete"
            class="wa-menu-font flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M5 7h14" />
            </svg>
            Hapus
        </button>
        <button id="context-report"
            class="wa-menu-font hidden flex items-center w-full px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Laporkan
        </button>
    </div>
</div>

<!-- Report Modal -->
<div id="report-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
        <!-- Header -->
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Laporkan Pengguna</h3>
            <button id="report-modal-close" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="report-form" class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Laporan <span class="text-red-500">*</span></label>
                <select id="report-reason" name="reason" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Pilih alasan...</option>
                    <option value="spam">Spam</option>
                    <option value="harassment">Pelecehan</option>
                    <option value="inappropriate">Konten Tidak Pantas</option>
                    <option value="fake">Informasi Palsu</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea id="report-notes" rows="4" maxlength="1000"
                    name="notes"
                    placeholder="Jelaskan lebih detail tentang masalah yang terjadi..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"></textarea>
                <p class="text-xs text-gray-500 mt-1">Maksimal 1000 karakter</p>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="cancel-report"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-300 rounded-lg">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
    {{-- Custom font only for Reply/Delete menu (safe if your layout has @stack('styles')) --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
@endpush

<style>
    /* Saat rekaman, sembunyikan composer & munculkan bar rekaman */
.is-recording #compose-row{ display:none; }
.is-recording #voice-recording{ display:flex; }


    :root{
    --wa-bubble-in:  #ffffff;   /* incoming (kiri) */
    --wa-bubble-out: #d9fdd3;   /* outgoing (kanan) hijau WA */
    --wa-radius: 16px;
    --wa-shadow: 0 1px 1px rgba(0,0,0,.06);
  }
  .wa-row{ display:flex; margin:8px 12px; }
  .wa-row.in { justify-content:flex-start; }
  .wa-row.out{ justify-content:flex-end; }

  .wa-bubble{
    max-width: 420px;           /* sesuaikan */
    background: var(--wa-bubble-in);
    border-radius: var(--wa-radius);
    padding: 6px; box-shadow: var(--wa-shadow);
  }
  .wa-row.out .wa-bubble{ background: var(--wa-bubble-out); }

  .wa-img{
    position:relative; overflow:hidden;
    border-radius: 12px; background:#f2f2f2;
  }
  .wa-img img{
    display:block; width:100%; height:auto;
  }
  .wa-time{
    position:absolute; right:8px; bottom:8px;
    font-size:12px; line-height:1;
    color:#fff; background: rgba(0,0,0,.35);
    padding:2px 6px; border-radius:10px;
    backdrop-filter: blur(1.5px);
  }
  .wa-caption{
    margin-top:6px; font-size:14px; color:#111;
    word-wrap:break-word; white-space:pre-wrap;
  }
    #emoji-panel {
    max-width: 90vw;
    max-height: 70vh;
    overflow-y: auto; /* Untuk scroll jika konten terlalu banyak */
}
@media (max-width: 640px) { /* Sesuaikan breakpoint sesuai kebutuhan */
    #emoji-panel {
        width: 90vw !important;
        height: 60vh !important;
    }
}
    #message-input {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI Emoji", Roboto, "Noto Color Emoji", sans-serif;
    unicode-bidi: embed;
    font-size: 16px; /* Pastikan ukuran cukup besar untuk emoji */
    color: #000; /* Pastikan teks terlihat */
    background: #fff; /* Pastikan kontras dengan teks */
}
    /* ====== Chat bubble ====== */
    .chat-bubble {
        /* sudah ada max-width 75% di HTML/utility, ini tambahan: */
        position: relative;
        word-wrap: break-word;
    }

    /* jika pesannya pendek, bikin minimum width secukupnya */
    .chat-bubble[data-short="1"] {
        /* 14ch ≈ cukup untuk 1–2 kata + area chip; clamp agar tidak kebablasan */
        min-width: clamp(14ch, 42vw, 260px);
    }

    /* ====== Reactions row ====== */
    .reactions-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem;
        /* jarak antar chip */
        justify-content: flex-end;
    }

    /* ====== Reaction chip ====== */
    .reaction-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: 2px 8px;
        border-radius: 9999px;
        background: #fff;
        border: 1px solid #e5e7eb;
        /* gray-200 */
        box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        /* halus */
        font-size: 12px;
        line-height: 1;
        cursor: pointer;
        user-select: none;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease;
    }

    .reaction-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
    }

    .reaction-chip:active {
        transform: translateY(0);
    }

    /* chip yang sudah direaksi oleh current user */
    .reaction-chip.is-mine {
        background: #f0fdf4;
        /* green-50 */
        border-color: #86efac;
        /* green-300 */
    }

    /* emoji & angka */
    .reaction-chip .chip-emoji {
        font-size: 16px;
        line-height: 1;
    }

    .reaction-chip .chip-count {
        font-variant-numeric: tabular-nums;
    }

    /* ====== Reaction toast (sudah ada inline style, ini tambahan opsional) ====== */
    .reaction-toast.show {
        opacity: 1 !important;
        transform: translateY(0) scale(1) !important;
    }

    /* === WA THEME (biarkan punyamu) === */
    :root {
        --wa-header: #075E54;
        --wa-accent: #25D366;
        --wa-outgoing: #DCF8C6;
        --wa-bg: #ECE5DD;
    }

    /* Context menu smooth */
    #context-menu {
        min-width: 170px;
        transition: opacity .18s ease, transform .18s ease;
        transform: translateY(-8px);
        opacity: 0;
    }

    #context-menu.show {
        transform: translateY(0);
        opacity: 1;
    }

    /* Bubbles */
    .chat-bubble {
        position: relative;
        padding: .5rem .75rem;
        border: 0;
        box-shadow: 0 1px 0 rgba(0, 0, 0, .06);
        max-width: 85%;
        word-break: break-word;
    }

    .chat-bubble--right {
        background: var(--wa-outgoing);
        margin-left: auto;
        border-radius: 14px 14px 2px 14px;
    }

    .chat-bubble--left {
        background: #fff;
        border-radius: 14px 14px 14px 2px;
    }

    .chat-bubble--right::after {
        content: "";
        position: absolute;
        bottom: 0;
        right: -6px;
        width: 12px;
        height: 12px;
        background: var(--wa-outgoing);
        clip-path: polygon(0 0, 100% 100%, 0 100%);
        filter: drop-shadow(0 1px 0 rgba(0, 0, 0, .06));
    }

    .chat-bubble--left::before {
        content: "";
        position: absolute;
        bottom: 0;
        left: -6px;
        width: 12px;
        height: 12px;
        background: #fff;
        clip-path: polygon(100% 0, 0 100%, 100% 100%);
        filter: drop-shadow(0 1px 0 rgba(0, 0, 0, .06));
    }

    /* Date chip, time label, animasi, dll (punyamu) */
    .date-chip {
        margin: .75rem 0;
        padding: .125rem .5rem;
        font-size: 10.5px;
        color: #3f3f3f;
        background: #e2e2e2;
        display: inline-block;
        border-radius: 999px;
    }

    .time-label {
        display: block;
        font-size: 10px;
        color: #6b7280;
        margin-top: .125rem;
        text-align: right;
        user-select: none;
    }

    @keyframes msgFade {
        from {
            opacity: 0;
            transform: translateY(3px)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .jump-highlight {
        animation: jumpFlash 1.2s ease-in-out;
    }

    @keyframes jumpFlash {
        0% {
            box-shadow: 0 0 0 0 rgba(37, 211, 102, .0);
        }

        24% {
            box-shadow: 0 0 0 4px rgba(37, 211, 102, .35);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(37, 211, 102, .0);
        }
    }

    .wa-menu-font {
        font-family: 'Poppins', ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
        font-weight: 600;
        letter-spacing: .2px;
    }
</style>

@push('scripts')
    <script src="{{ asset('js/forum.js') }}"></script>
    <script>
          function renderWaImageMessage({ url, timeText, caption = "", outgoing = false }) {
    const side = outgoing ? "out" : "in";
    return `
      <div class="wa-row ${side}">
        <div class="wa-bubble">
          <div class="wa-img" onclick="openImageLightbox('${url.replace(/'/g,"&#39;")}')">
            <img src="${url}" alt="image message" loading="lazy" decoding="async">
            <span class="wa-time">${timeText}</span>
          </div>
          ${caption ? `<div class="wa-caption">${escapeHtml(caption)}</div>` : ""}
        </div>
      </div>`;
  }

  // Lightbox sederhana (boleh ganti dengan window.open bila ingin)
  function openImageLightbox(src){
    const wrap = document.createElement('div');
    wrap.style.cssText =
      "position:fixed;inset:0;background:rgba(0,0,0,.85);display:flex;align-items:center;justify-content:center;z-index:9999;";
    wrap.innerHTML = `
      <img src="${src}" alt="" style="max-width:95vw;max-height:95vh;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.4)">
    `;
    wrap.addEventListener('click', ()=> document.body.removeChild(wrap));
    document.body.appendChild(wrap);
  }

  function escapeHtml(s){
    return String(s)
      .replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")
      .replace(/"/g,"&quot;").replace(/'/g,"&#39;");
  }


    </script>
@endpush
@push('scripts')
    <script>
        (function() {
            const openBtn = document.getElementById('open-forum');
            const closeBtn = document.getElementById('close-forum');
            const chatBox = document.getElementById('chat-box');
            const attach = document.getElementById('attachment-menu');

            let savedY = 0;

            function lockBodyScroll() {
                savedY = window.scrollY || document.documentElement.scrollTop || 0;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${savedY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            }

            function unlockBodyScroll() {
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
                document.body.style.width = '';
                window.scrollTo(0, savedY);
            }

            // ekstra guard: bila di ujung scroll, jangan teruskan ke body (desktop)
            function stopChain(el) {
                if (!el) return;
                el.addEventListener('wheel', (e) => {
                    const up = e.deltaY < 0;
                    const atTop = el.scrollTop <= 0;
                    const atBottom = Math.ceil(el.scrollTop + el.clientHeight) >= el.scrollHeight;
                    if ((up && atTop) || (!up && atBottom)) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }, {
                    passive: false
                });
            }

            openBtn?.addEventListener('click', lockBodyScroll);
            closeBtn?.addEventListener('click', unlockBodyScroll);

            stopChain(chatBox);
            stopChain(attach);
        })();
    </script>
    <script type="module">
  window.ForumPolicy.init({
    statusUrl: '/forum/status',
    selectors: {
      messageInput:   '#chat-input',
      sendButton:     '#btn-send',
      attachmentToggle:'#btn-attach',
      buttons: {
        image:    '#btn-image',
        document: '#btn-doc',
        poll:     '#btn-poll',
        voiceIcon:'#btn-voice'
      }
    },
    showError: (m)=> (window.toast?.error(m) ?? alert(m))
  });

  // Contoh guard sebelum buka picker file
  document.querySelector('#btn-image')?.addEventListener('click', (e)=>{
    if (!ForumPolicy.guardAttachmentAction()) e.preventDefault();
  });
  document.querySelector('#btn-poll')?.addEventListener('click', (e)=>{
    if (!ForumPolicy.guardPollAction()) e.preventDefault();
  });
  document.querySelector('#btn-voice')?.addEventListener('click', (e)=>{
    if (!ForumPolicy.guardVoiceAction()) e.preventDefault();
  });
</script>

@endpush
