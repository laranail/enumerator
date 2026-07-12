# Installation

`laranail/enumerator` requires **PHP 8.3+** and **Laravel 13+**.

## Composer

```bash
composer require laranail/enumerator
```

The service provider auto-registers via `extra.laravel.providers`.

## Publishing assets

Every asset bundle is independently publishable:

```bash
# Config
php artisan vendor:publish --tag=enumerator-config

# Language strings
php artisan vendor:publish --tag=enumerator-lang

# All Blade view bundles
php artisan vendor:publish --tag=enumerator-views

# A single CSS-framework view bundle
php artisan vendor:publish --tag=enumerator-views-tailwind
php artisan vendor:publish --tag=enumerator-views-daisyui
php artisan vendor:publish --tag=enumerator-views-bootstrap
php artisan vendor:publish --tag=enumerator-views-bulma
php artisan vendor:publish --tag=enumerator-views-plain

# make:enumerator stubs (for customization)
php artisan vendor:publish --tag=enumerator-stubs

# State-history migration
php artisan vendor:publish --tag=enumerator-migrations
php artisan migrate

# Preset enums copied into app/Enums/
php artisan vendor:publish --tag=enumerator-presets
```

## CSS framework

Set the default CSS framework for Blade components in `config/enumerator.php`:

```php
'css_framework' => 'tailwind',   // plain | tailwind | daisyui | bootstrap | bulma
```

Or per-call:

```blade
<x-laranail-enumerator::badge :case="$status" framework="bootstrap" />
```

## Reflection cache (production)

For production deployments warm the file-backed reflection cache:

```bash
php artisan enumerator:cache
```

Clear it when releasing new enum cases:

```bash
php artisan enumerator:cache:clear
```

---

[← Docs index](../README.md#documentation)
