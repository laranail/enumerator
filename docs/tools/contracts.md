# Contracts

Six interfaces in `src/Contracts/`:

- `Enumerator` — marker interface, implemented by every enum.
- `HtmlRenderable` — `toHtml()`.
- `Stateful` — state-machine enums (`transitions()`, `initialStates()`).
- `Translatable` — overrides for translation key shape.
- `Bitwise` — bitmask enums (use with `#[Bit]`).
- `TransitionHook` — before/after hooks for state transitions.
