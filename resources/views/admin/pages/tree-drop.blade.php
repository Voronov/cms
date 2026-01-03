<ul class="pl-4 space-y-1" data-level="{{ $level }}">
    @foreach ($pages as $treePage)
        <li class="drop-target" data-page-id="{{ $treePage->id }}" data-page-title="{{ $treePage->translation($currentLocale ?? app()->getLocale())?->title ?? $treePage->title }}">
            <div class="flex items-center group py-1 px-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                @if($treePage->children->count() > 0)
                    <button type="button"
                        class="tree-toggle-drop mr-1 p-0.5 text-gray-400 hover:text-gray-600 focus:outline-none transition-transform duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                @else
                    <span class="w-6 inline-block"></span>
                @endif

                <div class="flex-grow flex items-center text-sm text-gray-700 dark:text-gray-300">
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

                    {{ $treePage->translation($currentLocale ?? app()->getLocale())?->title ?? $treePage->title }}

                    @if($treePage->is_root)
                        <span class="ml-2 px-1.5 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">Home</span>
                    @endif
                </div>
            </div>

            @if ($treePage->children->count() > 0)
                <div class="subtree-drop" style="display: none;">
                    @include('admin.pages.tree-drop', ['pages' => $treePage->children, 'level' => $level + 1])
                </div>
            @endif
        </li>
    @endforeach
</ul>
