# laranail/enumerator

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP 8.3+](https://img.shields.io/badge/php-%5E8.3-8892bf.svg)](https://packagist.org/packages/laranail/enumerator)
[![Laravel 13+](https://img.shields.io/badge/laravel-%5E13.0-ff2d20.svg)](https://packagist.org/packages/laranail/enumerator)

`laranail/enumerator` is not published to Packagist, so there is no registry-version badge to show: see [Install](#install).

> The integration-rich Laravel enum toolkit — native enums with declarative attributes, state machines, bitmasks, Blade components, Eloquent casts, validation rules, Filament/Nova/Livewire/Inertia integrations, optional Pest/OpenAPI/GraphQL modules, per-tenant overrides, and Rector migration codemods.

PHP `^8.3` on Laravel `^13`.

## Install

```bash
composer require laranail/enumerator
```

The service provider is auto-discovered. One trait — `use HasEnumerator;` — composes labels, comparisons, bitmasks, grouping, lifecycle, transitions, and factories.

## Quick start guide and usage

### Getting started

Nothing to configure: the service provider registers itself through package discovery, and an enum
needs only the trait. Optional steps, each for one feature:

```bash
# Scaffold an enum into App\Enums
php artisan laranail::enumerator.make UserStatusEnum

# The state-history table, only if you record transitions
php artisan vendor:publish --tag=laranail::enumerator-migrations
php artisan migrate
```

`ENUMERATOR_CSS` picks the Blade components' CSS framework (`plain` by default).

### Usage

```php
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum UserStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    case Active   = 'active';
    case Inactive = 'inactive';
    case Banned   = 'banned';
}

UserStatusEnum::Active->label();       // "Active"
UserStatusEnum::Active->is('active');  // true
UserStatusEnum::options();             // ['active' => 'Active', 'inactive' => 'Inactive', 'banned' => 'Banned']
```

```php
use Simtabi\Laranail\Enumerator\Casts\AsEnum;

protected function casts(): array
{
    return ['status' => AsEnum::of(UserStatusEnum::class)]; // null-aware + name lookup
}
```

```blade
<x-laranail-enumerator::badge :case="$user->status" />
```

The full walkthrough is in [Getting started](docs/getting-started.md); everything else is in the [documentation index](#documentation).

## <a name="documentation"></a>Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/enumerator](https://opensource.simtabi.com/documentation/laranail/enumerator/)** — getting started, the attribute metadata system, the consumer-side `HasEnumAttributes` trait, Eloquent, validation, Blade, state machines, bitmasks, translations, per-tenant overrides, database-backed dynamic enums, the optional integration modules, Artisan commands, TypeScript export, and migrating from BenSampo/Spatie enums.

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
