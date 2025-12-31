@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.crons.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Tasks
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $task->name }}</h1>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $task->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $task->is_enabled ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <form action="{{ route('admin.crons.run', $task) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium">
                        Run Now
                    </button>
                </form>
                <form action="{{ route('admin.crons.toggle', $task) }}" method="POST">
                    @csrf
                    <button type="submit" class="{{ $task->is_enabled ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded text-sm font-medium">
                        {{ $task->is_enabled ? 'Disable Task' : 'Enable Task' }}
                    </button>
                </form>
            </div>
        </div>
        
        <div class="px-6 py-4 space-y-4">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</h3>
                    <div class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($task->last_status === 'success') bg-green-100 text-green-800 
                            @elseif($task->last_status === 'failed') bg-red-100 text-red-800 
                            @elseif($task->last_status === 'running') bg-blue-100 text-blue-800 
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($task->last_status ?? 'Never Run') }}
                        </span>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Command</h3>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"><code>{{ $task->command }}</code></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Schedule</h3>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"><code>{{ $task->schedule }}</code></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Run</h3>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->last_run_at ? $task->last_run_at->format('Y-m-d H:i:s') . ' (' . $task->last_run_at->diffForHumans() . ')' : 'N/A' }}</p>
                </div>
            </div>


            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Last Output</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre class="text-xs"><code>{{ $task->last_output ?? 'No output available.' }}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
