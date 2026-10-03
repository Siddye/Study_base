<?php

namespace App\Services;

use App\Models\Chunk;
use App\Models\Document;
use Exception;

class SimilaritySearchService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Calculate Cosine Similarity between two float vectors.
     *
     * @param array<float> $vecA
     * @param array<float> $vecB
     * @return float Range from -1.0 to 1.0 (typically 0.0 to 1.0 for embeddings)
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $count = count($vecA);
        if ($count === 0 || $count !== count($vecB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];

            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Perform semantic search across documents.
     *
     * @param string $query
     * @param int $userId
     * @param array{folder_id?: int|null, document_id?: int|null, limit?: int, min_score?: float} $options
     * @return array<int, array{chunk_id: int, document_id: int, document_title: string, filetype: string, page_number: int, content: string, score: float}>
     */
    public function search(string $query, int $userId, array $options = []): array
    {
        $limit = $options['limit'] ?? 5;
        $minScore = $options['min_score'] ?? 0.25;
        $folderId = $options['folder_id'] ?? null;
        $documentId = $options['document_id'] ?? null;

        // 1. Get query embedding
        $queryVector = $this->embeddingService->getEmbedding($query);

        // 2. Fetch chunks belonging to the user's ready documents
        $chunkQuery = Chunk::query()
            ->join('documents', 'chunks.document_id', '=', 'documents.id')
            ->where('documents.user_id', $userId)
            ->where('documents.status', 'ready')
            ->whereNotNull('chunks.embedding')
            ->select([
                'chunks.id as chunk_id',
                'chunks.document_id',
                'chunks.page_number',
                'chunks.content',
                'chunks.embedding',
                'documents.title as document_title',
                'documents.filetype as document_filetype',
            ]);

        if (!empty($documentId)) {
            $chunkQuery->where('documents.id', $documentId);
        }

        if (!empty($folderId)) {
            $chunkQuery->where('documents.folder_id', $folderId);
        }

        $chunks = $chunkQuery->get();

        $scoredResults = [];

        foreach ($chunks as $chunk) {
            $chunkVector = is_array($chunk->embedding) ? $chunk->embedding : json_decode($chunk->embedding, true);

            if (empty($chunkVector) || !is_array($chunkVector)) {
                continue;
            }

            $score = $this->cosineSimilarity($queryVector, $chunkVector);

            if ($score >= $minScore) {
                $scoredResults[] = [
                    'chunk_id' => $chunk->chunk_id,
                    'document_id' => $chunk->document_id,
                    'document_title' => $chunk->document_title,
                    'filetype' => $chunk->document_filetype,
                    'page_number' => $chunk->page_number,
                    'content' => $chunk->content,
                    'score' => round($score, 4),
                ];
            }
        }

        // Sort by similarity score descending
        usort($scoredResults, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($scoredResults, 0, $limit);
    }
}
