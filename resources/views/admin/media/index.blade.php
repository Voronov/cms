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
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
        @foreach($media as $item)
            <div class="relative group">
                <!-- Image Tile -->
                <div @click="showDetails({{ $item->id }})"
                     class="relative aspect-square cursor-pointer border-2 rounded-lg overflow-hidden transition-all border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg">
                    <img src="{{ Storage::url($item->path) }}"
                         alt="{{ $item->original_name }}"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all"></div>
                </div>

                <!-- Actions - Show on hover -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1" @click.stop>
                    <button @click="startRename({{ $item->id }}, '{{ addslashes($item->original_name) }}')"
                            class="bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 p-1.5 rounded shadow-lg"
                            title="Rename">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </button>
                    <button @click="confirmDelete({{ $item->id }})"
                            class="bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900 text-red-600 dark:text-red-400 p-1.5 rounded shadow-lg"
                            title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>

                <!-- Filename (editable) -->
                <div class="mt-2 px-1">
                    <div x-show="editingItemId !== {{ $item->id }}"
                         class="text-xs text-gray-600 dark:text-gray-400 truncate"
                         title="{{ $item->original_name }}">
                        <span>{{ $item->original_name }}</span>
                    </div>
                    <div x-show="editingItemId === {{ $item->id }}" class="flex gap-1">
                        <input type="text"
                               x-model="editingName"
                               @keydown.enter="saveRename({{ $item->id }})"
                               @keydown.escape="cancelRename()"
                               class="flex-1 text-xs px-2 py-1 border border-indigo-500 rounded dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <button @click="saveRename({{ $item->id }})" class="text-green-600 hover:text-green-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <button @click="cancelRename()" class="text-red-600 hover:text-red-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Image info -->
                <div class="mt-1 px-1 text-xs text-gray-500 dark:text-gray-500">
                    <span>{{ $item->width }}×{{ $item->height }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ number_format($item->size / 1024, 1) }} KB</span>
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
                            <img x-show="detailsModal.item && detailsModal.item.url"
                                 :src="detailsModal.item?.url || ''"
                                 :alt="detailsModal.item?.original_name || ''"
                                 class="max-w-full max-h-full object-contain shadow-lg"
                                 x-on:error="console.error('Failed to load image:', detailsModal.item?.url)">
                            <div x-show="!detailsModal.item || !detailsModal.item.url" class="flex items-center justify-center">
                                <svg class="animate-spin h-12 w-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
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
                                            <p x-show="detailsModal.usage.length === 0" class="text-sm text-gray-400 italic">
                                                This image is not currently used in any blocks.
                                            </p>
                                            <ul x-show="detailsModal.usage.length > 0" class="space-y-2">
                                                <template x-for="usage in detailsModal.usage" :key="usage.url">
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
        editingItemId: null,
        editingName: '',

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
                console.log('Fetching media details for ID:', id);
                const response = await fetch(`/admin/media/${id}`);
                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Media details loaded:', data);
                console.log('Media item:', data.media);
                console.log('Media URL:', data.media?.url);

                this.detailsModal.item = data.media;
                this.detailsModal.usage = data.usage || [];
                this.detailsModal.isOpen = true;

                console.log('Modal state after setting:', this.detailsModal);
            } catch (error) {
                console.error('Failed to load media details:', error);
                alert('Failed to load image details. Please try again.');
            }
        },

        startRename(id, currentName) {
            this.editingItemId = id;
            this.editingName = currentName;
            this.$nextTick(() => {
                const input = document.querySelector('input[x-model="editingName"]');
                if (input) {
                    input.focus();
                    input.select();
                }
            });
        },

        cancelRename() {
            this.editingItemId = null;
            this.editingName = '';
        },

        async saveRename(id) {
            if (!this.editingName.trim()) {
                alert('Filename cannot be empty');
                return;
            }

            try {
                const response = await fetch(`/admin/media/${id}/rename`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.editingName
                    })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to rename image');
                }
            } catch (error) {
                console.error('Rename failed:', error);
                alert('Failed to rename: ' + error.message);
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
