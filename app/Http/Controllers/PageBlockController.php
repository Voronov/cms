<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageBlockController extends Controller
{
    /**
     * Save blocks for a specific page and locale
     */
    public function saveBlocks(Request $request, string $pageId)
    {
        $page = Page::findOrFail($pageId);
        $locale = $request->input('locale');
        $blocks = $request->input('blocks', []);
        
        // Update or create translation with new blocks
        $page->translations()->updateOrCreate(
            ['locale' => $locale],
            ['blocks' => $blocks]
        );
        
        // Clear page cache
        Cache::flush();
        
        return response()->json([
            'success' => true,
            'message' => 'Blocks saved successfully'
        ]);
    }
}
