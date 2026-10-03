@extends('layouts.app', ['title' => 'Library & Dokumen - Study Knowledge Base'])

@section('content')
<div class="p-6 md:p-8 max-w-7xl mx-auto w-full space-y-6">
    
    <!-- Top Bar: Header & Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i data-lucide="folder-kanban" class="w-6 h-6 text-brand-400"></i>
                Library Koleksi Materi
            </h1>
            <p class="text-xs md:text-sm text-slate-400 mt-1">
                Kelola file kuliah Anda (PDF/DOCX) yang dipecah dan dipelajari oleh RAG AI.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('newFolderModal').classList.remove('hidden')" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all">
                <i data-lucide="folder-plus" class="w-4 h-4 text-purple-400"></i>
                <span>Folder Baru</span>
            </button>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 flex items-center gap-1.5 transition-all">
                <i data-lucide="upload" class="w-4 h-4"></i>
                <span>Upload Dokumen</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Folder Pills Filter -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-none text-xs">
            <a href="{{ route('library.index') }}" class="px-3.5 py-1.5 rounded-xl font-medium transition-colors shrink-0 {{ !request('folder_id') ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:bg-slate-800 hover:text-slate-200 border border-slate-800' }}">
                Semua Dokumen
            </a>
            @foreach($folders as $f)
                <a href="{{ route('library.index', ['folder_id' => $f->id]) }}" class="px-3.5 py-1.5 rounded-xl font-medium transition-colors shrink-0 flex items-center gap-2 {{ request('folder_id') == $f->id ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:bg-slate-800 hover:text-slate-200 border border-slate-800' }}">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $f->color }}"></span>
                    <span>{{ $f->name }}</span>
                    <span class="text-[10px] opacity-75">({{ $f->documents_count }})</span>
                </a>
            @endforeach
        </div>

        <!-- Keyword Filter in Library -->
        <form action="{{ route('library.index') }}" method="GET" class="relative sm:w-64 shrink-0">
            @if(request('folder_id'))
                <input type="hidden" name="folder_id" value="{{ request('folder_id') }}">
            @endif
            <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Filter judul file..."
                class="w-full bg-slate-900/80 border border-slate-800 rounded-xl pl-9 pr-4 py-1.5 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 transition-colors">
        </form>
    </div>

    <!-- Active Folder Header (if filtered) -->
    @if($currentFolder)
        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $currentFolder->color }}"></div>
                <div>
                    <h2 class="text-sm font-bold text-slate-200">Folder: {{ $currentFolder->name }}</h2>
                    <p class="text-[11px] text-slate-400">{{ $currentFolder->documents()->count() }} dokumen di dalam folder ini</p>
                </div>
            </div>
            <form action="{{ route('folders.destroy', $currentFolder) }}" method="POST" onsubmit="return confirm('Hapus folder ini? Dokumen di dalamnya akan dipindahkan ke root.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-medium flex items-center gap-1">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Hapus Folder
                </button>
            </form>
        </div>
    @endif

    <!-- Document Grid -->
    @if($documents->isEmpty())
        <div class="glass-card rounded-2xl p-12 text-center border border-slate-800">
            <div class="w-14 h-14 rounded-2xl bg-slate-800/80 flex items-center justify-center mx-auto mb-3 text-slate-500">
                <i data-lucide="folder-open" class="w-7 h-7"></i>
            </div>
            <h3 class="text-base font-bold text-slate-300">Belum ada dokumen yang sesuai</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Silakan upload file modul kuliah (PDF atau DOCX) Anda untuk diproses oleh RAG AI.
            </p>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="mt-4 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 transition-colors">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Upload Dokumen Baru
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($documents as $doc)
                <div class="glass-card rounded-2xl p-4 border border-slate-800/80 hover:border-brand-500/50 transition-all flex flex-col justify-between group">
                    <div>
                        <!-- Card Top: Type badge & Status -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $doc->filetype === 'pdf' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                                {{ $doc->filetype }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                @if($doc->status === 'ready')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Ready
                                    </span>
                                @elseif($doc->status === 'processing')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Processing
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Failed
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body: Title & Folder info -->
                        <a href="{{ route('documents.show', $doc) }}" class="block">
                            <h3 class="text-sm font-bold text-slate-100 group-hover:text-brand-300 transition-colors line-clamp-2 mb-2 leading-snug">
                                {{ $doc->title }}
                            </h3>
                        </a>

                        @if($doc->folder)
                            <p class="text-[11px] text-slate-400 flex items-center gap-1.5 mb-2 truncate">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $doc->folder->color }}"></span>
                                <span class="truncate">{{ $doc->folder->name }}</span>
                            </p>
                        @endif
                    </div>

                    <!-- Card Footer: Stats & Actions -->
                    <div class="pt-3 border-t border-slate-800/80 mt-3 flex items-center justify-between text-[11px] text-slate-400">
                        <div>
                            <span>{{ $doc->page_count }} hal</span>
                            <span class="mx-1">•</span>
                            <span>{{ $doc->chunks_count }} chunk</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('documents.show', $doc) }}" class="p-1.5 hover:text-brand-300 hover:bg-slate-800 rounded-lg transition-colors" title="Lihat Detail & Chunk">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini beserta semua vector chunk miliknya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors" title="Hapus Dokumen">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $documents->links() }}
        </div>
    @endif

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
                <p class="text-[11px] text-slate-500 mt-1">Sistem akan mengekstrak teks per halaman & membuat vector chunk.</p>
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
                        <option value="{{ $f->id }}" {{ request('folder_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition-colors flex items-center gap-1.5">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                    Upload & Ekstrak RAG
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
                <input type="text" name="name" required placeholder="Contoh: Semester 4, Pemrograman Web"
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
