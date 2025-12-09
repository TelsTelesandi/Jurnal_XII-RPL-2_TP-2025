@props(['active' => false])

@php
$classes = $active
            ? 'inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-white bg-blue-600 rounded-md'
            : 'inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-gray-300 hover:text-white hover:bg-blue-500 rounded-md';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
