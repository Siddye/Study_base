<?php

namespace App\Services;

class ChunkingService
{
    protected int $targetWords;
    protected int $overlapWords;

    public function __construct(int $targetWords = 350, int $overlapWords = 50)
    {
        $this->targetWords = $targetWords;
        $this->overlapWords = $overlapWords;
    }

    /**
     * Chunk extracted pages into manageable pieces with overlap while preserving page metadata.
     *
     * @param array<int, array{page_number: int, text: string}> $pages
     * @return array<int, array{chunk_index: int, page_number: int, content: string, token_count: int}>
     */
    public function chunkPages(array $pages): array
    {
        $chunks = [];
        $chunkIndex = 0;

        foreach ($pages as $page) {
            $pageNumber = $page['page_number'];
            $text = trim($page['text']);

            if (empty($text)) {
                continue;
            }

            $words = preg_split('/\s+/', $text);
            $totalWords = count($words);

            if ($totalWords <= $this->targetWords) {
                $chunks[] = [
                    'chunk_index' => $chunkIndex++,
                    'page_number' => $pageNumber,
                    'content' => $text,
                    'token_count' => (int) ceil($totalWords * 1.3), // approx tokens
                ];
                continue;
            }

            // Slide window over words with overlap
            $start = 0;
            while ($start < $totalWords) {
                $slice = array_slice($words, $start, $this->targetWords);
                $chunkText = implode(' ', $slice);

                $chunks[] = [
                    'chunk_index' => $chunkIndex++,
                    'page_number' => $pageNumber,
                    'content' => $chunkText,
                    'token_count' => (int) ceil(count($slice) * 1.3),
                ];

                if ($start + $this->targetWords >= $totalWords) {
                    break;
                }

                $start += ($this->targetWords - $this->overlapWords);
            }
        }

        return $chunks;
    }
}
