<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CronTaskController extends Controller
{
    public function index()
    {
        $this->syncFromYaml();
        $tasks = \App\Models\CronTask::orderBy('name')->get();
        return view('admin.crons.index', compact('tasks'));
    }

    public function show(\App\Models\CronTask $task)
    {
        return view('admin.crons.show', compact('task'));
    }

    public function run(\App\Models\CronTask $task)
    {
        // Update status to running
        $task->update(['last_status' => 'running', 'last_run_at' => now()]);

        try {
            \Illuminate\Support\Facades\Artisan::call($task->command);
            $task->update([
                'last_status' => 'success',
                'last_output' => \Illuminate\Support\Facades\Artisan::output(),
            ]);
            return back()->with('success', "Task '{$task->name}' executed successfully.");
        } catch (\Exception $e) {
            $task->update([
                'last_status' => 'failed',
                'last_output' => $e->getMessage(),
            ]);
            return back()->with('error', "Task '{$task->name}' failed: " . $e->getMessage());
        }
    }

    public function toggle(\App\Models\CronTask $task)
    {
        $task->update(['is_enabled' => !$task->is_enabled]);
        $status = $task->is_enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Task '{$task->name}' has been {$status}.");
    }

    protected function syncFromYaml()
    {
        $cronFiles = glob(resource_path('crons/*.yaml'));
        $existingSlugs = [];

        foreach ($cronFiles as $file) {
            try {
                $config = \Symfony\Component\Yaml\Yaml::parseFile($file);
                $slug = basename($file, '.yaml');
                $existingSlugs[] = $slug;

                \App\Models\CronTask::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $config['name'] ?? $slug,
                        'command' => $config['command'],
                        'schedule' => $config['schedule'],
                        'is_enabled' => $config['enabled'] ?? true,
                    ]
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to parse cron YAML file: {$file}. Error: " . $e->getMessage());
            }
        }

        \App\Models\CronTask::whereNotIn('slug', $existingSlugs)->delete();
    }
}
