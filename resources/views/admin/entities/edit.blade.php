@extends('layouts.admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit {{ $singularName }}</h1>
        <div class="flex items-center space-x-2">
            @foreach($locales as $code => $lang)
                <a href="{{ route('admin.entities.edit', ['type' => $type, 'entity' => $entity, 'locale' => $code]) }}" 
                   class="px-3 py-1 text-sm rounded-md transition {{ $currentLocale === $code ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ $lang['name'] }}
                </a>
            @endforeach
        </div>
    </div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <form action="{{ route('admin.entities.update', [$type, $entity]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="locale" value="{{ $currentLocale }}">

        @foreach($fields as $field)
            <div class="mb-4">
                <label for="{{ $field['name'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $field['label'] }}
                    @if($field['required'] ?? false)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @php
                    $fieldValue = old($field['name'], $translation->content[$field['name']] ?? ($entity->getField($field['name']) ?? ''));
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
                    <div x-data="{ 
                        value: '{{ $fieldValue }}',
                        openMediaLibrary() {
                            // This would ideally open a media picker modal
                            // For now, let's keep it as a text input but styled better
                        }
                    }">
                        <div class="flex space-x-2">
                            <input type="text" 
                                   name="{{ $field['name'] }}" 
                                   id="{{ $field['name'] }}" 
                                   x-model="value"
                                   placeholder="Media ID or path"
                                   {{ ($field['required'] ?? false) ? 'required' : '' }}
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                            <button type="button" 
                                    @click="openMediaLibrary()"
                                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm transition">
                                Browse
                            </button>
                        </div>
                        <template x-if="value">
                            <div class="mt-2">
                                <p class="text-xs text-gray-500">Current media: <span x-text="value"></span></p>
                            </div>
                        </template>
                    </div>

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

                @elseif($field['type'] === 'blocks')
                    <div x-data="pageEditor({
                        mediaConfig: {{ Js::from($mediaConfig) }},
                        pageBlocks: {{ Js::from($fieldValue ?? []) }},
                        blockTypes: {{ Js::from($blocks) }},
                        showSettings: false
                    })" class="space-y-4">
                        <input type="hidden" name="{{ $field['name'] }}" :value="JSON.stringify(pageBlocks)">
                        
                        <!-- Blocks List -->
                        <div id="blocks-list" class="space-y-4">
                            <template x-for="(block, index) in pageBlocks" :key="block.id">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50 overflow-hidden shadow-sm">
                                    <!-- Block Header -->
                                    <div @click="toggleBlockCollapse(index)" class="bg-gray-100 dark:bg-gray-700 px-4 py-2 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <div class="drag-handle p-1 -ml-1 cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click.stop>
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                </svg>
                                            </div>
                                            <svg class="h-4 w-4 transition-transform duration-200" :class="block.collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-0.5 rounded" x-text="block.name"></span>
                                            <span x-text="'#' + (index + 1)"></span>
                                            <template x-if="block.collapsed">
                                                <div class="flex items-center ml-4 space-x-2" x-html="renderPreview(block)"></div>
                                            </template>
                                        </div>
                                        <div class="flex items-center space-x-2" @click.stop>
                                            <button type="button" @click="removeBlock(index)" class="p-1 text-gray-400 hover:text-red-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Block Fields -->
                                    <div x-show="!block.collapsed" class="p-4 grid grid-cols-1 gap-4">
                                        <template x-for="field in (blockTypes.find(b => b.id === block.type)?.fields || [])">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1" x-text="field.label"></label>
                                                
                                                <template x-if="field.type === 'input' || field.type === 'text'">
                                                    <input type="text" x-model="block.data[field.name]" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                </template>

                                                <template x-if="field.type === 'textarea'">
                                                    <textarea x-model="block.data[field.name]" rows="4" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white"></textarea>
                                                </template>

                                                <template x-if="field.type === 'select'">
                                                    <select x-model="block.data[field.name]" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                        <template x-for="(label, value) in field.options">
                                                            <option :value="value" x-text="label"></option>
                                                        </template>
                                                    </select>
                                                </template>

                                                <template x-if="field.type === 'media' || field.type === 'file' || field.type === 'image'">
                                                    <div class="flex items-center space-x-2">
                                                        <input type="text" x-model="block.data[field.name]" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                        <button type="button" @click="openImageLibrary(index, field.name)" class="px-3 py-1.5 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">Browse</button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Block Preview (When collapsed) -->
                                    <div x-show="block.collapsed" class="p-4 bg-white dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700" x-html="renderPreview(block)"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Add Block Button -->
                        <div class="mt-4 flex justify-center">
                            <button type="button" @click="showBlockPicker = true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                Add Block
                            </button>
                        </div>

                        <!-- Block Picker Modal -->
                        <div x-show="showBlockPicker" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showBlockPicker = false"></div>
                                <div class="relative bg-white dark:bg-gray-800 rounded-lg p-6 max-w-lg w-full shadow-xl">
                                    <h3 class="text-lg font-medium mb-4 dark:text-white">Select Block Type</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <template x-for="bt in blockTypes" :key="bt.id">
                                            <button type="button" @click="addBlock(bt.id)" class="p-4 border rounded hover:border-indigo-500 text-left dark:border-gray-700 dark:text-white transition">
                                                <div class="font-bold" x-text="bt.name"></div>
                                                <div class="text-xs text-gray-500" x-text="bt.description"></div>
                                            </button>
                                        </template>
                                    </div>
                                    <button type="button" @click="showBlockPicker = false" class="mt-6 w-full py-2 bg-gray-200 dark:bg-gray-700 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

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
            <x-revision-manager :model="$entity" :modelType="get_class($entity)" />
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                Save Changes
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="/js/page-editor.js"></script>
<script src="/js/repeater-field.js"></script>
@endpush

@endsection
