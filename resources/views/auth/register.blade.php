@extends('layouts.app', ['title' => 'Daftar Akun - Study Knowledge Base'])

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <div class="w-full max-w-md">
        <!-- Logo & Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 shadow-xl shadow-brand-500/25 mb-4">
                <i data-lucide="sparkles" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Buat Akun StudyBase</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola seluruh materi kuliah dalam satu repositori RAG cerdas</p>
        </div>

        <!-- Register Card -->
        <div class="glass-card rounded-2xl p-6 sm:p-8 shadow-2xl border border-slate-800">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Nama Lengkap / Panggilan</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                            placeholder="Contoh: Budi Santoso">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                            placeholder="nama@kampus.ac.id">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Kata Sandi</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password" required
                            class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                            placeholder="Minimal 6 karakter">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <i data-lucide="check-check" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password_confirmation" required
                            class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                            placeholder="Ulangi kata sandi">
                    </div>
                </div>

                <button type="submit"
                    class="w-full mt-2 py-3 px-4 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center justify-center gap-2 group">
                    <span>Daftar & Mulai Belajar</span>
                    <i data-lucide="sparkles" class="w-4 h-4 group-hover:rotate-12 transition-transform"></i>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800/80 text-center">
                <p class="text-xs text-slate-400">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300 font-semibold ml-1">Masuk Sekarang</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
