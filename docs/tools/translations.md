# Translations

Key shape: `{namespace}::enums.{slug}.{case}`.

Defaults:
- namespace = `enumerator` (from config).
- slug = `Str::snake(class_basename())` minus trailing `Enum`.

Override per-enum by implementing `Contracts\Translatable` (override `translationNamespace()` and/or `translationSlug()`).

Lookup priority: translation file → `#[Label]` attribute → humanised name.
