@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $pluralName }}</h1>
        @if($definition['description'])
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $definition['description'] }}</p>
        @endif
    </div>
    <a href="{{ route('admin.entities.create', $type) }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition">
        Add {{ $singularName }}
    </a>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                @foreach(array_slice($definition['fields'], 0, 3) as $field)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        {{ $field['label'] }}
                    </th>
                @endforeach
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($entities as $entity)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ $entity->id }}
                </td>
                @foreach(array_slice($definition['fields'], 0, 3) as $field)
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        @php
                            $value = $entity->getField($field['name']);
                            if (is_array($value)) {
                                $value = implode(', ', $value);
                            }
                            $value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                        @endphp
                        {{ $value ?? '-' }}
                    </td>
                @endforeach
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($entity->isExpired())
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Expired
                        </span>
                    @elseif($entity->status === 'published')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Published
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            {{ ucfirst($entity->status) }}
                        </span>
                    @endif
                    @if($entity->expires_at && !$entity->isExpired())
                        <span class="block text-xs text-gray-500 mt-1">
                            Expires: {{ $entity->expires_at->format('M d, Y') }}
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ $entity->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.entities.edit', [$type, $entity]) }}" 
                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                        Edit
                    </a>
                    <form action="{{ route('admin.entities.destroy', [$type, $entity]) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                onclick="return confirm('Are you sure you want to delete this item?')"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    No {{ strtolower($pluralName) }} found. <a href="{{ route('admin.entities.create', $type) }}" class="text-indigo-600 hover:text-indigo-900">Create your first one</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($entities->hasPages())
    <div class="mt-4">
        {{ $entities->links() }}
    </div>
@endif
@endsection
