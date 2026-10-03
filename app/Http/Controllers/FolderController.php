<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:folders,id'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $folder = Auth::user()->folders()->create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
        ]);

        return back()->with('success', "Folder '{$folder->name}' berhasil dibuat!");
    }

    public function update(Request $request, Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:folders,id', 'different:id'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $folder->update($validated);

        return back()->with('success', "Folder '{$folder->name}' berhasil diperbarui!");
    }

    public function destroy(Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403);
        }

        $name = $folder->name;
        $folder->delete();

        return redirect()->route('library.index')->with('success', "Folder '{$name}' telah dihapus.");
    }
}
