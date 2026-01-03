<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TrashController extends Controller
{
    public function index(): View
    {
        $pages = Page::onlyTrashed()->get();
        $entities = Entity::onlyTrashed()->get();

        return view('admin.trash.index', compact('pages', 'entities'));
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        $model = $type === 'page' ? Page::onlyTrashed()->findOrFail($id) : Entity::onlyTrashed()->findOrFail($id);
        $model->restore();

        return back()->with('success', ucfirst($type) . ' restored successfully.');
    }

    public function forceDelete(Request $request, string $type, int $id): RedirectResponse
    {
        $model = $type === 'page' ? Page::onlyTrashed()->findOrFail($id) : Entity::onlyTrashed()->findOrFail($id);
        $model->forceDelete();

        return back()->with('success', ucfirst($type) . ' permanently deleted.');
    }

    public function emptyTrash(): RedirectResponse
    {
        Page::onlyTrashed()->forceDelete();
        Entity::onlyTrashed()->forceDelete();

        return back()->with('success', 'Trash emptied successfully.');
    }
}
