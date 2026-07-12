# make:enumerator command

Generate a new enum from a publishable stub:

```bash
php artisan make:enumerator UserStatusEnum
php artisan make:enumerator UserStatusEnum --stub=attributes
php artisan make:enumerator OrderStatusEnum --stub=state-machine
php artisan make:enumerator FeatureFlagEnum --stub=bitmask
php artisan make:enumerator Color --stub=pure
```

Customize stubs:

```bash
php artisan vendor:publish --tag=enumerator-stubs
# edit resources/stubs/enumerator/enumerator.*.stub
```

---

[← Docs index](../../README.md#documentation)
