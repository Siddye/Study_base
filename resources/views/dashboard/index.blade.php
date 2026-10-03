@extends('layouts.app', ['title' => 'Dashboard - Study Knowledge Base'])

@section('content')
<div class="p-6 md:p-8 max-w-7xl mx-auto w-full space-y-8">
    
    <!-- Welcome Header & Quick Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight flex items-center gap-2">
                Halo, {{ explode(' ', Auth::user()->name)[0] }}! 👋
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Perpustakaan digital pribadi & asisten RAG materi kuliah Anda siap digunakan.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('chat.index') }}" class="px-4 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-brand-600/25 flex items-center gap-2 transition-all">
                <i data-lucide="bot" class="w-4 h-4"></i>
                <span>Tanya AI Materi</span>
            </a>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all">
                <i data-lucide="upload-cloud" class="w-4 h-4 text-brand-400"></i>
                <span>Upload Dokumen</span>
            </button>
        </div>
    </div>

    <!-- Quick Semantic Search Hero Banner -->
    <div class="glass-card rounded-2xl p-6 md:p-8 relative overflow-hidden border border-brand-500/20 bg-gradient-to-br from-slate-900 via-indigo-950/30 to-slate-900">
        <div class="max-w-2xl relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-300 border border-brand-500/30 mb-3">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                Semantic Vector Search
            </span>
            <h2 class="text-xl md:text-2xl font-bold text-white tracking-tight mb-2">
                Cari Berdasarkan Makna, Bukan Sekadar Kata Kunci
            </h2>
            <p class="text-xs md:text-sm text-slate-300 mb-6">
                Ketik pertanyaan atau konsep apa pun. Sistem akan mencari potongan teks (chunks) yang paling relevan dari seluruh dokumen kuliah Anda.
            </p>

            <form action="{{ route('search.index') }}" method="GET" class="flex gap-2">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" placeholder="Contoh: Apa perbedaan algoritma Dijkstra dan Bellman-Ford?"
                        class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-12 pr-4 py-3 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all shadow-inner">
                </div>
                <button type="submit" class="px-5 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 shrink-0">
                    <span>Cari</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Dokumen</p>
                <p class="text-2xl font-bold text-white tracking-tight">{{ $totalDocs }}</p>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Vector Chunks</p>
                <p class="text-2xl font-bold text-white tracking-tight">{{ $totalChunks }}</p>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 shrink-0">
                <i data-lucide="folder-tree" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Folder & Matkul</p>
                <p class="text-2xl font-bold text-white tracking-tight">{{ $totalFolders }}</p>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                <i data-lucide="hard-drive" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Ukuran</p>
                <p class="text-2xl font-bold text-white tracking-tight">{{ number_format($totalStorage / (1024 * 1024), 1) }} MB</p>
            </div>
        </div>
    </div>

    <!-- Recent Documents & Quick Folders Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Documents Column (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-200 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-brand-400"></i>
                    Dokumen Terbaru
                </h3>
                <a href="{{ route('library.index') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if($recentDocs->isEmpty())
                <div class="glass-card rounded-2xl p-8 text-center border border-slate-800">
                    <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <i data-lucide="files" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-300">Belum ada dokumen yang di-upload</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Upload slide kuliah, modul PDF, atau makalah DOCX Anda untuk mulai memanfaatkan fitur tanya jawab AI.
                    </p>
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="mt-4 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl inline-flex items-center gap-2 transition-colors">
                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                        Upload File Pertama
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($recentDocs as $doc)
                        <a href="{{ route('documents.show', $doc) }}" class="glass-card rounded-xl p-4 border border-slate-800/80 hover:border-brand-500/50 transition-all group flex flex-col justify-between h-36">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $doc->filetype === 'pdf' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                                            {{ $doc->filetype }}
                                        </span>
                                        @if($doc->folder)
                                            <span class="text-[11px] text-slate-400 truncate max-w-[120px]">
                                                📁 {{ $doc->folder->name }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($doc->status === 'ready')
                                        <span class="w-2 h-2 rounded-full bg-emerald-400" title="Siap untuk RAG"></span>
                                    @elseif($doc->status === 'processing')
                                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse" title="Sedang diproses"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-rose-400" title="Gagal diproses"></span>
                                    @endif
                                </div>
                                <h4 class="text-sm font-semibold text-slate-200 group-hover:text-brand-300 transition-colors line-clamp-2">
                                    {{ $doc->title }}
                                </h4>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-800/60">
                                <span>{{ $doc->page_count }} Halaman</span>
                                <span>{{ $doc->chunks_count ?? 0 }} Chunks</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Folders & Quick Sessions Column -->
        <div class="space-y-6">
            <!-- Folders Widget -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-200 flex items-center gap-2">
                        <i data-lucide="folder" class="w-4 h-4 text-purple-400"></i>
                        Kategori & Folder
                    </h3>
                    <button onclick="document.getElementById('newFolderModal').classList.remove('hidden')" class="text-xs text-brand-400 hover:text-brand-300 font-semibold flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Folder Baru
                    </button>
                </div>

                @if($folders->isEmpty())
                    <div class="glass-card rounded-xl p-4 text-center border border-slate-800 text-xs text-slate-500">
                        Belum ada folder matkul/semester.
                    </div>
                @else
                    <div class="space-y-1.5">
                        @foreach($folders as $folder)
                            <a href="{{ route('library.index', ['folder_id' => $folder->id]) }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800/80 text-xs font-medium text-slate-300 transition-all">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $folder->color }}"></div>
                                    <span class="truncate">{{ $folder->name }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px]">
                                    {{ $folder->documents_count }} file
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Recent Chat Sessions Widget -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-200 flex items-center gap-2">
                        <i data-lucide="message-square" class="w-4 h-4 text-emerald-400"></i>
                        Sesi Tanya AI
                    </h3>
                    <a href="{{ route('chat.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold">Buka Chat</a>
                </div>

                @if($recentChats->isEmpty())
                    <div class="glass-card rounded-xl p-4 text-center border border-slate-800 text-xs text-slate-500">
                        Belum ada riwayat chat.
                    </div>
                @else
                    <div class="space-y-1.5">
                        @foreach($recentChats as $chat)
                            <a href="{{ route('chat.index', ['session_id' => $chat->id]) }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800/80 text-xs font-medium text-slate-300 transition-all">
                                <span class="truncate pr-2">{{ $chat->title }}</span>
                                <span class="text-[10px] text-slate-500 shrink-0">{{ $chat->created_at->diffForHumans() }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="glass-card rounded-2xl p-6 max-w-md w-full border border-slate-700 shadow-2xl relative">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="upload-cloud" class="w-5 h-5 text-brand-400"></i>
                Upload Materi Kuliah
            </h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Pilih File (PDF atau DOCX)</label>
                <input type="file" name="file" accept=".pdf,.docx,.doc" required
                    class="w-full text-xs text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-600 file:text-white hover:file:bg-brand-500 cursor-pointer bg-slate-900/80 border border-slate-700/80 rounded-xl p-2">
                <p class="text-[11px] text-slate-500 mt-1">Maksimal ukuran file: 25 MB</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Judul Materi (Opsional)</label>
                <input type="text" name="title" placeholder="Kosongkan untuk menggunakan nama file asli"
                    class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Folder / Mata Kuliah</label>
                <select name="folder_id" class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                    <option value="">-- Tanpa Folder (Root) --</option>
                    @foreach($folders as $f)
                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition-colors flex items-center gap-1.5">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                    Upload & Proses RAG
                </button>
            </div>
        </form>
    </div>
</div>

<!-- New Folder Modal -->
<div id="newFolderModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="glass-card rounded-2xl p-6 max-w-sm w-full border border-slate-700 shadow-2xl relative">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-5 h-5 text-purple-400"></i>
                Buat Folder Baru
            </h3>
            <button onclick="document.getElementById('newFolderModal').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('folders.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Nama Folder</label>
                <input type="text" name="name" required placeholder="Contoh: Semester 4, Jaringan Komputer"
                    class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Warna Tag</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color" value="#6366f1" class="w-10 h-8 rounded-lg bg-transparent border-0 cursor-pointer">
                    <span class="text-xs text-slate-400">Pilih warna penanda folder</span>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('newFolderModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl transition-colors">
                    Simpan Folder
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
