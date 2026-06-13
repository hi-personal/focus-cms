{{-- resources/views/components/admin/posts/post-editor-menu-item.blade.php --}}

@props([
    'href'=>null,
    'icon',
    'text',
    'target'=>'_self',
    'type'=>'button',
    'offcanvasItem'=>false,
    'disabled'=>false,
])

@if ($href)
    <a
        href="{{ $disabled ? '#' : $href }}"
        target="{{ $target }}"
        @if($disabled)
            aria-disabled="true"
        @endif
        {{ $attributes->merge([
            'class'=>
            'flex md:flex-auto items-center px-2 py-2 text-sm font-medium items-center '.
            ($disabled
                ? 'text-gray-400 border border-gray-300 rounded cursor-not-allowed bg-gray-100'
                :
                'text-gray-900 '.(
                    $offcanvasItem
                    ? 'mb-2 bg-transparent hover:bg-gray-400 hover:text-white focus:bg-gray-300 justify-right'
                    : 'justify-center bg-transparent border border-gray-900 rounded hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white'
                )
            )
        ]) }}
    >
        <i class="{{ $icon }} mr-1"></i>
        <span>{{ $text }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        @if($disabled)
            disabled
        @endif
        {{ $attributes->merge([
            'class'=>
            'flex md:flex-auto px-2 py-2 text-sm font-medium items-center '.
            ($disabled
                ? 'text-gray-400 border-gray-300 cursor-not-allowed bg-gray-100'
                :
                'text-gray-900 '.(
                    $offcanvasItem
                    ? 'mb-2 bg-transparent hover:bg-gray-400 hover:text-white focus:bg-gray-300 w-full justify-right'
                    : 'justify-center bg-transparent border border-gray-900 rounded hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white'
                )
            )
        ]) }}
    >
        <i class="{{ $icon }} mr-1"></i>

        <span>{{ $text }}</span>

    </button>
@endif