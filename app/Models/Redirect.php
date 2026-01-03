<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use Auditable;

    protected $fillable = [
        'from_url',
        'to_url',
        'status_code',
        'page_id',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
