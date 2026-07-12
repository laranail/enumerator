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

## Environment variable surface

Every env var the package reads. Defaults shown match
`config/enumerator.php`. Set in `.env` (or your environment's
equivalent) to override; an empty/missing value falls back to the
default.

| Variable | Maps to | Default | Accepts |
|---|---|---|---|
| `ENUMERATOR_CSS` | `css_framework` | `plain` | `plain` / `tailwind` / `daisyui` / `bootstrap` / `bulma` |
| `ENUMERATOR_CACHE_DRIVER` | `cache.driver` | `layered` | `memory` / `file` / `layered` |
| `ENUMERATOR_ALPINE_VERSION` | `alpine.version` | `3.15.12` | Alpine.js semver (used by `<x-...::alpine-loader />`) |
| `ENUMERATOR_ALPINE_INTEGRITY` | `alpine.integrity` | (pinned SHA-384) | SRI hash for the pinned bundle; empty disables SRI |
| `ENUMERATOR_ALPINE_CDN` | `alpine.cdn_url` | jsDelivr | URL pattern with `{version}` placeholder |
| `ENUMERATOR_ALPINE_LOCAL` | `alpine.local_url` | `/vendor/laranail-enumerator/alpine.min.js` | Local-fallback URL after `vendor:publish --tag=enumerator-js` |
| `ENUMERATOR_TRANSLATOR` | `translator.adapter` | `null` | FQCN implementing `Contracts\TranslatorAdapter` |
| `ENUMERATOR_TENANCY_DRIVER` | `tenancy.driver` | `null` | FQCN implementing `Contracts\TenantContext` |
| `ENUMERATOR_MODULE_PEST` | `modules.pest` | `false` | `true` / `false` |
| `ENUMERATOR_MODULE_OPENAPI` | `modules.openapi` | `false` | `true` / `false` |
| `ENUMERATOR_MODULE_LIGHTHOUSE` | `modules.lighthouse` | `false` | `true` / `false` |
| `ENUMERATOR_MODULE_SALOON` | `modules.saloon` | `false` | `true` / `false` |
| `ENUMERATOR_MODULE_OCTANE` | `modules.octane` | `false` | `true` / `false` |
| `ENUMERATOR_MODULE_STRUCTURED_OUTPUT` | `modules.structured_output` | `false` | `true` / `false` |
| `ENUMERATOR_MODULE_GRAPHQL` | `modules.graphql` | `false` | `true` / `false` |
| `ENUMERATOR_MODULE_TENANCY` | `modules.tenancy` | `false` | `true` / `false` |

The `modules.*` toggles gate the optional service providers (Pest,
OpenAPI, Lighthouse, Saloon, Octane, StructuredOutput, GraphQL,
Tenancy). Each provider no-ops when its toggle is `false` AND when
its vendor marker class is absent — so leaving them at `false` adds
zero cost on boot.

The `translator.adapter` and `tenancy.driver` env vars accept a
fully-qualified class name. The class must `implements` the
corresponding contract; the service provider verifies this at boot
and silently falls back to the default (`LaravelTranslatorAdapter` /
`NullTenantContext`) on mismatch.

`.env` example:

```dotenv
ENUMERATOR_CSS=tailwind
ENUMERATOR_CACHE_DRIVER=layered
ENUMERATOR_MODULE_PEST=true
ENUMERATOR_MODULE_OPENAPI=true
ENUMERATOR_TRANSLATOR=App\Enum\DatabaseTranslatorAdapter
```

---

[← Docs index](../README.md#documentation)
