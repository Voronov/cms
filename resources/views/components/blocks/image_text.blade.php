@php
    $blockLayout = $block['layout'] ?? 'default';
    $layoutPath = resource_path("layouts/image_text/{$blockLayout}.blade.php");
    $align = $block['align'] ?? 'left';
@endphp

@if(file_exists($layoutPath))
    @include("layouts.image_text.{$blockLayout}", ['block' => $block])
        @slot('slot')
            @if($align === 'left' || $blockLayout === 'image-left' || $blockLayout === 'default')
                {{-- Image on left --}}
                <div class="image-container">
                    @if(!empty($block['image']))
                        <img src="{{ $block['image'] }}" 
                             alt="{{ $block['image_alt'] ?? $block['title'] ?? '' }}" 
                             class="w-full h-auto rounded-lg shadow-lg"
                             {{ ($isFirst ?? false) ? '' : 'loading=lazy' }}>
                    @endif
                </div>
                <div class="text-container prose dark:prose-invert">
                    @if(!empty($block['title']))
                        <h2 class="text-3xl font-bold mb-4">{{ $block['title'] }}</h2>
                    @endif
                    <div>{!! nl2br(e($block['content'] ?? '')) !!}</div>
                </div>
            @else
                {{-- Image on right --}}
                <div class="text-container prose dark:prose-invert">
                    @if(!empty($block['title']))
                        <h2 class="text-3xl font-bold mb-4">{{ $block['title'] }}</h2>
                    @endif
                    <div>{!! nl2br(e($block['content'] ?? '')) !!}</div>
                </div>
                <div class="image-container">
                    @if(!empty($block['image']))
                        <img src="{{ $block['image'] }}" 
                             alt="{{ $block['image_alt'] ?? $block['title'] ?? '' }}" 
                             class="w-full h-auto rounded-lg shadow-lg"
                             {{ ($isFirst ?? false) ? '' : 'loading=lazy' }}>
                    @endif
                </div>
            @endif
        @endslot
    @endinclude
@else
    {{-- Fallback if layout doesn't exist --}}
    <div class="image-text-block py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            @if($align === 'left')
                <div>
                    @if(!empty($block['image']))
                        <img src="{{ $block['image'] }}" 
                             alt="{{ $block['image_alt'] ?? $block['title'] ?? '' }}" 
                             class="w-full h-auto rounded-lg shadow-lg"
                             {{ ($isFirst ?? false) ? '' : 'loading=lazy' }}>
                    @endif
                </div>
                <div class="prose dark:prose-invert">
                    @if(!empty($block['title']))
                        <h2 class="text-3xl font-bold mb-4">{{ $block['title'] }}</h2>
                    @endif
                    <div>{!! nl2br(e($block['content'] ?? '')) !!}</div>
                </div>
            @else
                <div class="prose dark:prose-invert">
                    @if(!empty($block['title']))
                        <h2 class="text-3xl font-bold mb-4">{{ $block['title'] }}</h2>
                    @endif
                    <div>{!! nl2br(e($block['content'] ?? '')) !!}</div>
                </div>
                <div>
                    @if(!empty($block['image']))
                        <img src="{{ $block['image'] }}" 
                             alt="{{ $block['image_alt'] ?? $block['title'] ?? '' }}" 
                             class="w-full h-auto rounded-lg shadow-lg"
                             {{ ($isFirst ?? false) ? '' : 'loading=lazy' }}>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
