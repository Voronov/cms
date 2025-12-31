@php
    $blockLayout = $block['layout'] ?? 'default';
    $layoutPath = resource_path("layouts/text/{$blockLayout}.blade.php");
@endphp

@if(file_exists($layoutPath))
    @include("layouts.text.{$blockLayout}", ['block' => $block])
        @slot('slot')
            <div class="text-content">
                {!! nl2br(e($block['content'] ?? '')) !!}
            </div>
        @endslot
    @endinclude
@else
    {{-- Fallback if layout doesn't exist --}}
    <div class="text-block prose prose-lg dark:prose-invert max-w-none py-8">
        {!! nl2br(e($block['content'] ?? '')) !!}
    </div>
@endif
