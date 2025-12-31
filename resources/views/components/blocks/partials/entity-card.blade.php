@php
    $showImage = $block['show_image'] ?? true;
    $showExcerpt = $block['show_excerpt'] ?? true;
    $showDate = $block['show_date'] ?? true;
    $showCategory = $block['show_category'] ?? true;
    
    $title = $entity->getField('title');
    $slug = $entity->getField('slug');
    $excerpt = $entity->getField('excerpt') ?? $entity->getField('summary');
    $category = $entity->getField('category');
    
    // Get featured image
    $featuredImage = $entity->getFilesByField('front_image')->first() 
                  ?? $entity->getFilesByField('featured_image')->first()
                  ?? $entity->getFilesByField('cover_image')->first();
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
    @if($showImage && $featuredImage)
        <div class="aspect-w-16 aspect-h-9 bg-gray-200 dark:bg-gray-700">
            <img src="{{ $featuredImage->getUrl() }}" 
                 alt="{{ $title }}" 
                 class="w-full h-48 object-cover">
        </div>
    @endif
    
    <div class="p-6">
        @if($showCategory && $category)
            <span class="inline-block px-3 py-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900 rounded-full mb-2">
                {{ ucfirst($category) }}
            </span>
        @endif
        
        <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-gray-100">
            <a href="/{{ $entity->type }}/{{ $slug }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                {{ $title }}
            </a>
        </h3>
        
        @if($showDate && $entity->published_at)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                {{ $entity->published_at->format('F j, Y') }}
            </p>
        @endif
        
        @if($showExcerpt && $excerpt)
            <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-3">
                {{ $excerpt }}
            </p>
        @endif
        
        <a href="/{{ $entity->type }}/{{ $slug }}" 
           class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
            Read more
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
