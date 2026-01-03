<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMedia
{
    /**
     * Get all media for the model.
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'media_relations')
            ->withPivot(['field_name', 'order', 'metadata'])
            ->withTimestamps()
            ->orderBy('media_relations.order');
    }

    /**
     * Sync media for a specific field.
     *
     * @param array|int|null $mediaIds
     * @param string|null $fieldName
     */
    public function syncMedia($mediaIds, ?string $fieldName = null): void
    {
        $mediaIds = is_array($mediaIds) ? $mediaIds : ($mediaIds ? [$mediaIds] : []);
        
        $syncData = [];
        foreach ($mediaIds as $index => $id) {
            $syncData[$id] = [
                'field_name' => $fieldName,
                'order' => $index,
            ];
        }

        if ($fieldName) {
            // Remove existing relations for this field before syncing
            $this->media()->wherePivot('field_name', $fieldName)->detach();
            $this->media()->attach($syncData);
        } else {
            $this->media()->sync($syncData);
        }
    }

    /**
     * Get media for a specific field.
     */
    public function getMedia(?string $fieldName = null)
    {
        return $this->media()
            ->when($fieldName, function ($query) use ($fieldName) {
                return $query->wherePivot('field_name', $fieldName);
            })
            ->get();
    }
}
