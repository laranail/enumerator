# Livewire integration

Backed-enum properties work out of the box in Livewire 3.5+ via Laravel's native enum casting.

For pure enums or `AbstractEnumeratorClass` instances, use `EnumeratorCasts::hydrateProperty()` in your component's `hydrate{Prop}` hook.
