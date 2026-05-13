# Concerns

Sixteen traits ship in `src/Concerns/`:

**Always-on (composed by `HasEnumeratorBehavior`)**: `HasAttributes`, `HasEquality`, `HasFromHelpers`, `IsJsonable`, `IsTranslatable`, `RendersHtml`, `ResolvesMagicCalls`.

**Feature traits (opt-in)**: `HasBitmask`, `HasTransitions`, `HasGrouping`, `HasOrder`, `HasLifecycle`, `HasMagicComparisons`, `HasInvokableCases`.

**Class-const path**: `HasClassEnumBehavior` (used by `AbstractEnumeratorClass` — mutually exclusive with `HasEnumeratorBehavior`).
