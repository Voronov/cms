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
    <div class="w-2/3 pl-6 overflow-y-auto" x-data="menuEditor({
        menuId: {{ $menu->id }},
        initialItems: {{ Js::from($menu->rootItems()->with(['translations', 'children.translations', 'children.children.translations'])->get()->map(fn($item) => $this->formatItemForAlpine($item))) }},
        locales: {{ Js::from($locales) }},
        defaultLocale: '{{ $defaultLocale }}',
        pages: {{ Js::from($pages->map(fn($p) => ['id' => $p->id, 'title' => $p->translation($defaultLocale)?->title ?? $p->title])) }}
    })">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Menu: {{ $menu->name }}</h1>
            </div>
            <div class="flex space-x-3">
                <button @click="addItem()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                    Add Item
                </button>
                <button @click="saveMenu()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    Save Changes
                </button>
                <form action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" onsubmit="return confirm('Delete this menu?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Locale Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <template x-for="(lang, code) in locales" :key="code">
                    <button @click="currentTab = code"
                        :class="currentTab === code 
                                ? 'border-indigo-500 text-indigo-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition focus:outline-none">
                        <span x-text="lang.name"></span>
                        <span class="ml-2 px-1.5 py-0.5 text-[10px] bg-gray-100 text-gray-600 rounded uppercase tracking-wider" x-text="code"></span>
                    </button>
                </template>
            </nav>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-4" id="menu-items-list">
                    <template x-for="(item, index) in items" :key="item.tempId || item.id">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900/50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-4 flex-1">
                                    <div class="cursor-move text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-500 uppercase" x-text="'Title (' + currentTab.toUpperCase() + ')'"></label>
                                        <input type="text" x-model="item.translations[currentTab]" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            :placeholder="'Enter title in ' + locales[currentTab].name">
                                    </div>
                                </div>
                                <div class="ml-4 flex items-center space-x-2">
                                    <button @click="item.is_active = !item.is_active" 
                                        :class="item.is_active ? 'text-green-600 hover:text-green-900' : 'text-gray-400 hover:text-gray-600'"
                                        :title="item.is_active ? 'Item is active' : 'Item is inactive'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button @click="removeItem(items, index)" class="text-red-600 hover:text-red-900 p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Link Type</label>
                                    <select x-model="item.type" @change="item.page_id = ''; item.url = ''"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="page">Internal Page</option>
                                        <option value="url">External URL</option>
                                        <option value="anchor">Anchor Only</option>
                                    </select>
                                </div>
                                <div x-show="item.type === 'page'">
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Select Page</label>
                                    <select x-model="item.page_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">-- Choose Page --</option>
                                        <template x-for="page in pages" :key="page.id">
                                            <option :value="page.id" x-text="page.title" :selected="item.page_id == page.id"></option>
                                        </template>
                                    </select>
                                </div>
                                <div x-show="item.type === 'url'">
                                    <label class="block text-xs font-medium text-gray-500 uppercase">URL</label>
                                    <input type="text" x-model="item.url" placeholder="https://..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Anchor (Optional)</label>
                                    <input type="text" x-model="item.anchor" placeholder="#contact"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                            </div>

                            <!-- Sub-items -->
                            <div class="ml-8 border-l-2 border-gray-200 dark:border-gray-700 pl-4 mt-4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Sub-items</h4>
                                <div class="space-y-3">
                                    <template x-for="(child, childIndex) in item.children" :key="child.tempId || child.id">
                                        <div class="bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-1">
                                                    <label class="block text-[10px] font-medium text-gray-500 uppercase" x-text="'Title (' + currentTab.toUpperCase() + ')'"></label>
                                                    <input type="text" x-model="child.translations[currentTab]" :placeholder="'Sub-item title in ' + locales[currentTab].name"
                                                        class="mt-1 block w-full border-gray-300 rounded-md text-sm dark:bg-gray-700">
                                                </div>
                                                <div class="flex items-center space-x-2 pt-4">
                                                    <button @click="child.is_active = !child.is_active" 
                                                        :class="child.is_active ? 'text-green-600 hover:text-green-900' : 'text-gray-400 hover:text-gray-600'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </button>
                                                    <button @click="removeItem(item.children, childIndex)" class="text-red-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <button @click="addChild(item)" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">
                                        + Add Sub-item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('menuEditor', (config) => ({
        items: config.initialItems,
        locales: config.locales,
        defaultLocale: config.defaultLocale,
        currentTab: config.defaultLocale,
        pages: config.pages,

        addItem() {
            this.items.push(this.createNewItem());
        },

        addChild(item) {
            if (!item.children) item.children = [];
            item.children.push(this.createNewItem());
        },

        createNewItem() {
            const trans = {};
            Object.keys(this.locales).forEach(l => trans[l] = '');
            return {
                tempId: Date.now() + Math.random(),
                id: null,
                type: 'page',
                page_id: '',
                url: '',
                anchor: '',
                is_active: true,
                translations: trans,
                children: []
            };
        },

        removeItem(collection, index) {
            if (confirm('Remove this item and all its sub-items?')) {
                collection.splice(index, 1);
            }
        },

        async saveMenu() {
            try {
                const response = await fetch(`/admin/menus/${config.menuId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ items: this.items })
                });

                if (response.ok) {
                    alert('Menu saved successfully!');
                    window.location.reload();
                } else {
                    alert('Failed to save menu.');
                }
            } catch (error) {
                console.error('Error saving menu:', error);
                alert('An error occurred while saving.');
            }
        }
    }));
});
</script>
@endpush
@endsection
