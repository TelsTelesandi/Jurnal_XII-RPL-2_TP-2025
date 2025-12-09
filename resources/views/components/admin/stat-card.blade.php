@props([
    'icon' => 'fa-chart-bar',
    'color' => 'blue',
    'label' => '',
    'value' => 0
])

<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas {{ $icon }} text-2xl text-{{ $color }}-600"></i>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $label }}</dt>
                    <dd class="text-lg font-bold text-gray-900">{{ $value }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
