@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex-grow">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Site Settings</h1>
                
                @if($allRootPages->count() > 1)
                    <div class="mt-3">
                        <label for="site_selector" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Select Site to Configure
                        </label>
                        <select 
                            id="site_selector" 
                            onchange="window.location.href='{{ route('admin.settings.index') }}?site_id=' + this.value"
                            class="w-full max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        >
                            @foreach($allRootPages as $rootPage)
                                @php
                                    $translation = $rootPage->translations->first();
                                    $title = $translation ? $translation->title : 'Untitled Site';
                                @endphp
                                <option value="{{ $rootPage->id }}" {{ $rootPage->id == $site->id ? 'selected' : '' }}>
                                    {{ $title }} @if($rootPage->site_key)({{ $rootPage->site_key }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    @php
                        $siteTranslation = $site->translations->first();
                        $siteTitle = $siteTranslation ? $siteTranslation->title : 'Untitled Site';
                    @endphp
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configuring: <span class="font-medium">{{ $siteTitle }}</span></p>
                @endif
            </div>
            <a href="{{ route('admin.pages.edit', $site->id) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Page
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="site_id" value="{{ $site->id }}">

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">General Settings</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="site_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Site Key
                            <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="site_key" 
                            id="site_key" 
                            value="{{ old('site_key', $site->site_key) }}"
                            placeholder="test-site"
                            pattern="[a-z0-9\-]+"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        >
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Unique identifier for this site's resources folder (lowercase, numbers, and hyphens only). 
                            Resources will be stored in <code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-900 rounded">resources/sites/{{ old('site_key', $site->site_key ?: 'site-key') }}/</code>
                        </p>
                        @error('site_key')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Configured Domains
                        </label>
                        @if(!empty($availableDomains))
                            <div class="space-y-2">
                                @foreach($availableDomains as $domain)
                                    @php
                                        $isConflict = isset($usedDomains[$domain]);
                                    @endphp
                                    <div class="flex items-center px-3 py-2 {{ $isConflict ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800' : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700' }} border rounded-md">
                                        @if($isConflict)
                                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                        <span class="text-sm {{ $isConflict ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }} flex-grow">{{ $domain }}</span>
                                        @if($domain === $primaryDomain)
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">Primary</span>
                                        @endif
                                        @if($isConflict)
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300" title="Used by {{ $usedDomains[$domain]['site_name'] }}">Conflict</span>
                                        @endif
                                    </div>
                                    @if($isConflict)
                                        <p class="ml-6 text-xs text-red-600 dark:text-red-400">
                                            ⚠️ This domain is already used by <strong>{{ $usedDomains[$domain]['site_name'] }}</strong> ({{ $usedDomains[$domain]['site_key'] }})
                                        </p>
                                    @endif
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Domains are configured in <code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-900 rounded">resources/sites/{{ $site->site_key }}.yaml</code>
                            </p>
                        @else
                            <div class="px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md">
                                <p class="text-sm text-amber-700 dark:text-amber-300">
                                    No domains configured. Create a YAML configuration file at 
                                    <code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900 rounded">resources/sites/{{ $site->site_key }}.yaml</code>
                                </p>
                            </div>
                        @endif
                    </div>

                    @if(!empty($languages))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Configured Languages
                            </label>
                            <div class="space-y-2">
                                @foreach($languages as $code => $lang)
                                    <div class="flex items-center px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-10">{{ strtoupper($code) }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 flex-grow">{{ $lang['name'] }}@if(isset($lang['native'])) ({{ $lang['native'] }})@endif</span>
                                        @if($lang['default'] ?? false)
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Default</span>
                                        @endif
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ ucfirst($lang['mode'] ?? 'standalone') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Languages are configured in <code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-900 rounded">resources/sites/{{ $site->site_key }}.yaml</code>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">404 Error Page</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="404_page_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Select 404 Page
                        </label>
                        <select 
                            name="404_page_id" 
                            id="404_page_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        >
                            <option value="">-- Select a page --</option>
                            @foreach($pages as $page)
                                @php
                                    $translation = $page->translations->first();
                                    $title = $translation ? $translation->title : 'Untitled';
                                @endphp
                                <option value="{{ $page->id }}" {{ old('404_page_id', $notFoundPage?->id) == $page->id ? 'selected' : '' }}>
                                    {{ $title }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            This page will be displayed when a page is not found. It will not be included in the sitemap.
                        </p>
                        @error('404_page_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <a 
                            href="{{ route('admin.pages.create', ['page_type' => '404']) }}" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create New 404 Page
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Entity Settings</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Configure settings for each entity type. For example, specify which page should display the detail view for news items.
                </p>
                
                <div class="space-y-6">
                    @foreach($entities as $entityType => $entity)
                        @if(isset($entity['root_integration']) && $entity['root_integration']['enabled'])
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    {{ $entity['name'] }}
                                </h3>
                                
                                @foreach($entity['root_integration']['settings'] as $settingKey => $settingConfig)
                                    <div class="ml-7 mb-4">
                                        <label for="entity_{{ $entityType }}_{{ $settingKey }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ $settingConfig['label'] }}
                                            @if($settingConfig['required'] ?? false)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        
                                        @if($settingKey === 'archive_page' || str_ends_with($settingKey, '_page_id') || str_ends_with($settingKey, '_archive_id'))
                                            <select 
                                                name="entity_settings[{{ $entityType }}][{{ $settingConfig['key'] ?? $settingKey }}]" 
                                                id="entity_{{ $entityType }}_{{ $settingKey }}"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                                                {{ ($settingConfig['required'] ?? false) ? 'required' : '' }}
                                            >
                                                <option value="">-- Select a page --</option>
                                                @foreach($pages as $page)
                                                    @php
                                                        $translation = $page->translations->first();
                                                        $title = $translation ? $translation->title : 'Untitled';
                                                        $savedValue = $entitySettings[$entityType][$settingConfig['key'] ?? $settingKey] ?? null;
                                                    @endphp
                                                    <option value="{{ $page->id }}" {{ old("entity_settings.{$entityType}.{$settingKey}", $savedValue) == $page->id ? 'selected' : '' }}>
                                                        {{ $title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input 
                                                type="text" 
                                                name="entity_settings[{{ $entityType }}][{{ $settingConfig['key'] ?? $settingKey }}]" 
                                                id="entity_{{ $entityType }}_{{ $settingKey }}"
                                                value="{{ old("entity_settings.{$entityType}.{$settingKey}", $entitySettings[$entityType][$settingConfig['key'] ?? $settingKey] ?? '') }}"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                                                {{ ($settingConfig['required'] ?? false) ? 'required' : '' }}
                                            >
                                        @endif
                                        
                                        @if(isset($settingConfig['description']))
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $settingConfig['description'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a 
                    href="{{ route('admin.dashboard') }}" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cancel
                </a>
                <button 
                    type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Save Settings
                </button>
            </div>
        </form>
    </div>
@endsection
