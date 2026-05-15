# Enum-package landscape — 2026-05-15

Survey of the top published Laravel enum packages plus the two internal
references. Numbers and dates verified by direct WebFetch on the dates
recorded below; counts age fast.

## Published packages

| Package | Latest | Released | License | PHP | Laravel | Downloads | ⭐ | Verdict |
|---|---|---|---|---|---|---:|---:|---|
| `spatie/laravel-enum` | 3.3.0 | 2026-03-18 | MIT | ^8.2 | 11/12/13 | 5.59M | 364 | Wrapper around `spatie/enum`; predates native enums |
| `bensampo/laravel-enum` | 6.14.0 | 2026-03-29 | MIT | ≥8.0 | 9..13 | 16.4M | 2029 | **Author actively discourages new use** in favour of native PHP 8.1 enums |
| `archtechx/enums` | 1.1.2 | 2025-06-06 | MIT | ^8.1 | (any) | 11.2M | 567 | Pure-trait composition for native enums; 7 small traits |
| `henzeb/enumhancer` | 3.2.1 | 2026-01-05 | **AGPL-3.0** | ^8.1 | (any) | 328K | 69 | Wide feature set but **AGPL — cannot lift code, only inspiration** |
| `cerbero/laravel-enum` | 2.2.0 | 2025-07-05 | MIT | ^8.1 | ≥9.0 | 94K | 189 | Cache/Session encapsulation, magic translation, TypeScript sync |

Sources:

- <https://packagist.org/packages/spatie/laravel-enum>
- <https://packagist.org/packages/bensampo/laravel-enum>
- <https://packagist.org/packages/archtechx/enums>
- <https://packagist.org/packages/henzeb/enumhancer>
- <https://packagist.org/packages/cerbero/laravel-enum>
- <https://laravel-news.com/enum-helpers-for-php> (2025 archtechx coverage)
- <https://livewire.laravel.com/screencast/forms/enums> (paywalled — only the page chrome was readable)

## Reference codebases

### Reference A — Botble CMS (`platform/core/base/src/Supports/Enum.php`)

Class-based, pre-PHP-8.1 enum implementation extending Eloquent's
`CastsAttributes`. The enum **is** the cast. Key choices:

- `__callStatic` returns `(new static())->make($value)` — instances are
  short-lived value objects, not singletons.
- Translation key pattern: `{$langPath}.{value}` (e.g.
  `core/base::enums.statuses.published`).
- HTML rendering goes through `apply_filters(BASE_FILTER_ENUM_HTML, ...)`
  — **WordPress-style global filter hooks**. Wide adoption in Botble
  but a poor fit outside CMSes.
- Logs (rather than throws) on invalid values (`Log::error(...)`).
- Static class-level cache keyed by FQCN.
- Concrete `BaseStatusEnum` overrides `toHtml()` with a `match` per
  case → CSS class.
- `EnumColumn` (in `core/table/src/Columns/EnumColumn.php`) is a
  datatable integration that calls `Status::OF($value)->toHtml()` per
  row.

Translation tree ships **~40 locales** (`core/base/resources/lang/{ar,
cs,da,de,en,...}/enums.php`). Each locale carries the same key set.

### Reference B — Tusente Enum (`platform/modules/enum/`)

Modern native-enum approach, MIT-licensed (composer.json: `tusente/enum`).
Smaller surface than laranail/enumerator. Key choices:

- Three contracts: `EnumContract`, `HtmlRenderable`, `Translatable`.
- Two traits: `EnumBehavior`, `HtmlRendering`.
- One cast: `EnumCast` (parametric — `EnumCast:UserStatus::class`).
- Four Blade components: `badge`, `select`, `radio`, `grid` (one
  framework only — DaisyUI).
- Three Blade directives: `@enum`, `@enumBadge`, `@enumValue`.
- Translation pattern: `namespace::file.enum_group.value`
  (e.g. `enum::enum.statuses.active`).
- Explicit recommendation in `SUMMARY.md`: "Keep both systems —
  Core Module for complex, Tusente Enum for simple." Tusente
  intentionally does NOT try to be a one-stop solution.
- `Enum.php` (the abstract class-based variant) coexists with the
  native-enum trait-based variant; Tusente keeps both paths open.

### Native PHP `BackedEnum` / `UnitEnum` (PHP 8.1+ baseline)

- `cases()`, `from($value)`, `tryFrom($value)` are core.
- Native casting: `protected $casts = ['status' => Status::class]`
  works since Laravel 9.
- `enum` is final, so behaviour is added via traits.
- Attributes are read via `ReflectionEnumUnitCase::getAttributes()`.
- No built-in label / translation / colour / icon support — every
  third-party package fills that gap.

## Feature matrix

A `+` means "shipped today"; `·` means "absent"; `≈` means "partial /
configured but limited". Sourced from each project's README + my reads
of the reference src trees.

