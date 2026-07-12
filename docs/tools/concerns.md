# Concerns

Seventeen traits ship in `src/Concerns/`:

**Always-on (composed by `HasEnumeratorBehavior`)**: `HasAttributes`, `HasEquality`, `HasFromHelpers`, `IsJsonable`, `IsTranslatable`, `RendersHtml`, `ResolvesMagicCalls`.

**Feature traits (opt-in)**: `HasBitmask`, `HasTransitions`, `HasGrouping`, `HasOrder`, `HasLifecycle`, `HasMagicComparisons`, `HasInvokableCases`.

**Cache surface (v0.3.0)**: `IsCacheKey` (paired with `Contracts\Cacheable`) — see [Cache keys](cache-keys.md).

**Class-const path**: `HasClassEnumBehavior` (used by `AbstractEnumeratorClass` — mutually exclusive with `HasEnumeratorBehavior`).

---

[← Docs index](../../README.md#documentation)
