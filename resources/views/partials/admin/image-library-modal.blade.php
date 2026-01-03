<div x-data="imageLibraryModal()"
     @open-image-library.window="openModal($event.detail)"
     x-cloak
     class="fixed inset-0 overflow-y-auto"
     style="z-index: 9999; pointer-events: none;"
     :style="{'pointer-events': isOpen ? 'auto' : 'none'}">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0"
         x-show="isOpen"
         style="pointer-events: auto;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900"
             aria-hidden="true"
             @click.self="if (uploadProgress === 0 && !isCropping) closeModal()">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Content -->
        <div @click.stop
             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full relative z-10">
            
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

                    <div class="w-full" :class="isCropping ? 'min-h-[500px]' : 'min-h-[400px]'">
                        <!-- Library Tab -->
                        <div x-show="tab === 'library' && !isCropping" class="w-full min-h-[400px]">
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                <template x-for="item in mediaItems" :key="item.id">
                                    <div class="relative group">
                                        <!-- Image Tile -->
                                        <div @click="selectMedia(item)"
                                             class="relative aspect-square cursor-pointer border-2 rounded-lg overflow-hidden transition-all"
                                             :class="selectedMedia?.id === item.id ? 'border-indigo-500 ring-2 ring-indigo-500 ring-offset-2' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600'">
                                            <img :src="'/storage/' + item.path" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all"></div>

                                            <!-- Checkmark for selected -->
                                            <div x-show="selectedMedia?.id === item.id"
                                                 class="absolute top-2 left-2 bg-indigo-600 rounded-full p-1">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Actions - Show on hover -->
                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1" @click.stop>
                                            <button @click="startRename(item)"
                                                    class="bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 p-1.5 rounded shadow-lg"
                                                    title="Rename">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                </svg>
                                            </button>
                                            <button @click="confirmDelete(item)"
                                                    class="bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900 text-red-600 dark:text-red-400 p-1.5 rounded shadow-lg"
                                                    title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Filename (editable) -->
                                        <div class="mt-2 px-1">
                                            <div x-show="editingItemId !== item.id"
                                                 class="text-xs text-gray-600 dark:text-gray-400 truncate"
                                                 :title="item.original_name || item.filename">
                                                <span x-text="item.original_name || item.filename"></span>
                                            </div>
                                            <div x-show="editingItemId === item.id" class="flex gap-1">
                                                <input type="text"
                                                       x-model="editingName"
                                                       @keydown.enter="saveRename(item)"
                                                       @keydown.escape="cancelRename()"
                                                       class="flex-1 text-xs px-2 py-1 border border-indigo-500 rounded dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                <button @click="saveRename(item)" class="text-green-600 hover:text-green-700">
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
                                            <span x-text="item.width + '×' + item.height"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="loading" class="flex justify-center py-8">
                                <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div x-show="!loading && mediaItems.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No images found.
                            </div>
                        </div>

                        <!-- Upload Tab -->
                        <div x-show="tab === 'upload' && !isCropping"
                             x-transition
                             class="w-full h-full flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-12">
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
                        <div x-show="isCropping" class="w-full flex flex-col items-center" style="min-height: 400px;">
                            <div class="w-full max-w-3xl bg-gray-100 dark:bg-gray-900 rounded-lg p-4 relative" style="min-height: 400px;">
                                <div id="cropper-container" style="width: 100%; max-height: 500px;">
                                    <img id="cropper-image" src="" style="max-width: 100%; display: block;">
                                </div>
                                <div x-show="!cropper" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-900 bg-opacity-90 rounded-lg">
                                    <div class="text-center">
                                        <svg class="animate-spin h-12 w-12 text-indigo-500 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Loading image editor...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex space-x-4">
                                <button @click="cancelCrop()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200">
                                    Cancel
                                </button>
                                <button @click="saveCrop()" :disabled="!cropper" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
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
        editingItemId: null,
        editingName: '',

        init() {
            console.log('imageLibraryModal initialized');
            // Watch for isOpen changes
            this.$watch('isOpen', (value) => {
                console.log('isOpen changed to:', value, 'Stack trace:', new Error().stack);
            });
        },

        openModal(options = {}) {
            console.log('openModal called with options:', options);

            try {
                this.options = options;
                this.isOpen = true;
                this.tab = options.tab || 'library';
                this.selectedMedia = null;
                this.isCropping = false;
                this.uploadProgress = 0;

                // Destroy any existing cropper
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                console.log('Modal state set - isOpen:', this.isOpen, 'tab:', this.tab);

                if (this.tab === 'library') {
                    this.fetchMedia();
                }

                // Check if modal is actually visible after a short delay
                setTimeout(() => {
                    console.log('After 500ms - isOpen:', this.isOpen, 'tab:', this.tab, 'isCropping:', this.isCropping);
                }, 500);
            } catch (error) {
                console.error('Error in openModal:', error);
            }
        },

        closeModal() {
            console.log('closeModal called');
            this.isOpen = false;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            // Reset state
            this.isCropping = false;
            this.uploadProgress = 0;
            this.selectedMedia = null;
            // Reset file input
            const fileInput = document.getElementById('image-upload-input');
            if (fileInput) {
                fileInput.value = '';
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
            console.log('startCropping called with URL:', url);

            if (!url) {
                console.error('No URL provided to startCropping');
                alert('Error: No image URL available');
                return;
            }

            // Destroy previous cropper instance if it exists
            if (this.cropper) {
                console.log('Destroying previous cropper instance');
                this.cropper.destroy();
                this.cropper = null;
            }

            this.isCropping = true;

            // Wait for DOM to update
            this.$nextTick(() => {
                const img = document.getElementById('cropper-image');

                if (!img) {
                    console.error('Cropper image element not found');
                    alert('Error: Cropper not initialized');
                    this.isCropping = false;
                    return;
                }

                // Check if Cropper is available
                if (typeof Cropper === 'undefined') {
                    console.error('Cropper.js library not loaded');
                    alert('Error: Cropper library not loaded. Please refresh the page.');
                    this.isCropping = false;
                    return;
                }

                console.log('Setting image source:', url);

                // Set the image source
                img.src = url;

                // Ensure the image is visible in the DOM
                img.style.display = 'block';
                img.style.maxWidth = '100%';

                // Wait for image to actually load before initializing Cropper
                img.onload = () => {
                    console.log('Image loaded successfully, dimensions:', img.naturalWidth, 'x', img.naturalHeight);

                    // Small delay to ensure the image is rendered
                    setTimeout(() => {
                        console.log('Initializing Cropper.js...');

                        try {
                            this.cropper = new Cropper(img, {
                                aspectRatio: this.options.aspectRatio || NaN,
                                viewMode: 1,
                                autoCropArea: 1,
                                background: false,
                                responsive: true,
                                restore: true,
                                checkCrossOrigin: false,
                                ready: () => {
                                    console.log('Cropper is ready!');
                                }
                            });
                        } catch (error) {
                            console.error('Error initializing Cropper:', error);
                            alert('Error initializing image cropper: ' + error.message);
                            this.isCropping = false;
                        }
                    }, 100);
                };

                img.onerror = (error) => {
                    console.error('Error loading image:', error, 'URL:', url);
                    alert('Failed to load image. Please try again.');
                    this.isCropping = false;
                    this.tab = 'upload';
                    // Reset file input
                    const fileInput = document.getElementById('image-upload-input');
                    if (fileInput) {
                        fileInput.value = '';
                    }
                };
            });
        },

        cancelCrop() {
            console.log('Cancelling crop');
            this.isCropping = false;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            // Reset file input
            const fileInput = document.getElementById('image-upload-input');
            if (fileInput) {
                fileInput.value = '';
            }
            // Go back to upload tab
            this.tab = 'upload';
        },

        saveCrop() {
            if (!this.cropper) {
                console.error('No cropper instance available');
                alert('Error: Cropper not initialized');
                return;
            }

            if (!this.selectedMedia || !this.selectedMedia.path) {
                console.error('No media selected');
                alert('Error: No image selected');
                return;
            }

            console.log('Saving crop for:', this.selectedMedia.path);

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
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(result => {
                console.log('Crop result:', result);

                if (result.success) {
                    this.$dispatch('image-selected', {
                        url: result.url,
                        path: result.path,
                        id: result.media_id,
                        target: this.options.target
                    });
                    this.closeModal();
                } else {
                    console.error('Crop failed:', result);
                    alert('Error cropping image: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Crop error:', error);
                alert('Failed to crop image: ' + error.message);
            });
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                event.target.value = '';
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB');
                event.target.value = '';
                return;
            }

            console.log('Starting upload for:', file.name, 'Size:', file.size);

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

                console.log(`Uploading chunk ${currentChunk + 1}/${totalChunks}`);

                fetch('/admin/media/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Chunk response:', data);

                    currentChunk++;
                    this.uploadProgress = Math.round((currentChunk / totalChunks) * 100);

                    if (currentChunk < totalChunks) {
                        uploadNextChunk();
                    } else {
                        // Upload complete
                        console.log('Upload complete, response:', data);
                        this.uploadProgress = 0;

                        if (data.success && data.url) {
                            this.selectedMedia = {
                                path: data.path,
                                id: data.media_id
                            };
                            console.log('Starting cropper with URL:', data.url);
                            this.startCropping(data.url);
                        } else {
                            console.error('Upload completed but missing data:', data);
                            alert('Upload completed but received invalid response');
                            event.target.value = '';
                        }
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    this.uploadProgress = 0;
                    alert('Upload failed: ' + error.message);
                    event.target.value = '';
                });
            };

            uploadNextChunk();
        },

        startRename(item) {
            this.editingItemId = item.id;
            this.editingName = item.original_name || item.filename;
            // Focus the input after DOM update
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

        saveRename(item) {
            if (!this.editingName.trim()) {
                alert('Filename cannot be empty');
                return;
            }

            fetch(`/admin/media/${item.id}/rename`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    name: this.editingName
                })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(result => {
                if (result.success) {
                    // Update the item in the list
                    const index = this.mediaItems.findIndex(i => i.id === item.id);
                    if (index !== -1) {
                        this.mediaItems[index].original_name = result.original_name;
                    }
                    this.cancelRename();
                } else {
                    alert('Failed to rename: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Rename error:', error);
                alert('Failed to rename: ' + error.message);
            });
        },

        confirmDelete(item) {
            if (!confirm(`Are you sure you want to delete "${item.original_name || item.filename}"?`)) {
                return;
            }

            fetch(`/admin/media/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(result => {
                if (result.success) {
                    // Remove from list
                    this.mediaItems = this.mediaItems.filter(i => i.id !== item.id);
                    // Clear selection if deleted item was selected
                    if (this.selectedMedia?.id === item.id) {
                        this.selectedMedia = null;
                    }
                } else {
                    alert('Failed to delete: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('Failed to delete: ' + error.message);
            });
        }
    };
}
</script>
