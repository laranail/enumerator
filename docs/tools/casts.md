# Eloquent casts

Four casts:

- `AsEnum::of(MyEnum::class)` — single enum, throws on invalid.
- `AsNullableEnum::of(MyEnum::class)` — single enum, null on invalid.
- `AsEnumeratorCollection::of(MyEnum::class)` — JSON column of enums.
- `AsBitmask::of(FeatureFlagEnum::class)` — int column ↔ `Bitmask` value object.

Native Laravel enum casts also work (`'status' => UserStatusEnum::class`) for backed enums.
