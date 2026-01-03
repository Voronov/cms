<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaProcessor
{
    protected array $config;
    protected ImageManager $manager;

    public function __construct()
    {
        $this->config = Yaml::parseFile(resource_path('media.yaml'));
        $this->manager = new ImageManager(new Driver());
    }

    public function crop(string $path, int $x, int $y, int $width, int $height): string
    {
        if (!File::exists($path)) {
            throw new \Exception("File does not exist: {$path}");
        }

        $mime = File::mimeType($path);
        if (!str_starts_with($mime, 'image/')) {
            throw new \Exception("File is not a valid image: {$path} (MIME: {$mime})");
        }

        $image = $this->manager->read($path);
        $image->crop($width, $height, $x, $y);
        
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $path);
        
        $image->toWebp($this->config['image_processing']['quality'] ?? 85)->save($webpPath);

        if (strtolower($extension) !== 'webp' && $webpPath !== $path) {
            File::delete($path);
        }

        return $webpPath;
    }

    public function process(string $path, ?array $cropData = null): array
    {
        if (!File::exists($path)) {
            throw new \Exception("File does not exist: {$path}");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp']);

        if (!$isImage) {
            return [
                'path' => $path,
                'variants' => []
            ];
        }

        // Additional check: Ensure it's a valid image file before reading
        $mime = File::mimeType($path);
        if (!str_starts_with($mime, 'image/')) {
            return [
                'path' => $path,
                'variants' => []
            ];
        }

        // Step A: Determine Source Master
        if ($cropData) {
            $path = $this->crop(
                $path,
                $cropData['x'],
                $cropData['y'],
                $cropData['width'],
                $cropData['height']
            );
        }

        $image = $this->manager->read($path);
        
        // Step B: Format Conversion (WebP)
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $path);
        $quality = $this->config['image_processing']['quality'] ?? 85;
        
        $image->toWebp($quality)->save($webpPath);
        
        if (strtolower($extension) !== 'webp' && $webpPath !== $path) {
            File::delete($path);
        }

        // Step C: Variant Generation
        $variants = $this->generateVariants($webpPath);

        return [
            'path' => $webpPath,
            'variants' => $variants,
            'width' => $image->width(),
            'height' => $image->height(),
            'filesize' => File::size($webpPath)
        ];
    }

    protected function generateVariants(string $path): array
    {
        $variants = [];
        $variantConfigs = $this->config['image_processing']['variants'] ?? [];
        $basename = pathinfo($path, PATHINFO_FILENAME);
        $dirname = pathinfo($path, PATHINFO_DIRNAME);
        $quality = $this->config['image_processing']['quality'] ?? 85;

        foreach ($variantConfigs as $suffix => $maxWidth) {
            try {
                $image = $this->manager->read($path);
                
                // Condition: Do not upscale
                if ($image->width() > $maxWidth) {
                    $image->scale(width: $maxWidth);
                    $variantName = "{$basename}-{$suffix}.webp";
                    $variantPath = "{$dirname}/{$variantName}";
                    $image->toWebp($quality)->save($variantPath);
                    $variants[] = $suffix;
                }
            } catch (\Exception $e) {
                \Log::error("Failed to generate variant {$suffix} for {$path}: " . $e->getMessage());
                continue;
            }
        }

        return $variants;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
