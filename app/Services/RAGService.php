<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGService
{
    protected SimilaritySearchService $searchService;
    protected string $provider;
    protected ?string $geminiApiKey;
    protected ?string $openaiApiKey;
    protected string $geminiChatModel;
    protected string $openaiChatModel;

    public function __construct(SimilaritySearchService $searchService)
    {
        $this->searchService = $searchService;
        $this->provider = config('services.ai.provider', env('AI_PROVIDER', 'gemini'));
        $this->geminiApiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->openaiApiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->geminiChatModel = env('GEMINI_CHAT_MODEL', 'gemini-3.8-flash');
        $this->openaiChatModel = env('OPENAI_CHAT_MODEL', 'gpt-4o-mini');
    }

    /**
     * Answer a question using RAG over the user's documents.
     *
     * @param string $question
     * @param int $userId
     * @param array{folder_id?: int|null, document_id?: int|null, mode?: string, chat_history?: array} $options
     * @return array{answer: string, sources: array<int, array{document_id: int, document_title: string, page_number: int, snippet: string, score: float}>}
     */
    public function ask(string $question, int $userId, array $options = []): array
    {
        $mode = $options['mode'] ?? 'ask'; // 'ask', 'explain', 'summarize', 'quiz', 'flashcards'

        // 1. Retrieve relevant chunks
        $chunks = $this->searchService->search($question, $userId, [
            'folder_id' => $options['folder_id'] ?? null,
            'document_id' => $options['document_id'] ?? null,
            'limit' => $mode === 'summarize' ? 8 : 5,
            'min_score' => 0.15,
        ]);

        $sources = [];
        $contextBlocks = [];

        foreach ($chunks as $chunk) {
            $sources[] = [
                'document_id' => $chunk['document_id'],
                'document_title' => $chunk['document_title'],
                'page_number' => $chunk['page_number'],
                'snippet' => mb_substr($chunk['content'], 0, 160) . '...',
                'score' => $chunk['score'],
            ];

            $contextBlocks[] = sprintf(
                "--- Sumber: [%s] (Halaman %d) ---\n%s",
                $chunk['document_title'],
                $chunk['page_number'],
                $chunk['content']
            );
        }

        $contextText = implode("\n\n", $contextBlocks);

        if (empty($contextText)) {
            $userDocs = \App\Models\Document::where('user_id', $userId)->where('status', 'ready')->pluck('title')->unique()->toArray();
            $docsList = !empty($userDocs) ? implode(', ', array_map(fn($d) => "**{$d}**", $userDocs)) : 'belum ada dokumen';

            $lowerQ = strtolower(trim($question));
            $isGreeting = preg_match('/^(halo|hai|hi|hello|selamat\s*(pagi|siang|sore|malam)|assalamu[\'\w]*|bisa\s*bantu\s*apa|kamu\s*siapa)/i', $lowerQ);

            if ($isGreeting) {
                return [
                    'answer' => "Halo! 👋 Saya adalah **Asisten Belajar AI StudyBase** Anda.\n\n"
                        . "Saya siap membantu Anda mempelajari materi kuliah yang telah Anda upload. Koleksi materi Anda saat ini meliputi: {$docsList}.\n\n"
                        . "💡 **Apa yang bisa Anda lakukan:**\n"
                        . "- **Tanya Konsep**: Tanyakan materi atau rumus apa pun, saya akan sertakan nomor halaman rujukan.\n"
                        . "- **🧠 Explain**: Jelaskan materi sulit dengan bahasa sederhana & analogi.\n"
                        . "- **📝 Summarize**: Meringkas poin-poin penting per bab/topik.\n"
                        . "- **❓ Quiz Me**: Membuat latihan soal pilihan ganda dari materi.\n"
                        . "- **🃏 Flashcards**: Membuat kartu hafalan definisi kunci.\n\n"
                        . "Silakan ajukan pertanyaan seputar materi Anda!",
                    'sources' => [],
                ];
            }

            return [
                'answer' => "Maaf, saya tidak menemukan materi yang relevan dengan pertanyaan tersebut di dalam koleksi dokumen Anda saat ini ({$docsList}).\n\n"
                    . "💡 **Tips:**\n"
                    . "- Coba gunakan kata kunci yang lebih spesifik sesuai isi materi (misalnya topik bab atau istilah yang ada di slide/modul).\n"
                    . "- Atau upload materi kuliah terkait melalui menu **Library & Materi**.",
                'sources' => [],
            ];
        }

        // 2. Build system instructions according to study mode
        $systemPrompt = $this->buildSystemPrompt($mode);
        $userPrompt = "Konteks Dokumen Kuliah:\n" . $contextText . "\n\nPertanyaan / Perintah User:\n" . $question;

        // 3. Call LLM
        $answer = $this->callLLM($systemPrompt, $userPrompt, $options['chat_history'] ?? []);

        return [
            'answer' => $answer,
            'sources' => $sources,
        ];
    }

    /**
     * Build system prompt depending on chosen study mode.
     */
    protected function buildSystemPrompt(string $mode): string
    {
        $base = "Anda adalah Asisten Belajar AI Cerdas untuk Personal Study Knowledge Base.\n"
            . "ATURAN UTAMA:\n"
            . "1. Jawablah HANYA berdasarkan konteks dokumen yang disediakan.\n"
            . "2. JANGAN mengarang atau menggunakan pengetahuan umum yang tidak tertulis di dokumen.\n"
            . "3. Selalu cantumkan rujukan sumber dengan format: **[Nama Dokumen, Hal. X]** pada setiap penjelasan penting.\n"
            . "4. Gunakan Bahasa Indonesia yang jelas, akademis namun mudah dipahami oleh mahasiswa.\n"
            . "5. Format output menggunakan Markdown yang rapi (bold, bullet points, headers).\n\n";

        switch ($mode) {
            case 'explain':
                return $base . "MODE JELASKAN (Explain):\nJelaskan konsep yang ditanyakan dengan bahasa yang sangat sederhana, intuitif, dan gunakan analogi kehidupan sehari-hari jika relevan agar mudah dipahami mahasiswa.";
            case 'summarize':
                return $base . "MODE RINGKASAN (Summarize):\nBuat ringkasan komprehensif dan terstruktur (Poin-poin Utama, Definisi Kunci, Kesimpulan) dari potongan materi yang diberikan.";
            case 'quiz':
                return $base . "MODE KUIS (Quiz Me):\nBuat 3-5 soal pilihan ganda (A, B, C, D) berdasarkan dokumen yang disediakan, lengkap dengan Kunci Jawaban, Pembahasan, dan Rujukan Halaman di bagian bawah.";
            case 'flashcards':
                return $base . "MODE FLASHCARDS:\nEkstrak istilah dan konsep kunci menjadi format kartu hafalan:\n- 💡 **Konsep/Pertanyaan**: ...\n  **Penjelasan/Jawaban**: ...\n  **Rujukan**: [Nama Dokumen, Hal. X]";
            default:
                return $base . "MODE TANYA JAWAB (Q&A):\nJawab pertanyaan secara langsung, tepat sasaran, dan sertakan sitasi halaman yang jelas.";
        }
    }

    /**
     * Call the configured LLM API.
     */
    protected function callLLM(string $systemPrompt, string $userPrompt, array $history = []): string
    {
        if ($this->provider === 'openai' || empty($this->geminiApiKey)) {
            if (!empty($this->openaiApiKey)) {
                return $this->callOpenAI($systemPrompt, $userPrompt, $history);
            }
        }

        if (!empty($this->geminiApiKey)) {
            return $this->callGemini($systemPrompt, $userPrompt, $history);
        }

        throw new Exception("API Key untuk AI Provider ({$this->provider}) belum dikonfigurasi di file .env");
    }

    /**
     * Gemini 1.5 Flash Chat API call.
     */
    protected function callGemini(string $systemPrompt, string $userPrompt, array $history = []): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiChatModel}:generateContent?key={$this->geminiApiKey}";

        $contents = [];

        // Add history if present
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userPrompt]],
        ];

        $response = Http::timeout(60)->post($url, [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 2048,
            ]
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new Exception("Gemini Chat API Error: " . $error);
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (empty($text)) {
            throw new Exception("Gemini tidak mengembalikan respon teks.");
        }

        return $text;
    }

    /**
     * OpenAI GPT Chat API call.
     */
    protected function callOpenAI(string $systemPrompt, string $userPrompt, array $history = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content']
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        $response = Http::withToken($this->openaiApiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->openaiChatModel,
                'messages' => $messages,
                'temperature' => 0.2,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new Exception("OpenAI Chat API Error: " . $error);
        }

        return $response->json('choices.0.message.content') ?? '';
    }
}
