@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Redirect Management</h1>
        </div>

        <!-- Add Redirect Form -->
        <div
            class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Create New Redirect</h3>
                <form action="{{ route('admin.redirects.store') }}" method="POST"
                    class="mt-5 grid grid-cols-1 gap-y-4 sm:grid-cols-4 sm:gap-x-4">
                    @csrf
                    <div class="sm:col-span-1">
                        <label for="from_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From
                            URL</label>
                        <input type="text" name="from_url" id="from_url" required placeholder="/old-path"
                            class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="sm:col-span-1">
                        <label for="to_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To
                            URL</label>
                        <input type="text" name="to_url" id="to_url" required placeholder="/new-path"
                            class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="sm:col-span-1">
                        <label for="status_code"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                        <select name="status_code" id="status_code"
                            class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="301">301 (Permanent)</option>
                            <option value="302">302 (Temporary)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit"
                            class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add Redirect
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Redirects List -->
        <div
            class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            From</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            To</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">
                                            Type</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-48">
                                            Page (Context)</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Delete</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($redirects as $redirect)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-gray-400">
                                                {{ $redirect->from_url }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-gray-400">
                                                {{ $redirect->to_url }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $redirect->status_code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($redirect->page)
                                                    <a href="{{ route('admin.pages.edit', $redirect->page_id) }}"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        {{ $redirect->page->title }}
                                                    </a>
                                                @else
                                                    <span class="italic">Manual</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('admin.redirects.destroy', $redirect->id) }}"
                                                    method="POST" onsubmit="return confirm('Delete this redirect?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"
                                                class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                                No redirects found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection