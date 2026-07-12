# laranail/enumerator

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/enumerator.svg)](https://packagist.org/packages/laranail/enumerator)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP 8.3+](https://img.shields.io/badge/php-%5E8.3-8892bf.svg)](https://packagist.org/packages/laranail/enumerator)
[![Laravel 13+](https://img.shields.io/badge/laravel-%5E13.0-ff2d20.svg)](https://packagist.org/packages/laranail/enumerator)

> The integration-rich Laravel enum toolkit — native enums with declarative attributes, state machines, bitmasks, Blade components, Eloquent casts, validation rules, Filament/Nova/Livewire/Inertia integrations, optional Pest/OpenAPI/GraphQL modules, per-tenant overrides, and Rector migration codemods.

PHP `^8.3` on Laravel `^13`.

## Install

```bash
composer require laranail/enumerator
```

The service provider is auto-discovered. One trait — `use HasEnumerator;` — composes labels, comparisons, bitmasks, grouping, lifecycle, transitions, and factories.

## Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/enumerator](https://opensource.simtabi.com/documentation/laranail/enumerator/)** — getting started, the attribute metadata system, the consumer-side `HasEnumAttributes` trait, Eloquent, validation, Blade, state machines, bitmasks, translations, per-tenant overrides, database-backed dynamic enums, the optional integration modules, Artisan commands, TypeScript export, and migrating from BenSampo/Spatie enums.

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
