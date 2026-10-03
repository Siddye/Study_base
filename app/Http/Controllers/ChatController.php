<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Folder;
use App\Services\RAGService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected RAGService $ragService;

    public function __construct(RAGService $ragService)
    {
        $this->ragService = $ragService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $sessions = $user->chatSessions()->latest()->get();
        $folders = $user->folders()->get();

        $activeSession = null;
        if ($request->query('session_id')) {
            $activeSession = $user->chatSessions()->with('messages')->find($request->query('session_id'));
        }

        if (!$activeSession && $sessions->isNotEmpty()) {
            $activeSession = $sessions->first()->load('messages');
        }

        return view('chat.index', compact('sessions', 'activeSession', 'folders'));
    }

    public function storeSession(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'folder_id' => ['nullable', 'exists:folders,id'],
        ]);

        $session = Auth::user()->chatSessions()->create([
            'title' => ($validated['title'] ?? null) ?: 'Sesi Tanya Materi ' . date('d M H:i'),
            'folder_id' => $validated['folder_id'] ?? null,
        ]);

        return redirect()->route('chat.index', ['session_id' => $session->id]);
    }

    public function sendMessage(Request $request, ChatSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
            'mode' => ['nullable', 'string', 'in:ask,explain,summarize,quiz,flashcards'],
        ]);

        $userMessage = $validated['message'];
        $mode = $validated['mode'] ?? 'ask';

        // 1. Save user question
        $session->messages()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Auto update session title if still default
        if ($session->messages()->count() <= 2 && str_starts_with($session->title, 'Sesi Tanya')) {
            $session->update([
                'title' => mb_substr($userMessage, 0, 45) . (mb_strlen($userMessage) > 45 ? '...' : '')
            ]);
        }

        // 2. Prepare past context history (last 4 messages)
        $history = $session->messages()
            ->latest()
            ->take(5)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        // 3. Call RAG Service
        try {
            $ragResult = $this->ragService->ask($userMessage, Auth::id(), [
                'folder_id' => $session->folder_id,
                'mode' => $mode,
                'chat_history' => $history,
            ]);

            $session->messages()->create([
                'role' => 'assistant',
                'content' => $ragResult['answer'],
                'sources' => $ragResult['sources'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'answer' => $ragResult['answer'],
                    'sources' => $ragResult['sources'],
                ]);
            }

            return redirect()->route('chat.index', ['session_id' => $session->id]);
        } catch (Exception $e) {
            $errorMessage = "Maaf, terjadi kendala saat memproses jawaban: " . $e->getMessage();

            $session->messages()->create([
                'role' => 'assistant',
                'content' => $errorMessage,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
            }

            return redirect()->route('chat.index', ['session_id' => $session->id])->with('error', $errorMessage);
        }
    }

    public function destroySession(ChatSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        $session->delete();

        return redirect()->route('chat.index')->with('success', 'Sesi percakapan telah dihapus.');
    }
}
