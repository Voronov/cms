@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.menus.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Menus
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mt-2">Create New Menu</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700">
        <form action="{{ route('admin.menus.store') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Menu Name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="e.g. Main Navigation">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug (Optional)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="e.g. main-nav">
                    @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">Unique identifier for PHP helper. Auto-generated if empty.</p>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        Create Menu
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
