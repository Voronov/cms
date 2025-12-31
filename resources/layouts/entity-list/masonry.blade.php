{{-- Masonry Entity List Layout - Pinterest-style layout --}}
<div class="entity-list-masonry py-8">
    @if(!empty($block['title']))
        <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-gray-100">{{ $block['title'] }}</h2>
    @endif
    
    @if(!empty($block['description']))
        <p class="text-gray-600 dark:text-gray-400 mb-8">{{ $block['description'] }}</p>
    @endif
    
    {{ $slot }}
</div>
