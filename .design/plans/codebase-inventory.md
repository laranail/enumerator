# Codebase inventory — laranail/enumerator

Last refreshed 2026-05-15 against HEAD `e6fb99d`. Numbers re-derive
from `find src tests -name '*.php' | wc -l` (current count:
**152 source files** + **94 test files**).

## Top-level tree (excl. `vendor/`, `.git/`, build caches)

```
.
├── .design/                        # Audit + design artifacts (this folder)
│   ├── plans/                      # Live planning + handoff (gitignored)
│   └── references/                 # Botble + Tusente reference slices (gitignored)
├── .editorconfig                   # UTF-8, LF, trim trailing ws
├── .github/                        # CI / templates / dependabot
├── .gitignore                      # Stale: still references /plans/, not .design/plans/
├── CHANGELOG.md                    # Keep-a-Changelog; [0.1.0] only
├── CODE_OF_CONDUCT.md              # Contributor Covenant
├── composer.json                   # Package manifest (PHP ^8.3, Laravel ^13)
├── composer.lock                   # Committed despite global rule (CLAUDE.md says no)
├── config/enumerator.php           # Runtime config
├── CONTRIBUTING.md                 # Contributing guide
├── database/migrations/            # 1 migration: enumerator_state_history table
├── docs/                           # 35 markdown files (tools/, recipes/, top-level)
├── extension.neon                  # PHPStan custom extension include
├── LICENSE                         # MIT, Copyright 2026 Simtabi LLC
├── lang/en/enumerator.php          # Default translations (1 locale shipped)
├── phpstan-baseline.neon           # 393 KB baseline
├── phpstan.neon                    # level: max
├── phpunit.xml                     # Pest config
├── pint.json                       # Code style
├── README.md                       # Public surface manual (has stale stat at line 53)
├── resources/                      # Stubs + Blade view bundles
├── scripts/                        # 7 scaffolding scripts (likely one-time)
├── SECURITY.md                     # Disclosure address
├── src/                            # Source (see breakdown below)
└── tests/                          # Pest test suite
```

## `src/` — 152 PHP files, 42 directories

```
src/
├── AbstractEnumeratorClass.php             # Class-const fallback (D49)
├── EnumeratorServiceProvider.php           # Boot order, module registration
├── Attributes/                             # #[Bit] #[Color] #[CssClass] #[Description] #[Help] #[Icon] #[Label] #[Meta] #[Order] (9 attrs)
├── Blade/
│   ├── BladeDirectives.php                 # @enumeratorLabel / @enumeratorBadge / @enumeratorIs / @enumeratorIn / case-insensitive aliases
│   └── Components/                         # 7 components: Badge, Checkboxes, Dropdown, Element, Grid, Listing, Radio, Select
│       └── Concerns/                       # GroupsCases, RoutesToFrameworkView (covered batch 13)
├── Casts/                                  # AsBitmask, AsEnum, AsEnumeratorCollection, AsNullableEnum (4 casts)
├── Concerns/
│   ├── Core/BehaviorCore.php               # PR-1 extraction: bundles 7 always-on behaviour traits
│   ├── HasAttributes.php                   # Attribute reader
│   ├── HasBitmask.php                      # Bitmask-aware mixin
│   ├── HasClassEnumBehavior.php            # AbstractEnumeratorClass mixin; #[Label]/#[Color]/render (batch 12)
│   ├── HasEnumAttributes.php               # Consumer-side trait (D35: mergeCasts)
│   ├── HasEnumerator.php                   # Umbrella trait (D34/D54: incl. HasInvokableCases)
│   ├── HasEnumeratorBehavior.php           # Now a thin wrapper around BehaviorCore (PR-1)
│   ├── HasEquality.php                     # is() / equals() / not()
│   ├── HasFromHelpers.php                  # from(name) / fromValue / tryFromName variants (batch 10)
│   ├── HasGrouping.php                     # Declarative case groups
│   ├── HasInvokableCases.php               # Status::Active() shorthand (D34)
│   ├── HasLifecycle.php                    # next/previous/isFirst/isLast
│   ├── HasMagicComparisons.php             # isFoo()/isNotFoo() (batch 11+13)
│   ├── HasOrder.php                        # Order attribute reader
│   ├── HasTransitions.php                  # State machine (batch 8)
│   ├── IsJsonable.php                      # JsonSerializable
│   ├── IsTranslatable.php                  # Lang lookup
│   ├── RendersHtml.php                     # toHtml() with sprintf+e() escape (batch 10)
│   └── ResolvesMagicCalls.php              # Dispatches to magicCallHandlers (extensibility win, b3ab84d)
├── Console/                                # 6 commands: AnnotateEnumerator, CacheClearEnumerator, CacheEnumerator, ExportEnumerator, IdeHelper, MakeEnumerator
├── Contracts/                              # 8 contracts (Enumerator, HtmlRenderable, Stateful, Translatable, Bitwise, TransitionHook, TranslatorAdapter, TenantContext)
├── DynamicEnums/DatabaseBackedEnum.php     # Runtime case set (D49)
├── Eloquent/
│   ├── EnumeratorStateHistory.php          # Audit-trail model with explicit $fillable
│   ├── HasEnumeratorScopes.php             # whereEnumIn/whereEnumMeta/whereEnumBitMatches
│   └── HasEnumeratorStateMachine.php       # Save-time transition enforcement + history write
├── Exceptions/                             # Package-base exception + concrete subclasses (low coverage)
├── Facades/                                # Laravel facade(s) over the registry
├── Helpers/Bitmask.php                     # Immutable bitmask value type
├── Integrations/                           # PHPStan-excluded; vendor-soft
│   ├── Filament/                           # Columns/Components/Filters/Forms/Infolists (6 files)
│   ├── Inertia/EnumeratorTransformer.php
│   ├── Livewire/EnumeratorCasts.php
│   └── Nova/                               # EnumeratorField, EnumeratorFilter
├── Modules/                                # 8 modules, each config-gated (D37)
│   ├── GraphQL/                            # SchemaExporter + provider
│   ├── Lighthouse/                         # EnumScalar + provider
│   ├── Octane/                             # WarmCachesListener + provider
│   ├── OpenApi/                            # OpenApiSchemaExporter + provider
│   ├── Pest/                               # Expectations + provider
│   ├── Saloon/                             # EnumCaster + provider
│   └── StructuredOutput/                   # OpenAi, Anthropic, Mcp emitters + provider
├── PHPStan/                                # Custom dynamic-method extension (extension.neon)
├── Presets/Enums/                          # 26 production-ready enums (D34 documents)
├── Rector/                                 # BenSampo + Spatie migration codemods (D46)
│   └── Sets/MigrationSet.php
├── Routing/                                # EnumeratorRouteBinder
├── Rules/                                  # 5 validation rules (EnumValue/EnumName/EnumIn/EnumNotIn/EnumTransition)
├── Support/                                # AttributesOverrideResolver (D50), EnumExporter, EnumeratorRegistry, IsEnumeratorClass, LayeredCache, NullTenantContext, OptionsArrayBuilder, ReflectionCachePersistor
└── Translations/                           # LaravelTranslatorAdapter (default), DatabaseTranslatorAdapter (reference)
```

