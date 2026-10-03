<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\ChunkingService;
use App\Services\DocumentExtractionService;
use App\Services\EmbeddingService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ReprocessDocuments extends Command
{
    protected $signature = 'documents:reprocess {id?}';
    protected $description = 'Reprocess document extraction and vector embedding generation';

    public function handle(
        DocumentExtractionService $extractionService,
        ChunkingService $chunkingService,
        EmbeddingService $embeddingService
    ) {
        $id = $this->argument('id');
        $documents = $id ? Document::where('id', $id)->get() : Document::all();

        $this->info("Memproses {$documents->count()} dokumen...");

        foreach ($documents as $doc) {
            $this->line("-> Memproses dokumen: [{$doc->id}] {$doc->title} ({$doc->filename})");
            try {
                $fullPath = Storage::disk('public')->path($doc->file_path);
                if (!file_exists($fullPath)) {
                    $this->error("   File tidak ditemukan di disk: {$fullPath}");
                    continue;
                }

                $pages = $extractionService->extractPages($fullPath, $doc->filetype);
                $chunks = $chunkingService->chunkPages($pages);

                $doc->chunks()->delete();

                $embeddedCount = 0;
                foreach ($chunks as $chunk) {
                    $vector = $embeddingService->getEmbedding($chunk['content']);
                    $doc->chunks()->create([
                        'chunk_index' => $chunk['chunk_index'],
                        'page_number' => $chunk['page_number'],
                        'content' => $chunk['content'],
                        'token_count' => $chunk['token_count'],
                        'embedding' => $vector,
                    ]);
                    $embeddedCount++;
                }

                $doc->update([
                    'page_count' => count($pages),
                    'status' => 'ready',
                    'error_message' => null,
                ]);

                $this->info("   SUKSES: {$embeddedCount} chunks berhasil di-embed!");
            } catch (Exception $e) {
                $doc->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $this->error("   GAGAL: " . $e->getMessage());
            }
        }

        $this->info("Selesai.");
    }
}
