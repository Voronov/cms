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

    public function show(Media $media)
    {
        $usage = $media->getUsage();
        return response()->json([
            'media' => $media,
            'usage' => $usage
        ]);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->path);
        $media->delete();

        return response()->json(['success' => true]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'identifier' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
            'filename' => 'required|string',
        ]);

        $file = $request->file('file');
        $identifier = $request->input('identifier');
        $index = $request->input('chunk_index');
        $total = $request->input('total_chunks');
        $originalName = $request->input('filename');

        $tempPath = "chunks/{$identifier}";
        $chunkName = "chunk_{$index}";

        Storage::disk('local')->putFileAs($tempPath, $file, $chunkName);

        // Check if all chunks are uploaded
        $chunks = Storage::disk('local')->files($tempPath);

        if (count($chunks) === $total) {
            return $this->assembleChunks($tempPath, $originalName, $total);
        }

        return response()->json(['success' => true, 'message' => 'Chunk uploaded']);
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

        $processedPath = $this->processor->crop(
            $fullPath,
            $request->input('x'),
            $request->input('y'),
            $request->input('width'),
            $request->input('height')
        );

        $image = $this->manager->read($processedPath);
        
        $media = Media::create([
            'filename' => basename($processedPath),
            'original_name' => 'cropped_' . basename($relativePath),
            'path' => 'media/' . basename($processedPath),
            'mime_type' => File::mimeType($processedPath),
            'size' => File::size($processedPath),
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

    protected function assembleChunks(string $tempPath, string $originalName, int $total)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::random(40) . '.' . $extension;
        $finalPath = storage_path("app/public/media/{$fileName}");

        if (!File::isDirectory(storage_path('app/public/media'))) {
            File::makeDirectory(storage_path('app/public/media'), 0755, true);
        }

        $out = fopen($finalPath, 'ab');

        for ($i = 0; $i < $total; $i++) {
            $chunkPath = storage_path("app/{$tempPath}/chunk_{$i}");
            $in = fopen($chunkPath, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }

        fclose($out);

        // Clean up chunks
        Storage::disk('local')->deleteDirectory($tempPath);

        $processedPath = $this->processor->process($finalPath);

        $image = $this->manager->read($processedPath);

        $media = Media::create([
            'filename' => basename($processedPath),
            'original_name' => $originalName,
            'path' => 'media/' . basename($processedPath),
            'mime_type' => File::mimeType($processedPath),
            'size' => File::size($processedPath),
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
