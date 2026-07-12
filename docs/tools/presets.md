# Presets

Twenty-six native enum presets ship under `Simtabi\Laranail\Enumerator\Presets\Enums\`.

**Lifecycle:** `StatusEnum`, `PublicationStatusEnum`, `ApprovalStatusEnum`, `OrderStatusEnum`, `PaymentStatusEnum`, `CommentStatusEnum`, `TaskStatusEnum`.

**Severity:** `PriorityEnum`, `SeverityEnum`.

**UI:** `VisibilityEnum`, `SizeEnum`, `DirectionEnum`, `ToggleEnum`.

**HTTP:** `HttpMethodEnum`, `HttpStatusClassEnum`.

**Bitmask:** `BasicPermissionEnum` (int), `FeatureFlagEnum` (string), `NotificationOptInEnum` (pure), `RoleFlagEnum` (string).

**Demographic:** `GenderEnum`, `MaritalStatusEnum`, `RaceEnum`, `ReligionEnum`.

**Calendar:** `WeekdayEnum` (Sunday-first), `MonthEnum` (ISO).

**MIME:** `MimeTypeCategoryEnum`.

Use directly via `use`, or copy into your app:

```bash
php artisan vendor:publish --tag=enumerator-presets
```

---

[← Docs index](../../README.md#documentation)
