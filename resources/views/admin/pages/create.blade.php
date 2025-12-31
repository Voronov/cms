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
        pageBlocks: [],
        blockTypes: {{ Js::from($blocks) }},
        pageId: null,
        locale: '{{ $currentLocale }}',
        showSettings: true
    })" class="relative pb-12">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Create Page
                    <span class="text-sm font-normal text-gray-500">({{ strtoupper($currentLocale) }})</span>
                </h1>
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
            </div>

        <!-- Language Switcher Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                @foreach($locales as $code => $lang)
                    <a href="{{ route('admin.pages.create', ['locale' => $code, 'parent_id' => $selectedParentId]) }}"
                       class="{{ $currentLocale === $code 
                                ? 'border-indigo-500 text-indigo-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                                whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition">
                        {{ $lang['name'] }}
                    </a>
                @endforeach
            </nav>
        </div>
        </div>

        <form id="page-form" action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="locale" value="{{ $currentLocale }}">

            <!-- Settings Panel -->
            <div x-show="showSettings" x-transition
                class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6 border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:p-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">

                    <div class="sm:col-span-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Page Settings</h3>
                        <p class="mt-1 text-sm text-gray-500">General and SEO configuration.</p>
                    </div>

                    <!-- Title -->
                    <div class="sm:col-span-3">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                        <div class="mt-1">
                            <input type="text" name="title" id="title" required
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Page Title">
                        </div>
                    </div>

                    <!-- Slug -->
                    <div class="sm:col-span-3">
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug
                            (Optional)</label>
                        <div class="mt-1">
                            <input type="text" name="slug" id="slug"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="custom-slug">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate.</p>
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
                                    <option value="{{ $parent->id }}" {{ (isset($selectedParentId) && $selectedParentId == $parent->id) ? 'selected' : '' }}>
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
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <!-- Meta Description -->
                    <div class="sm:col-span-3">
                        <label for="meta_description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Description</label>
                        <div class="mt-1">
                            <textarea name="meta_description" id="meta_description" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                    </div>

                    <!-- Facebook (OG) Title -->
                    <div class="sm:col-span-3">
                        <label for="og_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facebook
                            (OG) Title</label>
                        <div class="mt-1">
                            <input type="text" name="og_title" id="og_title"
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
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                    </div>

                    <!-- Facebook (OG) Image (Auto-Fill Supported) -->
                    <div class="sm:col-span-6">
                        <label for="og_image_url_suggested" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facebook (OG) Image URL</label>
                        <div class="mt-1">
                            <input type="text" name="og_image" id="og_image_url_suggested"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Will be auto-filled if you upload an image in blocks">
                        </div>
                    </div>

                    <!-- Publish & Root -->
                    <div class="sm:col-span-6">
                        <div class="flex items-start space-x-8">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_published" name="is_published" type="checkbox"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_published"
                                        class="font-medium text-gray-700 dark:text-gray-300">Publish</label>
                                    <p class="text-gray-500 dark:text-gray-400">Make visible.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_root" name="is_root" type="checkbox"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_root" class="font-medium text-gray-700 dark:text-gray-300">Root
                                        Page</label>
                                    <p class="text-gray-500 dark:text-gray-400">Set as Homepage.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Layout -->
                    <div class="sm:col-span-6">
                        <label for="layout"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Layout</label>
                        <select id="layout" name="layout"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                            @foreach($layouts as $key => $name)
                                <option value="{{ $key }}" {{ (old('layout', 'default') == $key) ? 'selected' : '' }}>{{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sitemap -->
                    <div class="sm:col-span-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Sitemap Settings</h3>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sitemap_include" name="sitemap_include" type="checkbox" value="1" checked
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
                                    value="{{ old('sitemap_priority', 0.8) }}"
                                    class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="sitemap_changefreq" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Change Frequency</label>
                                <select id="sitemap_changefreq" name="sitemap_changefreq"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach(['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'] as $freq)
                                        <option value="{{ $freq }}" {{ old('sitemap_changefreq', 'weekly') == $freq ? 'selected' : '' }}>
                                            {{ ucfirst($freq) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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
                                            <span class="text-xs text-gray-500 dark:text-gray-400 font-normal truncate max-w-xs ml-2" x-text="block.data.title || block.data.content?.substring(0, 50) + '...' || ''"></span>
                                        </template>
                                    </div>
                                    <div class="flex items-center space-x-2" @click.stop>
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

                                            <template x-if="field.type === 'file'">
                                                <div class="mt-1 space-y-2">
                                                    <div class="flex items-center space-x-4">
                                                        <div class="flex-1">
                                                            <input type="text" x-model="block.data[field.name]" placeholder="URL or path"
                                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            <label class="cursor-pointer bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                <span>Upload</span>
                                                                <input type="file" class="hidden" @change="handleFileUpload($event, index, field.name)">
                                                            </label>
                                                        </div>
                                                        <template x-if="['jpg', 'jpeg', 'png', 'webp'].includes(block.data[field.name]?.split('.').pop()?.toLowerCase())">
                                                            <button type="button" @click="initCropper(index, field.name, block.data[field.name], block.data[field.name + '_path'] || block.data[field.name])"
                                                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                                Crop
                                                            </button>
                                                        </template>
                                                    </div>

                                                    <!-- Progress Bar -->
                                                    <template x-if="block.uploads && block.uploads[field.name]">
                                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1 overflow-hidden">
                                                            <div class="bg-indigo-600 h-1 transition-all duration-300" 
                                                                :style="'width: ' + block.uploads[field.name].progress + '%'"></div>
                                                        </div>
                                                    </template>
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
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Changes are saved only when you click "Create Page".
                    </div>
                    <button x-show="showSettings" type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Page
                    </button>
                    <a href="{{ route('admin.pages.index') }}"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </div>
        </form>

        <!-- Block Picker Modal -->
        <div x-show="showBlockPicker" class="fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showBlockPicker" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showBlockPicker = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showBlockPicker" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Select Block Type</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <template x-for="blockType in blockTypes" :key="blockType.id">
                                <button type="button" @click="addBlock(blockType.id)"
                                    class="relative rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-indigo-500 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
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
        <script>
            document.getElementById('title').addEventListener('input', function () {
                if (!document.getElementById('slug').value) {
                    let slug = this.value.toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/--+/g, '-')
                        .trim();
                    document.getElementById('slug').placeholder = slug || 'custom-slug';
                }
            });
        </script>
    @endpush
@endsection