<?php

namespace App\Http\Controllers;

use App\Services\SiteContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CacheController extends Controller
{
    public function index()
    {
        return view('admin.cache.index');
    }

    public function clear(Request $request)
    {
        $type = $request->input('type', 'all');
        
        $cleared = [];
        
        switch ($type) {
            case 'site_config':
                SiteContextService::clearCache();
                $cleared[] = 'Site Configuration Cache';
                break;
                
            case 'views':
                Artisan::call('view:clear');
                $cleared[] = 'View Cache';
                break;
                
            case 'routes':
                Artisan::call('route:clear');
                $cleared[] = 'Route Cache';
                break;
                
            case 'config':
                Artisan::call('config:clear');
                $cleared[] = 'Configuration Cache';
                break;
                
            case 'application':
                Cache::flush();
                $cleared[] = 'Application Cache';
                break;
                
            case 'all':
            default:
                SiteContextService::clearCache();
                Cache::flush();
                Artisan::call('view:clear');
                Artisan::call('route:clear');
                Artisan::call('config:clear');
                $cleared[] = 'All Caches';
                break;
        }
        
        return back()->with('success', implode(', ', $cleared) . ' cleared successfully.');
    }
}
