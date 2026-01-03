@extends('admin.pages.layout')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        .cropper-container { max-height: 500px; }
        .upload-progress { height: 4px; transition: width 0.3s ease; }
    </style>
@endpush

@section('page-content')
    <div x-data="pageEditor({
        mediaConfig: {{ Js::from($mediaConfig) }},
        pageBlocks: {{ Js::from($translation->blocks ?? []) }},
        blockTypes: {{ Js::from($blocks) }},
        pageId: {{ $page->id }},
        locale: '{{ $currentLocale }}',
        showSettings: false
    })" class="relative pb-12">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $translation->title ?? 'Untitled' }}
                    <span class="text-sm font-normal text-gray-500">({{ strtoupper($currentLocale) }})</span>
                </h1>
                @if($page->is_root)
                    <a href="{{ route('admin.settings.index', ['site_id' => $page->id]) }}"
                        class="inline-flex items-center px-3 py-1.5 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-100 dark:hover:bg-blue-800 dark:border-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Site Settings
                    </a>
                @endif
                <button type="button" @click="showSettings = !showSettings"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="showSettings ? 'Hide Settings' : 'Settings'"></span>
                </button>
                <button x-show="showSettings" type="button" onclick="document.getElementById('page-form').submit()"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save
                </button>
                <a href="{{ $page->getFullUrl($currentLocale) }}" target="_blank"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Open
                </a>
                <x-revision-manager :model="$page" :modelType="get_class($page)" />
            </div>

            <div class="flex items-center space-x-2">
                <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this page?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-red-600 hover:text-red-900 border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-1 rounded text-sm transition">
                        Delete Page
                    </button>
                </form>
            </div>
        </div>

        <!-- Language Switcher Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                @foreach($locales as $code => $lang)
                    <a href="{{ route('admin.pages.edit', ['page' => $page->id, 'locale' => $code]) }}"
                       class="{{ $currentLocale === $code 
                                ? 'border-indigo-500 text-indigo-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                                whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition">
                        {{ $lang['name'] }}
                        @if(!$page->translation($code))
                            <span class="ml-2 px-1.5 py-0.5 text-[10px] bg-gray-100 text-gray-600 rounded uppercase tracking-wider">Empty</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        <form id="page-form" action="{{ route('admin.pages.update', $page->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="locale" value="{{ $currentLocale }}">

            <!-- Settings Panel (Hidden by default) -->
            <div x-show="showSettings" x-transition
                class="mb-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:p-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">

                    <div class="sm:col-span-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Page Settings</h3>
                        <p class="mt-1 text-sm text-gray-500">General and SEO configuration.</p>
                    </div>

                    <!-- Title -->
                    <div class="sm:col-span-3">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                        <div class="mt-1">
                            <input type="text" name="title" id="title" value="{{ old('title', $translation->title) }}" required
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <!-- Slug -->
                    <div class="sm:col-span-3">
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $translation->slug) }}"
                                class="flex-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-none rounded-l-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <button type="button" @click="confirmSlugRegeneration()"
                                class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500">
                                <span>Regenerate</span>
                            </button>
                        </div>
                        <div class="mt-1 flex justify-between items-center">
                            <p class="text-xs text-gray-500">Path: <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $page->getFullPath() }}</span></p>
                            <div class="flex items-center">
                                <input id="create_redirect" name="create_redirect" type="checkbox" value="1" checked
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="create_redirect" class="ml-2 block text-xs text-gray-700 dark:text-gray-300">Create Redirect</label>
                            </div>
                        </div>
                    </div>

                    <!-- Parent -->
                    <div class="sm:col-span-6">
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent
                            Page</label>
                        <div class="mt-1">
                            <select name="parent_id" id="parent_id"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None (Top Level)</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ $page->parent_id == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="sm:col-span-6 relative py-2">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-start">
                            <span class="pr-2 bg-white dark:bg-gray-800 text-sm text-gray-500">SEO</span>
                        </div>
                    </div>

                    <!-- Meta Title -->
                    <div class="sm:col-span-3">
                        <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta
                            Title</label>
                        <div class="mt-1">
                            <input type="text" name="meta_title" id="meta_title"
                                value="{{ old('meta_title', $translation->meta_title) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <!-- Meta Description -->
                    <div class="sm:col-span-3">
                        <label for="meta_description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Description</label>
                        <div class="mt-1">
                            <textarea name="meta_description" id="meta_description" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('meta_description', $translation->meta_description) }}</textarea>
                        </div>
                    </div>

                    <!-- Facebook (OG) Title -->
                    <div class="sm:col-span-3">
                        <label for="og_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facebook
                            (OG) Title</label>
                        <div class="mt-1">
                            <input type="text" name="og_title" id="og_title" value="{{ old('og_title', $translation->og_title) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <!-- Facebook (OG) Description -->
                    <div class="sm:col-span-3">
                        <label for="og_description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facebook (OG)
                            Description</label>
                        <div class="mt-1">
                            <textarea name="og_description" id="og_description" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('og_description', $translation->og_description) }}</textarea>
                        </div>
                    </div>

                    <!-- Facebook (OG) Image -->
                    <div class="sm:col-span-6">
                        <label for="og_image_url_suggested" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facebook (OG) Image URL</label>
                        <div class="mt-1 flex items-center space-x-4">
                            @if($translation->og_image)
                                <div class="flex-shrink-0">
                                    <img src="{{ Str::startsWith($translation->og_image, 'http') ? $translation->og_image : Storage::url($translation->og_image) }}" 
                                         alt="OG Image" class="h-10 w-10 object-cover rounded shadow-sm">
                                </div>
                            @endif
                            <input type="text" name="og_image" id="og_image_url_suggested" value="{{ old('og_image', $translation->og_image) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Will be auto-filled if you upload an image in blocks">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended size: 1200x630px.</p>
                    </div>

                    <!-- Publish -->
                    <div class="sm:col-span-6">
                        <div class="flex items-start space-x-8">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_published" name="is_published" type="checkbox" {{ $translation->is_published ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_published" class="font-medium text-gray-700 dark:text-gray-300">Publish ({{ strtoupper($currentLocale) }})</label>
                                    <p class="text-gray-500 dark:text-gray-400">Make translation visible.</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="robots_noindex" name="robots_noindex" type="checkbox" {{ $translation->robots_noindex ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="robots_noindex" class="font-medium text-gray-700 dark:text-gray-300">Hide from search engines</label>
                                    <p class="text-gray-500 dark:text-gray-400">Adds noindex tag.</p>
                                </div>
                            </div>

                            @if($page->is_root || !$hasRootPage)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_root" name="is_root" type="checkbox" value="1"
                                        {{ $page->is_root ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_root" class="font-medium text-gray-700 dark:text-gray-300">Root
                                        Page</label>
                                    <p class="text-gray-500 dark:text-gray-400">Set as Homepage.</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Layout -->
                    <div class="sm:col-span-6">
                        <label for="layout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Layout</label>
                        <select id="layout" name="layout" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @foreach($layouts as $key => $name)
                                <option value="{{ $key }}" {{ (old('layout', $page->layout) == $key) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Select a layout for this page.</p>
                    </div>

                    <!-- Sitemap -->
                    <div class="sm:col-span-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Sitemap Settings</h3>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sitemap_include" name="sitemap_include" type="checkbox" value="1" {{ $page->sitemap_include ? 'checked' : '' }}
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="sitemap_include" class="font-medium text-gray-700 dark:text-gray-300">Include in Sitemap</label>
                                        <p class="text-gray-500 dark:text-gray-400">Make searchable.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="sitemap_priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority (0.0 - 1.0)</label>
                                <input type="number" step="0.1" min="0" max="1" name="sitemap_priority" id="sitemap_priority"
                                    value="{{ old('sitemap_priority', $page->sitemap_priority) }}"
                                    class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="sitemap_changefreq" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Change Frequency</label>
                                <select id="sitemap_changefreq" name="sitemap_changefreq"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach(['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'] as $freq)
                                        <option value="{{ $freq }}" {{ old('sitemap_changefreq', $page->sitemap_changefreq) == $freq ? 'selected' : '' }}>
                                            {{ ucfirst($freq) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Redirects -->
                    <div class="sm:col-span-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Redirects</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">Code</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($redirects as $redirect)
                                        <tr>
                                            <td class="px-3 py-2 text-sm font-mono text-gray-600 dark:text-gray-400">{{ $redirect->from_url }}</td>
                                            <td class="px-3 py-2 text-sm font-mono text-gray-600 dark:text-gray-400">{{ $redirect->to_url }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $redirect->status_code }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-4 text-sm text-gray-500 italic text-center">No redirects found for this page.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Manual Redirect Form -->
                        <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md border border-gray-200 dark:border-gray-600">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Add Manual Redirect</h4>
                            <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-4 sm:gap-x-4">
                                <div class="sm:col-span-2">
                                    <label for="manual_from" class="block text-xs font-medium text-gray-500 uppercase">From URL</label>
                                    <input type="text" name="manual_from" id="manual_from" placeholder="/old-path"
                                        class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                </div>
                                <div class="sm:col-span-1">
                                    <label for="manual_to" class="block text-xs font-medium text-gray-500 uppercase">To URL</label>
                                    <input type="text" name="manual_to" id="manual_to" placeholder="/new-path"
                                        class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                </div>
                                <div class="sm:col-span-1">
                                    <label for="manual_status" class="block text-xs font-medium text-gray-500 uppercase">Code</label>
                                    <select name="manual_status" id="manual_status"
                                        class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                        <option value="301">301 (Permanent)</option>
                                        <option value="302">302 (Temporary)</option>
                                    </select>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Redirects created here will be saved when you click the main "Save" button.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Editor / Block Builder -->
            <div x-show="!showSettings" x-transition class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Page Blocks</h3>
                        <button type="button" @click.stop="showBlockPicker = true"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add Block
                        </button>
                    </div>

                    <input type="hidden" name="blocks" :value="JSON.stringify(pageBlocks)">

                    <!-- Blocks List -->
                    <div id="blocks-list" class="space-y-4">
                        <template x-for="(block, index) in pageBlocks" :key="block.id">
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50 overflow-hidden shadow-sm">
                                <!-- Block Header -->
                                <div @click="toggleBlockCollapse(index)" class="bg-gray-100 dark:bg-gray-700 px-4 py-2 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    <div class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <div class="drag-handle p-1 -ml-1 cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click.stop>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                        <svg class="h-4 w-4 transition-transform duration-200" :class="block.collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-0.5 rounded" x-text="block.name"></span>
                                        <span x-text="'#' + (index + 1)"></span>
                                        <template x-if="block.collapsed">
                                            <div class="flex items-center ml-4 space-x-2" x-html="renderPreview(block)"></div>
                                        </template>
                                    </div>
                                    <div class="flex items-center space-x-2" @click.stop>
                                        <button type="button" @click="toggleBlockCollapse(index)"
                                            class="p-1 text-gray-400 hover:text-indigo-600" title="Edit this block">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button" @click="saveBlock(index)"
                                            class="p-1 text-gray-400 hover:text-green-600" title="Save this block">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                        <button type="button" @click="moveBlock(index, -1)" :disabled="index === 0"
                                            class="p-1 text-gray-400 hover:text-indigo-600 disabled:opacity-30">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                        <button type="button" @click="moveBlock(index, 1)" :disabled="index === pageBlocks.length - 1"
                                            class="p-1 text-gray-400 hover:text-indigo-600 disabled:opacity-30">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <button type="button" @click="removeBlock(index)"
                                            class="p-1 text-gray-400 hover:text-red-600">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Block Content (Fields or Preview) -->
                                <div x-show="!block.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                                    <div class="p-4 grid grid-cols-1 gap-4">
                                        <template x-for="field in (blockTypes.find(b => b.id === block.type)?.fields || [])">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1" x-text="field.label"></label>
                                                
                                                <template x-if="field.type === 'input'">
                                                    <input type="text" x-model="block.data[field.name]"
                                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                                        :placeholder="field.placeholder">
                                                </template>

                                                <template x-if="field.type === 'textarea'">
                                                    <textarea x-model="block.data[field.name]" rows="4"
                                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                                        :placeholder="field.placeholder"></textarea>
                                                </template>

                                                <template x-if="field.type === 'select'">
                                                <select x-model="block.data[field.name]"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                    <template x-for="(label, value) in field.options">
                                                        <option :value="value" x-text="label"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="field.type === 'entity_select'">
                                                <select x-model="block.data[field.name]"
                                                    @change="if(field.name === 'entity_type') { loadCategories(index, block.data[field.name]); loadEntityItems(index, block.data[field.name]); loadPaginationOptions(index, block.data[field.name]); }"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                    <option value="">Select entity type...</option>
                                                    <template x-for="(label, value) in entityTypes">
                                                        <option :value="value" x-text="label"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="field.type === 'pagination_select'">
                                                <select x-model="block.data[field.name]"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                    <option value="">Use default</option>
                                                    <template x-for="(label, value) in (block.paginationOptions || {})">
                                                        <option :value="value" x-text="label"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="field.type === 'entity_item_select'">
                                                <select x-model="block.data[field.name]"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                    <option value="">Select entity...</option>
                                                    <template x-for="(label, value) in (block.entityItems || {})">
                                                        <option :value="value" x-text="label"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="field.type === 'category_select'">
                                                <select x-model="block.data[field.name]"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                    <option value="">All categories</option>
                                                    <template x-for="(label, value) in (block.categories || {})">
                                                        <option :value="value" x-text="label"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="field.type === 'checkbox'">
                                                <label class="flex items-center space-x-2 cursor-pointer">
                                                    <input type="checkbox" 
                                                           x-model="block.data[field.name]"
                                                           :checked="block.data[field.name] !== false"
                                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400" x-text="field.help || ''"></span>
                                                </label>
                                            </template>

                                            <template x-if="field.type === 'number'">
                                                <input type="number" 
                                                       x-model="block.data[field.name]"
                                                       :placeholder="field.placeholder"
                                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                            </template>

                                            <template x-if="field.type === 'file'">
                                                <div class="mt-1 space-y-2">
                                                    <div class="flex items-center space-x-4">
                                                        <div class="flex-shrink-0 w-24 h-24 border rounded bg-gray-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center">
                                                            <template x-if="block.data[field.name]">
                                                                <img :src="block.data[field.name]" class="w-full h-full object-cover">
                                                            </template>
                                                            <template x-if="!block.data[field.name]">
                                                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            </template>
                                                        </div>
                                                        <div class="flex-1 space-y-2">
                                                            <div class="flex items-center space-x-2">
                                                                <input type="text" x-model="block.data[field.name]" placeholder="URL or path"
                                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                            </div>
                                                            <div class="flex items-center space-x-2">
                                                                <button type="button" @click="openImageLibrary(index, field.name)"
                                                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                                                    <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                    </svg>
                                                                    Library / Upload
                                                                </button>
                                                                <template x-if="block.data[field.name]">
                                                                    <button type="button" @click="block.data[field.name] = ''; block.data[field.name + '_path'] = ''; block.data[field.name + '_id'] = '';"
                                                                        class="text-red-600 hover:text-red-900 text-xs font-medium">
                                                                        Remove
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <!-- Block Preview (When collapsed) -->
                                <div x-show="block.collapsed" class="p-4 bg-white dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700" x-html="renderPreview(block)"></div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="pageBlocks.length === 0" class="text-center py-12 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
                            <p class="text-gray-500 dark:text-gray-400">No blocks added yet. Click &quot;Add Block&quot; to start building your page.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right sm:px-6 rounded-b-lg flex justify-between items-center">
                    <button x-show="showSettings" type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>

        <!-- Block Picker Modal -->
        <template x-teleport="body">
            <div x-show="showBlockPicker" 
                 x-cloak
                 class="fixed inset-0 z-[100] overflow-y-auto" 
                 @keydown.escape.window="showBlockPicker = false"
                 role="dialog" 
                 aria-modal="true">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Backdrop -->
                    <div x-show="showBlockPicker" 
                         class="fixed inset-0 bg-gray-500/75 transition-opacity" 
                         aria-hidden="true"
                         @click="showBlockPicker = false"></div>
                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <!-- Modal Panel -->
                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-6 relative z-[101]"
                         @click.stop>
                        <div class="relative">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Select Block Type</h3>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <template x-for="blockType in blockTypes" :key="blockType.id">
                                    <button type="button" @click="addBlock(blockType.id)"
                                        class="relative rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <div class="flex-1 min-w-0 text-left">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="blockType.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="blockType.description"></p>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6">
                            <button type="button" @click="showBlockPicker = false"
                                class="inline-flex justify-center w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Slug Confirmation Modal -->
        <div x-show="showSlugModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="showSlugModal" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0" 
                    x-transition:enter-end="opacity-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100" 
                    x-transition:leave-end="opacity-0" 
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                    @click="showSlugModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Content -->
                <div x-show="showSlugModal" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                    @click.stop>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Regenerate Slug?</h3>
                            <div class="mt-2 space-y-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Changing the slug will change the page URL. This can negatively impact <strong>SEO</strong> and break existing links.
                                </p>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">New Slug Preview</p>
                                    <p class="text-sm font-mono text-indigo-600 dark:text-indigo-400 break-all" x-text="pendingSlug"></p>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    If "Create Redirect" is checked, we will automatically redirect users from the old URL to the new one.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="applySlug()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Confirm & Update
                        </button>
                        <button type="button" @click="showSlugModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cropping Modal -->
        <div x-show="cropping.show" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="cropping.show" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeCropper()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="cropping.show" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Edit Image</h3>
                        <div class="mt-4 cropper-container">
                            <img :src="cropping.imageUrl" id="cropper-image" class="max-w-full">
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeCropper()" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="button" @click="applyCrop()" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700">
                            Apply Crop
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="/js/page-editor.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    @endpush
@endsection