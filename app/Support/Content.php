<?php

namespace App\Support;

/**
 * Static, non-editable reference data.
 *
 *  - locations: the SEO city pages (resources/content/locations.json). Shared with the
 *    React app so routes and prerendering agree. Not editable from admin by design.
 *  - seed(): FIRST-INSTALL starter data (database/seeders/data/*.json). Used only by
 *    the seeders. PostgreSQL is the single source of truth afterwards; nothing at
 *    runtime reads these seed files.
 */
class Content
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private static array $cache = [];

    /** @return array<int, array<string, mixed>> */
    public static function seed(string $name): array
    {
        return self::$cache["seed.$name"] ??= self::read(database_path("seeders/data/{$name}.json"));
    }

    /** @return array<int, array{slug: string, name: string}> */
    public static function locations(): array
    {
        return self::$cache['locations'] ??= self::read(resource_path('content/locations.json'));
    }

    /** @return array{slug: string, name: string}|null */
    public static function location(string $slug): ?array
    {
        foreach (self::locations() as $location) {
            if ($location['slug'] === $slug) {
                return $location;
            }
        }

        return null;
    }

    /** @return array<int, array<string, mixed>> */
    private static function read(string $file): array
    {
        return json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    }
}
