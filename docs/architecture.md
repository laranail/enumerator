# Architecture

`laranail/enumerator` is a composition-first toolkit. The package design
follows three principles:

1. **Native PHP 8.3+ enums are the primary API.** Class-const enums are a
   fallback for niche cases (mixed backing types, dynamic case generation).
2. **Composition over inheritance.** Behaviour comes from traits
   (`Concerns/`) that can be combined freely. There is no abstract base
   class for native enums.
3. **Zero third-party Composer dependencies.** Only Laravel framework
   packages are required.

## Layers

```
┌─────────────────────────────────────────────────────────────┐
│  Consumer enum                                              │
│    enum UserStatusEnum: string implements Enumerator { ... }│
└───────────────┬─────────────────────────────────────────────┘
                │ uses
                ▼
┌─────────────────────────────────────────────────────────────┐
│  Concerns (traits)                                          │
│  HasEnumeratorBehavior (umbrella)                           │
│    ├ HasAttributes        ├ HasEquality                     │
│    ├ HasFromHelpers       ├ IsJsonable                      │
│    ├ IsTranslatable       ├ RendersHtml                     │
│    └ ResolvesMagicCalls                                     │
│  Feature traits (opt-in)                                    │
│    HasBitmask, HasTransitions, HasGrouping, HasOrder,       │
│    HasLifecycle, HasMagicComparisons, HasInvokableCases     │
│  Class-const fallback                                       │
│    HasClassEnumBehavior (used by AbstractEnumeratorClass)   │
└───────────────┬─────────────────────────────────────────────┘
                │ reads
                ▼
┌─────────────────────────────────────────────────────────────┐
│  Attributes                                                 │
│    #[Bit], #[Color], #[CssClass], #[Description],           │
│    #[Help], #[Icon], #[Label], #[Meta], #[Order]            │
└───────────────┬─────────────────────────────────────────────┘
                │ resolved by
                ▼
┌────────────────────────────────────────────────────────────────────┐
│  Support layer                                                     │
│    AttributesCache (per-process reflection memoisation)            │
│    AttributesOverrideResolver (config('enumerator.overrideeees'))  │
│    CasesCache, LayeredCache, EnumeratorRegistry                    │
│    CasesCollection, EnumExporter, Humanizer                        │
└───────────────┬────────────────────────────────────────────────────┘
                │ surfaced via
                ▼
┌─────────────────────────────────────────────────────────────┐
│  Surface APIs                                               │
│    Eloquent casts (AsEnum, AsBitmask, AsNullableEnum,       │
│      AsEnumeratorCollection)                                │
│    Validation rules (EnumValue/EnumName/EnumIn/EnumNotIn/   │
│      EnumTransition)                                        │
│    Blade components + directives                            │
│    Route binder                                             │
│    Artisan commands                                         │
│    Integrations (Filament, Livewire, Nova, Inertia)         │
└─────────────────────────────────────────────────────────────┘
```

## Override priority

For any attribute (color, icon, meta, etc.) the resolution order is:

1. `config('enumerator.overrides.{FQCN}.{CaseName}.{key}')` — config file.
2. `#[Attribute]` on the case declaration — PHP attribute.
3. `#[Attribute]` on the enum class (where applicable) — class-level
   attribute.
4. Sensible default (`null`, or humanised case name for `label`).

This keeps presets customisable without forking. `meta` is merged shallowly
(override wins on key conflict).

## Trait conflicts

`HasEnumeratorBehavior` (native) and `HasClassEnumBehavior` (class-const)
both declare `__call`/`__callStatic`. They are **mutually exclusive** by
design; pick one path per enum.

`HasMagicComparisons` and `HasInvokableCases` plug into the dispatcher in
`ResolvesMagicCalls` via `magicCompare()` / `__callStatic` respectively —
they do not declare `__call` themselves, so multiple opt-in concerns
co-exist without trait collision.

## Cache strategy

Reflection is the dominant cost. `AttributesCache` and `CasesCache` are
in-memory; combined with `LayeredCache` they can persist to a single PHP
file under `bootstrap/cache/enumerator.php`. The `layered` driver reads
memory first, file second, and writes both on miss — Octane- and
Swoole-friendly.

## Why no `HasMutableMetadata`?

Prior designs in `laranail/laranail` allowed mutating static metadata in
`boot()`. This leaks state across requests under Octane/Swoole and breaks
multi-tenant isolation. The package replaces it with the
`config('enumerator.overrides')` layer — declarative, request-isolated,
testable.

---

[← Docs index](../README.md#documentation)
