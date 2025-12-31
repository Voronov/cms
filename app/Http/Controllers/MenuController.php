<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    protected $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function index()
    {
        $menus = Menu::withCount('items')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:menus,slug',
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
        ]);

        return redirect()->route('admin.menus.edit', $menu->id)->with('success', 'Menu created successfully.');
    }

    public function edit(string $id)
    {
        $menu = Menu::with(['items.translations', 'items.children.translations', 'items.children.children.translations'])->findOrFail($id);
        $menus = Menu::all(); // Load all menus for the sidebar
        $pages = Page::all(); // For selection in menu items
        $locales = $this->languageService->getLanguages();
        $defaultLocale = $this->languageService->getDefaultLocale();

        return view('admin.menus.edit', compact('menu', 'menus', 'pages', 'locales', 'defaultLocale'));
    }

    private function formatItemForAlpine($item)
    {
        $translations = [];
        foreach ($this->languageService->getLanguages() as $code => $lang) {
            $trans = $item->translations->where('locale', $code)->first();
            $translations[$code] = $trans ? $trans->title : '';
        }

        return [
            'id' => $item->id,
            'type' => $item->page_id ? 'page' : ($item->url ? 'url' : 'anchor'),
            'page_id' => $item->page_id,
            'url' => $item->url,
            'anchor' => $item->anchor,
            'is_active' => $item->is_active,
            'translations' => $translations,
            'children' => $item->children->map(fn($child) => $this->formatItemForAlpine($child))->toArray()
        ];
    }

    public function update(Request $request, string $id)
    {
        $menu = Menu::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug,' . $id,
        ]);

        $menu->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(string $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }

    public function saveItems(Request $request, Menu $menu)
    {
        $items = $request->input('items', []);

        // Delete existing items and recreate to simplify hierarchy saving
        // Or better, update existing and delete missing. For simplicity in this complex nested case, 
        // we'll use a recursive approach to sync them.
        
        $this->syncMenuItems($menu->id, $items);

        return response()->json(['success' => true]);
    }

    private function syncMenuItems($menuId, $items, $parentId = null)
    {
        $existingIds = [];
        
        foreach ($items as $index => $itemData) {
            $menuItem = MenuItem::updateOrCreate(
                ['id' => $itemData['id'] ?? null],
                [
                    'menu_id' => $menuId,
                    'parent_id' => $parentId,
                    'page_id' => $itemData['page_id'] ?: null,
                    'url' => $itemData['url'] ?: null,
                    'anchor' => $itemData['anchor'] ?: null,
                    'order' => $index,
                    'is_active' => $itemData['is_active'] ?? true,
                ]
            );

            $existingIds[] = $menuItem->id;

            // Save translations
            foreach ($itemData['translations'] as $locale => $title) {
                if ($title) {
                    $menuItem->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['title' => $title]
                    );
                }
            }

            if (!empty($itemData['children'])) {
                $childIds = $this->syncMenuItems($menuId, $itemData['children'], $menuItem->id);
                $existingIds = array_merge($existingIds, $childIds);
            }
        }

        // If this is the root call, delete items that weren't in the request
        if ($parentId === null) {
            MenuItem::where('menu_id', $menuId)->whereNotIn('id', $existingIds)->delete();
        }

        return $existingIds;
    }
}
