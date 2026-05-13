# Attributes

Nine attributes ship with the package: `Bit`, `Color`, `CssClass`, `Description`, `Help`, `Icon`, `Label`, `Meta`, `Order`. Apply them on enum cases (or the enum class itself for `Description`).

Read with `$case->color()`, `->icon()`, `->label()`, `->help()`, `->order()`, `->meta($key)`, `->cssClass($framework)`.

Overrides flow through `config('enumerator.overrides')` — see [configuration.md](../configuration.md).
