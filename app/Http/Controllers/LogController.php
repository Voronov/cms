<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $logs = SystemLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.logs.index', compact('logs'));
    }
}
