@extends('layouts.admin')

@section('content')
<div class="flex h-[calc(100vh-theme(spacing.24))]">
    <!-- Sidebar / Tree (1/3) -->
    <div class="w-1/3 border-r border-gray-200 dark:border-gray-700 pr-4 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Menus</h2>
            <a href="{{ route('admin.menus.create') }}" 
               class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white py-1 px-3 rounded">
                + New
            </a>
        </div>
        <div class="space-y-1">
            @forelse($menus as $menuItem)
                <a href="{{ route('admin.menus.edit', $menuItem->id) }}" 
                   class="block px-3 py-2 rounded-md text-sm font-medium {{ isset($menu) && $menu->id === $menuItem->id ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                    {{ $menuItem->name }}
                    <span class="text-xs text-gray-400 block">{{ $menuItem->slug }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-500 italic">No menus found.</p>
            @endforelse
        </div>
    </div>

    <!-- Main Content Area (2/3) -->
    <div class="w-2/3 pl-6 overflow-y-auto">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="flex flex-col items-center justify-center h-full text-center p-10">
            <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100">Select a Menu</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Select a menu from the sidebar to edit its items or create a new one.</p>
            <div class="mt-6">
                <a href="{{ route('admin.menus.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create New Menu
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
