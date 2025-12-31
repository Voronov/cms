@php
    use App\Models\Entity;
    
    $blockLayout = $block['layout'] ?? 'default';
    $layoutPath = resource_path("layouts/entity-single/{$blockLayout}.blade.php");
    
    $entityType = $block['entity_type'] ?? 'news';
    $entityId = $block['entity_id'] ?? null;
    $entitySlug = $block['entity_slug'] ?? null;
    
    $entity = null;
    if ($entityId) {
        $entity = Entity::ofType($entityType)->find($entityId);
    } elseif ($entitySlug) {
        $entity = Entity::ofType($entityType)->whereJsonContains('content->slug', $entitySlug)->first();
    }
    
    if (!$entity) {
        return;
    }
@endphp

@if(file_exists($layoutPath))
    @include("layouts.entity-single.{$blockLayout}", ['block' => $block])
        @slot('slot')
            <article>
                @if(($block['show_title'] ?? true))
                    <h1 class="text-4xl font-bold mb-4">{{ $entity->getField('title') }}</h1>
                @endif
                @if(($block['show_image'] ?? true) && $entity->getFilesByField('front_image')->first())
                    <img src="{{ $entity->getFilesByField('front_image')->first()->getUrl() }}" alt="{{ $entity->getField('title') }}" class="w-full rounded-lg mb-6">
                @endif
                @if(($block['show_content'] ?? true))
                    <div class="prose max-w-none">{!! $entity->getField('content') !!}</div>
                @endif
            </article>
        @endslot
    @endinclude
@else
    <div class="entity-single-block py-8">
        <article>
            @if(($block['show_title'] ?? true))
                <h1 class="text-4xl font-bold mb-4">{{ $entity->getField('title') }}</h1>
            @endif
            @if(($block['show_image'] ?? true) && $entity->getFilesByField('front_image')->first())
                <img src="{{ $entity->getFilesByField('front_image')->first()->getUrl() }}" alt="{{ $entity->getField('title') }}" class="w-full rounded-lg mb-6">
            @endif
            @if(($block['show_content'] ?? true))
                <div class="prose max-w-none">{!! $entity->getField('content') !!}</div>
            @endif
        </article>
    </div>
@endif