| Feature | bensampo | spatie | archtechx | henzeb | cerbero | Botble | Tusente | **laranail/enumerator (v0.1.0)** |
|---|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| Native PHP 8.1+ enums first-class | · | ≈ | + | + | + | · | + | **+** |
| Class-const fallback for legacy | · | · | · | · | · | + | + | **+** (`AbstractEnumeratorClass`) |
| Attribute-driven label/color/icon | · | · | + | + | · | · | · | **+** (9 attributes) |
| Eloquent cast | + | + | · | + | · | + | + | **+** (4 casts) |
| Validation rule(s) | + | + | · | + | · | · | + | **+** (5 rules) |
| Blade directives | · | · | · | + | · | · | + (3) | **+** (11) |
| Blade components | · | · | · | · | · | · | + (4) | **+** (8 × 5 frameworks) |
| Translations | + | + | · | + | + magic | + | + | **+** (pluggable adapter) |
| Filament integration | · | · | · | · | · | · | · | **+** |
| Livewire integration | · | + | · | · | · | · | · | **+** (cast helpers) |
| Nova integration | + | · | · | · | · | · | · | **+** |
| Inertia integration | · | · | · | · | · | · | · | **+** (transformer) |
| Bitmask / flagged | + | · | · | + | · | · | · | **+** (`AsBitmask` + `#[Bit]`) |
| State machine + history | · | · | · | + state | · | · | · | **+** |
| Route binding | + | + | · | + | · | · | · | **+** |
| Cache encapsulation | · | · | · | · | + | · | · | · (Phase 2 candidate) |
| Session encapsulation | · | · | · | · | + | · | · | · (Phase 2 candidate) |
| TypeScript export | · | · | · | · | + | · | · | **+** (`enumerator:export --ts`) |
| OpenAPI 3.1 schema export | · | · | · | · | · | · | · | **+** (`Modules\OpenApi`) |
| GraphQL schema export | · | · | · | · | · | · | · | **+** (`Modules\GraphQL` + `Lighthouse`) |
| OpenAI / Anthropic / MCP JSON-Schema | · | · | · | · | · | · | · | **+** (`Modules\StructuredOutput`) |
| Saloon (HTTP client) caster | · | · | · | · | · | · | · | **+** (`Modules\Saloon`) |
| Octane warmup | · | · | · | · | · | · | · | **+** (`Modules\Octane`) |
| Per-tenant overrides | · | · | · | · | · | · | · | **+** (`TenantContext` + resolver) |
| Reflection cache (in-mem + file) | · | · | · | ≈ | · | ≈ | · | **+** (`LayeredCache`) |
| Faker provider | · | + | · | · | · | · | · | · |
| Make / annotate / IDE-helper command | + | + | · | + | + | · | · | **+** (6 commands) |
| Rector migration codemod | · | · | · | · | · | · | · | **+** (BenSampo + Spatie) |
| Pest expectations | · | · | · | · | · | · | · | **+** (`Modules\Pest`) |
| Magic comparisons (`isActive()`) | + via `static::is*` | + | · | + | · | · | · | **+** (with case-insensitive + ambiguity policy) |
| Invokable cases (`Status::Active()`) | · | · | + | + | · | + via `__callStatic` | + via `__callStatic` | **+** |
| Preset enum library | · | · | · | · | · | + (concrete) | + (3 examples) | **+** (26 presets) |

## Reading the matrix

laranail/enumerator already covers **every column** in the surveyed
landscape except:

1. **Cache encapsulation** (cerbero only) — define cache keys as enum
   cases with `->put()` / `->get()` / `->forget()` proxying through
   Laravel's `Cache` facade.
2. **Session encapsulation** (cerbero only) — same pattern, for
   sessions.
3. **Faker provider** (spatie only) — `$faker->status()` returning a
   random case. Trivial; can be supplied by docs ("here is a recipe").

Plus the v0.2.0 deltas the user authorised in Phase-2 pre-flight:

4. **Livewire-first Blade components** — no other package ships
   reactive components. Tusente has Blade-only; spatie has property
   casting hooks but no UI.
5. **Alpine-enhanced Dropdown / Select** — searchable, clearable,
   keyboard-navigable from a single `<x-...::dropdown>` tag.

Plus the documentation gaps from Phase 1:

6. `docs/README.md` index (Simtabi scaffolding standard) — I-5.
7. Coverage-gate alignment — I-2.
8. `[Unreleased]` CHANGELOG block — I-10.

## "Godfather" framing — honest assessment

The "godfather of Laravel enum packages" claim is **defensible** for
v0.1.0 IF and only if we honestly acknowledge:

- We **do not** displace the published landscape's deepest single-
  axis packages: `archtechx/enums` is still a smaller, lovelier
  pure-trait library; `henzeb/enumhancer` (AGPL) is broader on certain
  axes but its license excludes commercial-permissive adoption.
- The breadth is real (every column covered) but **breadth without
  depth in any one area is fragile** — particularly the Livewire,
  Filament, and Nova integration files were not exercised against
  vendor dependencies during initial CI (they're PHPStan-excluded
  per `phpstan.neon:14`).
- bensampo retiring its own package in favour of native enums was the
  most-honest move in this space. We should adopt that humility:
  laranail/enumerator's job is not to "replace native enums" but to
  add the missing Laravel-aware veneer.

A more honest tagline: **"the integration-rich Laravel enum toolkit"**
rather than "godfather." Or stick with "godfather" but back it with the
matrix in `docs/comparison.md` (Phase 5 deliverable).

[VERIFIED 2026-05-15 via WebFetch: spatie/laravel-enum v3.3.0 / 2026-03-18]
[VERIFIED 2026-05-15 via WebFetch: bensampo/laravel-enum v6.14.0 / 2026-03-29]
[VERIFIED 2026-05-15 via WebFetch: archtechx/enums v1.1.2 / 2025-06-06]
[VERIFIED 2026-05-15 via WebFetch: henzeb/enumhancer v3.2.1 / 2026-01-05 AGPL-3.0]
[VERIFIED 2026-05-15 via WebFetch: cerbero/laravel-enum v2.2.0 / 2025-07-05]
