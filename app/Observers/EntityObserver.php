<?php

namespace App\Observers;

use App\Models\Entity;

class EntityObserver
{
    /**
     * Handle the Entity "saved" event.
     */
    public function saved(Entity $entity): void
    {
        $this->syncMedia($entity);
    }

    /**
     * Sync media from entity content and SEO.
     */
    protected function syncMedia(Entity $entity): void
    {
        $content = $entity->content;
        $mediaIds = [];

        if (is_array($content)) {
            // Check for common image fields in content
            foreach ($content as $key => $value) {
                if (in_array($key, ['image', 'media_id', 'background_image']) && is_numeric($value)) {
                    $mediaIds[] = (int)$value;
                }
                
                // Check for arrays of images
                if ($key === 'images' && is_array($value)) {
                    foreach ($value as $item) {
                        if (is_numeric($item)) {
                            $mediaIds[] = (int)$item;
                        } elseif (is_array($item) && !empty($item['id'])) {
                            $mediaIds[] = (int)$item['id'];
                        }
                    }
                }
            }
        }

        // Check SEO image
        $seo = $entity->seo;
        if (is_array($seo) && !empty($seo['image']) && is_numeric($seo['image'])) {
            $mediaIds[] = (int)$seo['image'];
        }

        if (method_exists($entity, 'syncMedia')) {
            $entity->syncMedia(array_unique($mediaIds), 'content');
        }
    }
}
