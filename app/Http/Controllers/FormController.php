<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::all();
        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        $form = new Form();
        return view('admin.forms.builder', compact('form'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array',
        ]);

        $form = new Form([
            'title' => $request->title,
            'description' => $request->description,
            'fields' => $request->fields,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $page->save();

        // Automatic logging handled by Auditable trait

        return redirect()->route('admin.forms.index')->with('success', 'Form created successfully.');
    }

    public function edit(string $identifier)
    {
        $form = Form::find($identifier);
        if (!$form) {
            abort(404);
        }

        return view('admin.forms.builder', compact('form'));
    }

    public function update(Request $request, string $identifier)
    {
        $form = Form::find($identifier);
        if (!$form) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array',
        ]);

        $form->title = $request->title;
        $form->description = $request->description;
        $form->fields = $request->fields;
        $form->is_active = $request->boolean('is_active', true);

        $form->save();

        // Automatic logging handled by Auditable trait

        return redirect()->route('admin.forms.index')->with('success', 'Form updated successfully.');
    }

    public function destroy(string $identifier)
    {
        $form = Form::find($identifier);
        if ($form) {
            $form->delete();
            // Automatic logging handled by Auditable trait
        }

        return redirect()->route('admin.forms.index')->with('success', 'Form deleted successfully.');
    }
}
