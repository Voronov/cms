@extends('layouts.admin')

@section('title', 'Cache Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Cache Management</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Clear various caches to ensure fresh content and configurations.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Site Configuration Cache -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Site Configuration</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Clear cached site configuration including root page settings and entity routes.</p>
            <form method="POST" action="{{ route('admin.cache.clear') }}">
                @csrf
                <input type="hidden" name="type" value="site_config">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                    Clear Site Config
                </button>
            </form>
        </div>

        <!-- View Cache -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">View Cache</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Clear compiled Blade templates and view cache.</p>
            <form method="POST" action="{{ route('admin.cache.clear') }}">
                @csrf
                <input type="hidden" name="type" value="views">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded transition">
                    Clear Views
                </button>
            </form>
        </div>

        <!-- Route Cache -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Route Cache</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Clear cached application routes.</p>
            <form method="POST" action="{{ route('admin.cache.clear') }}">
                @csrf
                <input type="hidden" name="type" value="routes">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded transition">
                    Clear Routes
                </button>
            </form>
        </div>

        <!-- Config Cache -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Configuration Cache</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Clear cached configuration files.</p>
            <form method="POST" action="{{ route('admin.cache.clear') }}">
                @csrf
                <input type="hidden" name="type" value="config">
                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded transition">
                    Clear Config
                </button>
            </form>
        </div>

        <!-- Application Cache -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Application Cache</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Clear all application cache data.</p>
            <form method="POST" action="{{ route('admin.cache.clear') }}">
                @csrf
                <input type="hidden" name="type" value="application">
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded transition">
                    Clear App Cache
                </button>
            </form>
        </div>

        <!-- Clear All -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-2 border-red-200 dark:border-red-800">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Clear All Caches</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Clear all caches at once. Use this for a complete refresh.</p>
            <form method="POST" action="{{ route('admin.cache.clear') }}" onsubmit="return confirm('Are you sure you want to clear all caches?');">
                @csrf
                <input type="hidden" name="type" value="all">
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition">
                    Clear All Caches
                </button>
            </form>
        </div>
    </div>

    <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">About Cache Management</h3>
                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                    Caches are automatically cleared when you update root pages or entity configurations. 
                    Use these manual options if you need to force a refresh or troubleshoot issues.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
