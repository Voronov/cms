@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add Navigation Item</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <form action="{{ route('admin.navigation.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Name *
            </label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   value="{{ old('name') }}"
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="route" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Route Name *
            </label>
            <input type="text" 
                   name="route" 
                   id="route" 
                   value="{{ old('route') }}"
                   placeholder="e.g., admin.custom.index"
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
            @error('route')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="section" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Section *
            </label>
            <select name="section" 
                    id="section" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                @foreach($sections as $key => $section)
                    <option value="{{ $key }}" {{ old('section') == $key ? 'selected' : '' }}>
                        {{ $section['label'] }}
                    </option>
                @endforeach
            </select>
            @error('section')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Icon *
            </label>
            <select name="icon" 
                    id="icon" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                @foreach($icons as $icon)
                    <option value="{{ $icon }}" {{ old('icon') == $icon ? 'selected' : '' }}>
                        {{ ucfirst($icon) }}
                    </option>
                @endforeach
            </select>
            @error('icon')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Order *
            </label>
            <input type="number" 
                   name="order" 
                   id="order" 
                   value="{{ old('order', 100) }}"
                   min="0"
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
            @error('order')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.navigation.index') }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition">
                Create Item
            </button>
        </div>
    </form>
</div>
@endsection
