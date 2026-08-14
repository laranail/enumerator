# Comparison with other Laravel enum packages

`laranail/enumerator` is one of several packages in the Laravel
enum space. This page is an honest comparison — where it overlaps
existing options, where it differs, and where another package may
suit you better.

Surveyed 2026-05-15.

## At a glance

| Package | Latest | Focus | Pick it when |
|---|---|---|---|
| `bensampo/laravel-enum` | v6.14 | Pre-native-enum compatibility layer | Maintaining a legacy app still on PHP < 8.1 (note: the package's own author no longer recommends it for new projects) |
| `archtechx/enums` | v1.1 | Small, lovely trait composition | You want **only** a few helpers (`Names`, `Values`, `Options`, `Comparable`, `From`) and zero Laravel coupling |
| `spatie/laravel-enum` | v3.3 | Wrapper around `spatie/enum` | You're already in the Spatie ecosystem and have other Spatie packages installed |
| `henzeb/enumhancer` | v3.2 | Framework-agnostic Swiss Army knife | You're outside Laravel and can accept **AGPL-3.0** |
| `cerbero/laravel-enum` | v2.2 | Cache / session encapsulation + magic translation | You specifically want enums as cache-key or session-key namespaces |
| **`laranail/enumerator`** | (this) | **Integration-rich Laravel enum toolkit** | You're on Laravel 13 + PHP 8.3 and want a single dep that covers attributes, casts, validation, Blade, Filament, Nova, Livewire, Inertia, state machines, bitmasks |

## Feature matrix

`+` = shipped today, `·` = absent, `≈` = partial / configured but limited.

| Feature | bensampo | spatie | archtechx | henzeb | cerbero | **laranail/enumerator** |
|---|:-:|:-:|:-:|:-:|:-:|:-:|
| Native PHP 8.1+ enums first-class | · | ≈ | + | + | + | **+** |
| Class-const fallback for legacy | · | · | · | · | · | **+** |
| Attribute-driven label / color / icon | · | · | + | + | · | **+** (9 attributes) |
| Eloquent cast | + | + | · | + | · | **+** (4 casts) |
| Validation rule(s) | + | + | · | + | · | **+** (5 rules) |
| Blade directives | · | · | · | + | · | **+** (11) |
| Blade components | · | · | · | · | · | **+** (8 × 5 framework variants) |
| Translations | + | + | · | + | + magic | **+** (pluggable adapter) |
| Filament integration | · | · | · | · | · | **+** |
| Livewire integration | · | + | · | · | · | **+** |
| Nova integration | + | · | · | · | · | **+** |
| Inertia integration | · | · | · | · | · | **+** |
| Bitmask / flagged | + | · | · | + | · | **+** |
| State machine + history | · | · | · | ≈ state | · | **+** |
| Route binding | + | + | · | + | · | **+** |
| Cache encapsulation | · | · | · | · | **+** | · |
| Session encapsulation | · | · | · | · | **+** | · |
| TypeScript export | · | · | · | · | + | **+** |
| OpenAPI 3.1 schema export | · | · | · | · | · | **+** |
| GraphQL schema export | · | · | · | · | · | **+** |
| OpenAI / Anthropic / MCP JSON-Schema | · | · | · | · | · | **+** |
| Saloon caster | · | · | · | · | · | **+** |
| Octane warmup | · | · | · | · | · | **+** |
| Per-tenant overrides | · | · | · | · | · | **+** |
| Faker provider | · | + | · | · | · | (recipe in `docs/recipes/factories.md`) |
| Make / annotate / IDE-helper command | + | + | · | + | + | **+** (6 commands) |
| Rector migration codemod | · | · | · | · | · | **+** (BenSampo + Spatie) |
| Pest expectations | · | · | · | · | · | **+** |
| Magic comparisons (`isActive()`) | + | + | · | + | · | **+** (ambiguity policy configurable) |
| Invokable cases (`Status::Active()`) | · | · | + | + | · | **+** |
| Preset enum library | · | · | · | · | · | **+** (27 presets) |

## Where `laranail/enumerator` does NOT win

Three honest concessions:

- **Cache / session encapsulation** (`cerbero/laravel-enum`). If you
  want `MyCacheKey::User->put($value)` syntax, `cerbero/laravel-enum`
  does this directly; `laranail/enumerator` doesn't.
- **Smallest surface area** (`archtechx/enums`). If you want just a
  handful of native-enum helpers and **zero** Laravel coupling,
  `archtechx/enums` is lovelier.
- **Pre-PHP-8.1 reach** (`bensampo/laravel-enum`). If you're still on
  PHP 8.0 (~5% of projects per recent Packagist stats),
  `laranail/enumerator` won't load. Use `bensampo` for that legacy
  path, but plan a PHP upgrade.

## Migration codemods

If you're currently on `bensampo/laravel-enum`, `spatie/laravel-enum`,
or a class-constant enum base your own application invented, three
Rector rules ship in `src/Rector/`:

```php
use Rector\Config\RectorConfig;
use Simtabi\Laranail\Enumerator\Rector\Sets\MigrationSet;

return RectorConfig::configure()->sets(MigrationSet::rules());
```

```bash
composer require rector/rector --dev
vendor/bin/rector process app/Enums --dry-run
vendor/bin/rector process app/Enums
```

- `RectorBenSampoEnumToEnumerator` does a full class → enum transform
  (detects the backing type, copies constants → cases, adds
  `use HasEnumerator`).
- `RectorSpatieEnumToEnumerator` produces a structural skeleton; case
  bodies need a manual fill-in because Spatie's docblock-driven case
  pattern can't be auto-mapped reliably.
- `RectorClassConstEnumToEnumerator` rebases a bespoke class-constant
  base onto `AbstractEnumeratorClass`. It is **configurable** and does
  nothing until told which base classes to match, so pass it through
  `withConfiguredRule()` rather than relying on the set:

  ```php
  use Rector\Config\RectorConfig;
  use Simtabi\Laranail\Enumerator\Rector\RectorClassConstEnumToEnumerator;

  return RectorConfig::configure()
      ->withPaths([__DIR__ . '/app/Enums'])
      ->withConfiguredRule(RectorClassConstEnumToEnumerator::class, [
          'Acme\\Enum\\Enum',
          'Acme\\Enum\\StatusEnum',
      ]);
  ```

  It rewrites `extends`, turns every `$this->value` into
  `$this->getValue()`, renames `from`/`of` → `fromValue` and `tryFrom`
  → `tryFromValue`, and drops `$langPath`.

### Why that third rule targets a class, not a native enum

The other two flip their source into a native enum, because that is what
those libraries were emulating. A class-constant enum is different: its
cases **are** string constants, and its methods are full of
`match ($this->value) { self::ACTIVE => … }`. Promote `self::ACTIVE`
from a string constant to an enum case and every one of those
comparisons silently stops matching — the class still compiles, the
suite still runs, and every `match` quietly falls to its default.

So the rule rebases onto `AbstractEnumeratorClass` and leaves the
constants alone. Going on to a native enum afterwards is a separate,
deliberate step with its own diff.

The `$this->value` rewrite is the part that is not optional:
`AbstractEnumeratorClass` holds `$value` **private**, so a missed one is
a fatal error the first time that branch runs rather than something
static analysis catches.

Two things the rule leaves for you:

- **`$langPath` is dropped, not translated.** It pointed at a namespace
  whose keys the rule cannot see; enumerator resolves labels through
  `#[Label]` attributes plus `Concerns\IsTranslatable`. A guessed label
  would be wrong rather than absent, and absent is visible — `label()`
  falls back to humanising the case name.
- **Model casts are untouched.** A legacy base usually implements
  `Castable` on the enum itself; enumerator uses `Casts\AsEnum` on the
  model. That edit belongs where the model is. `AsEnum` persists
  `getValue()`, i.e. the same constant value the old cast stored, so the
  column contents do not change.

## When NOT to use this package

Be honest about scope:

- **You only need 1-2 features.** `archtechx/enums` is a smaller
  install for `Names` + `Values` + `Options` alone.
- **You can't accept Laravel as a dep.** All of `laranail/enumerator`
  presupposes Laravel ≥ 13. The pure native-enum cases work in any
  PHP context, but the package's value-add doesn't.
- **You want AGPL.** This package is MIT. If you want the
  enumhancer feature set under AGPL, pick `henzeb/enumhancer`.
- **You want a community-maintained one.** This package is currently
  maintained by Simtabi LLC; community PRs are welcome but the
  release cadence is one-org-driven, not a foundation.

## Sources

- [spatie/laravel-enum on Packagist](https://packagist.org/packages/spatie/laravel-enum)
- [bensampo/laravel-enum on Packagist](https://packagist.org/packages/bensampo/laravel-enum)
- [archtechx/enums on Packagist](https://packagist.org/packages/archtechx/enums)
- [henzeb/enumhancer on Packagist](https://packagist.org/packages/henzeb/enumhancer)
- [cerbero/laravel-enum on Packagist](https://packagist.org/packages/cerbero/laravel-enum)
- [Enum Helpers for PHP — Laravel News](https://laravel-news.com/enum-helpers-for-php)

---

[← Docs index](../README.md#documentation)
