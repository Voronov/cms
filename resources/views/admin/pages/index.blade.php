@extends('admin.pages.layout')

@section('page-content')
    <div class="flex flex-col items-center justify-center h-full text-center p-10">
        <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
            </path>
        </svg>
        <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100">Select a Page</h3>
        <p class="mt-2 text-gray-500 dark:text-gray-400">Select a page from the tree to edit or create a new one.</p>
        <div class="mt-6">
            <a href="{{ route('admin.pages.create') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create New Page
            </a>
        </div>
    </div>
@endsection