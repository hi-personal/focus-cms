<?php

namespace App\Services;

class ShortcodesService
{
    public function __construct(
        protected ShortcodeRegistry $registry
    ) {}


    public function parse(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        return preg_replace_callback_array(
            $this->registry->getCallbacks(),
            $content
        );
    }
}