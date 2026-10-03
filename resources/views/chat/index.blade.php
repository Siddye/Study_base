@extends('layouts.app', ['title' => 'Ask Your Knowledge - AI Study Chat'])

@push('styles')
<!-- Markdown Parser CDN -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
    .markdown-content p { margin-bottom: 0.75rem; }
    .markdown-content p:last-child { margin-bottom: 0; }
    .markdown-content ul { list-style-type: disc; margin-left: 1.25rem; margin-bottom: 0.75rem; }
    .markdown-content ol { list-style-type: decimal; margin-left: 1.25rem; margin-bottom: 0.75rem; }
    .markdown-content li { margin-bottom: 0.25rem; }
    .markdown-content h1, .markdown-content h2, .markdown-content h3 { font-weight: 700; color: #f8fafc; margin-top: 1rem; margin-bottom: 0.5rem; }
    .markdown-content strong { color: #818cf8; font-weight: 600; }
    .markdown-content code { background: rgba(15, 23, 42, 0.8); padding: 0.2rem 0.4rem; border-radius: 0.375rem; font-family: monospace; font-size: 0.85em; color: #cbd5e1; }
    .markdown-content pre { background: #0b0f19; padding: 0.75rem; border-radius: 0.5rem; overflow-x: auto; margin-bottom: 0.75rem; }
    .markdown-content blockquote { border-left: 3px solid #6366f1; padding-left: 0.75rem; color: #94a3b8; margin-bottom: 0.75rem; font-style: italic; }
</style>
@endpush

@section('content')
<div class="flex-1 flex flex-col md:flex-row h-full overflow-hidden">

    <!-- Sessions Sidebar -->
    <div class="w-full md:w-72 bg-slate-900/70 border-r border-slate-800 flex flex-col shrink-0 h-48 md:h-full">
        <!-- New Chat Button & Folder Scope -->
        <div class="p-3.5 border-b border-slate-800 space-y-2">
            <form action="{{ route('chat.session.store') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2.5 px-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/25 flex items-center justify-center gap-2 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Sesi Tanya Baru</span>
                </button>
            </form>
        </div>

        <!-- Sessions List -->
        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            @if($sessions->isEmpty())
                <div class="p-4 text-center text-xs text-slate-500">
                    Belum ada sesi tanya jawab. Klik tombol di atas untuk memulai.
                </div>
            @else
                @foreach($sessions as $s)
                    <div class="group flex items-center justify-between p-2.5 rounded-xl text-xs font-medium transition-all {{ optional($activeSession)->id === $s->id ? 'bg-brand-600/20 text-brand-200 border border-brand-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <a href="{{ route('chat.index', ['session_id' => $s->id]) }}" class="flex items-center gap-2.5 truncate flex-1 pr-2">
                            <i data-lucide="message-square" class="w-3.5 h-3.5 shrink-0 {{ optional($activeSession)->id === $s->id ? 'text-brand-400' : 'text-slate-500' }}"></i>
                            <span class="truncate">{{ $s->title }}</span>
                        </a>

                        <form action="{{ route('chat.session.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus riwayat sesi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-rose-400 rounded transition-opacity" title="Hapus Sesi">
                                <i data-lucide="trash" class="w-3 h-3"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Active Chat Conversation Area -->
    <div class="flex-1 flex flex-col h-full bg-slate-950 overflow-hidden relative">

        @if(!$activeSession)
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-brand-600/10 border border-brand-500/20 flex items-center justify-center text-brand-400 mb-4">
                    <i data-lucide="bot" class="w-8 h-8"></i>
                </div>
                <h2 class="text-xl font-bold text-white">Ask Your Study Knowledge</h2>
                <p class="text-xs text-slate-400 max-w-sm mt-1 mb-6">
                    Mulai sesi belajar baru untuk bertanya, membuat rangkuman, atau menguji pemahaman dengan kuis dari materi kuliah Anda.
                </p>
                <form action="{{ route('chat.session.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 flex items-center gap-2 transition-all">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Buat Sesi Pertama</span>
                    </button>
                </form>
            </div>
        @else
            <!-- Chat Header -->
            <div class="p-3.5 px-6 border-b border-slate-800/90 bg-slate-900/60 backdrop-blur-md flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand-600/20 text-brand-400 border border-brand-500/30 flex items-center justify-center">
                        <i data-lucide="bot" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-xs md:text-sm font-bold text-white truncate max-w-xs md:max-w-md">{{ $activeSession->title }}</h2>
                        <p class="text-[10px] text-slate-400 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            RAG Model: Hanya menjawab berdasarkan sitasi dokumen
                        </p>
                    </div>
                </div>

                @if($activeSession->folder)
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-slate-800 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $activeSession->folder->color }}"></span>
                        Scope: {{ $activeSession->folder->name }}
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-slate-800/80 text-slate-400 border border-slate-800">
                        Scope: Seluruh Perpustakaan
                    </span>
                @endif
            </div>

            <!-- Messages Stream -->
            <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-5">
                @if($activeSession->messages->isEmpty())
                    <!-- Prompt Suggestions -->
                    <div class="max-w-2xl mx-auto py-10 text-center space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/60 flex items-center justify-center mx-auto text-brand-400">
                            <i data-lucide="messages-square" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold text-white">Apa yang ingin Anda pelajari hari ini?</h3>
                        <p class="text-xs text-slate-400 max-w-md mx-auto">
                            Pilih salah satu mode belajar di bawah atau langsung ketik pertanyaan seputar materi PDF/DOCX yang telah di-upload.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-4 text-left">
                            <button onclick="setQueryPrompt('Jelaskan konsep utama dari dokumen kuliah yang sudah di-upload dengan bahasa sederhana.', 'explain')" class="p-3.5 rounded-xl bg-slate-900/70 hover:bg-slate-800 border border-slate-800 text-xs transition-all">
                                <div class="font-bold text-brand-300 flex items-center gap-1.5 mb-1">
                                    <span>🧠 Explain Concept</span>
                                </div>
                                <p class="text-slate-400 text-[11px]">Jelaskan materi dengan analogi mudah & ramah mahasiswa.</p>
                            </button>

                            <button onclick="setQueryPrompt('Buat ringkasan terstruktur poin penting dari seluruh materi.', 'summarize')" class="p-3.5 rounded-xl bg-slate-900/70 hover:bg-slate-800 border border-slate-800 text-xs transition-all">
                                <div class="font-bold text-emerald-300 flex items-center gap-1.5 mb-1">
                                    <span>📝 Summarize Key Points</span>
                                </div>
                                <p class="text-slate-400 text-[11px]">Ringkas materi menjadi bullet-point & definisi inti.</p>
                            </button>

                            <button onclick="setQueryPrompt('Buatkan 3 soal pilihan ganda dari materi beserta kunci dan penjelasannya.', 'quiz')" class="p-3.5 rounded-xl bg-slate-900/70 hover:bg-slate-800 border border-slate-800 text-xs transition-all">
                                <div class="font-bold text-amber-300 flex items-center gap-1.5 mb-1">
                                    <span>❓ Quiz Me</span>
                                </div>
                                <p class="text-slate-400 text-[11px]">Uji pemahaman Anda dengan soal & kunci jawaban.</p>
                            </button>

                            <button onclick="setQueryPrompt('Buatkan daftar flashcards konsep dan istilah kunci dari materi.', 'flashcards')" class="p-3.5 rounded-xl bg-slate-900/70 hover:bg-slate-800 border border-slate-800 text-xs transition-all">
                                <div class="font-bold text-purple-300 flex items-center gap-1.5 mb-1">
                                    <span>🃏 Flashcards</span>
                                </div>
                                <p class="text-slate-400 text-[11px]">Kartu hafalan definisi dan formula penting.</p>
                            </button>
                        </div>
                    </div>
                @else
                    @foreach($activeSession->messages as $msg)
                        @if($msg->role === 'user')
                            <!-- User Message -->
                            <div class="flex justify-end">
                                <div class="max-w-2xl bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-2xl rounded-tr-none px-4 py-3 text-xs md:text-sm shadow-md">
                                    <p class="whitespace-pre-wrap leading-relaxed">{{ $msg->content }}</p>
                                </div>
                            </div>
                        @else
                            <!-- AI Message -->
                            <div class="flex items-start gap-3 max-w-3xl">
                                <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-brand-400 shrink-0 mt-1">
                                    <i data-lucide="bot" class="w-4 h-4"></i>
                                </div>

                                <div class="flex-1 space-y-3">
                                    <!-- AI Content Card -->
                                    <div class="glass-card rounded-2xl rounded-tl-none p-4 md:p-5 border border-slate-800 text-xs md:text-sm text-slate-200 leading-relaxed shadow-sm markdown-content">
                                        {!! Str::markdown($msg->content) !!}
                                    </div>

                                    <!-- Sources Pill & Drawer (If Available) -->
                                    @if(!empty($msg->sources) && is_array($msg->sources))
                                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800/80 space-y-2">
                                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-400">
                                                <span class="flex items-center gap-1.5">
                                                    <i data-lucide="book-marked" class="w-3.5 h-3.5 text-brand-400"></i>
                                                    Sumber Rujukan Terverifikasi ({{ count($msg->sources) }})
                                                </span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                                @foreach($msg->sources as $src)
                                                    <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/60 text-[11px] text-slate-300">
                                                        <div class="flex items-center justify-between gap-1 mb-1 font-semibold text-brand-300">
                                                            <span class="truncate">{{ $src['document_title'] ?? 'Dokumen' }}</span>
                                                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] shrink-0">
                                                                Hal. {{ $src['page_number'] ?? 1 }}
                                                            </span>
                                                        </div>
                                                        <p class="text-slate-400 line-clamp-2 text-[10px] font-mono">
                                                            "{{ $src['snippet'] ?? '' }}"
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Chat Input Area -->
            <div class="p-4 border-t border-slate-800/90 bg-slate-900/70 backdrop-blur-md shrink-0 space-y-3">
                <!-- Mode Selector Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none text-[11px]">
                    <span class="text-slate-500 font-semibold uppercase text-[10px] mr-1">Mode:</span>
                    
                    <button type="button" onclick="selectMode('ask')" id="mode-ask" class="mode-pill px-3 py-1 rounded-full font-medium transition-colors bg-brand-600 text-white shadow-sm">
                        💬 Tanya Biasa
                    </button>
                    <button type="button" onclick="selectMode('explain')" id="mode-explain" class="mode-pill px-3 py-1 rounded-full font-medium transition-colors bg-slate-800 text-slate-300 hover:bg-slate-700">
                        🧠 Explain
                    </button>
                    <button type="button" onclick="selectMode('summarize')" id="mode-summarize" class="mode-pill px-3 py-1 rounded-full font-medium transition-colors bg-slate-800 text-slate-300 hover:bg-slate-700">
                        📝 Summarize
                    </button>
                    <button type="button" onclick="selectMode('quiz')" id="mode-quiz" class="mode-pill px-3 py-1 rounded-full font-medium transition-colors bg-slate-800 text-slate-300 hover:bg-slate-700">
                        ❓ Quiz Me
                    </button>
                    <button type="button" onclick="selectMode('flashcards')" id="mode-flashcards" class="mode-pill px-3 py-1 rounded-full font-medium transition-colors bg-slate-800 text-slate-300 hover:bg-slate-700">
                        🃏 Flashcards
                    </button>
                </div>

                <!-- Input form -->
                <form id="chatForm" action="{{ route('chat.send', $activeSession) }}" method="POST" class="relative flex items-end gap-2">
                    @csrf
                    <input type="hidden" name="mode" id="selectedModeInput" value="ask">
                    
                    <div class="relative flex-1">
                        <textarea name="message" id="messageInput" rows="2" required placeholder="Tanyakan apa pun tentang materi kuliah Anda (Shift + Enter untuk baris baru)..."
                            class="w-full bg-slate-950/90 border border-slate-700/80 rounded-xl pl-4 pr-12 py-3 text-xs md:text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all resize-none"></textarea>
                    </div>

                    <button type="submit" id="sendBtn" class="p-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center justify-center shrink-0">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentMode = 'ask';

    function selectMode(mode) {
        currentMode = mode;
        document.getElementById('selectedModeInput').value = mode;

        document.querySelectorAll('.mode-pill').forEach(btn => {
            btn.classList.remove('bg-brand-600', 'text-white', 'shadow-sm');
            btn.classList.add('bg-slate-800', 'text-slate-300');
        });

        const activeBtn = document.getElementById('mode-' + mode);
        if (activeBtn) {
            activeBtn.classList.remove('bg-slate-800', 'text-slate-300');
            activeBtn.classList.add('bg-brand-600', 'text-white', 'shadow-sm');
        }
    }

    function setQueryPrompt(text, mode) {
        selectMode(mode);
        const input = document.getElementById('messageInput');
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    // Auto scroll to bottom
    const container = document.getElementById('messagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }

    // Submit on Enter without Shift
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').submit();
            }
        });
    }
</script>
@endpush
