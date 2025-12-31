<ul class="{{ $class ?? 'flex space-x-4' }}">
    @foreach($items as $item)
        <li class="relative group">
            <a href="{{ $item->getUrl() }}" class="hover:text-indigo-600 transition-colors">
                {{ $item->getTitle() }}
            </a>
            
            @if($item->children->count() > 0)
                <ul class="absolute hidden group-hover:block bg-white shadow-lg py-2 mt-0 min-w-[200px] border border-gray-100 z-50">
                    @foreach($item->children as $child)
                        <li>
                            <a href="{{ $child->getUrl() }}" class="block px-4 py-2 hover:bg-gray-50">
                                {{ $child->getTitle() }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul>
