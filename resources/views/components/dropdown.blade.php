@props(['align' => 'right', 'width' => '48'])

@php
$alignmentClasses = match($align) {
    'left' => 'origin-top-left left-0',
    'top' => 'origin-top',
    default => 'origin-top-right right-0',
};

$widthClass = match($width) {
    '48' => 'w-48',
    default => 'w-48',
};
@endphp

<div class="relative" x-data="{ open: false }">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div x-show="open" @click.away="open = false"
         class="absolute z-50 mt-2 rounded-md shadow-lg {{ $widthClass }} {{ $alignmentClasses }}">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 bg-white">
            {{ $content }}
        </div>
    </div>
</div>
