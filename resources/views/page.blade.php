@extends('layouts.frontend')

@section('title', $translation->meta_title ?? $translation->title)

@section('meta')
    <meta name="description" content="{{ $translation->meta_description }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $translation->og_title ?? $translation->meta_title ?? $translation->title }}">
    <meta property="og:description" content="{{ $translation->og_description ?? $translation->meta_description }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($translation->og_image)
        <meta property="og:image"
            content="{{ \Illuminate\Support\Str::startsWith($translation->og_image, 'http') ? $translation->og_image : \Illuminate\Support\Facades\Storage::url($translation->og_image) }}">
    @endif
@endsection

@section('content')
    <article class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-8">{{ $translation->title }}</h1>

        @if(isset($translation->blocks) && is_array($translation->blocks) && count($translation->blocks) > 0)
            <div class="space-y-16">
                @foreach($translation->blocks as $block)
                    @includeIf('blocks.' . $block['type'], ['data' => $block['data']])
                @endforeach
            </div>
        @else
            <div class="prose dark:prose-invert lg:prose-xl mx-auto">
                <p class="text-gray-500 italic">No content available for this page in {{ strtoupper(app()->getLocale()) }}.</p>
            </div>
        @endif
    </article>
@endsection