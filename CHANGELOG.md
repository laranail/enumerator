# Changelog

All notable changes to `laranail/enumerator` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

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

### Changed

- **`rector/rector` moved from `suggest` to `require-dev`.** The migration rules
  were previously unrunnable in this package's own suite, which is why the two
  existing ones assert their shape rather than their output. The new rule is
  covered by a test that runs the codemod through the Rector binary over a
  fixture and reads the transformed file.

## [0.1.0] - 2026-07-11

Initial public release.
