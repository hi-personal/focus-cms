<?php

namespace App\Services\Contracts;

interface DynamicShortcodeInterface
{
    /**
     * Regex pattern
     */
    public function pattern(): string;


    /**
     * Render callback
     */
    public function render(array $matches): string;
}