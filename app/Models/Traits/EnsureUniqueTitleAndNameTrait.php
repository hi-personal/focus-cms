<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait EnsureUniqueTitleAndNameTrait
{
    /**
     * Ensure unique title
     */
    protected function ensureUniqueTitle(): ?string
    {
        if (empty($this->title)) {
            return null;
        }

        $originalTitle = $this->title;

        $title = $originalTitle;

        $counter = 1;

        while (
            static::where(
                'title',
                $title
            )
            ->where(
                'id',
                '!=',
                $this->id ?? 0
            )
            ->exists()
        ) {

            $title =
                $originalTitle
                . ' - '
                . $counter;

            $counter++;
        }

        $this->title = $title;

        return $title;
    }

    /**
     * Ensure unique slug/name
     */
    protected function ensureUniqueName(): ?string
    {
        $source = !empty($this->name)
            ? $this->name
            : $this->title;

        if (empty($source)) {
            return null;
        }

        $slug = Str::slug($source);

        $originalSlug = $slug;

        $counter = 2;

        while (
            static::where(
                'name',
                $slug
            )
            ->where(
                'id',
                '!=',
                $this->id ?? 0
            )
            ->exists()
        ) {

            $slug =
                $originalSlug
                . '-'
                . $counter;

            $counter++;
        }

        $this->name = $slug;

        return $slug;
    }
}