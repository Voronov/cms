{{-- Grid Entity List Layout - Clean grid with cards --}}
<div class="entity-list-grid py-8">
    @if(!empty($block['title']))
        <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-gray-100">{{ $block['title'] }}</h2>
    @endif
    
    @if(!empty($block['description']))
        <p class="text-gray-600 dark:text-gray-400 mb-8">{{ $block['description'] }}</p>
    @endif
    
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6">
        {{ $slot }}
    </div>
</div>
