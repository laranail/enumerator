# Magic comparisons

Opt-in via `use HasMagicComparisons;`:

```php
$status->isActive();         // true|false
$status->isNotBanned();      // true|false
$status->isOneOf($a, $b);    // bool
$status->isNoneOf($a, $b);   // bool
```

Case-insensitive by default. Ambiguity resolution via `config('enumerator.magic.ambiguous_resolution')`.