## `tests/` — 94 files, 30 directories

```
tests/
├── Application/                            # Testbench application skeleton + migration
├── Feature/                                # Behaviour tests (PHPStan covers, with 2 file exclusions)
│   ├── BladeDirectivesTest.php
│   ├── ComponentRenderingTest.php
│   ├── Eloquent/                           # HasEnumeratorScopesTest + StateMachineModelTest (both phpstan-excluded)
│   └── StateMachineTest.php
├── Pest.php                                # Pest config + custom expectations
├── TestCase.php                            # Testbench bootstrap (sets memory cache + 'plain' css)
├── Unit/                                   # PHPStan-excluded; covers Casts / Rules / Translators / etc.
└── Fixtures/                               # Test fixtures (e.g. CasingAmbiguousEnum from batch 13)
```

## `docs/` — 38 markdown files

- Top-level: `architecture.md`, `configuration.md`, `getting-started.md`,
  `installation.md`, `release.md`, `shipping-checklist.md` (6 files)
- `docs/tools/` — 21 files (one per public tool/feature)
- `docs/recipes/` — 11 files (one per copy-paste recipe)
- **Missing: `docs/README.md`** (the index file the global Simtabi
  scaffolding standard specifies, and that the audit task brief lists
  under `docs/` requirements)

## `resources/`

- `stubs/` — 5 stubs (`attributes`, `backed`, `bitmask`, `pure`,
  `state-machine`) matching `make:enumerator --stub=` options.
- `views/components/` — 6 directories: `_base` (8 base views) +
  5 frameworks (each 8 views: badge / checkboxes / dropdown /
  element / grid / listing / radio / select).
- **Naming drift:** `_base/list.blade.php` vs. per-framework
  `listing.blade.php` — flagged as issue I-3.

## `scripts/`

7 shell + python scaffolding scripts:

| File | Likely purpose |
|---|---|
| `refactor-facades.py` | One-time migration to Laravel facades |
| `scaffold.sh` | Bootstrap empty package skeleton |
| `scaffold-docs.sh` | Generate docs/ tree |
| `scaffold-framework-variants.sh` | Generate per-framework view variants |
| `scaffold-framework-views.sh` | Generate `_base` views |
| `scaffold-presets.sh` | Generate the 26 preset enum files |
| `scaffold-step-2.sh` | Second-pass scaffolding |

All were used during initial scaffolding; none referenced in CI or
documented. **Cleanup candidates** — propose moving into a
`.design/scaffold-history/` archive (not deleting outright; see
issue I-11).

## `.github/` — workflows and templates

- `workflows/ci.yml` — PHP 8.3 / 8.4 / 8.5 matrix, Pint + PHPStan + Pest
  with `--min=80`
- `workflows/release.yml` — release process
- `workflows/static-analysis.yml` — (separate from ci.yml; intentional?
  flagged as I-12)
- `dependabot.yml` — weekly schedule
- `FUNDING.yml` — sponsor config
- `ISSUE_TEMPLATE/` — `bug_report.yml`, `feature_request.yml`,
  `config.yml`
- `PULL_REQUEST_TEMPLATE.md`

[VERIFIED 2026-05-15: find src -name '*.php' | wc -l → 152]
[VERIFIED 2026-05-15: find tests -name '*.php' | wc -l → 94]
[VERIFIED 2026-05-15: find docs -name '*.md' | wc -l → 38]
[VERIFIED 2026-05-15: find src -type d | wc -l → 42]
[VERIFIED 2026-05-15: find tests -type d | wc -l → 30]
