<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemTranslation extends Model
{
    protected $fillable = [
        'menu_item_id',
        'locale',
        'title'
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
