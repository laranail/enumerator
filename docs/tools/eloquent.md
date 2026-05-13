# Eloquent integration

Casts: see [casts.md](casts.md).

`HasEnumeratorScopes` adds query scopes: `whereEnum()`, `whereEnumNot()`, `whereEnumIn()`, `whereEnumNotIn()`, `whereEnumMeta()`.

`HasEnumeratorStateMachine` enforces transitions and (optionally) records history to the `enumerator_state_history` table.
