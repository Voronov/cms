<ul class="pl-4 sortable-tree" data-parent-id="{{ $pages->first()->parent_id ?? '' }}" data-site-id="{{ $pages->first()->getRootPage()?->id ?? '' }}">
    @foreach ($pages as $treePage)
        <li class="mb-1" data-id="{{ $treePage->id }}">
            <div class="flex items-center group">
                @if($treePage->children->count() > 0)
                    <button type="button"
                        class="tree-toggle mr-1 p-0.5 text-gray-400 hover:text-gray-600 focus:outline-none transition-transform duration-200 {{ (isset($activePath) && in_array($treePage->id, $activePath)) ? '' : '-rotate-90' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                @else
                    <span class="w-6 inline-block"></span>
                @endif

                <a href="{{ route('admin.pages.edit', $treePage->id) }}"
                    class="flex-grow flex items-center px-2 py-1 text-sm rounded-md transition-colors duration-150
                                            {{ (isset($page) && $page->id == $treePage->id) ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                    
                    @php $pageTypeInfo = $treePage->getPageTypeInfo(); @endphp
                    <svg class="w-4 h-4 mr-2 text-{{ $pageTypeInfo['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($pageTypeInfo['icon'] === 'home')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        @elseif($pageTypeInfo['icon'] === 'document')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        @endif
                    </svg>

                    {{ $treePage->translation($currentLocale ?? app()->getLocale())?->title ?? $treePage->translation(collect($locales)->keys()->first() ?? 'en')?->title ?? $treePage->title }}

                    <div class="ml-auto flex space-x-1 pr-2">
                        @isset($locales)
                            @foreach($locales as $code => $lang)
                                @php $hasTrans = (bool) $treePage->translation($code); @endphp
                                <span title="{{ $lang['name'] }}: {{ $hasTrans ? 'Translated' : 'Missing' }}"
                                    class="w-1.5 h-1.5 rounded-full {{ $hasTrans ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                                </span>
                            @endforeach
                        @endisset
                    </div>

                    @if(!$treePage->is_published)
                        <span
                            class="ml-2 inline-flex items-center px-1.5 py-0.5 text-xs rounded-full bg-gray-200 text-gray-600">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Draft
                        </span>
                    @elseif(!$treePage->isEffectivelyPublished())
                        <span
                            class="ml-2 inline-flex items-center px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700"
                            title="Hidden because a parent is unpublished">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.046m4.596-4.596A9.964 9.964 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.225-4.225L3 3m10.125 10.125a3 3 0 11-4.25-4.25l4.25 4.25z">
                                </path>
                            </svg>
                            Hidden
                        </span>
                    @endif

                    @if($treePage->is_root)
                        <span class="ml-2 px-1.5 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">Home</span>
                    @endif
                </a>
            </div>

            @if ($treePage->children->count() > 0)
                <div class="subtree"
                    style="{{ (isset($activePath) && in_array($treePage->id, $activePath)) ? '' : 'display: none;' }}">
                    @include('admin.pages.tree', ['pages' => $treePage->children, 'locales' => $locales ?? null])
                </div>
            @endif
        </li>
    @endforeach
</ul>