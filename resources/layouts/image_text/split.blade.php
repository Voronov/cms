{{-- Split Image & Text Layout - 50/50 split with no gap --}}
<div class="image-text-split py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 rounded-xl overflow-hidden shadow-lg">
        {{ $slot }}
    </div>
</div>
