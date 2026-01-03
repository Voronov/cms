{{-- Default Entity List Layout - Simple wrapper --}}
<div class="entity-list-default py-8">
    @if(!empty($block['title']))
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">{{ $block['title'] }}</h2>
    @endif
    
    @if(!empty($block['description']))
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">{{ $block['description'] }}</p>
    @endif
    
    {{-- Content (entities) rendered by partials --}}
    {{ $slot }}
</div>
