# Views publishing

Each CSS-framework view bundle is independently publishable:

```bash
php artisan vendor:publish --tag=enumerator-views                 # all bundles
php artisan vendor:publish --tag=enumerator-views-plain
php artisan vendor:publish --tag=enumerator-views-tailwind
php artisan vendor:publish --tag=enumerator-views-daisyui
php artisan vendor:publish --tag=enumerator-views-bootstrap
php artisan vendor:publish --tag=enumerator-views-bulma
```

Published views land under `resources/views/vendor/laranail-enumerator/components/{framework}/` — edit freely; they override package defaults.

---

[← Docs index](../../README.md#documentation)
