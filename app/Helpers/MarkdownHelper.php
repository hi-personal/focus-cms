<?php

use App\Services\CustomMarkdownService;
use App\Services\ShortcodesService;
use Illuminate\Support\Facades\Blade;


if (!function_exists('markdownToHtml')) {

    function markdownToHtml(
        string|null $text,
        bool $nl2br = true
    ): string {

        if (empty($text)) {
            return '';
        }


        static $markdownService = null;
        static $shortcodesService = null;


        if ($markdownService === null) {
            $markdownService =
                app(CustomMarkdownService::class);
        }


        if ($shortcodesService === null) {
            $shortcodesService =
                app(ShortcodesService::class);
        }


        /*
         |--------------------------------------------------------------------------
         | 1. Shortcodes
         |--------------------------------------------------------------------------
         |
         | Először shortcode, mert:
         | - shortcode generálhat HTML-t
         | - shortcode generálhat markdownot
         |
         */

        $text =
            $shortcodesService->parse($text);


        /*
         |--------------------------------------------------------------------------
         | 2. Markdown
         |--------------------------------------------------------------------------
         */

        $text =
            $markdownService->text(
                $text,
                $nl2br
            );


        /*
         |--------------------------------------------------------------------------
         | 3. Blade render
         |--------------------------------------------------------------------------
         |
         | lehetővé teszi Blade shortcode jellegű használatot:
         |
         | {!! something !!}
         | <x-component />
         |
         */

        $text =
            Blade::render(
                $text,
                []
            );


        return $text;
    }
}