<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('partials.admin.head')

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen overflow-hidden">
        @include('partials.admin.sidebar')

        <div class="flex flex-col flex-1 w-0 overflow-hidden">
            @include('partials.admin.header')

            <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none" tabindex="0">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
    @include('partials.admin.image-library-modal')
    @stack('scripts')
</body>

</html>