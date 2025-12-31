@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit {{ $singularName }}</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <form action="{{ route('admin.entities.update', [$type, $entity]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @foreach($fields as $field)
            <div class="mb-4">
                <label for="{{ $field['name'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $field['label'] }}
                    @if($field['required'] ?? false)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @php
                    $fieldValue = old($field['name'], $entity->getField($field['name']));
                @endphp

                @if($field['type'] === 'text')
                    <input type="text" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ $fieldValue }}"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">

                @elseif($field['type'] === 'textarea')
                    <textarea name="{{ $field['name'] }}" 
                              id="{{ $field['name'] }}" 
                              rows="4"
                              placeholder="{{ $field['placeholder'] ?? '' }}"
                              {{ ($field['required'] ?? false) ? 'required' : '' }}
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">{{ $fieldValue }}</textarea>

                @elseif($field['type'] === 'wysiwyg')
                    <textarea name="{{ $field['name'] }}" 
                              id="{{ $field['name'] }}" 
                              rows="10"
                              placeholder="{{ $field['placeholder'] ?? '' }}"
                              {{ ($field['required'] ?? false) ? 'required' : '' }}
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">{{ $fieldValue }}</textarea>

                @elseif($field['type'] === 'number')
                    <input type="number" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ $fieldValue }}"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           step="{{ $field['step'] ?? '1' }}"
                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">

                @elseif($field['type'] === 'datetime')
                    <input type="datetime-local" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ $fieldValue }}"
                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">

                @elseif($field['type'] === 'select')
                    <select name="{{ $field['name'] }}" 
                            id="{{ $field['name'] }}" 
                            {{ ($field['required'] ?? false) ? 'required' : '' }}
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Select {{ $field['label'] }}</option>
                        @foreach($field['options'] ?? [] as $key => $value)
                            @php
                                $optionValue = is_numeric($key) ? $value : $key;
                                $optionLabel = is_numeric($key) ? $value : $value;
                            @endphp
                            <option value="{{ $optionValue }}" {{ $fieldValue == $optionValue ? 'selected' : '' }}>
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    </select>

                @elseif($field['type'] === 'file')
                    <input type="file" 
                           name="{{ $field['name'] }}{{ ($field['multiple'] ?? false) ? '[]' : '' }}" 
                           id="{{ $field['name'] }}" 
                           accept="{{ $field['accept'] ?? '*' }}"
                           {{ ($field['multiple'] ?? false) ? 'multiple' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                    @if(isset($field['max_size']))
                        <p class="mt-1 text-xs text-gray-500">Max size: {{ number_format($field['max_size'] / 1024, 1) }}MB</p>
                    @endif
                    @if(isset($field['help']))
                        <p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>
                    @endif
                    @php
                        $existingFiles = $entity->getFilesByField($field['name']);
                    @endphp
                    @if($existingFiles->isNotEmpty())
                        <div class="mt-2 space-y-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Current files:</p>
                            @foreach($existingFiles as $file)
                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                    <div class="flex items-center space-x-2">
                                        @if(str_starts_with($file->mime_type, 'image/'))
                                            <img src="{{ $file->getUrl() }}" alt="{{ $file->original_name }}" class="w-16 h-16 object-cover rounded">
                                        @endif
                                        <div>
                                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $file->original_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $file->getHumanReadableSize() }}</p>
                                        </div>
                                    </div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="delete_files[]" value="{{ $file->id }}" class="rounded">
                                        <span class="ml-2 text-sm text-red-600">Delete</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif

                @elseif($field['type'] === 'media')
                    <input type="text" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ $fieldValue }}"
                           placeholder="Media URL or path"
                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                    <p class="mt-1 text-xs text-gray-500">Enter media URL or use media library</p>

                @elseif($field['type'] === 'category')
                    <select name="{{ $field['name'] }}" 
                            id="{{ $field['name'] }}" 
                            {{ ($field['required'] ?? false) ? 'required' : '' }}
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Select Category</option>
                        @php
                            $entityDef = app(\App\Services\EntityDefinitionService::class);
                            $categories = $entityDef->getCategories($type);
                        @endphp
                        @foreach($categories as $category)
                            <option value="{{ $category['slug'] }}" {{ $fieldValue == $category['slug'] ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>

                @elseif($field['type'] === 'repeater')
                    <div class="border border-gray-300 dark:border-gray-600 rounded-md p-4" 
                         x-data='repeaterField({
                             fieldName: "{{ $field["name"] }}",
                             fields: {{ Js::from($field["fields"] ?? []) }},
                             existingData: {{ Js::from($fieldValue ?? []) }}
                         })'>
                        <div x-ref="container">
                            <!-- Repeater items will be added here -->
                        </div>
                        <button type="button" 
                                @click="addItem()"
                                class="mt-3 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm">
                            + Add {{ $field['label'] }} Item
                        </button>
                    </div>
                    @if(isset($field['help']))
                        <p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>
                    @endif

                @elseif($field['type'] === 'tags')
                    <input type="text" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue }}"
                           placeholder="{{ $field['placeholder'] ?? 'Enter tags separated by commas' }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                    <p class="mt-1 text-xs text-gray-500">Separate tags with commas</p>

                @endif

                @error($field['name'])
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="flex justify-end space-x-3 mt-6">
            <a href="{{ route('admin.entities.index', $type) }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition">
                Update {{ $singularName }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="/js/repeater-field.js"></script>
<script>
</script>
@endpush

@endsection
