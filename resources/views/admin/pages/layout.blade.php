@extends('layouts.admin')

@section('content')
    <div class="flex h-[calc(100vh-theme(spacing.24))]">
        <!-- Sidebar Tree (1/3) -->
        <div class="w-1/3 border-r border-gray-200 dark:border-gray-700 pr-4 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pages</h2>
                <a href="{{ route('admin.pages.create', ['parent_id' => isset($page) ? $page->id : null]) }}"
                    class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white py-1 px-3 rounded">
                    + New
                </a>
            </div>

            <div class="space-y-1">
                @if(isset($tree) && $tree->count() > 0)
                    @include('admin.pages.tree', ['pages' => $tree])
                @else
                    <p class="text-sm text-gray-500 italic">No pages found.</p>
                @endif
            </div>
        </div>

        <!-- Content Area (2/3) -->
        <div class="w-2/3 pl-6 overflow-y-auto">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @yield('page-content')
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Function to init sortable on all nested lists
                function initSortable(el) {
                    // Get the site ID from the tree element
                    var siteId = el.getAttribute('data-site-id');
                    
                    new Sortable(el, {
                        group: siteId ? 'site-' + siteId : 'nested', // Site-specific groups to prevent cross-site dragging
                        animation: 150,
                        fallbackOnBody: true,
                        swapThreshold: 0.65,
                        onEnd: function (evt) {
                            var itemEl = evt.item;
                            var parentEl = evt.to;
                            var newParentId = parentEl.getAttribute('data-parent-id');
                            var pageId = itemEl.getAttribute('data-id');

                            // If newParentId is empty (top level UL in tree.blade.php needs checking), assume null
                            // We need to make sure the root UL has data-parent-id=""

                            var payload = { parent_id: newParentId ? newParentId : null };

                            fetch('/admin/pages/' + pageId + '/move', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(payload)
                            }).then(response => {
                                if (!response.ok) {
                                    return response.json().then(data => {
                                        alert(data.error || 'Failed to move page');
                                        location.reload();
                                    });
                                }
                            }).catch(error => {
                                alert('Failed to move page');
                                location.reload();
                            });
                        }
                    });
                }

                // Init on all .sortable-tree elements
                var nestedSortables = [].slice.call(document.querySelectorAll('.sortable-tree'));
                for (var i = 0; i < nestedSortables.length; i++) {
                    initSortable(nestedSortables[i]);
                }

                // Tree Toggle Logic
                document.querySelectorAll('.tree-toggle').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Toggle Icon Rotation
                        this.classList.toggle('transform');
                        this.classList.toggle('-rotate-90');

                        var li = this.closest('li');
                        var subtree = li.querySelector('.subtree');

                        if (subtree) {
                            if (subtree.style.display === 'none') {
                                subtree.style.display = 'block';
                                this.classList.remove('-rotate-90');
                            } else {
                                subtree.style.display = 'none';
                                this.classList.add('-rotate-90');
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection