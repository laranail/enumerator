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
php artisan vendor:publish --tag=laranail::enumerator-config

# Language strings
php artisan vendor:publish --tag=laranail::enumerator-lang

# Blade views (every CSS-framework bundle; there is no per-framework tag)
php artisan vendor:publish --tag=laranail::enumerator-views

# laranail::enumerator.make stubs (for customization)
php artisan vendor:publish --tag=laranail::enumerator-stubs

# State-history migration
php artisan vendor:publish --tag=laranail::enumerator-migrations
php artisan migrate

# Preset enums copied into app/Enums/
php artisan vendor:publish --tag=laranail::enumerator-presets
```

## CSS framework

Set the default CSS framework for Blade components in `config/laranail/enumerator.php`:

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
php artisan laranail::enumerator.cache
```

Clear it when releasing new enum cases:

```bash
php artisan laranail::enumerator.cache-clear
```

---

[← Docs index](../README.md#documentation)
