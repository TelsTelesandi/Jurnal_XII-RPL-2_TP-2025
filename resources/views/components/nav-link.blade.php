@props(['active' => false])

@php
$classes = $active
            ? 'inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600'
            : 'inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 hover:border-blue-600 border-b-2 border-transparent';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
