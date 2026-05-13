# Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=enumerator-config
```

It writes to `config/enumerator.php`. Every key is documented inline.

## Keys

| Key | Default | Notes |
|---|---|---|
| `css_framework` | `plain` | One of `plain`, `tailwind`, `daisyui`, `bootstrap`, `bulma`. Drives the Blade component view bundle. Override per-call with `framework=`. |
| `view_namespace` | `laranail-enumerator` | Blade component prefix: `<x-laranail-enumerator::badge>`. |
| `translation_namespace` | `enumerator` | Translation key prefix. Full key: `{namespace}::enums.{slug}.{case}`. |
| `cache.driver` | `layered` | `memory` (per-request) / `file` (bootstrap/cache/enumerator.php) / `layered` (memory over file). |
| `cache.file_path` | `null` | Defaults to `bootstrap/cache/enumerator.php`. |
| `cache.auto_warm` | `false` | If `true`, warm `auto_warm_classes` on boot. Adds startup latency. |
| `cache.auto_warm_classes` | `[]` | FQCNs to warm. Use with `auto_warm=true` or `enumerator:cache` artisan command. |
| `state_machine.table_name` | `enumerator_state_history` | Table for the optional history model. |
| `state_machine.record_history` | `true` | Persist transitions automatically. |
| `state_machine.enforce_initial_state` | `true` | Reject creating models with a non-initial state. |
| `magic.case_insensitive_method_names` | `true` | `isActive()` ≡ `isactive()`. |
| `magic.allow_invokable_cases` | `false` | Required only when using `HasInvokableCases` globally — most consumers leave at `false` and use the trait per-enum. |
| `magic.ambiguous_resolution` | `throw` | `throw` / `first` / `null` — how to behave when a magic call matches multiple cases. |
| `overrides` | `[]` | Attribute overrides keyed by FQCN + case name. See below. |

## Overrides

`config('enumerator.overrides')` lets you override metadata declared on a
case without forking the enum.

```php
return [
    'overrides' => [
        Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum::class => [
            'Critical' => [
                'color' => 'magenta',
                'icon'  => 'fire',
                'meta'  => ['pages_oncall' => true],
            ],
        ],
    ],
];
```

Resolution priority: config override → `#[Attribute]` → default.

`meta` arrays merge shallowly (override wins on key conflict). All other
keys fully replace the attribute value.

## Environment overrides

Two keys read from environment variables:

```bash
ENUMERATOR_CSS=tailwind            # css_framework
ENUMERATOR_CACHE_DRIVER=layered    # cache.driver
```
