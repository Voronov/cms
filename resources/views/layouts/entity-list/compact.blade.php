{{-- Compact Entity List Layout - Minimal wrapper --}}
<div class="entity-list-compact py-4">
    @if(!empty($block['title']))
        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ $block['title'] }}</h3>
    @endif
    
    @if(!empty($block['description']))
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $block['description'] }}</p>
    @endif
    
    {{-- Content uses existing partials: entity-card.blade.php, entity-list-item.blade.php --}}
    {{ $slot }}
</div>
