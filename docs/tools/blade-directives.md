# Blade directives

Package-prefixed directives:

| Directive | Use |
|---|---|
| `@enumeratorLabel($case)`  | Translated label |
| `@enumeratorValue($case)`  | Backing value |
| `@enumeratorName($case)`   | Case name |
| `@enumeratorBadge($case)`  | `toHtml()` output |
| `@enumeratorColor($case)`  | Color attribute |
| `@enumeratorIcon($case)`   | Icon attribute |
| `@enumeratorIs($case, $target) ... @endEnumeratorIs` | Conditional |
| `@enumeratorIn($case, [..])  ... @endEnumeratorIn` | In-list conditional |
