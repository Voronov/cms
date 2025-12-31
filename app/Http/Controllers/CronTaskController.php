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
