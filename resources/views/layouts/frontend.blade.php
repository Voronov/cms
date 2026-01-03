<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    @if(isset($entity))
        @php
            $seoTitle = $entity->seo['title'] ?? $entity->content['title'] ?? $entity->content['name'] ?? config('app.name');
            $seoDescription = $entity->seo['description'] ?? $entity->content['summary'] ?? $entity->content['excerpt'] ?? '';
            $seoImage = $entity->seo['social_image'] ?? $entity->seo['image'] ?? $entity->content['cover_image'] ?? $entity->content['featured_image'] ?? null;
            if ($seoImage && !filter_var($seoImage, FILTER_VALIDATE_URL)) {
                $seoImage = url($seoImage);
            }
            $isNoIndex = $entity->seo['is_noindex'] ?? false;
            $canonicalUrl = $entity->seo['canonical_url'] ?? null;
        @endphp

        @if($isNoIndex)
            <meta name="robots" content="noindex">
        @endif

        @if($canonicalUrl)
            <link rel="canonical" href="{{ $canonicalUrl }}" />
        @else
            <link rel="canonical" href="{{ url()->current() }}" />
        @endif

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        @if($seoImage)
            <meta property="og:image" content="{{ $seoImage }}">
        @endif

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="{{ $seoTitle }}">
        <meta property="twitter:description" content="{{ $seoDescription }}">
        @if($seoImage)
            <meta property="twitter:image" content="{{ $seoImage }}">
        @endif
    @endif

    @stack('schema')

    <!-- Styles / Scripts -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>

<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-900 dark:text-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="/" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ config('app.name', 'CMS') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'CMS') }}. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>

</html>