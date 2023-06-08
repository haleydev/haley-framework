<?php

namespace Haley\Jobs;

class JobMemory
{
    public static array $jobs = [];

    private static array $attributes = [
        'name' => [],
        'namespace' => [],
        'description' => null
    ];

    public static function job(mixed $action, bool $valid)
    {
        $name = null;
        $namespace = null;
        $description = null;

        if (count(self::$attributes['name'])) $name = implode('.', self::$attributes['name']);
        if (count(self::$attributes['namespace'])) $namespace = implode('\\', self::$attributes['namespace']);
        if(!empty(self::$attributes['description'])) $description = self::$attributes['description'];

        self::$jobs[] = [
            'action' => $action,
            'valid' => $valid,
            'name' => $name,
            'namespace' => $namespace,
            'description' => $description
        ];
    }

    public static function setAttribute(string $name, mixed $value)
    {
        self::$attributes[$name][] = $value;
    }

    public static function removeAttribute(string $name)
    {
        if (!count(self::$attributes[$name])) return;

        $key = array_key_last(self::$attributes[$name]);

        if ($key !== null) unset(self::$attributes[$name][$key]);
    }
}
