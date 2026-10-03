<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RAGService;
use Illuminate\Console\Command;

class TestRAG extends Command
{
    protected $signature = 'test:rag {query=Jelaskan tentang bangun datar}';
    protected $description = 'Test RAG pipeline end to end';

    public function handle(RAGService $ragService)
    {
        $user = User::where('email', 'mahasiswa@gmail.com')->first() ?? User::first();
        $query = $this->argument('query');

        $this->info("Menjalankan RAG untuk user: {$user->email}");
        $this->info("Pertanyaan: {$query}");

        $result = $ragService->ask($query, $user->id);

        $this->info("\n--- Sumber Ditemukan: " . count($result['sources']) . " ---");
        foreach ($result['sources'] as $src) {
            $this->line("📍 [{$src['document_title']}] Hal. {$src['page_number']} (Score: {$src['score']})");
        }

        $this->info("\n--- Jawaban AI ---");
        $this->line($result['answer']);
    }
}
