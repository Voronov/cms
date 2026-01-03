@php
    use App\Models\Entity;
    use App\Services\EntityDefinitionService;
    
    $block = $data;
    $entityType = $block['entity_type'] ?? 'news';
    $entityDef = app(EntityDefinitionService::class);
    
    // Use entity-specific default pagination
    $limit = $entityDef->getDefaultPagination($entityType);
    
    $orderBy = $block['order_by'] ?? 'created_at';
    $category = $block['category'] ?? null;
    
    // Get layout and columns from entity definition
    $layout = $entityDef->getListLayout($entityType);
    $columns = $entityDef->getListColumns($entityType);
    $template = $entityDef->getTemplate($entityType, $layout);
    
    // Build query
    $query = Entity::ofType($entityType)->active();
    
    // Filter by category if specified
    if ($category) {
        $query->whereJsonContains('content->category', $category);
    }
    
    // Order results
    if ($orderBy === 'published_at') {
        $query->orderBy('published_at', 'desc');
    } elseif ($orderBy === 'title') {
        $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(content, '$.title'))");
    } else {
        $query->orderBy('created_at', 'desc');
    }
    
    // Paginate
    $entities = $query->paginate($limit);
    
    // Grid column classes
    $gridClasses = [
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    ];
@endphp

@php
    $blockLayout = $block['layout'] ?? 'default';
    $layoutView = "layouts.entity-list.{$blockLayout}";
@endphp

@if(view()->exists($layoutView))
    @component($layoutView, ['block' => $block])
    
    @if($entities->isEmpty())
        <p class="text-gray-500 dark:text-gray-400">No items found.</p>
    @else
        @if($layout === 'grid' || $layout === 'shop')
            <div class="grid {{ $gridClasses[$columns] ?? 'grid-cols-1 md:grid-cols-3' }} gap-6">
                @foreach($entities as $entity)
                    @if($template)
                        @include($template, ['entity' => $entity, 'block' => $block])
                    @else
                        @include('components.blocks.partials.entity-card', ['entity' => $entity, 'block' => $block])
                    @endif
                @endforeach
            </div>
        @elseif($layout === 'list' || $layout === 'catalog')
            <div class="space-y-6">
                @foreach($entities as $entity)
                    @if($template)
                        @include($template, ['entity' => $entity, 'block' => $block])
                    @else
                        @include('components.blocks.partials.entity-list-item', ['entity' => $entity, 'block' => $block])
                    @endif
                @endforeach
            </div>
        @else
            <div class="columns-1 md:columns-2 lg:columns-3 gap-6">
                @foreach($entities as $entity)
                    @if($template)
                        @include($template, ['entity' => $entity, 'block' => $block])
                    @else
                        @include('components.blocks.partials.entity-card', ['entity' => $entity, 'block' => $block])
                    @endif
                @endforeach
            </div>
        @endif
        
        @if($entities->hasPages())
            <div class="mt-8">
                {{ $entities->links() }}
            </div>
        @endif
    @endif
    @endcomponent
@else
    {{-- Fallback if layout doesn't exist --}}
    <div class="entity-list-block py-8">
        @if(!empty($block['title']))
            <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">{{ $block['title'] }}</h2>
        @endif
        
        @if(!empty($block['description']))
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">{{ $block['description'] }}</p>
        @endif
        
        @if($entities->isEmpty())
            <p class="text-gray-500 dark:text-gray-400">No items found.</p>
        @else
            @if($layout === 'grid' || $layout === 'shop')
                <div class="grid {{ $gridClasses[$columns] ?? 'grid-cols-1 md:grid-cols-3' }} gap-6">
                    @foreach($entities as $entity)
                        @if($template)
                            @include($template, ['entity' => $entity, 'block' => $block])
                        @else
                            @include('components.blocks.partials.entity-card', ['entity' => $entity, 'block' => $block])
                        @endif
                    @endforeach
                </div>
            @elseif($layout === 'list' || $layout === 'catalog')
                <div class="space-y-6">
                    @foreach($entities as $entity)
                        @if($template)
                            @include($template, ['entity' => $entity, 'block' => $block])
                        @else
                            @include('components.blocks.partials.entity-list-item', ['entity' => $entity, 'block' => $block])
                        @endif
                    @endforeach
                </div>
            @else
                <div class="columns-1 md:columns-2 lg:columns-3 gap-6">
                    @foreach($entities as $entity)
                        @if($template)
                            @include($template, ['entity' => $entity, 'block' => $block])
                        @else
                            @include('components.blocks.partials.entity-card', ['entity' => $entity, 'block' => $block])
                        @endif
                    @endforeach
                </div>
            @endif
            
            @if($entities->hasPages())
                <div class="mt-8">
                    {{ $entities->links() }}
                </div>
            @endif
        @endif
    </div>
@endif
