<?php

namespace App\Providers;

use App\Models\Entity;
use App\Models\PageTranslation;
use App\Observers\EntityObserver;
use App\Observers\PageTranslationObserver;
use App\Services\MenuService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Symfony\Component\Yaml\Yaml;
use App\Models\NavigationItem;
use App\Services\EntityDefinitionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Entity::observe(EntityObserver::class);
        PageTranslation::observe(PageTranslationObserver::class);

        Blade::directive('menu', function ($expression) {
            return "<?php echo app(\App\Services\MenuService::class)->render($expression); ?>";
        });

        View::composer('layouts.admin', function ($view) {
            $navigationFile = resource_path('config/navigation.yaml');
            $navigationData = Yaml::parseFile($navigationFile);
            
            $sections = collect($navigationData['sections'] ?? [])
                ->map(function ($section, $key) {
                    return array_merge(['id' => $key], $section);
                })
                ->sortBy('order')
                ->values();
            
            $yamlNavigation = collect($navigationData['navigation'] ?? []);
            
            $dbNavigation = NavigationItem::active()->ordered()->get()->map(function ($item) {
                return [
                    'name' => $item->name,
                    'route' => $item->route,
                    'icon' => $item->icon,
                    'section' => $item->section,
                    'order' => $item->order,
                ];
            });
            
            $entityDefinition = app(EntityDefinitionService::class);
            $entityTypes = $entityDefinition->getTypes();
            $entityNavigation = collect($entityTypes)->map(function ($type) use ($entityDefinition) {
                return [
                    'name' => $entityDefinition->getPluralName($type),
                    'route' => 'admin.entities.index',
                    'route_params' => ['type' => $type],
                    'icon' => $entityDefinition->getIcon($type),
                    'section' => 'entities',
                    'order' => 100,
                ];
            });
            
            if (!$sections->contains('id', 'entities') && $entityNavigation->isNotEmpty()) {
                $sections->push([
                    'id' => 'entities',
                    'label' => 'Entities',
                    'order' => 50,
                ]);
                $sections = $sections->sortBy('order')->values();
            }
            
            $allNavigation = $yamlNavigation->concat($dbNavigation)->concat($entityNavigation)
                ->sortBy('order')
                ->groupBy('section');
            
            $view->with([
                'navigationSections' => $sections,
                'navigationBySection' => $allNavigation,
            ]);
        });
    }
}
