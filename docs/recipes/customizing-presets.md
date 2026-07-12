# Customizing presets

Two paths:

**1. Override in config** (preferred, no code copy):

```php
// config/enumerator.php
'overrides' => [
    Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum::class => [
        'Critical' => ['color' => 'magenta', 'meta' => ['paging' => true]],
    ],
],
```

**2. Copy and own** (when you need structural changes):

```bash
php artisan vendor:publish --tag=enumerator-presets
# Files copied into app/Enums/ — edit freely.
```

---

[← Docs index](../../README.md#documentation)
