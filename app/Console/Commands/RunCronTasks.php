<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunCronTasks extends Command
{
    protected $signature = 'cron:run';
    protected $description = 'Run scheduled cron tasks defined in YAML files';

    public function handle()
    {
        $cronFiles = glob(resource_path('crons/*.yaml'));

        foreach ($cronFiles as $file) {
            $config = \Symfony\Component\Yaml\Yaml::parseFile($file);
            $slug = basename($file, '.yaml');

            if (!($config['enabled'] ?? false)) {
                continue;
            }

            $task = \App\Models\CronTask::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $config['name'] ?? $slug,
                    'command' => $config['command'],
                    'schedule' => $config['schedule'],
                    'is_enabled' => $config['enabled'] ?? true,
                ]
            );

            if ($this->shouldRun($task)) {
                $this->runTask($task);
            }
        }
    }

    protected function shouldRun($task)
    {
        $cron = \Cron\CronExpression::factory($task->schedule);
        return $cron->isDue();
    }

    protected function runTask($task)
    {
        $this->info("Running task: {$task->name}");
        $task->update(['last_status' => 'running', 'last_run_at' => now()]);

        try {
            $output = \Illuminate\Support\Facades\Artisan::call($task->command);
            $task->update([
                'last_status' => 'success',
                'last_output' => \Illuminate\Support\Facades\Artisan::output(),
            ]);
        } catch (\Exception $e) {
            $task->update([
                'last_status' => 'failed',
                'last_output' => $e->getMessage(),
            ]);
        }
    }
}
