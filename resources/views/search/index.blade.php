@extends('layouts.app', ['title' => 'Semantic Vector Search - Study Knowledge Base'])

@section('content')
<div class="p-6 md:p-8 max-w-5xl mx-auto w-full space-y-6">
    
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="sparkles" class="w-6 h-6 text-brand-400"></i>
            Semantic Search Dokumen
        </h1>
        <p class="text-xs md:text-sm text-slate-400 mt-1">
            Mencari potongan materi berdasarkan arti dan konteks makna (Cosine Similarity Vector Search).
        </p>
    </div>

    <!-- Search Form Bar -->
    <div class="glass-card rounded-2xl p-4 md:p-6 border border-slate-800">
        <form action="{{ route('search.index') }}" method="GET" class="space-y-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ $query }}" required placeholder="Ketik kata kunci atau pertanyaan materi (contoh: normalisasi database 1NF ke 3NF)..."
                        class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-12 pr-4 py-3 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>

                <!-- Scope by Folder -->
                <select name="folder_id" class="bg-slate-900/90 border border-slate-700/80 rounded-xl px-4 py-3 text-xs text-slate-200 focus:outline-none focus:border-brand-500 shrink-0">
                    <option value="">Semua Folder / Matkul</option>
                    @foreach($folders as $f)
                        <option value="{{ $f->id }}" {{ $folderId == $f->id ? 'selected' : '' }}>
                            📁 {{ $f->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2 shrink-0 shadow-lg shadow-brand-600/30">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Cari Semantik</span>
                </button>
            </div>
        </form>
    </div>

    @if($errorMessage)
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-rose-400 mt-0.5"></i>
            <div>
                <p class="font-bold">Gagal melakukan pencarian vektor:</p>
                <p class="mt-1">{{ $errorMessage }}</p>
                <p class="text-[11px] text-rose-400/80 mt-2">Pastikan konfigurasi API Key (Gemini/OpenAI) di file .env sudah diisi dengan benar.</p>
            </div>
        </div>
    @endif

    <!-- Results Section -->
    @if(!empty($query) && !$errorMessage)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-200">
                    Hasil Pencarian untuk: <span class="text-brand-300 font-mono">"{{ $query }}"</span>
                </h2>
                <span class="text-xs text-slate-400">{{ count($results) }} potongan paling relevan ditemukan</span>
            </div>

            @if(empty($results))
                <div class="glass-card rounded-2xl p-8 text-center border border-slate-800 text-xs text-slate-400">
                    Tidak ditemukan potongan materi yang relevan dengan query tersebut. Coba gunakan istilah atau kalimat lain.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($results as $res)
                        <div class="glass-card rounded-2xl p-5 border border-slate-800 hover:border-slate-700 transition-all space-y-3">
                            <!-- Card Header: Title, Page, Similarity score -->
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $res['filetype'] === 'pdf' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                                        {{ $res['filetype'] }}
                                    </span>
                                    <a href="{{ route('documents.show', $res['document_id']) }}" class="text-sm font-bold text-slate-100 hover:text-brand-300 transition-colors">
                                        {{ $res['document_title'] }}
                                    </a>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-800 border border-slate-700 text-xs text-slate-200 font-semibold flex items-center gap-1">
                                        📍 Halaman {{ $res['page_number'] }}
                                    </span>

                                    <!-- Score Meter -->
                                    <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-brand-500/10 border border-brand-500/20 text-xs text-brand-300">
                                        <i data-lucide="gauge" class="w-3.5 h-3.5"></i>
                                        <span>{{ number_format($res['score'] * 100, 1) }}% Match</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Text Content Snippet -->
                            <div class="p-3.5 rounded-xl bg-slate-900/70 border border-slate-800/80 text-xs text-slate-300 font-mono leading-relaxed whitespace-pre-wrap">
                                {{ $res['content'] }}
                            </div>

                            <!-- Quick Action: Chat this context -->
                            <div class="flex justify-end pt-1">
                                <a href="{{ route('chat.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold flex items-center gap-1">
                                    <span>Buka Chat & Diskusikan</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
