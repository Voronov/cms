<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $pages = Page::where('is_published', true)
            ->where('sitemap_include', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($pages as $page) {
            $xml .= '<url>';
            $xml .= '<loc>' . $page->getFullUrl() . '</loc>';
            $xml .= '<lastmod>' . $page->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>' . $page->sitemap_changefreq . '</changefreq>';
            $xml .= '<priority>' . number_format($page->sitemap_priority, 1) . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return Response::make($xml, 200, ['Content-Type' => 'text/xml']);
    }
}
