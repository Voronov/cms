<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Facades\View;

class MenuService
{
    /**
     * Get a menu by slug with its items and translations.
     *
     * @param string $slug
     * @return Menu|null
     */
    public function getMenu(string $slug)
    {
        return Menu::where('slug', $slug)
            ->with(['rootItems' => function($query) {
                $query->where('is_active', true);
            }, 'rootItems.translations', 'rootItems.children' => function($query) {
                $query->where('is_active', true);
            }, 'rootItems.children.translations', 'rootItems.children.children' => function($query) {
                $query->where('is_active', true);
            }, 'rootItems.children.children.translations'])
            ->first();
    }

    /**
     * Render a menu using a specified view or a default one.
     *
     * @param string $slug
     * @param string|null $view
     * @return string
     */
    public function render(string $slug, ?string $view = null)
    {
        $menu = $this->getMenu($slug);
        if (!$menu) {
            return '';
        }

        $view = $view ?? 'components.menu.default';
        
        return View::make($view, [
            'menu' => $menu,
            'items' => $menu->rootItems
        ])->render();
    }
}
