# Attributes

Nine attributes ship with the package: `Bit`, `Color`, `CssClass`, `Description`, `Help`, `Icon`, `Label`, `Meta`, `Order`. Apply them on enum cases (or the enum class itself for `Description`).

Read with `$case->color()`, `->icon()`, `->label()`, `->help()`, `->order()`, `->meta($key)`, `->cssClass($framework)`.

Overrides flow through `config('enumerator.overrides')` — see [configuration.md](../configuration.md).

## Trust contract for `#[Icon]`, `#[Color]`, `#[CssClass]`, and overrides

The `Icon` attribute is intentionally rendered as raw HTML in the
Blade components (`{!! $caseIcon !!}` in
`resources/views/components/_base/badge.blade.php` and
`element.blade.php`). This lets you embed inline SVG, `<i class="...">`,
or any other markup as the case's icon.

**Implication:** the value you put in `#[Icon('...')]` is treated as
**trusted markup**. The package does NOT escape it.

This is safe by default — attribute values are declared in source code,
not at runtime. Two paths can violate that assumption:

1. **`config('enumerator.overrides')`** lets you override `icon`,
   `color`, or `css_class` for a case. Values in the config file are
   developer-authored — keep them so.
2. **`TenantContext::overridesFor()`** lets a runtime tenant context
   return per-tenant overrides. **If your `TenantContext` populates
   `icon` / `color` / `css_class` from a database table that
   administrative users can edit, you have introduced an XSS surface.**

If you need user-editable icons or colors per tenant:

- Constrain the icon to a known token (e.g. an icon-set lookup key
  like `"fa-circle-check"`), not raw HTML.
- Validate the token at write time. Reject anything containing `<`,
  `>`, `script`, or `style`.
- Or escape the override value in your `TenantContext` implementation
  before returning it.

The `Label`, `Description`, `Help`, and `Meta` attribute values pass
through Laravel's translator and are HTML-escaped at render time —
**they are safe under runtime override**. Only `Icon`, `Color`, and
`CssClass` carry the trust assumption.

---

[← Docs index](../../README.md#documentation)
