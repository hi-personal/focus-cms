<?php

namespace App\Services;

class TaxonomyRegistry
{
    public static function all(): array
    {
        return config('taxonomies');
    }

    public static function get(string $taxonomy): array
    {
        return config(
            "taxonomies.$taxonomy",
            []
        );
    }

    public static function exists(string $taxonomy): bool
    {
        return array_key_exists(
            $taxonomy,
            config('taxonomies')
        );
    }

    public static function isHierarchical(string $taxonomy): bool
    {
        return config(
            "taxonomies.$taxonomy.hierarchical",
            false
        );
    }

    public static function getRouteSlug(
        string $taxonomy,
        string $locale
    ): ?string {

        return config(
            "taxonomies.$taxonomy.route.slug.$locale"
        );
    }

    public static function routesEnabled(string $taxonomy): bool
    {
        return config(
            "taxonomies.$taxonomy.route.enabled",
            true
        );
    }

    public static function getViews(string $taxonomy): array
    {
        return config(
            "taxonomies.$taxonomy.views",
            []
        );
    }
}