<?php

namespace App\Http\Controllers;

use App\Services\MediaProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    protected MediaProcessor $processor;

    public function __construct(MediaProcessor $processor)
    {
        $this->processor = $processor;
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

        $publicUrl = Storage::url('media/' . basename($processedPath));

        return response()->json([
            'success' => true,
            'url' => $publicUrl,
            'path' => 'media/' . basename($processedPath)
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

        // Process image (WebP, scale, etc.)
        $processedPath = $this->processor->process($finalPath);

        $publicUrl = Storage::url('media/' . basename($processedPath));

        return response()->json([
            'success' => true,
            'url' => $publicUrl,
            'path' => 'media/' . basename($processedPath)
        ]);
    }
}
