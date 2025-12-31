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
        $image = $this->manager->read($path);
        $image->crop($width, $height, $x, $y);
        $image->save($path);

        // Re-process to ensure WebP and scaling are applied to the cropped version
        return $this->process($path);
    }

    public function process(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        if (!$isImage) {
            return $path;
        }

        // Generate responsive variants first if configured
        $variants = $this->generateVariants($path);

        if (!$this->config['image_processing']['convert_to_webp']) {
            return $path;
        }

        $image = $this->manager->read($path);

        // Optional Resize for original
        $maxWidth = $this->config['image_processing']['max_width'] ?? 1920;
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $path);
        $image->toWebp($this->config['image_processing']['quality'] ?? 80)->save($webpPath);

        // Delete original if it's not webp
        if (strtolower($extension) !== 'webp') {
            File::delete($path);
        }

        return $webpPath;
    }

    protected function generateVariants(string $path): array
    {
        $variants = [];
        $widths = $this->config['image_processing']['responsive_variants'] ?? [];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $basename = pathinfo($path, PATHINFO_FILENAME);
        $dirname = pathinfo($path, PATHINFO_DIRNAME);

        foreach ($widths as $width) {
            $image = $this->manager->read($path);
            if ($image->width() > $width) {
                $image->scale(width: $width);
                $variantName = "{$basename}_{$width}.webp";
                $variantPath = "{$dirname}/{$variantName}";
                $image->toWebp($this->config['image_processing']['quality'] ?? 80)->save($variantPath);
                $variants[$width] = $variantName;
            }
        }

        return $variants;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
