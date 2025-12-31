<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Str;

class Form
{
    public string $identifier;
    public string $title;
    public ?string $description;
    public array $fields;
    public bool $is_active;

    protected static string $storagePath = 'forms';

    public function __construct(array $attributes = [])
    {
        $this->identifier = $attributes['identifier'] ?? '';
        $this->title = $attributes['title'] ?? '';
        $this->description = $attributes['description'] ?? '';
        $this->fields = $attributes['fields'] ?? [];
        $this->is_active = $attributes['is_active'] ?? true;
    }

    public static function all(): array
    {
        if (!Storage::exists(self::$storagePath)) {
            Storage::makeDirectory(self::$storagePath);
            return [];
        }

        $files = Storage::files(self::$storagePath);
        $forms = [];

        foreach ($files as $file) {
            if (Str::endsWith($file, '.yaml')) {
                $forms[] = self::load($file);
            }
        }

        return $forms;
    }

    public static function find(string $identifier): ?self
    {
        $path = self::$storagePath . '/' . $identifier . '.yaml';
        if (Storage::exists($path)) {
            return self::load($path);
        }
        return null;
    }

    protected static function load(string $path): self
    {
        $content = Storage::get($path);
        $data = Yaml::parse($content);

        $identifier = basename($path, '.yaml');
        $data['identifier'] = $identifier;

        return new self($data);
    }

    public function save(): bool
    {
        if (empty($this->identifier)) {
            $this->identifier = Str::slug($this->title);
        }

        $path = self::$storagePath . '/' . $this->identifier . '.yaml';
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'fields' => $this->fields,
        ];

        return Storage::put($path, Yaml::dump($data, 4));
    }

    public function delete(): bool
    {
        $path = self::$storagePath . '/' . $this->identifier . '.yaml';
        return Storage::delete($path);
    }
}
