<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected string $provider;
    protected ?string $geminiApiKey;
    protected ?string $openaiApiKey;
    protected string $geminiEmbeddingModel;
    protected string $openaiEmbeddingModel;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', env('AI_PROVIDER', 'gemini'));
        $this->geminiApiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->openaiApiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->geminiEmbeddingModel = env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001');
        $this->openaiEmbeddingModel = env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small');
    }

    /**
     * Generate embedding vector (array of floats) for a given text.
     *
     * @param string $text
     * @return array<float>
     * @throws Exception
     */
    public function getEmbedding(string $text): array
    {
        $text = trim($text);
        if (empty($text)) {
            throw new Exception("Teks untuk embedding tidak boleh kosong.");
        }

        if ($this->provider === 'openai' || empty($this->geminiApiKey)) {
            if (!empty($this->openaiApiKey)) {
                return $this->getOpenAIEmbedding($text);
            }
        }

        if (!empty($this->geminiApiKey)) {
            return $this->getGeminiEmbedding($text);
        }

        throw new Exception("API Key untuk AI Provider ({$this->provider}) belum dikonfigurasi di file .env");
    }

    /**
     * Batch embedding for multiple texts.
     *
     * @param array<string> $texts
     * @return array<int, array<float>>
     */
    public function getBatchEmbeddings(array $texts): array
    {
        $embeddings = [];
        foreach ($texts as $index => $text) {
            try {
                $embeddings[$index] = $this->getEmbedding($text);
            } catch (Exception $e) {
                Log::error("Gagal embed chunk {$index}: " . $e->getMessage());
                $embeddings[$index] = [];
            }
        }
        return $embeddings;
    }

    /**
     * Call Google Gemini Embedding API.
     */
    protected function getGeminiEmbedding(string $text): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiEmbeddingModel}:embedContent?key={$this->geminiApiKey}";

        $response = Http::timeout(30)->post($url, [
            'model' => "models/{$this->geminiEmbeddingModel}",
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new Exception("Gemini Embedding API Error: " . $error);
        }

        $values = $response->json('embedding.values');
        if (!is_array($values) || empty($values)) {
            throw new Exception("Gemini API tidak mengembalikan vector embedding yang valid.");
        }

        return $values;
    }

    /**
     * Call OpenAI Embedding API.
     */
    protected function getOpenAIEmbedding(string $text): array
    {
        $response = Http::withToken($this->openaiApiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $this->openaiEmbeddingModel,
                'input' => $text,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new Exception("OpenAI Embedding API Error: " . $error);
        }

        $embedding = $response->json('data.0.embedding');
        if (!is_array($embedding) || empty($embedding)) {
            throw new Exception("OpenAI API tidak mengembalikan vector embedding.");
        }

        return $embedding;
    }
}
