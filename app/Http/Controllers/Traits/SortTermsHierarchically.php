<?php

namespace App\Http\Controllers\Traits;


trait SortTermsHierarchically
{
    /**
     * Method sortTermsHierarchically
     *
     * @param $terms $terms [explicite description]
     * @param $depth $depth [explicite description]
     *
     * @return void
     */
    private function sortTermsHierarchically($terms, $depth = 0)
    {
        $sorted = collect();

        foreach ($terms as $term) {
            $term->depth = $depth;
            $sorted->push($term);
            $sorted = $sorted->merge($term->allDescendants());
        }

        return $sorted;
    }
}