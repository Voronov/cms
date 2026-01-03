<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityTranslation extends Model
{
    protected static function booted()
    {
        static::saved(function ($translation) {
            if ($translation->entity && $translation->wasChanged()) {
                $description = $translation->generateRevisionDescription();
                if ($description) {
                    $translation->entity->createRevision($description);
                }
            }
        });
    }
    
    protected function generateRevisionDescription(): string
    {
        $changes = [];
        $dirty = $this->getChanges();

        foreach ($dirty as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at', 'entity_id', 'locale'])) {
                continue;
            }

            $oldValue = $this->getOriginal($field);

            if ($field === 'content' && is_array($newValue)) {
                $changes[] = "Content updated";
                continue;
            }

            if ($field === 'seo' && is_array($newValue)) {
                $oldSeo = $oldValue ?? [];
                foreach ($newValue as $key => $val) {
                    $oldVal = $oldSeo[$key] ?? null;
                    if ($val !== $oldVal) {
                        $label = str_replace('_', ' ', $key);
                        $changes[] = "SEO $label changed" . ($oldVal ? " from \"$oldVal\"" : "") . " to \"$val\"";
                    }
                }
                continue;
            }

            $label = str_replace('_', ' ', $field);
            $changes[] = ucfirst($label) . " changed" . ($oldValue ? " from \"$oldValue\"" : "") . " to \"$newValue\"";
        }

        return implode(', ', $changes);
    }
    
    protected $fillable = [
        'entity_id',
        'locale',
        'content',
        'seo',
    ];

    protected $casts = [
        'content' => 'array',
        'seo' => 'array',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
