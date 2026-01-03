# Media Management Documentation

## Overview
The Media Management module handles image uploads with SEO-friendly naming, automatic WebP conversion, manual cropping, and responsive variant generation.

## Uploading Media
**Endpoint:** `POST /api/media/upload`
**Payload:**
- `file`: The image file (max 5MB).
- `crop_data`: (Optional) JSON string with `x`, `y`, `width`, `height`.

## Displaying Images in Blade

Use the `<x-responsive-image>` component for consistent and performant image rendering.

### 1. Default (Responsive)
Generates a full `srcset` with all available variants (`sm`, `md`, `lg`) and the original.
```blade
<x-responsive-image :media="$media" />
```

### 2. Custom Sizes Hint
Provide a `sizes` attribute for better browser selection.
```blade
<x-responsive-image 
    :media="$media" 
    sizes="(max-width: 768px) 100vw, 50vw" 
/>
```

### 3. Specific Variant
Show only a specific size (e.g., thumbnail). No `srcset` will be generated.
Valid sizes: `sm`, `md`, `lg`.
```blade
<x-responsive-image :media="$media" size="sm" />
```

### 4. Original Image Only
Show the full original image without any responsive variants.
```blade
<x-responsive-image :media="$media" size="original" />
```

### 5. Additional Attributes
Any extra attributes will be passed to the `<img>` tag.
```blade
<x-responsive-image 
    :media="$media" 
    class="rounded-lg shadow-lg" 
    id="hero-image"
    :lazy="false" 
/>
```

## Storage Structure
Files are stored in `/storage/app/public/uploads/{YYYY}/{MM}/`.
Variants follow the naming: `{slug}-{variant}.webp`.
