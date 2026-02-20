<?php

namespace App\Services;

use App\Services\Contracts\DynamicShortcodeInterface;

class ShortcodeRegistry
{
    protected array $shortcodes = [];


    public function register(DynamicShortcodeInterface $shortcode): void
    {
        $this->shortcodes[] = $shortcode;
    }


    public function getCallbacks(): array
    {
        $callbacks = [];

        foreach ($this->shortcodes as $shortcode) {

            $callbacks[$shortcode->pattern()] =
                fn($matches) => $shortcode->render($matches);
        }

        return $callbacks;
    }
}