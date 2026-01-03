<?php

namespace App\Traits;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->logActivity('created');
        });

        static::updated(function (Model $model) {
            $model->logActivity('updated');
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted');
        });
    }

    protected function logActivity(string $event)
    {
        $oldValues = null;
        $newValues = null;

        if ($event === 'updated') {
            $newValues = $this->getDirty();
            $oldValues = array_intersect_key($this->getOriginal(), $newValues);
            
            // Filter out timestamps and other sensitive fields if necessary
            $ignore = ['updated_at', 'created_at', 'deleted_at'];
            $newValues = array_diff_key($newValues, array_flip($ignore));
            $oldValues = array_diff_key($oldValues, array_flip($ignore));

            if (empty($newValues)) {
                return;
            }
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
            $ignore = ['updated_at', 'created_at', 'deleted_at'];
            $newValues = array_diff_key($newValues, array_flip($ignore));
        }

        SystemLog::create([
            'level' => 'info',
            'event' => $event,
            'message' => "User '" . (Auth::user()->name ?? 'System') . "' {$event} " . strtolower(class_basename($this)) . " '" . ($this->title ?? $this->name ?? $this->id) . "'",
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'context' => [
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
