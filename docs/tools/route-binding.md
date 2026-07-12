# Route binding

Native Laravel 13 enum binding works out of the box for backed enums. For case-insensitive matching with a fallback:

```php
use Simtabi\Laranail\Enumerator\Routing\EnumeratorRouteBinder;

EnumeratorRouteBinder::register('status', UserStatusEnum::class);
EnumeratorRouteBinder::register('status', UserStatusEnum::class, fallback: UserStatusEnum::Inactive);
```

---

[← Docs index](../../README.md#documentation)
