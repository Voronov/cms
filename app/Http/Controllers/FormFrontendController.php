<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Services\FormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormFrontendController extends Controller
{
    protected FormService $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * Preview the form.
     */
    public function show(string $identifier)
    {
        $form = Form::find($identifier);
        if (!$form || !$form->is_active) {
            abort(404);
        }

        return view('forms.render', compact('form'));
    }

    /**
     * Handle form submission.
     */
    public function submit(Request $request, string $identifier)
    {
        $form = Form::find($identifier);
        if (!$form || !$form->is_active) {
            abort(404);
        }

        $validator = $this->formService->validateRequest($form, $request->all());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Handle File Uploads
        foreach ($form->fields as $field) {
            if ($field['type'] === 'file' && $request->hasFile($field['name'])) {
                $file = $request->file($field['name']);
                $path = $file->store('form_submissions/' . $identifier);
                $data[$field['name']] = $path;
            }
        }

        // For now, we'll just log or save to a file/DB. 
        // User didn't specify where to store submissions, so let's log them for now.
        \Log::info('Form submitted: ' . $form->title, [
            'identifier' => $identifier,
            'data' => $data,
            'ip' => $request->ip(),
        ]);

        return back()->with('success', 'Thank you! Your submission has been received.');
    }
}
