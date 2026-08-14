# laranail::enumerator.make command

Generate a new enum from a publishable stub:

```bash
php artisan laranail::enumerator.make UserStatusEnum
php artisan laranail::enumerator.make UserStatusEnum --stub=attributes
php artisan laranail::enumerator.make OrderStatusEnum --stub=state-machine
php artisan laranail::enumerator.make FeatureFlagEnum --stub=bitmask
php artisan laranail::enumerator.make Color --stub=pure
```

Customize stubs:

```bash
php artisan vendor:publish --tag=enumerator-stubs
# edit resources/stubs/enumerator/enumerator.*.stub
```

---

[← Docs index](../../README.md#documentation)
