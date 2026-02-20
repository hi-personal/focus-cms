{{-- resources/views/components/post-status-text-color-class.blade.php --}}
@props([
    'status',
    'icon' => false,
    'dark_mode' => false,
    'class' => '',
    'class_only' => false,
    'only' => 'both', // 'text', 'icon', 'bg', or 'all'
    'bg_light' => false, // Use lighter background variants
])

@php
    // Status color mappings
    $statusClasses = [
        'draft' => [
            'text' => 'text-blue-800 hover:text-blue-500',
            'bg' => 'bg-blue-100',
            'bg_light' => 'bg-blue-50',
            'text_dark' => 'dark:text-blue-200',
            'bg_dark' => 'dark:bg-blue-900',
            'bg_dark_light' => 'dark:bg-blue-800',
            'icon' => 'mdi mdi-file-edit'
        ],
        'published' => [
            'text' => 'text-green-700 hover:text-green-500',
            'bg' => 'bg-green-100',
            'bg_light' => 'bg-green-50',
            'bold' => 'font-bold',
            'text_dark' => 'dark:text-green-200',
            'bg_dark' => 'dark:bg-green-900',
            'bg_dark_light' => 'dark:bg-green-800',
            'icon' => 'mdi mdi-lightbulb-on-outline'
        ],
        'trash' => [
            'text' => 'text-gray-800 hover:text-gray-500',
            'bg' => 'bg-gray-100',
            'bg_light' => 'bg-gray-50',
            'bold' => 'font-bold',
            'text_dark' => 'dark:text-gray-200',
            'bg_dark' => 'dark:bg-teal-900',
            'bg_dark_light' => 'dark:bg-teal-800',
            'icon' => 'mdi mdi-trash-can-outline'
        ],
        'private' => [
            'text' => 'text-red-800 hover:text-red-500',
            'bg' => 'bg-red-100',
            'bg_light' => 'bg-red-50',
            'text_dark' => 'dark:text-red-200',
            'bg_dark' => 'dark:bg-red-900',
            'bg_dark_light' => 'dark:bg-red-800',
            'icon' => 'mdi mdi-account-circle-outline'
        ],
        'system' => [
            'text' => 'text-red-800 hover:text-red-500',
            'bg' => 'bg-red-100',
            'bg_light' => 'bg-red-50',
            'text_dark' => 'dark:text-red-200',
            'bg_dark' => 'dark:bg-red-900',
            'bg_dark_light' => 'dark:bg-red-800',
            'icon' => 'mdi mdi-lock-outline'
        ],
        'default' => [
            'text' => 'text-gray-800 hover:text-red-500',
            'bg' => 'bg-gray-100',
            'bg_light' => 'bg-gray-50',
            'text_dark' => 'dark:text-gray-200',
            'bg_dark' => 'dark:bg-gray-900',
            'bg_dark_light' => 'dark:bg-gray-800'
        ]
    ];

    // Determine status
    $statusKey = strtolower($status);
    if (!array_key_exists($statusKey, $statusClasses)) {
        $statusKey = 'default';
    }

    // Build class string based on parameters
    $classes = '';

    // Text colors
    if ($only === 'all' || $only === 'text') {
        $classes .= $statusClasses[$statusKey]['text'] . ' ';
    }

    // Background colors
    if ($only === 'all' || $only === 'bg') {
        $classes .= ($bg_light ? $statusClasses[$statusKey]['bg_light'] : $statusClasses[$statusKey]['bg']) . ' ';
    }

    // Bold if exists
    if (isset($statusClasses[$statusKey]['bold'])) {
        $classes .= $statusClasses[$statusKey]['bold'] . ' ';
    }

    // Dark mode handling
    if ($dark_mode) {
        // Text colors
        if ($only === 'all' || $only === 'text') {
            $classes .= $statusClasses[$statusKey]['text_dark'] . ' ';
        }

        // Background colors
        if ($only === 'all' || $only === 'bg') {
            $classes .= ($bg_light ? $statusClasses[$statusKey]['bg_dark_light'] : $statusClasses[$statusKey]['bg_dark']) . ' ';
        }
    }

    // Icon classes
    if ($icon) {
        $classes .= 'flex items-center ';
    }

    // Additional classes
    $classes .= $class;
    $classes .= " hover:bg-white hover:font-bold";
    $classes = trim($classes);
@endphp

@if($class_only)
    {{ $classes }}
@elseif($only == "icon")
    <i class="{{ $statusClasses[$status]['icon'] }} mr-1"></i>
@else
<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="{{ $statusClasses[$status]['icon'] }} mr-1"></i>
    @endif
    @if($only == "text" || $only == "all")
        {{ $slot }}
    @endif
</span>
@endif