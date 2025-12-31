{{-- Sidebar Entity Single Layout - Content with sidebar --}}
<div class="entity-single-sidebar py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            {{ $slot }}
        </div>
        <aside class="lg:col-span-1">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 sticky top-4">
                <h3 class="font-bold text-lg mb-4">Related</h3>
                {{-- Sidebar content --}}
            </div>
        </aside>
    </div>
</div>
