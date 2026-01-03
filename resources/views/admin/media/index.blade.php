@extends('layouts.admin')

@section('content')
<div x-data="mediaSection()" class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Media Library</h1>
        <button @click="openUploadModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Upload Image
        </button>
    </div>

    <!-- Media Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        @foreach($media as $item)
            <div class="group relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer"
                 @click="showDetails({{ $item->id }})">
                <div class="aspect-square bg-gray-100 dark:bg-gray-900">
                    <img src="{{ Storage::url($item->path) }}" class="w-full h-full object-cover group-hover:opacity-75 transition-opacity">
                </div>
                <div class="p-2 truncate text-xs text-gray-500 dark:text-gray-400">
                    {{ $item->original_name }}
                </div>
                
                <!-- Quick Actions Overlay -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                    <button @click.stop="confirmDelete({{ $item->id }})" class="p-1.5 bg-red-600 text-white rounded-md hover:bg-red-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $media->links() }}
    </div>

    <!-- Details Modal -->
    <template x-teleport="body">
        <div x-show="detailsModal.isOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="detailsModal.isOpen" 
                     class="fixed inset-0 bg-gray-500/75 transition-opacity" 
                     @click="detailsModal.isOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full relative z-50">
                    <div class="flex flex-col md:flex-row h-[600px]">
                        <!-- Image Preview -->
                        <div class="md:w-2/3 bg-gray-100 dark:bg-gray-900 flex items-center justify-center p-6 border-r border-gray-200 dark:border-gray-700">
                            <img :src="detailsModal.item?.url" class="max-w-full max-h-full object-contain shadow-lg">
                        </div>

                        <!-- Sidebar Info -->
                        <div class="md:w-1/3 flex flex-col">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Image Details</h3>
                                <button @click="detailsModal.isOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-6 flex-1 overflow-y-auto">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-wider">Filename</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 break-all" x-text="detailsModal.item?.original_name"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-wider">Storage Path</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 break-all font-mono" x-text="detailsModal.item?.path"></dd>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-wider">Dimensions</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300" x-text="(detailsModal.item?.width || '?') + ' x ' + (detailsModal.item?.height || '?') + 'px'"></dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-wider">Size</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300" x-text="formatSize(detailsModal.item?.size)"></dd>
                                        </div>
                                    </div>
                                    
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Usage in Pages/Blocks</dt>
                                        <dd>
                                            <template x-if="detailsModal.usage.length === 0">
                                                <p class="text-sm text-gray-400 italic">This image is not currently used in any blocks.</p>
                                            </template>
                                            <ul class="space-y-2">
                                                <template x-for="usage in detailsModal.usage">
                                                    <li class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700/50 rounded-md border border-gray-100 dark:border-gray-600">
                                                        <div class="flex flex-col overflow-hidden">
                                                            <span class="text-xs font-medium text-gray-400 uppercase" x-text="usage.type"></span>
                                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate" x-text="usage.name"></span>
                                                        </div>
                                                        <a :href="usage.url" class="ml-2 text-gray-400 hover:text-indigo-600">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </a>
                                                    </li>
                                                </template>
                                            </ul>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="p-6 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                <button @click="confirmDelete(detailsModal.item?.id)" class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/50 dark:hover:bg-red-900/40">
                                    Delete Permanently
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function mediaSection() {
    return {
        detailsModal: {
            isOpen: false,
            item: null,
            usage: []
        },

        openUploadModal() {
            window.dispatchEvent(new CustomEvent('open-image-library', {
                detail: {
                    tab: 'upload'
                }
            }));
            
            // Listen for selection to refresh the page
            window.addEventListener('image-selected', () => {
                window.location.reload();
            }, { once: true });
        },

        async showDetails(id) {
            try {
                const response = await fetch(`/admin/media/${id}`);
                const data = await response.json();
                this.detailsModal.item = data.media;
                this.detailsModal.usage = data.usage;
                this.detailsModal.isOpen = true;
            } catch (error) {
                console.error('Failed to load media details:', error);
            }
        },

        async confirmDelete(id) {
            if (!confirm('Are you sure you want to delete this image? This will break any blocks using it.')) {
                return;
            }

            try {
                const response = await fetch(`/admin/media/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Delete failed:', error);
            }
        },

        formatSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}
</script>
@endsection
