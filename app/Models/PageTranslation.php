<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasMedia;

class PageTranslation extends Model
{
    use HasFactory, HasMedia;
    
    protected static function booted()
    {
        static::saved(function ($translation) {
            if ($translation->page && $translation->wasChanged()) {
                $description = $translation->generateRevisionDescription();
                if ($description) {
                    $translation->page->createRevision($description);
                }
            }
        });
    }
    
    protected function generateRevisionDescription(): string
    {
        $changes = [];
        $dirty = $this->getChanges();

        foreach ($dirty as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at', 'page_id', 'locale'])) {
                continue;
            }

            $oldValue = $this->getOriginal($field);

            if ($field === 'blocks') {
                // Handle JSON-encoded values from database
                if (is_string($oldValue)) {
                    $oldValue = json_decode($oldValue, true) ?? [];
                }
                if (is_string($newValue)) {
                    $newValue = json_decode($newValue, true) ?? [];
                }
                
                $oldBlocks = is_array($oldValue) ? $oldValue : [];
                $newBlocks = is_array($newValue) ? $newValue : [];
                $blockChanges = $this->detectBlockChanges($oldBlocks, $newBlocks);
                
                if (!empty($blockChanges)) {
                    $changes = array_merge($changes, $blockChanges);
                }
                continue;
            }

            if (str_starts_with($field, 'meta_') || str_starts_with($field, 'og_')) {
                $label = str_replace(['meta_', 'og_', '_'], ['', 'OG ', ' '], $field);
                $changes[] = ucfirst($label) . " changed" . ($oldValue ? " from \"$oldValue\"" : "") . " to \"$newValue\"";
                continue;
            }

            $label = str_replace('_', ' ', $field);
            $changes[] = ucfirst($label) . " changed" . ($oldValue ? " from \"$oldValue\"" : "") . " to \"$newValue\"";
        }

        return implode(', ', $changes);
    }
    
    protected function detectBlockChanges(array $oldBlocks, array $newBlocks): array
    {
        $changes = [];
        $oldCount = count($oldBlocks);
        $newCount = count($newBlocks);
        
        // Detect additions
        if ($newCount > $oldCount) {
            for ($i = $oldCount; $i < $newCount; $i++) {
                $block = $newBlocks[$i];
                $blockType = $block['type'] ?? 'unknown';
                $position = $i + 1; // 1-indexed for user display
                $changes[] = "Added #{$position} {$blockType} block";
            }
        }
        
        // Detect deletions - track which positions were deleted
        if ($newCount < $oldCount) {
            for ($i = $newCount; $i < $oldCount; $i++) {
                $block = $oldBlocks[$i];
                $blockType = $block['type'] ?? 'unknown';
                $position = $i + 1; // 1-indexed for user display
                $changes[] = "Deleted #{$position} {$blockType} block";
            }
        }
        
        // Detect modifications in existing blocks
        $minCount = min($oldCount, $newCount);
        for ($i = 0; $i < $minCount; $i++) {
            $oldBlock = $oldBlocks[$i];
            $newBlock = $newBlocks[$i];
            $blockType = $newBlock['type'] ?? $oldBlock['type'] ?? 'unknown';
            $position = $i + 1; // 1-indexed for user display
            
            // Check if block type changed
            if (($oldBlock['type'] ?? null) !== ($newBlock['type'] ?? null)) {
                $changes[] = "Changed #{$position} block type from {$oldBlock['type']} to {$newBlock['type']}";
                continue;
            }
            
            // Check for field changes within the block
            $blockFieldChanges = $this->detectBlockFieldChanges($oldBlock, $newBlock, $blockType, $position);
            if (!empty($blockFieldChanges)) {
                $changes = array_merge($changes, $blockFieldChanges);
            }
        }
        
        return $changes;
    }
    
    protected function detectBlockFieldChanges(array $oldBlock, array $newBlock, string $blockType, int $position): array
    {
        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($oldBlock), array_keys($newBlock)));
        
        foreach ($allKeys as $key) {
            if ($key === 'type' || $key === 'id') {
                continue;
            }
            
            $oldVal = $oldBlock[$key] ?? null;
            $newVal = $newBlock[$key] ?? null;
            
            // Skip if values are the same
            if ($oldVal === $newVal) {
                continue;
            }
            
            // Handle nested arrays/objects
            if (is_array($oldVal) || is_array($newVal)) {
                if (json_encode($oldVal) !== json_encode($newVal)) {
                    $changes[] = "Updated {$key} in #{$position} {$blockType} block";
                }
                continue;
            }
            
            // Handle simple value changes
            $fieldLabel = str_replace('_', ' ', $key);
            if ($oldVal === null || $oldVal === '') {
                $changes[] = "Set {$fieldLabel} in #{$position} {$blockType} block";
            } elseif ($newVal === null || $newVal === '') {
                $changes[] = "Cleared {$fieldLabel} in #{$position} {$blockType} block";
            } else {
                $changes[] = "Updated {$fieldLabel} in #{$position} {$blockType} block";
            }
        }
        
        return $changes;
    }
    
    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'blocks',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'is_published',
        'robots_noindex',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_published' => 'boolean',
        'robots_noindex' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
