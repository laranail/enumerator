# Views publishing

Each CSS-framework view bundle is independently publishable:

```bash
php artisan vendor:publish --tag=laranail::enumerator-views   # every bundle
```

There is no per-framework tag: publishing writes all bundles, and you keep the one you use.

Published views land under `resources/views/vendor/laranail-enumerator/components/{framework}/` — edit freely; they override package defaults.

---

[← Docs index](../../README.md#documentation)
