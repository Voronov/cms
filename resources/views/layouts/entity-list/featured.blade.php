{{-- Featured Entity List Layout - Premium wrapper with gradient --}}
<div class="entity-list-featured bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 rounded-xl p-8 shadow-lg my-8">
    @if(!empty($block['title']))
        <h2 class="text-4xl font-bold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
            {{ $block['title'] }}
        </h2>
    @endif
    
    @if(!empty($block['description']))
        <p class="text-xl text-gray-700 dark:text-gray-300 mb-8">{{ $block['description'] }}</p>
    @endif
    
    {{-- Content uses existing partials from components/blocks/partials/ --}}
    {{ $slot }}
</div>
