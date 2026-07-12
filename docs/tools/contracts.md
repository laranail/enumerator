# Contracts

Seven interfaces in `src/Contracts/`:

- `Enumerator` — marker interface, implemented by every enum.
- `HtmlRenderable` — `toHtml()`.
- `Stateful` — state-machine enums (`transitions()`, `initialStates()`).
- `Translatable` — overrides for translation key shape.
- `Bitwise` — bitmask enums (use with `#[Bit]`).
- `TransitionHook` — before/after hooks for state transitions.
- `Cacheable` — enum-as-cache-key (`key()`), v0.3.0. See [Cache keys](cache-keys.md).

---

[← Docs index](../../README.md#documentation)
