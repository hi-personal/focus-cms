<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;


trait EnsureUniqueTitleAndNameTrait
{
     /**
     * Method ensureUniqueTitle
     *
     * @return void
     */
    protected function ensureUniqueTitle()
    {
        $originalTitle = $this->title;
        $counter = 1;

        while ($this->titleExistsInDatabase('title', $this->title)) {
            $this->title = $originalTitle . ' - ' . $counter;
            $counter++;
        }
    }

    /**
     * Method ensureUniqueName
     *
     * @return void
     */
    protected function ensureUniqueName()
    {
        // Ha van név, abból generálunk, különben a title-ből
        $source = !empty($this->name) ? $this->name : $this->title;
        $slug = Str::slug($source);
        $originalSlug = $slug;
        $counter = 2;

        while ($this->titleExistsInDatabase('name', $slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $this->name = $slug;
    }

    /**
     * Method titleExistsInDatabase
     *
     * @param $field $field [explicite description]
     * @param $value $value [explicite description]
     *
     * @return void
     */
    protected function titleExistsInDatabase($field, $value)
    {
        if (isset($this->post_type_name)) {
            return self::where($field, $value)
                ->where('post_type_name', $this->post_type_name)
                ->where('id', '!=', $this->id ?? 0)
                ->exists();
        } else {
            return self::where($field, $value)
                ->where('id', '!=', $this->id ?? 0)
                ->exists();
        }

    }
}