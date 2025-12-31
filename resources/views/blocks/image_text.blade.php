<div
    class="block-image-text flex flex-col {{ ($data['align'] ?? 'left') === 'right' ? 'md:flex-row-reverse' : 'md:flex-row' }} gap-8 items-center">
    @if($data['image'] ?? false)
        <div class="md:w-1/2">
            @php
                $widths = $mediaConfig['image_processing']['responsive_variants'] ?? [];
                $path = $data['image_path'] ?? $data['image'];
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $basename = pathinfo($path, PATHINFO_FILENAME);
                $dirname = pathinfo($path, PATHINFO_DIRNAME);
                $srcset = [];
                foreach ($widths as $width) {
                    $variantName = "{$basename}_{$width}.webp";
                    $variantUrl = \Illuminate\Support\Facades\Storage::url($dirname . '/' . $variantName);
                    $srcset[] = "{$variantUrl} {$width}w";
                }
            @endphp
            <img src="{{ $data['image'] }}" srcset="{{ implode(', ', $srcset) }}"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 800px" alt="{{ $data['title'] ?? '' }}"
                class="rounded-lg shadow-lg w-full">
        </div>
    @endif
    <div class="{{ ($data['image'] ?? false) ? 'md:w-1/2' : 'w-full' }}">
        @if($data['title'] ?? false)
            <h2 class="text-2xl font-bold mb-4">{{ $data['title'] }}</h2>
        @endif
        <div class="prose dark:prose-invert">
            {!! nl2br(e($data['content'] ?? '')) !!}
        </div>
    </div>
</div>