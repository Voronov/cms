<?php

namespace App\Models;

use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'filepath',
        'original_filepath',
        'original_name',
        'alt_text',
        'width',
        'height',
        'filesize',
        'variants',
    ];

    protected $casts = [
        'variants' => 'array',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->filepath);
    }

    public function getUsage()
    {
        $usage = [];

        // Check relations table
        $relations = \DB::table('media_relations')
            ->where('media_id', $this->id)
            ->get();

        foreach ($relations as $relation) {
            $modelClass = $relation->mediable_type;
            $model = $modelClass::find($relation->mediable_id);
            
            if ($model) {
                $name = method_exists($model, 'getTitle') ? $model->getTitle() : ($model->title ?? $model->name ?? 'Related Item');
                $usage[] = [
                    'type' => class_basename($modelClass),
                    'name' => $name,
                    'field' => $relation->field_name,
                ];
            }
        }

        return $usage;
    }
}
