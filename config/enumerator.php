<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | CSS Framework
    |--------------------------------------------------------------------------
    | Drives the default Blade component view bundle.
    | One of: plain, tailwind, daisyui, bootstrap, bulma.
    | Override per-call with <x-laranail-enumerator::badge framework="bootstrap" />.
    */
    'css_framework' => env('ENUMERATOR_CSS', 'plain'),

    /*
    |--------------------------------------------------------------------------
    | View Namespace
    |--------------------------------------------------------------------------
    | Prefix for the Blade component namespace. Tag becomes
    | <x-{view_namespace}::badge />.
    */
    'view_namespace' => 'laranail-enumerator',

    /*
    |--------------------------------------------------------------------------
    | Translation Namespace
    |--------------------------------------------------------------------------
    | Default key prefix. Full key: {namespace}::enums.{enum_slug}.{case}.
    */
    'translation_namespace' => 'enumerator',

    /*
    |--------------------------------------------------------------------------
    | Reflection cache
    |--------------------------------------------------------------------------
    | driver: memory  (per-request, default in dev)
    |         file    (bootstrap/cache/enumerator.php)
    |         layered (memory over file — recommended for prod)
    */
    'cache' => [
        'driver' => env('ENUMERATOR_CACHE_DRIVER', 'layered'),
        'file_path' => null,
        'auto_warm' => false,
        'auto_warm_classes' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | State machine
    |--------------------------------------------------------------------------
    */
    'state_machine' => [
        'table_name' => 'enumerator_state_history',
        'record_history' => true,
        'enforce_initial_state' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Magic behaviour
    |--------------------------------------------------------------------------
    */
    'magic' => [
        'case_insensitive_method_names' => true,
        'allow_invokable_cases' => false,
        'ambiguous_resolution' => 'throw', // throw | first | null
    ],

    /*
    |--------------------------------------------------------------------------
    | Translator adapter
    |--------------------------------------------------------------------------
    | Pluggable translation source. null → Translations\LaravelTranslatorAdapter
    | (default; wraps Laravel's translator).
    | Set to a FQCN implementing Contracts\TranslatorAdapter to override
    | (e.g. Translations\DatabaseTranslatorAdapter for DB-backed labels).
    */
    'translator' => [
        'adapter' => env('ENUMERATOR_TRANSLATOR', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-tenant overrides
    |--------------------------------------------------------------------------
    | FQCN of a class implementing Contracts\TenantContext. The service
    | provider binds Support\NullTenantContext (no-op) by default;
    | consumers swap via env / config. Per-tenant overrides take
    | precedence over `config('enumerator.overrides')`.
    */
    'tenancy' => [
        'driver' => env('ENUMERATOR_TENANCY_DRIVER', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional modules
    |--------------------------------------------------------------------------
    | Each module is gated by a boolean toggle. The module's service
    | provider no-ops when its toggle is false (and/or the relevant
    | vendor package is absent). All default to false — opt in per app.
    */
    'modules' => [
        'pest' => env('ENUMERATOR_MODULE_PEST', false),
        'openapi' => env('ENUMERATOR_MODULE_OPENAPI', false),
        'lighthouse' => env('ENUMERATOR_MODULE_LIGHTHOUSE', false),
        'saloon' => env('ENUMERATOR_MODULE_SALOON', false),
        'octane' => env('ENUMERATOR_MODULE_OCTANE', false),
        'structured_output' => env('ENUMERATOR_MODULE_STRUCTURED_OUTPUT', false),
        'graphql' => env('ENUMERATOR_MODULE_GRAPHQL', false),
        'tenancy' => env('ENUMERATOR_MODULE_TENANCY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attribute overrides
    |--------------------------------------------------------------------------
    | Override compile-time attributes without forking a preset.
    |
    | Example:
    |   Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum::class => [
    |       'Critical' => [
    |           'color' => 'magenta',
    |           'meta'  => ['paging' => true],
    |       ],
    |   ],
    */
    'overrides' => [],

];
