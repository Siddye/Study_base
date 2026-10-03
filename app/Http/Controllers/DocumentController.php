<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Folder;
use App\Services\ChunkingService;
use App\Services\DocumentExtractionService;
use App\Services\EmbeddingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    protected DocumentExtractionService $extractionService;
    protected ChunkingService $chunkingService;
    protected EmbeddingService $embeddingService;

    public function __construct(
        DocumentExtractionService $extractionService,
        ChunkingService $chunkingService,
        EmbeddingService $embeddingService
    ) {
        $this->extractionService = $extractionService;
        $this->chunkingService = $chunkingService;
        $this->embeddingService = $embeddingService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $folderId = $request->query('folder_id');
        $search = $request->query('q');

        $folders = $user->folders()->withCount('documents')->get();
        $currentFolder = $folderId ? Folder::where('id', $folderId)->where('user_id', $user->id)->first() : null;

        $docQuery = $user->documents()->with('folder')->withCount('chunks');

        if ($folderId) {
            $docQuery->where('folder_id', $folderId);
        }

        if ($search) {
            $docQuery->where('title', 'like', "%{$search}%");
        }

        $documents = $docQuery->latest()->paginate(12)->withQueryString();

        return view('library.index', compact('folders', 'currentFolder', 'documents', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,docx,doc', 'max:25600'], // 25MB max
            'title' => ['nullable', 'string', 'max:255'],
            'folder_id' => ['nullable', 'exists:folders,id'],
        ]);

        $file = $request->file('file');
        $user = Auth::user();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $extension;
        $title = $request->input('title') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $path = $file->storeAs("documents/{$user->id}", $filename, 'public');

        $document = $user->documents()->create([
            'folder_id' => $request->input('folder_id') ?: null,
            'title' => $title,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'filetype' => $extension,
            'file_size' => $file->getSize(),
            'status' => 'processing',
        ]);

        // Process extraction, chunking, and embedding immediately
        try {
            $this->processDocument($document);
            return back()->with('success', "Dokumen '{$document->title}' berhasil di-upload dan diproses untuk RAG!");
        } catch (Exception $e) {
            Log::error("Document processing error: " . $e->getMessage());
            return back()->with('warning', "Dokumen '{$document->title}' berhasil di-upload, namun terjadi catatan saat pemrosesan AI: " . $e->getMessage());
        }
    }

    public function show(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        $chunks = $document->chunks()->orderBy('chunk_index', 'asc')->paginate(15);

        return view('library.show', compact('document', 'chunks'));
    }

    public function reprocess(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $this->processDocument($document);
            return back()->with('success', "Dokumen '{$document->title}' berhasil diproses ulang!");
        } catch (Exception $e) {
            return back()->with('error', "Gagal memproses ulang dokumen: " . $e->getMessage());
        }
    }

    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        $title = $document->title;

        // Delete physical file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('library.index')->with('success', "Dokumen '{$title}' berhasil dihapus.");
    }

    /**
     * Process document text extraction, chunking, and embedding vectors.
     */
    protected function processDocument(Document $document): void
    {
        $document->update(['status' => 'processing', 'error_message' => null]);
        $fullPath = Storage::disk('public')->path($document->file_path);

        try {
            // 1. Extract text per page
            $pages = $this->extractionService->extractPages($fullPath, $document->filetype);
            $pageCount = count($pages);

            // 2. Chunk text
            $chunksData = $this->chunkingService->chunkPages($pages);

            // 3. Clear old chunks if any
            $document->chunks()->delete();

            // 4. Extract embeddings and save chunks
            foreach ($chunksData as $data) {
                $embeddingVector = null;
                try {
                    $embeddingVector = $this->embeddingService->getEmbedding($data['content']);
                } catch (Exception $e) {
                    Log::warning("Embedding failed for chunk: " . $e->getMessage());
                }

                $document->chunks()->create([
                    'chunk_index' => $data['chunk_index'],
                    'page_number' => $data['page_number'],
                    'content' => $data['content'],
                    'token_count' => $data['token_count'],
                    'embedding' => $embeddingVector,
                ]);
            }

            $document->update([
                'page_count' => $pageCount,
                'status' => 'ready',
                'error_message' => null,
            ]);
        } catch (Exception $e) {
            $document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
