<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\SimilaritySearchService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    protected SimilaritySearchService $searchService;

    public function __construct(SimilaritySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $request->query('q');
        $folderId = $request->query('folder_id');
        $folders = $user->folders()->get();

        $results = [];
        $errorMessage = null;

        if (!empty($query)) {
            try {
                $results = $this->searchService->search($query, $user->id, [
                    'folder_id' => $folderId ?: null,
                    'limit' => 12,
                    'min_score' => 0.15,
                ]);
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        return view('search.index', compact('query', 'folderId', 'folders', 'results', 'errorMessage'));
    }
}
