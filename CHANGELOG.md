# Changelog

All notable changes to `laranail/enumerator` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-08-15

### Removed

- **The generic command aliases.** `make:enumerator`, `enumerator:cache`, `enumerator:cache:clear`,
  `enumerator:annotate`, `enumerator:export` and `enumerator:ide-helper` are gone; the
  `laranail::enumerator.*` names they shadowed are the only ones now.

  Artisan's registry is a flat map, so those aliases handed back exactly the collision the
  namespaced names exist to prevent — and made the convention decorative, since the short name is
  the one anybody would actually type. `make:enumerator` was the worst of them: it colonised
  Laravel's own `make:` namespace, where a future framework generator or another package would
  simply have replaced it.

### Changed

- **The config key is `laranail.enumerator`,** published to `config/laranail/enumerator.php`. Every
  read moves with it — `config('enumerator.cache.driver')` is now
  `config('laranail.enumerator.cache.driver')`. Laravel's config repository is a flat map and
  `enumerator` is a name an application could plausibly use for its own file.

- **Publish tags are vendor-scoped:** `enumerator-config` → `laranail::enumerator-config`, and the
  same for `-lang`, `-views`, `-stubs`, `-migrations`, `-js` and `-presets`.

### Fixed

- **`SupportsNamespacedNames` read `$commandAliases` without declaring it.** Any command that used
  the trait without declaring the property died with `Undefined property` the moment Laravel built
  it — a trait that requires an undeclared property of its user is a trap, and the failure lands at
  boot rather than at the call site. It is declared on the trait now, defaulting to empty; a command
  wanting aliases still declares its own list and overrides it.

- **Published translations went to the wrong directory.** The namespace registered is
  `laranail-enumerator` but the publish target was `lang/vendor/enumerator`, and Laravel looks for
  overrides under `lang/vendor/{namespace}` — so every published translation was silently ignored
  while the packaged default kept answering. Stubs moved the same way, to
  `resources/stubs/laranail-enumerator`.

- Guarded by `tests/Feature/NamingConventionTest.php`, which reads the console kernel, the config
  repository, `Lang::getLoader()->namespaces()`, the view finder and
  `ServiceProvider::publishableGroups()` on a booted app.

### Added

- **`Presets\Enums\AlertTypeEnum`** — the twenty-seventh preset: the styling of a
  flash message or inline notice (`default`, `primary`, `success`, `info`,
  `warning`, `error`, `mono`).

  Distinct from `SeverityEnum`, which classifies a log record on the RFC 5424
  scale — `Primary` and `Mono` are not severities and nothing here maps to a
  syslog level.

  Migrated from a class-constant enum in a consuming application, where a public
  `Request::alert($message, string $type)` macro passed arbitrary strings to
  `new AlertType($type)`. Its `icon()` was a `match` with no default arm, so any
  value outside the seven constants threw `UnhandledMatchError` from a helper
  whose only job was showing the user a message. As a native enum that failure
  mode does not exist rather than being patched.

  `color()` and `->value` deliberately differ for two cases: the backing values
  are the CSS tokens a front end already uses (`default`, `mono`), while
  `color()` answers with this package's palette (`secondary`, `ghost`).

- **`Rector\RectorClassConstEnumToEnumerator`** — a third migration codemod, for
  the class-constant enum base an application invented for itself rather than a
  named vendor package. Configurable with the base classes to match, and it does
  nothing until told.

  Unlike the other two rules it rebases onto `AbstractEnumeratorClass` rather
  than flipping to a native enum, because a class-constant enum's cases **are**
  string constants and its methods are full of
  `match ($this->value) { self::ACTIVE => … }`. Promoting `self::ACTIVE` to an
  enum case would leave the class compiling, the suite running, and every one of
  those comparisons silently falling to its default.

  It rewrites `extends`, turns `$this->value` into `$this->getValue()` — not
  cosmetic, since `$value` is private on the new base, so a missed one is a fatal
  error rather than a type error — renames `from`/`of` → `fromValue` and
  `tryFrom` → `tryFromValue`, and drops `$langPath` rather than guessing a
  translation mapping it cannot see.

- **Translation support on the class-constant path.** `HasClassEnumBehavior`
  now composes `IsTranslatable`, so an `AbstractEnumeratorClass` subclass
  implementing `Contracts\Translatable` resolves `label()`, `description()`,
  `help()` and `placeholder()` through the translator.

  This closes a gap that bit exactly the enums the class-const path exists for.
  `AbstractEnumeratorClass` is the documented migration target for legacy
  class-constant enums, and those are precisely the ones already carrying a
  translation namespace — but only native enums got `IsTranslatable`, through
  `BehaviorCore`. Migrating onto the class-const path therefore downgraded every
  translated label to a humanised case name: nothing errored, the strings just
  changed and the existing lang files went quietly unused.

  `IsTranslatable`'s `caseKey()` and `caseName()` were written for native enums
  and read `->value` and `->name` as properties. On a class-const enum the
  backing property is private, so those were undefined-property reads; both now
  dispatch on the shape and use `getValue()`/`getKey()`.

  An enum with no translations registered is unaffected — the fallback chain
  still ends at the `#[Label]` attribute and then the humaniser.

### Fixed

- **`HasOrder::compareTo()` sorted an unorderable case first instead of last.**
  It read a foreign case's position as `(int) $other->getOrder()`, and
  `(int) null` is 0 — so an enum whose `getOrder()` could not answer sorted
  before everything, the opposite of the documented default that a case with no
  order sorts last (`PHP_INT_MAX`). It now narrows with `is_int()` and falls
  back to the `#[Order]` attribute.

  `method_exists()` proves the method is callable and says nothing about what it
  returns, which is also why this showed up as 25 identical `cast.int` entries
  in the PHPStan baseline — one per enum using the trait. Fixing the trait
  cleared all of them.

### Changed

- **`rector/rector` moved from `suggest` to `require-dev`.** The migration rules
  were previously unrunnable in this package's own suite, which is why the two
  existing ones assert their shape rather than their output. The new rule is
  covered by a test that runs the codemod through the Rector binary over a
  fixture and reads the transformed file.

Initial public release.
