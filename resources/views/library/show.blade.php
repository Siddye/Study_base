@extends('layouts.app', ['title' => $document->title . ' - Detail Dokumen'])

@section('content')
<div class="p-6 md:p-8 max-w-6xl mx-auto w-full space-y-6">
    
    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('library.index') }}" class="hover:text-slate-200 flex items-center gap-1">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            Kembali ke Library
        </a>
        @if($document->folder)
            <span>/</span>
            <a href="{{ route('library.index', ['folder_id' => $document->folder->id]) }}" class="hover:text-slate-200">
                📁 {{ $document->folder->name }}
            </a>
        @endif
        <span>/</span>
        <span class="text-slate-300 truncate">{{ $document->title }}</span>
    </div>

    <!-- Document Info Card -->
    <div class="glass-card rounded-2xl p-6 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $document->filetype === 'pdf' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                    {{ $document->filetype }}
                </span>
                
                @if($document->status === 'ready')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Status: Siap (Ready for RAG)
                    </span>
                @elseif($document->status === 'processing')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        Status: Sedang Diproses
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                        Status: Gagal Diproses
                    </span>
                @endif
            </div>

            <h1 class="text-xl md:text-2xl font-bold text-white tracking-tight">
                {{ $document->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 pt-1">
                <span>File: <strong class="text-slate-200">{{ $document->filename }}</strong></span>
                <span>Ukuran: <strong class="text-slate-200">{{ number_format($document->file_size / 1024, 1) }} KB</strong></span>
                <span>Total Halaman: <strong class="text-slate-200">{{ $document->page_count }} hal</strong></span>
                <span>Total Chunks: <strong class="text-brand-300">{{ $document->chunks()->count() }} potongan</strong></span>
            </div>

            @if($document->error_message)
                <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs mt-2">
                    <strong>Catatan Error:</strong> {{ $document->error_message }}
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2 md:self-start">
            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
                <span>Buka File Asli</span>
            </a>

            <form action="{{ route('documents.reprocess', $document) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-200 border border-indigo-500/30 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Proses Ulang RAG</span>
                </button>
            </form>

            <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Chunks Breakdown Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-200 flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-brand-400"></i>
                Daftar Chunks Teks (Hasil Ekstraksi & Embedding)
            </h2>
            <span class="text-xs text-slate-400">Menampilkan potongan teks yang digunakan AI saat pencarian</span>
        </div>

        @if($chunks->isEmpty())
            <div class="glass-card rounded-xl p-8 text-center border border-slate-800 text-xs text-slate-500">
                Belum ada chunks teks untuk dokumen ini. Silakan klik "Proses Ulang RAG" di atas.
            </div>
        @else
            <div class="space-y-3">
                @foreach($chunks as $chunk)
                    <div class="glass-card rounded-xl p-4 border border-slate-800/90 hover:border-slate-700 transition-colors">
                        <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-slate-800/80">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-brand-500/20 text-brand-300 font-bold text-[11px] border border-brand-500/30">
                                    Chunk #{{ $chunk->chunk_index + 1 }}
                                </span>
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[11px] font-medium border border-slate-700">
                                    📍 Halaman {{ $chunk->page_number }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="text-slate-400">~{{ $chunk->token_count }} token</span>
                                @if(!empty($chunk->embedding))
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono text-[10px]">
                                        Vector Embedded ({{ count($chunk->embedding) }} dim)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px]">
                                        No Vector
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-xs text-slate-300 leading-relaxed font-mono whitespace-pre-wrap bg-slate-900/50 p-3 rounded-lg border border-slate-800/50">
                            {{ $chunk->content }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $chunks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
