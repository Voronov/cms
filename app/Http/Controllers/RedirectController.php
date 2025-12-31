<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function index()
    {
        $redirects = Redirect::orderBy('created_at', 'desc')->get();
        return view('admin.redirects.index', compact('redirects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_url' => 'required|string|unique:redirects,from_url',
            'to_url' => 'required|string',
            'status_code' => 'required|in:301,302',
        ]);

        Redirect::create([
            'from_url' => '/' . ltrim($request->from_url, '/'),
            'to_url' => '/' . ltrim($request->to_url, '/'),
            'status_code' => $request->status_code,
        ]);

        return back()->with('success', 'Redirect created successfully.');
    }

    public function destroy($id)
    {
        $redirect = Redirect::findOrFail($id);
        $redirect->delete();

        return back()->with('success', 'Redirect deleted successfully.');
    }
}
