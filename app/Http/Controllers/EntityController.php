<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\EntityFile;
use App\Models\Redirect;
use App\Services\EntityDefinitionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class EntityController extends Controller
{
    protected EntityDefinitionService $entityDefinition;

    public function __construct(EntityDefinitionService $entityDefinition)
    {
        $this->entityDefinition = $entityDefinition;
    }

    public function index(string $type): View
    {
        if (!$this->entityDefinition->exists($type)) {
            abort(404, "Entity type '{$type}' not found");
        }

        $definition = $this->entityDefinition->get($type);
        $entities = Entity::ofType($type)->latest()->paginate(20);

        return view('admin.entities.index', [
            'type' => $type,
            'definition' => $definition,
            'entities' => $entities,
            'pluralName' => $this->entityDefinition->getPluralName($type),
            'singularName' => $this->entityDefinition->getSingularName($type),
        ]);
    }

    public function create(string $type): View
    {
        if (!$this->entityDefinition->exists($type)) {
            abort(404, "Entity type '{$type}' not found");
        }

        $definition = $this->entityDefinition->get($type);
        $fields = $this->entityDefinition->getFields($type);
        $defaults = $this->entityDefinition->getDefaultValues($type);

        return view('admin.entities.create', [
            'type' => $type,
            'definition' => $definition,
            'fields' => $fields,
            'defaults' => $defaults,
            'singularName' => $this->entityDefinition->getSingularName($type),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        if (!$this->entityDefinition->exists($type)) {
            abort(404, "Entity type '{$type}' not found");
        }

        $fields = $this->entityDefinition->getFields($type);
        $rules = $this->buildValidationRules($fields);
        
        $validated = $request->validate($rules);
        
        // Separate file uploads from regular content
        $fileFields = [];
        $contentData = [];
        
        foreach ($fields as $field) {
            $fieldName = $field['name'];
            
            if ($field['type'] === 'file' && $request->hasFile($fieldName)) {
                $fileFields[$fieldName] = $request->file($fieldName);
            } elseif ($field['type'] === 'repeater') {
                // Handle repeater fields separately
                $contentData[$fieldName] = $validated[$fieldName] ?? [];
            } elseif (isset($validated[$fieldName])) {
                $contentData[$fieldName] = $validated[$fieldName];
            }
        }

        // Auto-generate slug if needed
        if ($slugField = $this->entityDefinition->getSlugField($type)) {
            if (isset($contentData[$slugField])) {
                // If slug is empty or not provided, generate from slug field
                if (empty($contentData['slug'])) {
                    $contentData['slug'] = Str::slug($contentData[$slugField]);
                } else {
                    // Ensure slug is properly formatted
                    $contentData['slug'] = Str::slug($contentData['slug']);
                }
                
                // Ensure uniqueness
                $baseSlug = $contentData['slug'];
                $counter = 1;
                while (Entity::ofType($type)
                    ->whereJsonContains('content->slug', $contentData['slug'])
                    ->exists()) {
                    $contentData['slug'] = $baseSlug . '-' . $counter;
                    $counter++;
                }
            }
        }

        $seoData = $this->entityDefinition->buildSeoData($type, $contentData);

        $entity = Entity::create([
            'type' => $type,
            'content' => $contentData,
            'seo' => $seoData,
            'status' => $contentData['status'] ?? 'draft',
            'published_at' => $contentData['published_at'] ?? null,
            'expires_at' => $contentData['expires_at'] ?? null,
        ]);

        // Handle file uploads
        foreach ($fileFields as $fieldName => $files) {
            $this->handleFileUpload($entity, $fieldName, $files);
        }

        // Handle repeater field file uploads
        foreach ($fields as $field) {
            if ($field['type'] === 'repeater' && isset($contentData[$field['name']])) {
                $this->handleRepeaterFiles($entity, $field, $contentData[$field['name']]);
            }
        }

        return redirect()->route('admin.entities.index', $type)
            ->with('success', $this->entityDefinition->getSingularName($type) . ' created successfully.');
    }

    public function edit(string $type, Entity $entity): View
    {
        if (!$this->entityDefinition->exists($type)) {
            abort(404, "Entity type '{$type}' not found");
        }

        if ($entity->type !== $type) {
            abort(404, "Entity does not match type '{$type}'");
        }

        $definition = $this->entityDefinition->get($type);
        $fields = $this->entityDefinition->getFields($type);

        return view('admin.entities.edit', [
            'type' => $type,
            'definition' => $definition,
            'fields' => $fields,
            'entity' => $entity,
            'singularName' => $this->entityDefinition->getSingularName($type),
        ]);
    }

    public function update(Request $request, string $type, Entity $entity): RedirectResponse
    {
        if (!$this->entityDefinition->exists($type)) {
            abort(404, "Entity type '{$type}' not found");
        }

        if ($entity->type !== $type) {
            abort(404, "Entity does not match type '{$type}'");
        }

        $fields = $this->entityDefinition->getFields($type);
        $rules = $this->buildValidationRules($fields, $entity->id);
        
        $validated = $request->validate($rules);
        
        // Separate file uploads from regular content
        $fileFields = [];
        $contentData = [];
        
        foreach ($fields as $field) {
            $fieldName = $field['name'];
            
            if ($field['type'] === 'file' && $request->hasFile($fieldName)) {
                $fileFields[$fieldName] = $request->file($fieldName);
            } elseif ($field['type'] === 'repeater') {
                $contentData[$fieldName] = $validated[$fieldName] ?? [];
            } elseif (isset($validated[$fieldName])) {
                $contentData[$fieldName] = $validated[$fieldName];
            }
        }

        // Store old slug for redirect creation
        $oldSlug = $entity->getSlug();

        // Auto-generate slug if needed
        if ($slugField = $this->entityDefinition->getSlugField($type)) {
            if (isset($contentData[$slugField])) {
                // If slug is empty or not provided, generate from slug field
                if (empty($contentData['slug'])) {
                    $contentData['slug'] = Str::slug($contentData[$slugField]);
                } else {
                    // Ensure slug is properly formatted
                    $contentData['slug'] = Str::slug($contentData['slug']);
                }
            }
        }

        $newSlug = $contentData['slug'] ?? null;

        // Create redirect if slug changed and entity is published
        if ($oldSlug && $newSlug && $oldSlug !== $newSlug && $entity->status === 'published') {
            $this->createSlugRedirect($type, $oldSlug, $newSlug);
        }

        $seoData = $this->entityDefinition->buildSeoData($type, $contentData);

        $entity->update([
            'content' => $contentData,
            'seo' => $seoData,
            'status' => $contentData['status'] ?? 'draft',
            'published_at' => $contentData['published_at'] ?? null,
            'expires_at' => $contentData['expires_at'] ?? null,
        ]);

        // Handle file uploads
        foreach ($fileFields as $fieldName => $files) {
            $this->handleFileUpload($entity, $fieldName, $files);
        }

        // Handle file deletions
        if ($request->has('delete_files')) {
            foreach ($request->input('delete_files') as $fileId) {
                $file = EntityFile::where('entity_id', $entity->id)->find($fileId);
                if ($file) {
                    $file->delete();
                }
            }
        }

        // Handle repeater field file uploads
        foreach ($fields as $field) {
            if ($field['type'] === 'repeater' && isset($contentData[$field['name']])) {
                $this->handleRepeaterFiles($entity, $field, $contentData[$field['name']]);
            }
        }

        return redirect()->route('admin.entities.index', $type)
            ->with('success', $this->entityDefinition->getSingularName($type) . ' updated successfully.');
    }

    public function destroy(string $type, Entity $entity): RedirectResponse
    {
        if (!$this->entityDefinition->exists($type)) {
            abort(404, "Entity type '{$type}' not found");
        }

        if ($entity->type !== $type) {
            abort(404, "Entity does not match type '{$type}'");
        }

        $entity->delete();

        // Clear entity and page cache
        Cache::flush();

        return redirect()->route('admin.entities.index', $type)
            ->with('success', $this->entityDefinition->getSingularName($type) . ' deleted successfully.');
    }

    protected function buildValidationRules(array $fields, ?int $entityId = null): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $validation = $field['validation'] ?? '';

            if ($entityId && strpos($validation, 'unique:') !== false) {
                $validation = str_replace('unique:', "unique:entities,content->{$fieldName},{$entityId}", $validation);
            }

            if (!empty($validation)) {
                $rules[$fieldName] = $validation;
            }
        }

        return $rules;
    }

    protected function handleFileUpload(Entity $entity, string $fieldName, $files): void
    {
        $files = is_array($files) ? $files : [$files];
        
        foreach ($files as $index => $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $storedName = Str::random(40) . '.' . $extension;
            $path = $file->storeAs('entities/' . $entity->type . '/' . $entity->id, $storedName, 'public');
            
            EntityFile::create([
                'entity_id' => $entity->id,
                'field_name' => $fieldName,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'disk' => 'public',
                'order' => $index,
            ]);
        }
    }

    protected function handleRepeaterFiles(Entity $entity, array $field, array $repeaterData): void
    {
        // This method would handle file uploads within repeater fields
        // For now, files in repeaters are stored as part of the content JSON
        // A more advanced implementation would extract and store them separately
    }

    protected function createSlugRedirect(string $entityType, string $oldSlug, string $newSlug): void
    {
        $oldPath = '/' . $entityType . '/' . $oldSlug;
        $newPath = '/' . $entityType . '/' . $newSlug;

        // Check if redirect already exists
        $existingRedirect = Redirect::where('from_path', $oldPath)->first();
        
        if ($existingRedirect) {
            // Update existing redirect to point to new path
            $existingRedirect->update(['to_path' => $newPath]);
        } else {
            // Create new redirect
            Redirect::create([
                'from_path' => $oldPath,
                'to_path' => $newPath,
                'status_code' => 301, // Permanent redirect
            ]);
        }
    }
}
