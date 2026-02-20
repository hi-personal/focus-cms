<?php

return [
    'commonmark' => [
        'renderer' => [
            'block_separator' => "\n",
            'inner_separator' => "\n",
            'soft_break' => "\n",
        ],
        'enable_em' => true,
        'enable_strong' => true,
        'use_asterisk' => true,
        'use_underscore' => true,
        'html_input' => 'allow',
        'allow_unsafe_links' => false,
        'max_nesting_level' => 100,
    ],

    'extensions' => [
        League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension::class,
        League\CommonMark\Extension\Table\TableExtension::class,
        League\CommonMark\Extension\Strikethrough\StrikethroughExtension::class,
        League\CommonMark\Extension\Autolink\AutolinkExtension::class,
        League\CommonMark\Extension\SmartPunct\SmartPunctExtension::class,
    ],
];