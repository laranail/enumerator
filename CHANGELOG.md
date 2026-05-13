# Changelog

All notable changes to `laranail/enumerator` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] — 2026-05-12

Initial release.

### Adoption surface

- **`Concerns\HasEnumerator`** — one-stop umbrella trait for native PHP 8.3+
  enums. Composes every behaviour trait the package ships: attribute lookup,
  equality, collection helpers, JSON serialisation, HTML rendering,
  translations, bitmasks, declarative groups, invokable cases, lifecycle
  (`next` / `previous` / `isFirst` / `isLast`), magic comparisons
  (`isFoo()` / `isNotFoo()`), explicit ordering, and the state machine.
  Granular traits stay public for advanced composition.
- **`Concerns\HasEnumAttributes`** — consumer-side trait used on Eloquent
  Models, Livewire Components, FormRequests, and DTOs to declare enum-typed
  attributes. Auto-registers the `AsEnum` cast and exposes 9 magic
  accessors (`_label`, `_value`, `_name`, `_color`, `_icon`,
  `_description`, `_help`, `_badge`, `_meta`) plus 3 predicates
  (`statusIs`, `statusIn`, `statusEquals`). Host detection is duck-typed
  (no hard dependency on framework classes); consumer-declared casts are
  preserved.
- **`AbstractEnumeratorClass`** — class-constant fallback for cases where
  native enums don't fit (dynamic case sets, BenSampo-style migration
  shims, etc.). First-class across every integration where native enums
  work.

### Attributes

`Bit`, `Color`, `CssClass`, `Description`, `Help`, `Icon`, `Label`, `Meta`,
`Order` — declarative metadata for cases. `CssClass` is repeatable for
per-framework variants. `Meta` collects arbitrary named values.

### Contracts

`Enumerator` (marker), `HtmlRenderable`, `Stateful`, `Translatable`,
`Bitwise`, `TransitionHook`, `TranslatorAdapter`, `TenantContext`.

### Eloquent

- Casts: `AsEnum`, `AsNullableEnum`, `AsEnumeratorCollection`, `AsBitmask`.
- `HasEnumeratorScopes` trait: `whereEnumIn`, `whereEnumMeta`,
  `whereEnumBitMatches`, plus implicit-binding-friendly helpers.
- `EnumeratorStateHistory` model with explicit `$fillable` for transition
  audit trails.

### Routing

`EnumeratorRouteBinder` registers implicit route bindings for native and
class-const enums uniformly.

### Validation

`EnumValue`, `EnumName`, `EnumIn`, `EnumNotIn`, `EnumTransition` — first-
class rule objects with sensible messages. `EnumValue::coerce` accepts
backed values, case names (pure enums), and falls back gracefully.

### Blade

- Components: `badge`, `select`, `radio`, `checkboxes`, `grid`, `list`,
  `bitmask`, `option` — each with publishable view bundles for `plain`,
  `tailwind`, `daisyui`, `bootstrap`, `bulma`. Anonymous-component
  callers' `class="..."` attribute merges with the framework's class set;
  `data-*` / `aria-*` / `id` forward to the underlying element.
- Directives: `@enumeratorLabel`, `@enumeratorColor`, `@enumeratorIcon`,
  `@enumeratorBadge`, `@enumeratorOptions`, `@enumeratorDescription`,
  `@enumeratorHelp`, `@enumeratorMeta($case, 'key')`, and case-insensitive
  variants.

### Translations

Pluggable via `TranslatorAdapter`. Default `LaravelTranslatorAdapter` wires
through the Laravel translator. Reference `DatabaseTranslatorAdapter` ships
opt-in. Resolution chain: registered translation → `#[Label]` attribute →
humanised case name (handles acronym-PascalCase, e.g. `HTTPMethod` →
`HTTP Method`).

### Bitmask

`Helpers\Bitmask` value type for `#[Bit]`-tagged enums. Constructor
validates each case against the declared enum class. `AsBitmask` cast
accepts `Bitmask`, integer masks, numeric strings, and single `Bitwise`
cases (auto-wrapped into a one-case mask).

### State machine

`Concerns\HasTransitions` + `Stateful` contract. Declarative
`transitions()` map; `transitionTo($next)` enforces with detailed
exceptions. Optional `EnumeratorStateHistory` model writes audit rows
with `TransitionHook` callbacks.

### Per-tenant overrides

`AttributesOverrideResolver` consults the bound `TenantContext` first,
then the config-file `enumerator.overrides` map. Default
`NullTenantContext` returns no overrides (single-tenant). Lazy
container resolution — swap with `app()->instance()` at runtime.

