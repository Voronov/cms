<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaController extends Controller
{
    protected MediaProcessor $processor;
    protected ImageManager $manager;

    public function __construct(MediaProcessor $processor)
    {
        $this->processor = $processor;
        $this->manager = new ImageManager(new Driver());
    }

    public function index(Request $request)
    {
        $media = Media::latest()->paginate(24);
        
        if ($request->wantsJson()) {
            return response()->json($media);
        }

        return view('admin.media.index', compact('media'));
    }

    public function show($id)
    {
        $media = Media::findOrFail($id);
        $usage = $media->getUsage();
        return response()->json([
            'media' => $media,
            'usage' => $usage
        ]);
    }

    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        
        // Delete the main file
        if (Storage::disk('public')->exists($media->filepath)) {
            Storage::disk('public')->delete($media->filepath);
        }

        // Delete the original file
        if ($media->original_filepath && Storage::disk('public')->exists($media->original_filepath)) {
            Storage::disk('public')->delete($media->original_filepath);
        }

        // Delete variants
        if ($media->variants) {
            $pathInfo = pathinfo($media->filepath);
            $basename = $pathInfo['filename'];
            $dirname = $pathInfo['dirname'];

            foreach ($media->variants as $suffix) {
                $variantPath = "{$dirname}/{$basename}-{$suffix}.webp";
                if (Storage::disk('public')->exists($variantPath)) {
                    Storage::disk('public')->delete($variantPath);
                }
            }
        }

        $media->delete();

        return response()->json(['success' => true]);
    }

    public function rename(Request $request, Media $media)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $newName = $request->input('name');
        $newSlug = Str::slug(pathinfo($newName, PATHINFO_FILENAME));
        $uploadDir = pathinfo($media->filepath, PATHINFO_DIRNAME);

        // 1. Rename Main File
        $oldFilepath = $media->filepath;
        $extension = pathinfo($oldFilepath, PATHINFO_EXTENSION);
        $newFilename = "{$newSlug}.{$extension}";
        $newFilepath = "{$uploadDir}/{$newFilename}";

        if ($oldFilepath !== $newFilepath) {
            $counter = 1;
            while (Storage::disk('public')->exists($newFilepath)) {
                $newFilepath = "{$uploadDir}/{$newSlug}-{$counter}.{$extension}";
                $counter++;
            }
            Storage::disk('public')->move($oldFilepath, $newFilepath);
        }

        // 2. Rename Original File
        $oldOriginalFilepath = $media->original_filepath;
        $newOriginalFilepath = $oldOriginalFilepath;
        if ($oldOriginalFilepath) {
            $origExtension = pathinfo($oldOriginalFilepath, PATHINFO_EXTENSION);
            $newOrigFilename = "{$newSlug}-original.{$origExtension}";
            $newOriginalFilepath = "{$uploadDir}/{$newOrigFilename}";

            if ($oldOriginalFilepath !== $newOriginalFilepath) {
                $counter = 1;
                while (Storage::disk('public')->exists($newOriginalFilepath)) {
                    $newOriginalFilepath = "{$uploadDir}/{$newSlug}-original-{$counter}.{$origExtension}";
                    $counter++;
                }
                Storage::disk('public')->move($oldOriginalFilepath, $newOriginalFilepath);
            }
        }

        // 3. Rename Variants
        if ($media->variants) {
            $oldBasename = pathinfo($oldFilepath, PATHINFO_FILENAME);
            $newBasename = pathinfo($newFilepath, PATHINFO_FILENAME);

            foreach ($media->variants as $suffix) {
                $oldVariantPath = "{$uploadDir}/{$oldBasename}-{$suffix}.webp";
                $newVariantPath = "{$uploadDir}/{$newBasename}-{$suffix}.webp";

                if (Storage::disk('public')->exists($oldVariantPath)) {
                    Storage::disk('public')->move($oldVariantPath, $newVariantPath);
                }
            }
        }

        $media->update([
            'original_name' => $newName,
            'filepath' => $newFilepath,
            'original_filepath' => $newOriginalFilepath,
            'alt_text' => $newSlug, // Update alt text to match new slug
        ]);

        return response()->json([
            'success' => true,
            'original_name' => $media->original_name,
            'filepath' => $media->filepath,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120', // 5MB limit from spec
            'crop_data' => 'nullable|string', // JSON string as per spec
        ]);

        $file = $request->file('file');
        $cropData = $request->filled('crop_data') ? json_decode($request->input('crop_data'), true) : null;
        $originalName = $file->getClientOriginalName();

        // SEO Strategy: Naming Convention
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $slug = Str::slug($basename);
        $datePath = date('Y/m');
        $uploadDir = "uploads/{$datePath}";

        if (!Storage::disk('public')->exists($uploadDir)) {
            Storage::disk('public')->makeDirectory($uploadDir);
        }

        $finalName = "{$slug}.webp";
        $counter = 1;
        while (Storage::disk('public')->exists("{$uploadDir}/{$finalName}")) {
            $finalName = "{$slug}-{$counter}.webp";
            $counter++;
        }

        $finalPath = "{$uploadDir}/{$finalName}";

        // Save original file
        $originalExt = $file->getClientOriginalExtension();
        $originalFileName = "{$slug}-original.{$originalExt}";
        $counter = 1;
        while (Storage::disk('public')->exists("{$uploadDir}/{$originalFileName}")) {
            $originalFileName = "{$slug}-original-{$counter}.{$originalExt}";
            $counter++;
        }
        $originalPath = "{$uploadDir}/{$originalFileName}";
        Storage::disk('public')->put($originalPath, File::get($file->getRealPath()));

        // Temporary storage for processing
        $tempPath = $file->store('temp', 'local');
        $fullTempPath = storage_path("app/{$tempPath}");

        // Process (Crop, WebP, Variants)
        $result = $this->processor->process($fullTempPath, $cropData);

        // Move processed master to final public destination
        Storage::disk('public')->put($finalPath, File::get($result['path']));

        // Clean up temp
        File::delete($fullTempPath);
        if (File::exists($result['path'])) {
            File::delete($result['path']);
        }
        Storage::disk('local')->delete($tempPath);

        // Create Media record
        $media = Media::create([
            'filepath' => $finalPath,
            'original_filepath' => $originalPath,
            'original_name' => $originalName,
            'alt_text' => $slug,
            'width' => $result['width'],
            'height' => $result['height'],
            'filesize' => $result['filesize'],
            'variants' => $result['variants'],
        ]);

        return response()->json([
            'id' => $media->id,
            'base_path' => $media->filepath,
            'variants' => $media->variants,
            'alt' => $media->alt_text,
        ], 201);
    }

    public function crop(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'x' => 'required|integer',
            'y' => 'required|integer',
            'width' => 'required|integer',
            'height' => 'required|integer',
        ]);

        $relativePath = $request->input('path');
        $fullPath = storage_path('app/public/' . $relativePath);

        if (!File::exists($fullPath)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        // Find and delete the original media record
        $originalMedia = Media::where('path', $relativePath)->first();
        if ($originalMedia) {
            // Delete responsive variants of the original
            $pathInfo = pathinfo($originalMedia->path);
            $basename = $pathInfo['filename'];
            $dirname = $pathInfo['dirname'];

            $variants = ['640', '768', '1024', '1280', '1920'];
            foreach ($variants as $size) {
                $variantPath = "{$dirname}/{$basename}_{$size}.webp";
                if (Storage::disk('public')->exists($variantPath)) {
                    Storage::disk('public')->delete($variantPath);
                }
            }

            $originalMedia->delete();
        }

        $processedPath = $this->processor->crop(
            $fullPath,
            $request->input('x'),
            $request->input('y'),
            $request->input('width'),
            $request->input('height')
        );

        $image = $this->manager->read($processedPath);

        // Get original name from the old record or use a default
        $originalName = $originalMedia ? $originalMedia->original_name : basename($relativePath);
        
        // Use new naming and folder logic for the cropped version
        $datePath = date('Y/m');
        $uploadDir = "media/{$datePath}";
        
        if (!Storage::disk('public')->exists($uploadDir)) {
            Storage::disk('public')->makeDirectory($uploadDir);
        }

        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $slug = Str::slug($basename) . '-cropped';
        $finalName = "{$slug}.webp";
        
        $counter = 1;
        while (Storage::disk('public')->exists("{$uploadDir}/{$finalName}")) {
            $finalName = "{$slug}-{$counter}.webp";
            $counter++;
        }

        $finalPath = "{$uploadDir}/{$finalName}";
        
        // Move processed file to final destination
        $finalContent = File::get($processedPath);
        Storage::disk('public')->put($finalPath, $finalContent);
        
        // Clean up temporary processed file if it's different from fullPath
        if ($processedPath !== $fullPath && File::exists($processedPath)) {
            File::delete($processedPath);
        }
        
        // If we replaced the original file (unlikely given new naming but good to clean up)
        if (Storage::disk('public')->exists($relativePath) && $relativePath !== $finalPath) {
            Storage::disk('public')->delete($relativePath);
        }

        $media = Media::create([
            'filename' => $finalName,
            'original_name' => $originalName,
            'path' => $finalPath,
            'mime_type' => 'image/webp',
            'size' => Storage::disk('public')->size($finalPath),
            'width' => $image->width(),
            'height' => $image->height(),
        ]);

        return response()->json([
            'success' => true,
            'url' => Storage::url($media->path),
            'path' => $media->path,
            'media_id' => $media->id
        ]);
    }

    public function assembleChunks(string $tempPath, string $originalName, int $total)
    {
        $datePath = date('Y/m');
        $uploadDir = "media/{$datePath}";
        
        // Ensure date-based directory exists on public disk
        if (!Storage::disk('public')->exists($uploadDir)) {
            Storage::disk('public')->makeDirectory($uploadDir);
        }

        // Process filename: slugify, convert to webp extension
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $slug = Str::slug($basename);
        $finalName = "{$slug}.webp";
        
        // Check for duplicates and increment
        $counter = 1;
        while (Storage::disk('public')->exists("{$uploadDir}/{$finalName}")) {
            $finalName = "{$slug}-{$counter}.webp";
            $counter++;
        }

        $finalPath = "{$uploadDir}/{$finalName}";

        // Assemble chunks using Storage facade
        $assembledContent = '';
        for ($i = 0; $i < $total; $i++) {
            $chunkPath = "{$tempPath}/chunk_{$i}";

            if (!Storage::disk('local')->exists($chunkPath)) {
                \Log::error("Chunk file not found", [
                    'chunk_path' => $chunkPath,
                    'full_path' => Storage::disk('local')->path($chunkPath),
                    'temp_path' => $tempPath,
                    'index' => $i
                ]);
                throw new \Exception("Chunk file not found: {$chunkPath}");
            }

            $assembledContent .= Storage::disk('local')->get($chunkPath);
        }

        // Temporary storage for processing
        $tempFileName = Str::random(40);
        Storage::disk('local')->put("temp/{$tempFileName}", $assembledContent);
        $fullTempPath = Storage::disk('local')->path("temp/{$tempFileName}");

        // Process (WebP conversion, variants)
        // We pass the desired final path to the processor if needed, 
        // but here we let it process and then we move it to the final destination.
        $processedPath = $this->processor->process($fullTempPath);

        // Move to final public destination
        $finalContent = File::get($processedPath);
        Storage::disk('public')->put($finalPath, $finalContent);

        // Clean up
        Storage::disk('local')->deleteDirectory($tempPath);
        Storage::disk('local')->delete("temp/{$tempFileName}");
        if (File::exists($processedPath)) {
            File::delete($processedPath);
        }

        $image = $this->manager->read(Storage::disk('public')->path($finalPath));

        $media = Media::create([
            'filename' => $finalName,
            'original_name' => $originalName,
            'path' => $finalPath,
            'mime_type' => 'image/webp',
            'size' => Storage::disk('public')->size($finalPath),
            'width' => $image->width(),
            'height' => $image->height(),
        ]);

        return response()->json([
            'success' => true,
            'url' => Storage::url($media->path),
            'path' => $media->path,
            'media_id' => $media->id
        ]);
    }
}
