# Contributing a translation

`laranail/enumerator` ships with English translations only by design
(see [ADR-0003](../../.design/decisions/0003-translations-en-only-plus-community-scaffolding.md)
in the project's design record). Locale coverage grows through
community pull requests.

This recipe walks through adding a new locale.

## What ships today

The default locale lives at
[`lang/en/enumerator.php`](https://github.com/laranail/enumerator/blob/main/lang/en/enumerator.php).
As of v0.4.0 it carries four top-level keys:

- `validation` — strings used by the package's validation rules
  (`EnumValue`, `EnumName`, `EnumIn`, `EnumNotIn`, `EnumTransition`).
- `components` — placeholder / empty-state / aria-label strings used by
  the Blade components (`select`, `radio`, `grid`, `dropdown`).
- `aria` — `aria-label` templates for the rendered components.
- `commands` — output strings used by the Artisan commands.

A new locale should mirror the same key set, translated to the target
language.

### Strings inside `components.dropdown.*` (v0.4.0 additions)

The multi-select Alpine dropdown (PR-δ, v0.3.0) routes nine strings
through the translator namespace as of v0.4.0 (PR-ρ):

| Key | English | Notes |
|---|---|---|
| `search_placeholder` | `Search…` | search input `placeholder` |
| `search_label` | `Search options` | search input `aria-label` |
| `no_matches` | `No matches.` | empty-state row in the listbox |
| `clear_selection` | `Clear selection` | clear-button `aria-label` |
| `remove_value` | `Remove :label` | pill remove-button `aria-label` (Alpine substitutes `:label` at runtime — keep the placeholder verbatim) |
| `announce_added` | `Added :label` | screen-reader announcement when a pill is added (`:label` substituted at runtime) |
| `announce_removed` | `Removed :label` | same, on remove |
| `announce_selected` | `Selected :label` | single-select pick |
| `announce_cleared` | `Selection cleared` | clear-all |

The four `announce_*` keys feed the polite live region emitted when
`<x-...::dropdown :announce-changes="true" />`. The `:label`
placeholder MUST be preserved in any translation — it's split out in
PHP and concatenated with the runtime option label in JS. Leaving
trailing whitespace after stripping `:label` is fine; the helper
trims it.

## Step-by-step

### 1. Fork and clone

```bash
gh repo fork laranail/enumerator --clone
cd enumerator
```

### 2. Add the locale file

Copy `lang/en/enumerator.php` to your locale code (ISO 639-1 / 639-2,
optionally with a region):

```bash
cp lang/en/enumerator.php lang/{locale}/enumerator.php
# Examples:
#   lang/es/enumerator.php       (Spanish)
#   lang/pt_BR/enumerator.php    (Brazilian Portuguese)
#   lang/zh_CN/enumerator.php    (Simplified Chinese)
```

### 3. Translate each value

Only translate the **values** — leave the keys, the `:placeholder`
markers, and `declare(strict_types=1)` exactly as they are.

```php
<?php

declare(strict_types=1);

return [
    'validation' => [
        // English: 'The :attribute must be a valid :enum value.'
        'invalid_value' => 'Le :attribute doit être une valeur :enum valide.',
        // …
    ],
    // …
];
```

### 4. Verify the file parses

```bash
php -l lang/{locale}/enumerator.php
# Expected: "No syntax errors detected in lang/{locale}/enumerator.php"
```

### 5. (Optional) Add a smoke test

If you can, add a Pest test under
`tests/Feature/Translations/{Locale}TranslationTest.php` that asserts
your locale file loads and resolves the validation messages:

```php
it('loads the {locale} translations', function (): void {
    app()->setLocale('{locale}');
    expect(__('enumerator::validation.invalid_value', [
        'attribute' => 'status',
        'enum' => 'UserStatusEnum',
    ]))->not->toBe('enumerator::validation.invalid_value');
});
```

### 6. Run the local gates

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
vendor/bin/pest
```

All three should pass on your branch.

### 7. Open the PR

```bash
git checkout -b lang/add-{locale}
git add lang/{locale}/enumerator.php tests/Feature/Translations/
git commit -m "Add {locale} translation"
git push origin lang/add-{locale}
gh pr create
```

In the PR description, mention any keys you weren't sure about — the
maintainer or another speaker of the locale can review.

## What the maintainer will check

- File parses with `php -l`.
- Key set matches `lang/en/enumerator.php` exactly (no missing or
  extra keys).
- `:placeholder` markers preserved verbatim.
- No HTML / Blade syntax in the translations (translations are
  HTML-escaped by Laravel's `trans()`; embedding markup is a
  defense-in-depth concern).

## Updating an existing locale

The same flow — edit the file, run the gates, open a PR. The
maintainer will weigh your edits against the existing translation; a
native-speaker PR almost always wins over an upstream rewrite.

## Removing a locale

Translations are append-only. If a locale is genuinely broken (e.g.
machine-translated nonsense), open an issue rather than a deletion
PR — the maintainer would rather replace than remove.
