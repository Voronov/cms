<header class="relative z-10 flex-shrink-0 flex h-16 bg-white dark:bg-gray-800 shadow" x-data="{ 
    open: false,
    darkMode: {{ auth()->user()->dark_theme ? 'true' : 'false' }},
    toggleTheme() {
        this.darkMode = !this.darkMode;
        document.documentElement.classList.toggle('dark', this.darkMode);
        
        fetch('{{ route('admin.profile.toggle-theme') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
    }
}">
    <div class="flex-1 flex justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex-1 flex">
        </div>
        <div class="ml-4 flex items-center space-x-4 md:ml-6">
            <!-- Cache Clear Button -->
            <form method="POST" action="{{ route('admin.cache.clear') }}" class="inline-block">
                @csrf
                <input type="hidden" name="type" value="all">
                <button type="submit" 
                        onclick="return confirm('Clear all caches?');"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
                        title="Clear All Caches">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span class="ml-2 hidden md:inline">Clear Cache</span>
                </button>
            </form>

            <!-- User Dropdown -->
            <div class="relative ml-3">
                <div>
                    <button @click="open = !open" type="button" class="flex max-w-xs items-center rounded-full bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                        <span class="sr-only">Open user menu</span>
                        <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                            {{ substr(auth()->user()->name, 0, 1) }}{{ auth()->user()->surname ? substr(auth()->user()->surname, 0, 1) : '' }}
                        </div>
                        <span class="ml-3 hidden md:block text-gray-700 dark:text-gray-200 text-sm font-medium">
                            {{ auth()->user()->name }} {{ auth()->user()->surname }}
                        </span>
                        <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-700 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" x-cloak>
                    <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">
                        Settings
                    </div>
                    
                    <button @click.prevent.stop="toggleTheme" type="button" class="flex w-full items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem">
                        <div class="mr-3">
                            <template x-if="darkMode">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 18v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 16.95l.707.707M7.05 7.05l.707.707M12 8a4 4 0 110 8 4 4 0 010-8z" />
                                </svg>
                            </template>
                            <template x-if="!darkMode">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </template>
                        </div>
                        <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                    </button>

                    <div class="border-t border-gray-100 dark:border-gray-600 my-1"></div>

                    <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem" tabindex="-1">Your Profile</a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem" tabindex="-1">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
