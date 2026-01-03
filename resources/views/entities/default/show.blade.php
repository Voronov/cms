@extends('layouts.frontend')

@section('title', $entity->getField('meta_title') ?? $entity->getField('title'))

@section('meta')
    <meta name="description" content="{{ $entity->getField('meta_description') ?? $entity->getField('excerpt') }}">
    <meta property="og:title" content="{{ $entity->getField('og_title') ?? $entity->getField('title') }}">
    <meta property="og:description" content="{{ $entity->getField('og_description') ?? $entity->getField('excerpt') }}">
    <meta property="og:type" content="article">
    @php
        $featuredImage = $entity->getFilesByField('front_image')->first() 
                      ?? $entity->getFilesByField('featured_image')->first()
                      ?? $entity->getFilesByField('cover_image')->first();
    @endphp
    @if($featuredImage)
        <meta property="og:image" content="{{ $featuredImage->getUrl() }}">
    @endif
@endsection

@section('content')
<div class="bg-white dark:bg-gray-900">
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @php
            $category = $entity->getField('category');
        @endphp
        @if($category)
            <nav class="mb-8">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                    {{ ucfirst($category) }}
                </span>
            </nav>
        @endif

        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl mb-4">
                {{ $entity->getField('title') }}
            </h1>
            
            <div class="flex items-center space-x-4 text-gray-500 dark:text-gray-400 text-sm">
                @if($entity->published_at)
                    <time datetime="{{ $entity->published_at->toW3cString() }}">
                        {{ $entity->published_at->format('F j, Y') }}
                    </time>
                @endif
                
                @if($author = $entity->getField('author'))
                    <span class="flex items-center">
                        <span class="mx-2">•</span>
                        <span>By {{ $author }}</span>
                    </span>
                @endif
            </div>
        </header>

        @if($featuredImage)
            <div class="mb-12 rounded-2xl overflow-hidden shadow-2xl">
                <img src="{{ $featuredImage->getUrl() }}" alt="{{ $entity->getField('title') }}" class="w-full h-auto object-cover">
            </div>
        @endif

        <div class="space-y-12">
            @php
                $blocks = $entity->getField('blocks', []);
                // Support legacy content field if blocks are empty
                if (empty($blocks) && $content = $entity->getField('content')) {
                    $blocks = [['type' => 'text', 'data' => ['content' => $content]]];
                }
            @endphp

            @if(!empty($blocks))
                @foreach($blocks as $block)
                    @includeIf('blocks.' . $block['type'], ['data' => $block['data']])
                @endforeach
            @endif
        </div>

        <footer class="mt-16 pt-8 border-t border-gray-200 dark:border-gray-800">
            <a href="javascript:history.back()" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to list
            </a>
        </footer>
    </article>
</div>
@endsection
