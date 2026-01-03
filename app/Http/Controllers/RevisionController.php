<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class RevisionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $modelClass = $request->model_type;
        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $revisions = Revision::with('user')
            ->where('revisionable_type', $modelClass)
            ->where('revisionable_id', $request->model_id)
            ->latest()
            ->paginate(5);

        return response()->json([
            'data' => $revisions->items(),
            'current_page' => $revisions->currentPage(),
            'last_page' => $revisions->lastPage(),
            'total' => $revisions->total(),
        ]);
    }

    public function rollback(Revision $revision): RedirectResponse
    {
        $revisionable = $revision->revisionable;
        $revisionable->rollbackToRevision($revision->id);

        return back()->with('success', 'Rolled back to selected version.');
    }
}
