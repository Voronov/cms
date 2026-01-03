<div x-data="imageLibraryModal()" 
     x-show="isOpen" 
     @open-image-library.window="openModal($event.detail)"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity" 
             aria-hidden="true"
             @click="closeModal()">
            <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start flex-col w-full">
                    <div class="flex justify-between items-center w-full mb-4 border-b pb-2 dark:border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            Image Library
                        </h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="flex space-x-4 mb-4" x-show="!isCropping">
                        <button @click="tab = 'library'; fetchMedia()" 
                                :class="tab === 'library' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Library
                        </button>
                        <button @click="tab = 'upload'" 
                                :class="tab === 'upload' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Upload New
                        </button>
                    </div>

                    <div class="w-full min-h-[400px]">
                        <!-- Library Tab -->
                        <div x-show="tab === 'library' && !isCropping" class="w-full">
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                <template x-for="item in mediaItems" :key="item.id">
                                    <div @click="selectMedia(item)" 
                                         class="relative aspect-square cursor-pointer border-2 rounded overflow-hidden group"
                                         :class="selectedMedia?.id === item.id ? 'border-indigo-500' : 'border-transparent hover:border-gray-300'">
                                        <img :src="'/storage/' + item.path" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all"></div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="loading" class="flex justify-center py-8">
                                <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div x-show="!loading && mediaItems.length === 0" class="text-center py-8 text-gray-500">
                                No images found.
                            </div>
                        </div>

                        <!-- Upload Tab -->
                        <div x-show="tab === 'upload' && !isCropping" class="w-full h-full flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-12">
                            <input type="file" id="image-upload-input" class="hidden" @change="handleFileUpload($event)" accept="image/*">
                            <label for="image-upload-input" class="cursor-pointer flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Click to upload or drag and drop</span>
                                <span class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 10MB</span>
                            </label>
                            
                            <div x-show="uploadProgress > 0" class="w-full max-w-xs mt-4">
                                <div class="bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-indigo-600 h-2.5 rounded-full" :style="'width: ' + uploadProgress + '%'"></div>
                                </div>
                                <p class="text-xs text-center mt-1 text-gray-500" x-text="'Uploading: ' + uploadProgress + '%'"></p>
                            </div>
                        </div>

                        <!-- Cropping Area -->
                        <div x-show="isCropping" class="w-full flex flex-col items-center">
                            <div class="max-w-full overflow-hidden bg-gray-100 dark:bg-gray-900 rounded-lg">
                                <img id="cropper-image" src="" class="max-h-[500px]">
                            </div>
                            <div class="mt-4 flex space-x-4">
                                <button @click="isCropping = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                    Cancel
                                </button>
                                <button @click="saveCrop()" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                    Apply & Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse" x-show="!isCropping">
                <button @click="confirmSelection()" 
                        :disabled="!selectedMedia"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Next (Crop)
                </button>
                <button @click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function imageLibraryModal() {
    return {
        isOpen: false,
        tab: 'library',
        mediaItems: [],
        selectedMedia: null,
        loading: false,
        isCropping: false,
        cropper: null,
        uploadProgress: 0,
        options: {},

        openModal(options = {}) {
            this.options = options;
            this.isOpen = true;
            this.tab = 'library';
            this.selectedMedia = null;
            this.isCropping = false;
            this.fetchMedia();
        },

        closeModal() {
            this.isOpen = false;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },

        fetchMedia() {
            this.loading = true;
            fetch('/admin/media', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(data => {
                    this.mediaItems = data.data;
                    this.loading = false;
                })
                .catch(err => {
                    console.error('Error fetching media:', err);
                    this.loading = false;
                });
        },

        selectMedia(item) {
            this.selectedMedia = item;
        },

        confirmSelection() {
            if (!this.selectedMedia) return;
            this.startCropping('/storage/' + this.selectedMedia.path);
        },

        startCropping(url) {
            this.isCropping = true;
            const img = document.getElementById('cropper-image');
            img.src = url;
            
            this.$nextTick(() => {
                if (this.cropper) {
                    this.cropper.destroy();
                }
                this.cropper = new Cropper(img, {
                    aspectRatio: this.options.aspectRatio || NaN,
                    viewMode: 1,
                    autoCropArea: 1,
                });
            });
        },

        saveCrop() {
            const canvas = this.cropper.getCroppedCanvas();
            const data = this.cropper.getData();
            
            fetch('/admin/media/crop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    path: this.selectedMedia.path,
                    x: Math.round(data.x),
                    y: Math.round(data.y),
                    width: Math.round(data.width),
                    height: Math.round(data.height)
                })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    this.$dispatch('image-selected', {
                        url: result.url,
                        path: result.path,
                        id: result.media_id,
                        target: this.options.target
                    });
                    this.closeModal();
                }
            });
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const identifier = Math.random().toString(36).substring(2, 15);
            const chunkSize = 2 * 1024 * 1024; // 2MB
            const totalChunks = Math.ceil(file.size / chunkSize);
            let currentChunk = 0;

            const uploadNextChunk = () => {
                const start = currentChunk * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);

                const formData = new FormData();
                formData.append('file', chunk);
                formData.append('identifier', identifier);
                formData.append('chunk_index', currentChunk);
                formData.append('total_chunks', totalChunks);
                formData.append('filename', file.name);

                fetch('/admin/media/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    currentChunk++;
                    this.uploadProgress = Math.round((currentChunk / totalChunks) * 100);

                    if (currentChunk < totalChunks) {
                        uploadNextChunk();
                    } else {
                        // Upload complete
                        this.uploadProgress = 0;
                        if (data.success) {
                            this.selectedMedia = {
                                path: data.path,
                                id: data.media_id
                            };
                            this.startCropping(data.url);
                        }
                    }
                });
            };

            uploadNextChunk();
        }
    };
}
</script>
