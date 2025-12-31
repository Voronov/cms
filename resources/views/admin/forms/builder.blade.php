@extends('layouts.admin')

@section('content')
    <div x-data="formBuilder({
        title: {{ Js::from($form->title ?? 'New Form') }},
        description: {{ Js::from($form->description ?? '') }},
        identifier: {{ Js::from($form->identifier ?? '') }},
        isActive: {{ Js::from($form->is_active ?? true) }},
        fields: {{ Js::from($form->fields ?? []) }},
        csrfToken: '{{ csrf_token() }}',
        storeUrl: '{{ route('admin.forms.store') }}',
        updateUrl: '{{ route('admin.forms.update', ':id') }}',
        indexUrl: '{{ route('admin.forms.index') }}'
    })" x-cloak class="pb-20">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ isset($form) ? 'Edit Form: ' . $form->title : 'Create New Form' }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Drag and drop elements to build your form.</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.forms.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</a>
                <button @click="saveForm"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded transition">
                    Save Form
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Palette -->
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-bold uppercase text-gray-500 dark:text-gray-400 mb-4 tracking-wider">Form
                        Elements</h2>
                    <div id="palette" class="space-y-2">
                        <template x-for="type in fieldTypes" :key="type.id">
                            <div class="palette-item p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md cursor-move hover:border-indigo-500 transition flex items-center"
                                :data-type="type.id">
                                <span class="mr-3 text-gray-400" x-html="type.icon"></span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200"
                                    x-text="type.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Form Settings -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-bold uppercase text-gray-500 dark:text-gray-400 mb-4 tracking-wider">General
                        Settings</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Form
                                Title</label>
                            <input type="text" x-model="title"
                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea x-model="description" rows="2"
                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" x-model="isActive" id="is_active" class="rounded text-indigo-600">
                            <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canvas -->
            <div class="lg:col-span-3 space-y-4">
                <div
                    class="bg-white dark:bg-gray-800 shadow rounded-lg min-h-[500px] p-6 border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <div id="canvas" class="space-y-4 min-h-[400px]">
                        <template x-for="(field, index) in fields" :key="field.id">
                            <div
                                class="group relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm hover:border-indigo-500 transition">
                                <!-- Field Header -->
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="cursor-move text-gray-400 hover:text-gray-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                        </span>
                                        <span class="text-xs font-bold uppercase text-indigo-600 dark:text-indigo-400"
                                            x-text="field.type"></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button @click="removeField(index)"
                                            class="p-1 text-gray-400 hover:text-red-500 transition">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Field Content -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                                        <input type="text" x-model="field.label"
                                            class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">System
                                            Name (ID)</label>
                                        <input type="text" x-model="field.name"
                                            class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                            :placeholder="slugify(field.label)">
                                    </div>

                                    <template
                                        x-if="['select', 'multiselect', 'radio', 'checkbox-group'].includes(field.type)">
                                        <div class="md:col-span-2">
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Options
                                                (One per line)</label>
                                            <textarea x-model="field.options_text" @input="updateOptions(index)" rows="3"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                                placeholder="Option 1&#10;Option 2"></textarea>
                                        </div>
                                    </template>

                                    <div class="md:col-span-2 flex items-center space-x-4">
                                        <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                            <input type="checkbox" x-model="field.required"
                                                class="rounded text-indigo-600 mr-2">
                                            Required
                                        </label>

                                        <template x-if="field.type === 'file'">
                                            <div class="flex items-center space-x-2">
                                                <input type="text" x-model="field.extensions" placeholder="jpg,png,pdf"
                                                    class="text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md">
                                                <input type="number" x-model="field.max_size" placeholder="KB"
                                                    class="w-20 text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md">
                                            </div>
                                        </template>
                                    </div>

                                    <div
                                        class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 border-t border-gray-100 dark:border-gray-700 pt-4">
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Validation
                                                Rule</label>
                                            <select x-model="field.validation_rule" @change="applyRuleDefaults(index)"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                                <option value="none">None</option>
                                                <option value="email">Email</option>
                                                <option value="phone">Phone</option>
                                                <option value="number">Number</option>
                                                <option value="alpha">Alpha-numeric</option>
                                                <option value="custom">Custom Regex</option>
                                            </select>
                                        </div>
                                        <div x-show="field.validation_rule === 'custom'">
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Custom
                                                Regex</label>
                                            <input type="text" x-model="field.validation_regex" placeholder="^[a-zA-Z]+$"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                        </div>
                                        <div
                                            x-show="['text', 'textarea', 'email', 'number', 'phone'].includes(field.type) || field.validation_rule === 'phone'">
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Input
                                                Mask</label>
                                            <input type="text" x-model="field.mask" placeholder="99-99-99"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Validation
                                                Message</label>
                                            <input type="text" x-model="field.validation_message"
                                                placeholder="Custom error text..."
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Placeholder -->
                        <div x-show="fields.length === 0"
                            class="flex flex-col items-center justify-center py-20 text-gray-400">
                            <svg class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <p>Drag items here to start building</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="/js/form-builder.js"></script>

    @endpush
@endsection