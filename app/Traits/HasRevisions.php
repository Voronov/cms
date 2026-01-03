<?php

namespace App\Traits;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasRevisions
{
    protected static $revisionDescriptions = [];

    public static function bootHasRevisions()
    {
        static::saving(function ($model) {
            self::$revisionDescriptions[spl_object_hash($model)] = $model->generateRevisionDescription();
        });

        static::saved(function ($model) {
            $hash = spl_object_hash($model);
            $description = self::$revisionDescriptions[$hash] ?? null;
            $model->createRevision($description);
            unset(self::$revisionDescriptions[$hash]);
        });
    }

    protected function generateRevisionDescription(): string
    {
        $changes = [];
        $dirty = $this->getDirty();

        foreach ($dirty as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at', 'deleted_at'])) {
                continue;
            }

            $oldValue = $this->getOriginal($field);

            // Handle SEO fields (often JSON in seo column)
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

            // Handle Blocks
            if ($field === 'blocks') {
                $changes[] = "Blocks updated";
                continue;
            }

            // Handle system_config
            if ($field === 'system_config') {
                $changes[] = "System configuration updated";
                continue;
            }

            // Handle Meta/OG fields in Page model (direct columns)
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

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    public function createRevision(?string $description = null)
    {
        $revision = $this->revisions()->create([
            'content' => $this->getAttributes(),
            'user_id' => Auth::id(),
            'description' => $description,
        ]);

        $this->limitRevisions();

        return $revision;
    }

    protected function limitRevisions()
    {
        $keep = 10;
        $revisions = $this->revisions()->latest()->get();

        if ($revisions->count() > $keep) {
            $revisions->slice($keep)->each->delete();
        }
    }

    public function rollbackToRevision(int $revisionId)
    {
        $revision = $this->revisions()->findOrFail($revisionId);
        $this->fill($revision->content);
        $this->save();
        return $this;
    }
}
