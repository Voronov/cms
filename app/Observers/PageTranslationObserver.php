<?php

namespace App\Observers;

use App\Models\PageTranslation;

class PageTranslationObserver
{
    /**
     * Handle the PageTranslation "saved" event.
     */
    public function saved(PageTranslation $pageTranslation): void
    {
        $this->syncMedia($pageTranslation);
    }

    /**
     * Sync media from blocks.
     */
    protected function syncMedia(PageTranslation $pageTranslation): void
    {
        $blocks = $pageTranslation->blocks;
        if (!is_array($blocks)) {
            return;
        }

        $mediaIds = [];
        
        // Extract media IDs from blocks
        foreach ($blocks as $block) {
            $data = $block['data'] ?? [];
            
            // Single image fields
            foreach (['media_id', 'image', 'background_image'] as $field) {
                if (!empty($data[$field]) && is_numeric($data[$field])) {
                    $mediaIds[] = (int)$data[$field];
                }
            }
            
            // Multiple images (e.g. gallery)
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if (is_numeric($image)) {
                        $mediaIds[] = (int)$image;
                    } elseif (is_array($image) && !empty($image['id'])) {
                        $mediaIds[] = (int)$image['id'];
                    }
                }
            }
        }

        // Also check OG Image
        if (!empty($pageTranslation->og_image) && is_numeric($pageTranslation->og_image)) {
            $mediaIds[] = (int)$pageTranslation->og_image;
        }

        // Sync without field_name for now as it's mixed from blocks
        if (method_exists($pageTranslation, 'syncMedia')) {
            $pageTranslation->syncMedia(array_unique($mediaIds), 'blocks');
        }
    }
}
