<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->title }}</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6" x-data="{}">
    <div class="max-w-xl w-full bg-white shadow-lg rounded-2xl p-8 border border-gray-100">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $form->title }}</h1>
            @if($form->description)
                <p class="text-gray-500">{{ $form->description }}</p>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-8 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('forms.submit', $form->identifier) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @foreach($form->fields as $field)
                <div>
                    <label for="{{ $field['name'] }}" class="block text-sm font-semibold text-gray-700 mb-1">
                        {{ $field['label'] }}
                        @if($field['required'] ?? false)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @if($field['type'] === 'text')
                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] }}" 
                               value="{{ old($field['name']) }}"
                               @if(!empty($field['mask'])) x-mask="{{ $field['mask'] }}" @endif
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition bg-gray-50"
                               {{ ($field['required'] ?? false) ? 'required' : '' }}>
                    
                    @elseif($field['type'] === 'email')
                        <input type="email" name="{{ $field['name'] }}" id="{{ $field['name'] }}" 
                               value="{{ old($field['name']) }}"
                               @if(!empty($field['mask'])) x-mask="{{ $field['mask'] }}" @endif
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition bg-gray-50"
                               {{ ($field['required'] ?? false) ? 'required' : '' }}>

                    @elseif($field['type'] === 'number')
                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['name'] }}" 
                               value="{{ old($field['name']) }}"
                               @if(!empty($field['mask'])) x-mask="{{ $field['mask'] }}" @endif
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition bg-gray-50"
                               {{ ($field['required'] ?? false) ? 'required' : '' }}>

                    @elseif($field['type'] === 'textarea')
                        <textarea name="{{ $field['name'] }}" id="{{ $field['name'] }}" rows="4"
                                  @if(!empty($field['mask'])) x-mask="{{ $field['mask'] }}" @endif
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition bg-gray-50"
                                  {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ old($field['name']) }}</textarea>

                    @elseif($field['type'] === 'select')
                        <select name="{{ $field['name'] }}" id="{{ $field['name'] }}" 
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition bg-gray-50"
                                {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            <option value="">Select an option</option>
                            @foreach($field['options'] as $option)
                                <option value="{{ $option }}" {{ old($field['name']) == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>

                    @elseif($field['type'] === 'checkbox')
                        <div class="flex items-center">
                            <input type="checkbox" name="{{ $field['name'] }}" id="{{ $field['name'] }}" value="1"
                                   {{ old($field['name']) ? 'checked' : '' }}
                                   class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="{{ $field['name'] }}" class="ml-2 text-sm text-gray-600">
                                Confirm selection
                            </label>
                        </div>

                    @elseif($field['type'] === 'file')
                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['name'] }}" 
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                               {{ ($field['required'] ?? false) ? 'required' : '' }}>
                    @endif

                    @error($field['name'])
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="pt-4">
                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-indigo-100 transform active:scale-[0.98] transition duration-150">
                    Submit Form
                </button>
            </div>
        </form>
    </div>
</body>
</html>
