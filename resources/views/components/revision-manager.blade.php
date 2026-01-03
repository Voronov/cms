@props(['model', 'modelType'])

@php
    $revisionCount = $model->revisions()->count();
    $hasRevisions = $revisionCount > 0;
@endphp

<div x-data="revisionManager('{{ str_replace('\\', '\\\\', $modelType) }}', {{ $model->id }})" class="inline-block">
    <button @click="toggle" type="button" 
            :disabled="{{ $hasRevisions ? 'false' : 'true' }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Revisions ({{ $revisionCount }})
    </button>

    <template x-teleport="body">
        <div x-show="isOpen" 
             class="fixed inset-0 z-[9999]" 
             x-cloak>
            
            <!-- Backdrop -->
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                 @click="close"></div>

            <!-- Panel Container -->
            <div class="fixed inset-y-0 right-0 w-full max-w-2xl flex flex-col bg-white dark:bg-gray-800 shadow-2xl h-full"
                 x-show="isOpen"
                 x-transition:enter="transform transition ease-in-out duration-500"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex items-center justify-between flex-none">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Revision History</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">View and restore previous versions of this content.</p>
                    </div>
                    <button @click="close" class="rounded-full p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto min-h-0 bg-white dark:bg-gray-800 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                    <div class="p-6">
                        <template x-if="isLoading">
                            <div class="flex flex-col items-center justify-center py-20">
                                <svg class="animate-spin h-10 w-10 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-500">Loading history...</span>
                            </div>
                        </template>

                        <div class="space-y-6">
                            <template x-for="revision in revisions" :key="revision.id">
                                <div class="bg-white dark:bg-gray-700/30 rounded-2xl border border-gray-200 dark:border-gray-600 shadow-sm hover:shadow-md overflow-hidden">
                                    <div class="flex flex-col md:flex-row">
                                        <div class="md:w-64 p-5 bg-gray-50 dark:bg-gray-800/50 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-600 flex flex-col justify-between">
                                            <div class="space-y-3">
                                                <div class="flex items-center space-x-3">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm flex-none">
                                                        <span x-text="(revision.user ? revision.user.name[0] : 'S').toUpperCase()"></span>
                                                    </div>
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="text-sm font-bold text-gray-900 dark:text-white truncate" x-text="revision.user ? revision.user.name : 'System'"></span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="timeAgo(revision.created_at)"></span>
                                                    </div>
                                                </div>
                                                <div class="pt-2">
                                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Date</span>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatDate(revision.created_at)"></p>
                                                </div>
                                            </div>
                                            <div class="mt-6 md:mt-4">
                                                <form :action="rollbackUrl(revision.id)" method="POST" onsubmit="return confirm('Restore this version?')">
                                                    @csrf
                                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                        Restore
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="flex-1 p-5">
                                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Description</span>
                                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 min-h-[80px] border border-gray-100 dark:border-gray-800" 
                                                 x-text="revision.description || 'No description provided.'"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-6 bg-gray-50 dark:bg-gray-900 flex-none" x-show="!isLoading && lastPage > 1">
                    <div class="flex items-center justify-between">
                        <button @click="prevPage" :disabled="currentPage === 1" 
                                class="inline-flex items-center px-6 py-2 border border-gray-300 text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Previous
                        </button>
                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="currentPage + ' / ' + lastPage"></span>
                        <button @click="nextPage" :disabled="currentPage === lastPage" 
                                class="inline-flex items-center px-6 py-2 border border-gray-300 text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    if (Alpine.data('revisionManager')) return;
    Alpine.data('revisionManager', (modelType, modelId) => ({
        isOpen: false,
        revisions: [],
        isLoading: false,
        currentPage: 1,
        lastPage: 1,
        async toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                document.body.style.overflow = 'hidden';
                await this.fetchRevisions();
            } else {
                document.body.style.overflow = '';
            }
        },
        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },
        async fetchRevisions(page = 1) {
            this.isLoading = true;
            try {
                const response = await fetch(`{{ route('admin.revisions.index') }}?model_type=${encodeURIComponent(modelType)}&model_id=${modelId}&page=${page}`);
                const data = await response.json();
                this.revisions = data.data;
                this.currentPage = data.current_page;
                this.lastPage = data.last_page;
            } catch (error) {
                console.error('Failed to fetch revisions:', error);
            } finally {
                this.isLoading = false;
            }
        },
        async nextPage() {
            if (this.currentPage < this.lastPage) await this.fetchRevisions(this.currentPage + 1);
        },
        async prevPage() {
            if (this.currentPage > 1) await this.fetchRevisions(this.currentPage - 1);
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', day: 'numeric', year: 'numeric', 
                hour: '2-digit', minute: '2-digit' 
            });
        },
        timeAgo(dateString) {
            const date = new Date(dateString);
            const seconds = Math.floor((new Date() - date) / 1000);
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + " years ago";
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + " months ago";
            interval = seconds / 864000;
            if (interval > 1) return Math.floor(interval) + " days ago";
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + " hours ago";
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + " minutes ago";
            return Math.floor(seconds) + " seconds ago";
        },
        rollbackUrl(revisionId) {
            return `{{ route('admin.revisions.rollback', ':id') }}`.replace(':id', revisionId);
        }
    }));
});
</script>
@endonce
