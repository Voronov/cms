<?php

namespace App\Http\Controllers;

use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Yaml\Yaml;

class NavigationItemController extends Controller
{
    public function index(): View
    {
        $items = NavigationItem::ordered()->get();
        $sections = $this->getAvailableSections();
        $icons = $this->getAvailableIcons();
        
        return view('admin.navigation.index', compact('items', 'sections', 'icons'));
    }

    public function create(): View
    {
        $sections = $this->getAvailableSections();
        $icons = $this->getAvailableIcons();
        
        return view('admin.navigation.create', compact('sections', 'icons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'section' => 'required|string|max:255',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        NavigationItem::create($validated);

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Navigation item created successfully.');
    }

    public function edit(NavigationItem $navigation): View
    {
        $sections = $this->getAvailableSections();
        $icons = $this->getAvailableIcons();
        
        return view('admin.navigation.edit', compact('navigation', 'sections', 'icons'));
    }

    public function update(Request $request, NavigationItem $navigation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'section' => 'required|string|max:255',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $navigation->update($validated);

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Navigation item updated successfully.');
    }

    public function destroy(NavigationItem $navigation): RedirectResponse
    {
        $navigation->delete();

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Navigation item deleted successfully.');
    }

    private function getAvailableSections(): array
    {
        $navigationFile = resource_path('config/navigation.yaml');
        $navigationData = Yaml::parseFile($navigationFile);
        
        return $navigationData['sections'] ?? [];
    }

    private function getAvailableIcons(): array
    {
        return [
            'home', 'users', 'document', 'server', 
            'clipboard', 'arrows', 'cog', 'chart',
            'folder', 'mail', 'bell', 'calendar'
        ];
    }
}
