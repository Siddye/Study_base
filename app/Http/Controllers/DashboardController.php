<?php

namespace App\Http\Controllers;

use App\Models\Chunk;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalDocs = $user->documents()->count();
        $totalChunks = Chunk::whereHas('document', fn($q) => $q->where('user_id', $user->id))->count();
        $totalFolders = $user->folders()->count();
        $totalStorage = $user->documents()->sum('file_size');

        $recentDocs = $user->documents()->with('folder')->latest()->take(6)->get();
        $folders = $user->folders()->withCount('documents')->get();
        $recentChats = $user->chatSessions()->latest()->take(4)->get();

        return view('dashboard.index', compact(
            'totalDocs',
            'totalChunks',
            'totalFolders',
            'totalStorage',
            'recentDocs',
            'folders',
            'recentChats'
        ));
    }
}
