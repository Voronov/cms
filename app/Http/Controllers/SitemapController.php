<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        // Get the site ID from the middleware
        $siteId = $request->attributes->get('site_id');
        
        if (!$siteId) {
            return Response::make('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>', 200, ['Content-Type' => 'text/xml']);
        }

        // Get the root page for this site
        $rootPage = Page::where('id', $siteId)->where('is_root', true)->first();
        
        if (!$rootPage) {
            return Response::make('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>', 200, ['Content-Type' => 'text/xml']);
        }

        // Get all pages that belong to this site (descendants of the root page)
        $pages = $this->getDescendantPages($rootPage)
            ->where('is_published', true)
            ->where('sitemap_include', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Include root page if it should be in sitemap
        if ($rootPage->is_published && $rootPage->sitemap_include) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($rootPage->getFullUrl()) . '</loc>';
            $xml .= '<lastmod>' . $rootPage->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>' . $rootPage->sitemap_changefreq . '</changefreq>';
            $xml .= '<priority>' . number_format($rootPage->sitemap_priority, 1) . '</priority>';
            $xml .= '</url>';
        }

        foreach ($pages as $page) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($page->getFullUrl()) . '</loc>';
            $xml .= '<lastmod>' . $page->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>' . $page->sitemap_changefreq . '</changefreq>';
            $xml .= '<priority>' . number_format($page->sitemap_priority, 1) . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return Response::make($xml, 200, ['Content-Type' => 'text/xml']);
    }

    private function getDescendantPages(Page $rootPage)
    {
        $descendantIds = $this->collectDescendantIds($rootPage);
        
        return Page::whereIn('id', $descendantIds);
    }

    private function collectDescendantIds(Page $page): array
    {
        $ids = [];
        
        foreach ($page->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->collectDescendantIds($child));
        }
        
        return $ids;
    }
}
