{{-- Image Right Layout --}}
<div class="image-text-right py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <div class="order-2 md:order-1">
            {{-- Text content goes here --}}
        </div>
        <div class="order-1 md:order-2">
            {{-- Image content goes here --}}
        </div>
    </div>
    {{ $slot }}
</div>
