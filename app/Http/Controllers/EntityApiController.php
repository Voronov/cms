<?php

namespace App\Http\Controllers;

use App\Services\EntityDefinitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntityApiController extends Controller
{
    protected EntityDefinitionService $entityDefinition;

    public function __construct(EntityDefinitionService $entityDefinition)
    {
        $this->entityDefinition = $entityDefinition;
    }

    public function getEntityTypes(): JsonResponse
    {
        $types = $this->entityDefinition->getTypes();
        $options = [];
        
        foreach ($types as $type) {
            $name = $this->entityDefinition->getName($type);
            $options[$type] = $name ?? ucfirst($type);
        }
        
        return response()->json($options);
    }

    public function getCategories(Request $request, string $entityType): JsonResponse
    {
        if (!$this->entityDefinition->exists($entityType)) {
            return response()->json(['error' => 'Entity type not found'], 404);
        }
        
        $categories = $this->entityDefinition->getCategories($entityType);
        $options = ['all' => 'All Categories'];
        
        foreach ($categories as $category) {
            $options[$category['slug']] = $category['name'];
        }
        
        return response()->json($options);
    }

    public function getEntities(Request $request, string $entityType): JsonResponse
    {
        if (!$this->entityDefinition->exists($entityType)) {
            return response()->json(['error' => 'Entity type not found'], 404);
        }
        
        $entities = \App\Models\Entity::ofType($entityType)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $options = [];
        foreach ($entities as $entity) {
            $title = $entity->getField('title') ?? $entity->getField('name') ?? 'Untitled';
            $options[$entity->id] = $title;
        }
        
        return response()->json($options);
    }

    public function getPaginationOptions(Request $request, string $entityType): JsonResponse
    {
        if (!$this->entityDefinition->exists($entityType)) {
            return response()->json(['error' => 'Entity type not found'], 404);
        }
        
        $paginationOptions = $this->entityDefinition->getPaginationOptions($entityType);
        $options = [];
        
        foreach ($paginationOptions as $option) {
            $options[$option['value']] = $option['label'];
        }
        
        return response()->json($options);
    }
}
