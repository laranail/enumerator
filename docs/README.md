# Documentation

This is the index for the `laranail/enumerator` documentation. The
[root README](../README.md) carries the package's tagline, quickstart,
and feature highlights; everything else lives under `docs/`.

## Getting started

- [Installation](installation.md)
- [Getting started](getting-started.md)
- [Configuration](configuration.md)
- [Architecture overview](architecture.md)
- [Release process](release.md)
- [Shipping checklist](shipping-checklist.md)
- [Comparison with other Laravel enum packages](comparison.md)

## Tools (one per public feature)

- [Alpine.js loader](tools/alpine-loader.md) — `<x-laranail-enumerator::alpine-loader />`, CDN-first + local fallback, for the Alpine-enhanced components
- [Attributes](tools/attributes.md) — `#[Label]`, `#[Color]`, `#[Icon]`, `#[Description]`, `#[Help]`, `#[Order]`, `#[Meta]`, `#[Bit]`, `#[CssClass]`
- [Bitmask](tools/bitmask.md) — `#[Bit]`-tagged enums, `Bitmask` value type, `AsBitmask` cast
- [Blade components](tools/blade-components.md) — `badge`, `select`, `radio`, `dropdown`, `checkboxes`, `grid`, `listing`, `element` × 5 frameworks
- [Cache keys](tools/cache-keys.md) — `IsCacheKey` trait + `Cacheable` contract for enum-as-cache-key pattern (v0.3.0)
- [Blade directives](tools/blade-directives.md) — `@enumeratorLabel`, `@enumeratorBadge`, `@enumeratorIs`, `@enumeratorIn`, …
- [Cases collection](tools/cases-collection.md) — `CasesCollection` (extends Laravel's `Collection`)
- [Eloquent casts](tools/casts.md) — `AsEnum`, `AsNullableEnum`, `AsEnumeratorCollection`, `AsBitmask`
- [Class-const API](tools/class-const-api.md) — `AbstractEnumeratorClass` for legacy / mixed-backing constants
- [Concerns](tools/concerns.md) — the trait set: `HasEnumerator`, `HasMagicComparisons`, `HasTransitions`, …
- [Contracts](tools/contracts.md) — `Enumerator`, `HtmlRenderable`, `Stateful`, `Translatable`, `Bitwise`, `TransitionHook`, `TranslatorAdapter`, `TenantContext`
- [Eloquent integration](tools/eloquent.md) — `HasEnumeratorScopes`, `HasEnumeratorStateMachine`
- [Exceptions](tools/exceptions.md)
- [Helpers](tools/helpers.md)
- [Invokable cases](tools/invokable-cases.md) — `Status::Active()` shorthand
- [Livewire transitions](tools/livewire-transitions.md) — `WithEnumTransitions` trait for Livewire 3 components (v0.3.0)
- [Magic comparisons](tools/magic-comparisons.md) — `$status->isActive()`, `->isNotBanned()`
- [`make:enumerator` command](tools/make-enumerator.md)
- [Presets](tools/presets.md) — 26 production-ready enums under `Presets\Enums\`
- [Route binding](tools/route-binding.md) — implicit binding for native + class-const enums
- [State machine](tools/state-machine.md)
- [Translations](tools/translations.md) — `TranslatorAdapter` resolution chain
- [Validation rules](tools/validation.md) — `EnumValue`, `EnumName`, `EnumIn`, `EnumNotIn`, `EnumTransition`
- [Views publishing](tools/views-publishing.md)

## Recipes (copy-paste, real-world)

- [API resources](recipes/api-resources.md)
- [Contributing a translation](recipes/contributing-translations.md)
- [Customizing presets](recipes/customizing-presets.md)
- [Factories (faker pattern)](recipes/factories.md)
- [Filament integration](recipes/filament.md)
- [Form requests](recipes/form-requests.md)
- [Inertia integration](recipes/inertia.md)
- [Livewire integration](recipes/livewire.md)
- [Notification channel example](recipes/notification-channel.md)
- [Nova integration](recipes/nova.md)
- [Tailwind config](recipes/tailwind-config.md)
- [Testing enums](recipes/testing-enums.md)
- [TypeScript export](recipes/typescript-export.md)

## Reporting issues / contributing

- Open an issue at <https://github.com/laranail/enumerator/issues>
- Security disclosures: see [SECURITY.md](../SECURITY.md)
- Contributing guidelines: see [CONTRIBUTING.md](../CONTRIBUTING.md)
