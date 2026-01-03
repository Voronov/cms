@props([
    'media', 
    'size' => null, // null (all/responsive), 'sm', 'md', 'lg', or 'original'
    'sizes' => '(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 33vw', 
    'lazy' => true
])

@php
    if (!$media instanceof \App\Models\Media) {
        $media = \App\Models\Media::find($media);
    }

    if (!$media) return;

    $pathInfo = pathinfo($media->filepath);
    $dirname = $pathInfo['dirname'];
    $basename = $pathInfo['filename'];
    $extension = $pathInfo['extension'];

    $src = asset('storage/' . $media->filepath);
    $srcset = [];

    // Map variant suffixes to widths as defined in tech spec
    $variantWidths = [
        'sm' => 300,
        'md' => 800,
        'lg' => 1200
    ];

    if ($size === 'original') {
        // Only show original, no srcset
        $srcset = [];
    } elseif ($size && isset($variantWidths[$size])) {
        // Show specific size only
        $variantPath = "{$dirname}/{$basename}-{$size}.{$extension}";
        $src = asset('storage/' . $variantPath);
        $srcset = [];
    } else {
        // Default responsive behavior (all available variants)
        if ($media->variants) {
            foreach ($media->variants as $suffix) {
                if (isset($variantWidths[$suffix])) {
                    $variantPath = "{$dirname}/{$basename}-{$suffix}.{$extension}";
                    $srcset[] = asset('storage/' . $variantPath) . " {$variantWidths[$suffix]}w";
                    
                    // Use medium as default src if available for better initial load
                    if ($suffix === 'md' && !$size) {
                        $src = asset('storage/' . $variantPath);
                    }
                }
            }
        }
    }
@endphp

<img 
    src="{{ $src }}" 
    @if(!empty($srcset))
    srcset="{{ implode(', ', $srcset) }}"
    @endif
    @if(!empty($srcset))
    sizes="{{ $sizes }}"
    @endif
    alt="{{ $media->alt_text }}"
    {{ $lazy ? 'loading=lazy' : '' }}
    {{ $attributes }}
>