### Dynamic enums

`DynamicEnums\DatabaseBackedEnum` extends `AbstractEnumeratorClass` and
hydrates cases from a database table at runtime. Use when the case set
genuinely needs runtime definition. ⚠ Caveats: instances are NOT native
PHP enums — `match` exhaustiveness doesn't apply, `BackedEnum` type-hints
reject them, and IDE refactoring tools don't understand them. Prefer the
native enum path when possible.

### Artisan commands

- `make:enumerator` — scaffolds a new enum class with attributes
- `enumerator:annotate` — annotates models that use enum-cast attributes
- `enumerator:export` — emits TypeScript / JSON / CSV exports for cases
- `enumerator:ide-helper` — emits real `@method` stubs (per-case factories
  for `HasInvokableCases`, `is{Case}` / `isNot{Case}` predicates for
  `HasMagicComparisons`, `@method static {Type} {CONSTANT}()` for
  `AbstractEnumeratorClass` subclasses), wrapped in real-namespace blocks
  for IDE indexing
- `enumerator:cache` — snapshots resolved attributes/cases to disk
- `enumerator:cache:clear` — flushes the snapshot

### Reflection cache

Two-tier (memory → file) via `LayeredCache`. The snapshot persistor bridges
in-memory `AttributesCache` / `CasesCache` to disk so a single warm-up
serves every worker.

### Integrations

- **Filament 4+** — `EnumeratorFilter`, `EnumeratorRadio`,
  `EnumeratorSelect` form components, column helpers.
- **Livewire 3.5+** — enum-aware property casts and rule helpers.
- **Nova 5+** — `EnumeratorField` + `EnumeratorFilter`.
- **Inertia 2+** — transformer for typed SPA responses.

### Optional modules

Each gated by a `config('enumerator.modules.*')` toggle (all default false):

- **`Modules\Pest`** — 5 custom expectations: `toBeCase`, `toBeIn`,
  `toEqualEnum`, `toHaveBit`, `toCanTransitionTo`.
- **`Modules\OpenApi`** — emits OpenAPI 3.1 component schema fragments
  with `x-enum-varnames` and `x-enum-descriptions` vendor extensions.
- **`Modules\Lighthouse`** — abstract `EnumScalar` extends
  `webonyx/graphql-php` `ScalarType`; subclass per-enum and register via
  `@scalar`. Safe to autoload without Lighthouse installed.
- **`Modules\Saloon`** — `EnumCaster` recursively serialises enum
  instances in headers/query/JSON-body payloads.
- **`Modules\Octane`** — listens to `WorkerStarting` and warms the
  reflection cache per worker boot.
- **`Modules\StructuredOutput`** — three sibling JSON-schema emitters:
  `OpenAiSchemaEmitter` (response format + tool parameter),
  `AnthropicSchemaEmitter` (tool input property), `McpSchemaEmitter`
  (tool input property + resource parameter). Pure schema emission, no
  AI-SDK dependency.
- **`Modules\GraphQL`** — `SchemaExporter` emits portable `.graphql` enum
  fragments usable in any GraphQL stack (Mercurius, API Platform, Apollo,
  webonyx/graphql-php, Hasura).
- **Tenancy** — `TenantContext` driver swap via
  `enumerator.tenancy.driver` or `ENUMERATOR_TENANCY_DRIVER`.

### Migration codemods (`src/Rector/`)

Rector rules to migrate from existing PHP enum packages:

- `RectorBenSampoEnumToEnumerator` — full class-to-enum transform with
  backing-type detection.
- `RectorSpatieEnumToEnumerator` — structural skeleton (cases need
  manual fill for Spatie's docblock-driven pattern).
- `Sets\MigrationSet` — bundles both rules for one-import adoption.

Source files guard with `class_exists(AbstractRector::class)` so they
load cleanly without `rector/rector` installed.

### PHPStan

Custom extension at `extension.neon` resolves dynamic enum methods
(magic comparisons, invokable cases) for static analysis. Codebase ships
clean at `level: max`.

### CI

GitHub Actions matrix runs against PHP 8.3 + 8.4 + 8.5 with
`fail-fast: false`. Pint, PHPStan, and Pest (with 90% min coverage) all
gate.

### Presets

26 production-ready enums bundled at `src/Presets/Enums/` — lifecycle,
severity, presentation, HTTP, bitmask demos, demographics, calendar,
MIME types, plus one class-const example.

[0.1.0]: https://github.com/laranail/enumerator/releases/tag/v0.1.0
