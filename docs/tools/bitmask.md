# Bitmask

Mark cases with `#[Bit(N)]` (positive power of two) and `use HasBitmask`. Backing type is irrelevant — int, string, or pure enums all work.

```php
$mask = FeatureFlagEnum::mask(FeatureFlagEnum::DarkMode, FeatureFlagEnum::BetaUI);
$mask->toInt();   // 3
$mask->has(...); $mask->add(...); $mask->remove(...);
FeatureFlagEnum::fromMask(3);
```

Persist via `AsBitmask::of(FeatureFlagEnum::class)` — see [casts.md](casts.md).
