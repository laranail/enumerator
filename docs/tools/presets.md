# Presets

Twenty-seven native enum presets ship under `Simtabi\Laranail\Enumerator\Presets\Enums\`.

**Lifecycle:** `StatusEnum`, `PublicationStatusEnum`, `ApprovalStatusEnum`, `OrderStatusEnum`, `PaymentStatusEnum`, `CommentStatusEnum`, `TaskStatusEnum`.

**Severity:** `PriorityEnum`, `SeverityEnum`.

**UI:** `VisibilityEnum`, `SizeEnum`, `DirectionEnum`, `ToggleEnum`, `AlertTypeEnum`.

**HTTP:** `HttpMethodEnum`, `HttpStatusClassEnum`.

**Bitmask:** `BasicPermissionEnum` (int), `FeatureFlagEnum` (string), `NotificationOptInEnum` (pure), `RoleFlagEnum` (string).

**Demographic:** `GenderEnum`, `MaritalStatusEnum`, `RaceEnum`, `ReligionEnum`.

**Calendar:** `WeekdayEnum` (Sunday-first), `MonthEnum` (ISO).

**MIME:** `MimeTypeCategoryEnum`.

Use directly via `use`, or copy into your app:

```bash
php artisan vendor:publish --tag=enumerator-presets
```

## `AlertTypeEnum` vs `SeverityEnum`

They look adjacent and are not interchangeable. `SeverityEnum` classifies a log
record on the RFC 5424 scale; `AlertTypeEnum` styles a flash message or inline
notice, where `Primary` and `Mono` are not severities and nothing maps to a
syslog level.

`AlertTypeEnum` is also the one preset where `color()` and `->value`
deliberately differ:

```php
AlertTypeEnum::Default->value;    // 'default'   — the CSS token
AlertTypeEnum::Default->color();  // 'secondary' — this package's palette
AlertTypeEnum::Mono->value;       // 'mono'
AlertTypeEnum::Mono->color();     // 'ghost'
```

The backing values are the strings a front end already writes into a class
name; `color()` answers with the palette the other presets share. Read `->value`
for your own vocabulary, `color()` for a common one.

---

[← Docs index](../../README.md#documentation)
