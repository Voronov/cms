<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

class FrontendEntityController extends Controller
{
    public function show(string $type, string $slug)
    {
        $entity = Entity::ofType($type)
            ->active()
            ->where('content->slug', $slug)
            ->firstOrFail();

        $mediaConfig = Yaml::parseFile(resource_path('media.yaml'));
        
        // Try to find a specific view for this entity type, fallback to default
        $view = view()->exists("entities.{$type}.show") 
            ? "entities.{$type}.show" 
            : "entities.default.show";

        return view($view, compact('entity', 'mediaConfig'));
    }
}
