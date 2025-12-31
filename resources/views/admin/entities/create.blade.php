@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Create {{ $singularName }}</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <form action="{{ route('admin.entities.store', $type) }}" method="POST" enctype="multipart/form-data">
        @csrf

        @foreach($fields as $field)
            <div class="mb-4">
                <label for="{{ $field['name'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $field['label'] }}
                    @if($field['required'] ?? false)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($field['type'] === 'text')
                    <input type="text" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ old($field['name'], $defaults[$field['name']] ?? '') }}"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">

                @elseif($field['type'] === 'textarea')
                    <textarea name="{{ $field['name'] }}" 
                              id="{{ $field['name'] }}" 
                              rows="4"
                              placeholder="{{ $field['placeholder'] ?? '' }}"
                              {{ ($field['required'] ?? false) ? 'required' : '' }}
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">{{ old($field['name'], $defaults[$field['name']] ?? '') }}</textarea>

                @elseif($field['type'] === 'wysiwyg')
                    <textarea name="{{ $field['name'] }}" 
                              id="{{ $field['name'] }}" 
                              rows="10"
                              placeholder="{{ $field['placeholder'] ?? '' }}"
                              {{ ($field['required'] ?? false) ? 'required' : '' }}
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">{{ old($field['name'], $defaults[$field['name']] ?? '') }}</textarea>

                @elseif($field['type'] === 'number')
                    <input type="number" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ old($field['name'], $defaults[$field['name']] ?? '') }}"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           step="{{ $field['step'] ?? '1' }}"
                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">

                @elseif($field['type'] === 'datetime')
                    <input type="datetime-local" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ old($field['name'], $defaults[$field['name']] ?? '') }}"
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
                            <option value="{{ $optionValue }}" {{ old($field['name'], $defaults[$field['name']] ?? '') == $optionValue ? 'selected' : '' }}>
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
                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                    @if(isset($field['max_size']))
                        <p class="mt-1 text-xs text-gray-500">Max size: {{ number_format($field['max_size'] / 1024, 1) }}MB</p>
                    @endif
                    @if(isset($field['help']))
                        <p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>
                    @endif

                @elseif($field['type'] === 'media')
                    <input type="text" 
                           name="{{ $field['name'] }}" 
                           id="{{ $field['name'] }}" 
                           value="{{ old($field['name'], $defaults[$field['name']] ?? '') }}"
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
                            <option value="{{ $category['slug'] }}" {{ old($field['name'], $defaults[$field['name']] ?? '') == $category['slug'] ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>

                @elseif($field['type'] === 'repeater')
                    <div class="border border-gray-300 dark:border-gray-600 rounded-md p-4" 
                         x-data='repeaterField({
                             fieldName: "{{ $field["name"] }}",
                             fields: {{ Js::from($field["fields"] ?? []) }}
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
                           value="{{ old($field['name'], $defaults[$field['name']] ?? '') }}"
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
                Create {{ $singularName }}
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
