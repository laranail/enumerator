# Invokable cases

Opt-in via `use HasInvokableCases;`. Once added, you can call cases statically:

```php
Color::Red();         // 'red' (alias for Color::Red->value)
Color::Red('label');  // 'Red'
```

Override per-enum by implementing `whenInvoked($case, ...$args)` for custom return shape.

---

[← Docs index](../../README.md#documentation)
