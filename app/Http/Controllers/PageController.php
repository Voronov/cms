<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Redirect;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Yaml\Yaml;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\LanguageService;

class PageController extends Controller
{
    protected $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }
    private function getLayouts(): array
    {
        $layouts = [];
        $files = File::files(resource_path('layouts'));
        foreach ($files as $file) {
            if ($file->getExtension() === 'yaml') {
                $content = Yaml::parse(File::get($file));
                $layouts[$file->getFilenameWithoutExtension()] = $content['name'] ?? $file->getFilenameWithoutExtension();
            }
        }
        return $layouts;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tree = Page::whereNull('parent_id')->with('children')
            ->orderBy('is_root', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $locales = $this->languageService->getLanguages();
        return view('admin.pages.index', compact('tree', 'locales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $tree = Page::whereNull('parent_id')->with('children')->orderBy('created_at', 'desc')->get();
        $parents = Page::all();
        $layouts = $this->getLayouts();
        $blocks = $this->getAvailableBlocks();
        $mediaConfig = Yaml::parseFile(resource_path('media.yaml'));
        $selectedParentId = $request->query('parent_id');
        $activePath = $selectedParentId ? Page::find($selectedParentId)?->getAncestorIds() : [];
        if ($selectedParentId) {
            $activePath[] = (int) $selectedParentId;
        }

        $locales = $this->languageService->getLanguages();
        $currentLocale = $request->query('locale', $this->languageService->getDefaultLocale());

        return view('admin.pages.create', compact('tree', 'parents', 'layouts', 'selectedParentId', 'blocks', 'mediaConfig', 'activePath', 'locales', 'currentLocale'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                })
            ],
            'content' => 'nullable|string',
            'parent_id' => 'nullable|exists:pages,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
        ]);

        // Ensure only one root page exists
        if ($request->boolean('is_root')) {
            Page::where('is_root', true)->update(['is_root' => false]);
        }

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);

        // Ensure unique slug within scope for auto-generated cases
        if (!$request->filled('slug')) {
            $originalSlug = $slug;
            $count = 1;
            while (Page::where('slug', $slug)->where('parent_id', $request->parent_id)->exists()) {
                $slug = $originalSlug . '-' . time();
                break;
            }
        }

        $page = Page::create([
            'is_root' => $request->boolean('is_root'),
            'parent_id' => $request->parent_id,
            'layout' => $request->layout ?? 'default',
            'sitemap_include' => $request->boolean('sitemap_include', true),
            'sitemap_priority' => $request->input('sitemap_priority', 0.8),
            'sitemap_changefreq' => $request->input('sitemap_changefreq', 'weekly'),
        ]);

        $locale = $request->input('locale', $this->languageService->getDefaultLocale());

        $page->translations()->create([
            'locale' => $locale,
            'title' => $request->title,
            'slug' => $slug,
            'blocks' => is_string($request->input('blocks')) ? json_decode($request->input('blocks'), true) : $request->input('blocks', []),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'og_title' => $request->input('og_title'),
            'og_description' => $request->input('og_description'),
            'og_image' => $request->input('og_image'),
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.pages.edit', ['page' => $page->id, 'locale' => $locale])->with('success', 'Page created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $page = Page::findOrFail($id);
        $tree = Page::whereNull('parent_id')->with('children')
            ->orderBy('is_root', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        // Prevent self-parenting:
        $parents = Page::where('id', '!=', $id)->get();
        $layouts = $this->getLayouts();
        $blocks = $this->getAvailableBlocks();
        $mediaConfig = Yaml::parseFile(resource_path('media.yaml'));
        $activePath = $page->getAncestorIds();

        $locales = $this->languageService->getLanguages();
        $currentLocale = request()->query('locale', $this->languageService->getDefaultLocale());
        $translation = $page->translation($currentLocale);

        // If translation doesn't exist, handle modes
        if (!$translation) {
            if ($this->languageService->getModeForLocale($currentLocale) === 'copy') {
                $defaultTranslation = $page->translation($this->languageService->getDefaultLocale());
                if ($defaultTranslation) {
                    $translation = new \App\Models\PageTranslation($defaultTranslation->toArray());
                    $translation->locale = $currentLocale;
                    // We don't save yet, just for the view
                }
            }
        }

        if (!$translation) {
            $translation = new \App\Models\PageTranslation(['locale' => $currentLocale, 'blocks' => []]);
        }
        
        // Ensure blocks is always an array
        if (!$translation->blocks) {
            $translation->blocks = [];
        }

        // Get redirects related to this page
        $redirects = \App\Models\Redirect::where('page_id', $page->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pages.edit', compact('page', 'tree', 'parents', 'layouts', 'blocks', 'mediaConfig', 'activePath', 'locales', 'currentLocale', 'translation', 'redirects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $page = Page::findOrFail($id);
        $locale = $request->input('locale', $this->languageService->getDefaultLocale());

        $oldTranslation = $page->translation($locale);
        $oldSlug = $oldTranslation ? $oldTranslation->slug : null;

        // Get old path with locale prefix if not default
        $oldPath = $this->getPrefixedPath($page, $locale);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                })->ignore($page->id)
            ],
            'content' => 'nullable|string',
            'parent_id' => 'nullable|exists:pages,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
        ]);

        // Prevent circular dependency
        if ($request->parent_id == $id) {
            return back()->withErrors(['parent_id' => 'Page cannot be its own parent.']);
        }

        // Ensure only one root page exists
        if ($request->boolean('is_root')) {
            Page::where('is_root', true)->where('id', '!=', $id)->update(['is_root' => false]);
        }

        $slug = $request->filled('slug') ? Str::slug($request->slug) : ($oldSlug ?? $page->slug);

        $data = [
            'is_root' => $request->boolean('is_root'),
            'parent_id' => $request->parent_id,
            'layout' => $request->layout ?? 'default',
            'sitemap_include' => $request->boolean('sitemap_include'),
            'sitemap_priority' => $request->input('sitemap_priority'),
            'sitemap_changefreq' => $request->input('sitemap_changefreq'),
        ];

        $page->update($data);

        $page->translations()->updateOrCreate(
            ['locale' => $locale],
            [
                'title' => $request->title,
                'slug' => $slug,
                'blocks' => is_string($request->input('blocks')) ? json_decode($request->input('blocks'), true) : $request->input('blocks', []),
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'og_title' => $request->input('og_title'),
                'og_description' => $request->input('og_description'),
                'og_image' => $request->input('og_image'),
                'is_published' => $request->has('is_published'),
            ]
        );

        // Handle Automatic Redirects
        if ($request->boolean('create_redirect') && $oldSlug && $oldSlug !== $slug) {
            $this->createRedirects($page, $oldPath, $locale);
        }

        // Handle Manual Redirects
        if ($request->filled('manual_from') && $request->filled('manual_to')) {
            Redirect::create([
                'from_url' => '/' . ltrim($request->manual_from, '/'),
                'to_url' => '/' . ltrim($request->manual_to, '/'),
                'status_code' => $request->manual_status ?? 301,
                'page_id' => $page->id,
            ]);
        }

        // Clear page cache
        Cache::flush();

        return redirect()->route('admin.pages.edit', $id)->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        // Clear page cache
        Cache::flush();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    /**
     * Move a page to a new parent.
     */
    public function move(Request $request, string $id)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:pages,id',
        ]);

        $page = Page::findOrFail($id);

        // Prevent circular dependency
        if ($request->parent_id == $id) {
            return response()->json(['error' => 'Cannot be own parent'], 422);
        }

        $page->parent_id = $request->parent_id;
        $page->save();

        return response()->json(['success' => true]);
    }

    private function getAvailableBlocks()
    {
        $blocksPath = resource_path('layouts');
        if (!File::exists($blocksPath)) {
            return [];
        }

        $blocks = [];
        $directories = File::directories($blocksPath);

        foreach ($directories as $directory) {
            $blockName = basename($directory);
            
            // Skip admin folder
            if ($blockName === 'admin') {
                continue;
            }
            
            $yamlFile = $directory . '/' . $blockName . '.yaml';
            
            if (File::exists($yamlFile)) {
                $config = Yaml::parseFile($yamlFile);
                $config['id'] = $blockName;
                $blocks[] = $config;
            }
        }

        return $blocks;
    }

    private function getPrefixedPath(Page $page, string $locale): string
    {
        $path = $page->getFullPath($locale);
        $defaultLocale = $this->languageService->getDefaultLocale();

        if ($locale !== $defaultLocale) {
            return '/' . $locale . rtrim($path, '/');
        }

        return $path;
    }

    private function createRedirects(Page $page, string $oldPath, string $locale)
    {
        $newPath = $this->getPrefixedPath($page, $locale);

        if ($oldPath !== $newPath) {
            Redirect::updateOrCreate(
                ['from_url' => $oldPath],
                ['to_url' => $newPath, 'page_id' => $page->id, 'status_code' => 301]
            );
        }

        foreach ($page->children as $child) {
            $childTrans = $child->translation($locale);
            $childSlug = $childTrans ? $childTrans->slug : $child->slug;

            $childOldPath = rtrim($oldPath, '/') . '/' . $childSlug;
            $this->createRedirects($child, $childOldPath, $locale);
        }
    }
}
