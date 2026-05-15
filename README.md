# laranail/enumerator

> The **integration-rich Laravel enum toolkit** for
> **Laravel 13** / **PHP 8.3+**.
> Native enums, declarative attributes, state machines, bitmasks,
> Blade components, Eloquent casts, validation rules, Filament / Nova /
> Livewire / Inertia integrations, optional Pest / OpenAPI / Lighthouse
> / Saloon / Octane / GraphQL / structured-output modules, per-tenant
> overrides, Rector migration codemods.

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP 8.3+](https://img.shields.io/badge/php-%5E8.3-8892bf.svg)](composer.json)
[![Laravel 13+](https://img.shields.io/badge/laravel-%5E13.0-ff2d20.svg)](composer.json)

## Highlights

- **One trait gets you everything**: `use HasEnumerator;` composes labels,
  comparisons, bitmasks, grouping, lifecycle, magic predicates,
  ordering, transitions, and invokable static factories in a single
  import.
- **Attribute-driven metadata**: declare `#[Label]`, `#[Color]`,
  `#[Icon]`, `#[Description]`, `#[Help]`, `#[Order]`, `#[Meta]`,
  `#[Bit]`, `#[CssClass]` on each case — read at runtime, cached,
  override-friendly.
- **Consumer-side trait** (`HasEnumAttributes`): drop on an Eloquent
  Model / Livewire Component / FormRequest and get auto-registered
  casts plus 9 magic accessors (`status_label`, `status_color`,
  `status_badge`, …) and 3 predicate methods (`statusIs(...)`,
  `statusIn([...])`, `statusEquals(...)`).
- **Pluggable i18n adapter**: ships a Laravel `Lang::*` wrapper by
  default; swap to a database-backed source via one config key.
- **State machines** with declarative transitions, optional history
  table, FormRequest validation rule.
- **Bitmasks** that work on any backing type (int / string / pure) via
  the `#[Bit]` attribute.
- **Blade components** for badge, select, dropdown, radio,
  checkboxes, grid, listing, element — five framework variants per
  component (plain / Tailwind / DaisyUI / Bootstrap / Bulma).
- **First-class integrations**: Filament 4, Nova 5, Livewire 3.5,
  Inertia 2 — drop-in field / column / filter classes.
- **Optional modules** (opt-in via config flags): Pest plugin, OpenAPI
  3.1 schema export, Lighthouse scalar, framework-agnostic GraphQL
  schema generator, Saloon caster, Octane warmup, OpenAI / Anthropic /
  MCP structured-output emitters, per-tenant override layer.
- **Migration codemods**: Rector rules to convert BenSampo and Spatie
  enums to laranail/enumerator.
- **26 preset enums** ship for common patterns (status, priority,
  visibility, HTTP method, demographics, calendar, MIME types, more).
- **Class-const fallback** (`AbstractEnumeratorClass`) for codebases
  that can't yet use native enums.
- **Reflection cache** with disk-backed snapshot for production
  + Octane warmup.
- **Quality**: PHPStan `level: max`, Pint clean, comprehensive Pest
  suite across the PHP 8.3 / 8.4 / 8.5 matrix.

## Requirements

- PHP `^8.3` (CI also tests 8.4 and 8.5)
- Laravel `^13.0`

## Installation

```bash
composer require laranail/enumerator
```

Service provider is auto-discovered via Laravel's package discovery.
No manual registration needed.

Optional publishes:

```bash
php artisan vendor:publish --tag=enumerator-config
php artisan vendor:publish --tag=enumerator-lang
php artisan vendor:publish --tag=enumerator-views
php artisan vendor:publish --tag=enumerator-stubs
php artisan vendor:publish --tag=enumerator-migrations
php artisan vendor:publish --tag=enumerator-presets    # copies the 26 preset enums into app/Enums/
```

## Quick start

Define an enum:

```php
namespace App\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum UserStatusEnum: string implements Enumerator
{
    use HasEnumerator;

    #[Label('Active'),   Color('success'), Icon('check-circle')]
    case Active = 'active';

    #[Label('Inactive'), Color('ghost'),   Icon('pause-circle')]
    case Inactive = 'inactive';

    #[Label('Banned'),   Color('danger'),  Icon('x-octagon')]
    case Banned = 'banned';
}
```

Use it:

```php
UserStatusEnum::Active->label();          // "Active"
UserStatusEnum::Active->color();          // "success"
UserStatusEnum::Active->icon();           // "check-circle"
UserStatusEnum::Active->is('active');     // true
UserStatusEnum::Active->isActive();       // true (magic, via HasMagicComparisons)
UserStatusEnum::Active->isNotBanned();    // true

// Static collection helpers
UserStatusEnum::cases();                  // array of cases
UserStatusEnum::values();                 // ['active', 'inactive', 'banned']
UserStatusEnum::labels();                 // ['active' => 'Active', ...]
UserStatusEnum::options('— pick one —');  // ['' => '— pick one —', 'active' => 'Active', ...]
UserStatusEnum::collect();                // CasesCollection (extends Illuminate\Support\Collection)
UserStatusEnum::random();                 // a random case

// Static factory (from HasInvokableCases, included in the umbrella)
UserStatusEnum::Active();                 // 'active' (the backing value)
UserStatusEnum::Active('label');          // 'Active'
```

## Eloquent

Native cast works because the enum implements `BackedEnum`:

```php
class User extends Model
{
    protected function casts(): array
    {
        return ['status' => UserStatusEnum::class];
    }
}

$user->status = UserStatusEnum::Active;
$user->status->label();   // "Active"
```

Or use the package's casts for nullable / collection / bitmask shapes:

```php
use Simtabi\Laranail\Enumerator\Casts\AsBitmask;
use Simtabi\Laranail\Enumerator\Casts\AsEnum;
use Simtabi\Laranail\Enumerator\Casts\AsEnumeratorCollection;
use Simtabi\Laranail\Enumerator\Casts\AsNullableEnum;

protected function casts(): array
{
    return [
        'status'      => AsEnum::of(UserStatusEnum::class),                  // explicit cast
        'role'        => AsNullableEnum::of(RoleEnum::class),                // null on missing
        'permissions' => AsEnumeratorCollection::of(PermissionEnum::class),  // JSON column → Collection of cases
        'flags'       => AsBitmask::of(FeatureFlagEnum::class),              // int column → Bitmask value object
    ];
}
```

## Consumer-side `HasEnumAttributes`

Drop on a model and get accessors + predicates for free — no manual
`getStatusLabelAttribute()` boilerplate:

```php
use Simtabi\Laranail\Enumerator\Concerns\HasEnumAttributes;

class User extends Model
{
    use HasEnumAttributes;

    protected function enumAttributes(): array
    {
        return [
            'status' => UserStatusEnum::class,
            'flags'  => ['enum' => FeatureFlagEnum::class, 'cast' => AsBitmask::class],
        ];
    }
}

// Generated, no manual accessor declarations:
$user->status_label;        // "Active"
$user->status_color;        // "success"
$user->status_icon;         // "check-circle"
$user->status_description;  // long-form (from #[Description])
$user->status_help;         // help text (from #[Help])
$user->status_value;        // 'active'
$user->status_name;         // 'Active'
$user->status_badge;        // rendered HtmlString
$user->status_meta;         // array of meta keys

$user->statusIs(UserStatusEnum::Active);                       // bool
$user->statusIn([UserStatusEnum::Active, UserStatusEnum::Inactive]);  // bool
$user->statusEquals(UserStatusEnum::Active);                   // bool
```

Works the same on Livewire components and FormRequests. Host detection
is `method_exists`-based — no hard dependency on framework classes.

## Validation

```php
use Simtabi\Laranail\Enumerator\Rules\EnumIn;
use Simtabi\Laranail\Enumerator\Rules\EnumName;
use Simtabi\Laranail\Enumerator\Rules\EnumNotIn;
use Simtabi\Laranail\Enumerator\Rules\EnumTransition;
use Simtabi\Laranail\Enumerator\Rules\EnumValue;

$request->validate([
    'status'    => ['required', new EnumValue(UserStatusEnum::class)],
    'role_name' => ['required', new EnumName(RoleEnum::class)],
    'tag'       => ['required', EnumValue::for(UserStatusEnum::class)->only([UserStatusEnum::Active])],
    'forbidden' => ['required', new EnumNotIn(UserStatusEnum::class, [UserStatusEnum::Banned])],
    'next'      => ['required', new EnumTransition(OrderStatusEnum::class, $order->status)],
]);

// Or the string-form rule (registered via Validator::extend):
$request->validate([
    'status' => 'required|enum_value:'.UserStatusEnum::class,
]);
```

## Blade

Eight components shipped, five framework variants each
(`plain` / `tailwind` / `daisyui` / `bootstrap` / `bulma`). Pick the
framework via `config('enumerator.css_framework')` or per-tag with
`framework="..."`:

```blade
<x-laranail-enumerator::badge :case="$user->status" />
<x-laranail-enumerator::badge :case="$user->status" framework="bootstrap" />

<x-laranail-enumerator::select :enum="UserStatusEnum::class" name="status" :selected="$user->status" />
<x-laranail-enumerator::dropdown :enum="UserStatusEnum::class" name="status" searchable clearable />
<x-laranail-enumerator::radio  :enum="UserStatusEnum::class" name="status" :selected="$user->status" layout="horizontal" />
<x-laranail-enumerator::checkboxes :enum="PermissionEnum::class" name="permissions[]" :selected="$user->permissions" />
<x-laranail-enumerator::grid :enum="UserStatusEnum::class" :columns="3" />
<x-laranail-enumerator::listing :enum="UserStatusEnum::class" />
<x-laranail-enumerator::element :case="$user->status" as="a" :href="route('users.filter', $user->status)" show-icon />
```

Eight Blade directives:

```blade
@enumeratorLabel($user->status)
@enumeratorValue($user->status)
@enumeratorName($user->status)
@enumeratorBadge($user->status)
@enumeratorColor($user->status)
@enumeratorIcon($user->status)
@enumeratorDescription($user->status)
@enumeratorHelp($user->status)
@enumeratorMeta($user->status, 'reason')

@enumeratorIs($user->status, UserStatusEnum::Active) Active! @endEnumeratorIs
@enumeratorIn($user->status, [UserStatusEnum::Active, UserStatusEnum::Inactive]) Not banned. @endEnumeratorIn
```

## State machine

Implement `Stateful` + use `HasTransitions` (already included in the
`HasEnumerator` umbrella):

```php
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;

enum OrderStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumerator;

    case Pending   = 'pending';
    case Paid      = 'paid';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value   => [self::Paid, self::Cancelled],
            self::Paid->value      => [self::Shipped, self::Cancelled],
            self::Shipped->value   => [self::Delivered],
            self::Delivered->value => [],
            self::Cancelled->value => [],
        ];
    }
}

OrderStatusEnum::Pending->canTransitionTo(OrderStatusEnum::Paid);      // true
OrderStatusEnum::Pending->transitionTo(OrderStatusEnum::Delivered);    // throws InvalidTransitionException
OrderStatusEnum::Pending->tryTransitionTo(OrderStatusEnum::Delivered); // null
OrderStatusEnum::Delivered->isTerminal();                              // true
```

Add `HasEnumeratorStateMachine` to your model to enforce transitions
at save time and record history in the `enumerator_state_history`
table.

## Bitmasks

```php
use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum FeatureFlagEnum: string implements Bitwise, Enumerator
{
    use HasEnumerator;

    #[Bit(1)] case DarkMode    = 'dark_mode';
    #[Bit(2)] case BetaUI      = 'beta_ui';
    #[Bit(4)] case Experiments = 'experiments';
    #[Bit(8)] case Telemetry   = 'telemetry';
}

$mask = FeatureFlagEnum::mask(FeatureFlagEnum::DarkMode, FeatureFlagEnum::BetaUI);
$mask->toInt();                              // 3
$mask->has(FeatureFlagEnum::DarkMode);       // true
$mask->add(FeatureFlagEnum::Telemetry);      // new Bitmask (immutable) with bit 8 set
FeatureFlagEnum::fromMask(3);                // hydrate from int back to Bitmask
```

Use the `AsBitmask` cast on a model column (see *Eloquent* above) and
the `Bitmask` instance round-trips through the database.

## Class-const fallback

For codebases that can't use native PHP enums (mixed-backing constants,
runtime case definition, legacy migration), extend `AbstractEnumeratorClass`:

```php
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;

class UserStatusEnum extends AbstractEnumeratorClass
{
    #[Label('Active'),   Color('success')] public const ACTIVE   = 'active';
    #[Label('Inactive'), Color('ghost')]   public const INACTIVE = 'inactive';
    #[Label('Banned'),   Color('danger')]  public const BANNED   = 'banned';
}

UserStatusEnum::ACTIVE();              // returns an instance
UserStatusEnum::ACTIVE()->label();     // "Active"
UserStatusEnum::isValid('active');     // true
UserStatusEnum::cases();               // array of instances
```

All integrations (Filament, Nova, Inertia, validation rules,
route binding) work for `AbstractEnumeratorClass` subclasses too.

## Translations

By default, labels resolve from your Laravel translations via
`{namespace}::enums.{slug}.{case}.label`. Override by implementing
`Translatable` per-enum, or swap the entire source with a
`TranslatorAdapter`:

```php
namespace App\Enum;

use Simtabi\Laranail\Enumerator\Contracts\TranslatorAdapter;
use Simtabi\Laranail\Enumerator\Translations\DatabaseTranslatorAdapter;

// In a service provider:
$this->app->bind(TranslatorAdapter::class, DatabaseTranslatorAdapter::class);
// Or via config:
// config('enumerator.translator.adapter') = DatabaseTranslatorAdapter::class
```

The default `LaravelTranslatorAdapter` wires through Laravel's translator.
The reference `DatabaseTranslatorAdapter` reads from an
`enum_translations` table (`key`, `locale`, `value`).

## Per-tenant overrides

Override case attributes per active tenant by binding a `TenantContext`:

```php
use Simtabi\Laranail\Enumerator\Contracts\TenantContext;

class CurrentTenant implements TenantContext
{
    public function currentTenant(): null|string|int { return auth()->user()?->tenant_id; }

    public function overridesFor(string $enumClass): array
    {
        return cache()->remember("enum-overrides:{$this->currentTenant()}:{$enumClass}", 60, fn () =>
            EnumOverride::query()
                ->where('tenant_id', $this->currentTenant())
                ->where('enum_class', $enumClass)
                ->get()
                ->keyBy('case_name')
                ->map->only(['color', 'icon', 'label'])
                ->toArray());
    }
}

// In a service provider:
$this->app->bind(TenantContext::class, CurrentTenant::class);
```

`AttributesOverrideResolver` consults `TenantContext::overridesFor()`
BEFORE falling through to `config('enumerator.overrides')` and the
compile-time attribute. Default binding is `NullTenantContext` —
zero overhead for single-tenant apps.

## Database-backed dynamic enums

> ⚠ **Design caveat:** these are NOT native PHP enums. `match`
> exhaustiveness doesn't apply; `BackedEnum` type hints reject them;
> Rector / IDE refactoring that understands native enums doesn't
> understand these. **Use the native enum path** unless your case set
> genuinely needs to be defined at runtime (CMS-driven taxonomies,
> tenant-customizable lifecycle states).

```php
use Simtabi\Laranail\Enumerator\DynamicEnums\DatabaseBackedEnum;

class TenantStatus extends DatabaseBackedEnum
{
    protected static function table(): string
    {
        return 'tenant_statuses';
    }
}

// In your service provider boot, after migrations:
TenantStatus::loadCases();

TenantStatus::cases();           // array of instances hydrated from the table
TenantStatus::ACTIVE();          // case factory works just like AbstractEnumeratorClass
TenantStatus::isValid('active'); // true if the table has that value
```

Run `TenantStatus::reloadCases()` after seeding new rows mid-process.

## Optional modules

All gated by `config('enumerator.modules.{slug}')` — default false,
opt-in per app. Each is loadable without its vendor library installed.

| Slug | Module | What it does | Suggest |
|---|---|---|---|
| `pest` | Pest plugin | Registers `toBeCase`, `toBeIn`, `toEqualEnum`, `toHaveBit`, `toCanTransitionTo` Pest expectations | `pestphp/pest` |
| `openapi` | OpenAPI 3.1 export | `OpenApiSchemaExporter` emits component schemas with `x-enum-varnames` + `x-enum-descriptions` | (no vendor dep) |
| `lighthouse` | Lighthouse scalar | Abstract `EnumScalar` to subclass per enum for GraphQL schema runtime parsing | `nuwave/lighthouse` |
| `graphql` | Framework-agnostic GraphQL | `SchemaExporter` emits portable `.graphql` enum fragments for Mercurius / API Platform / Apollo / Hasura | (no vendor dep) |
| `saloon` | Saloon caster | `EnumCaster` recursively serializes enum instances in headers/query/body arrays | `saloonphp/saloon` |
| `octane` | Octane warmup | `WarmCachesListener` restores the reflection-cache snapshot per worker boot | `laravel/octane` |
| `structured_output` | OpenAI / Anthropic / MCP | Three sibling JSON-schema emitters for `response_format`, tool parameters, MCP tool/resource templates | `openai-php/client`, `anthropic-ai/sdk` |
| `tenancy` | Per-tenant overrides | Provider-level binding for the `TenantContext` contract (see above) | `spatie/laravel-multitenancy`, `stancl/tenancy` |

Enable via env vars in `.env`:

```dotenv
ENUMERATOR_MODULE_PEST=true
ENUMERATOR_MODULE_OPENAPI=true
ENUMERATOR_MODULE_GRAPHQL=true
```

## Artisan commands

```
make:enumerator <Name> [--stub=backed|pure|attributes|bitmask|state-machine] [--namespace=]
enumerator:annotate <Class>             # print @method static stubs
enumerator:ide-helper [<Classes>...]    # write _ide_helper_enumerator.php
enumerator:export <Class> [--ts|--json|--php] [--out=]
enumerator:cache                        # warm + persist the reflection cache
enumerator:cache:clear
```

## Migrating from BenSampo or Spatie enums

Two Rector codemods ship in `src/Rector/`. Add to your `rector.php`:

```php
use Rector\Config\RectorConfig;
use Simtabi\Laranail\Enumerator\Rector\Sets\MigrationSet;

return RectorConfig::configure()->sets(MigrationSet::rules());
```

Then:

```bash
composer require rector/rector --dev
vendor/bin/rector process app/Enums --dry-run
vendor/bin/rector process app/Enums
```

The BenSampo rule does a full class→enum transform (detects backing
type, copies constants → cases, adds `use HasEnumerator`). The Spatie
rule produces a structural skeleton — manual case fill-in needed
because Spatie's docblock-driven case pattern can't be auto-mapped.

## TypeScript export

```bash
php artisan enumerator:export App\\Enums\\UserStatusEnum --ts --out=resources/js/enums/UserStatusEnum.ts
```

Emits:

```ts
export const UserStatusEnum = {
    Active: 'active',
    Inactive: 'inactive',
    Banned: 'banned',
} as const;

export type UserStatusEnumValue = 'active' | 'inactive' | 'banned';

export const UserStatusEnum_VALUES = [
    'active',
    'inactive',
    'banned',
] as const;
```

The `_VALUES` tuple is consumable as `z.enum(UserStatusEnum_VALUES)`
if you're using Zod, but no Zod schema is emitted by the export
itself.

## Configuration

All knobs live in `config/enumerator.php`:

```php
return [
    'css_framework'         => 'plain',                       // plain | tailwind | daisyui | bootstrap | bulma
    'view_namespace'        => 'laranail-enumerator',
    'translation_namespace' => 'enumerator',
    'cache' => [
        'driver'     => 'layered',                            // memory | file | layered
        'auto_warm'  => false,
        'auto_warm_classes' => [],                            // enumerator classes to warm in `enumerator:cache`
    ],
    'state_machine' => ['table_name' => 'enumerator_state_history', 'record_history' => true],
    'magic'         => ['ambiguous_resolution' => 'throw'],   // throw | first | null
    'translator'    => ['adapter' => null],                   // FQCN implementing Contracts\TranslatorAdapter
    'tenancy'       => ['driver'  => null],                   // FQCN implementing Contracts\TenantContext
    'modules'       => [
        'pest' => false, 'openapi' => false, 'lighthouse' => false, 'saloon' => false,
        'octane' => false, 'structured_output' => false, 'graphql' => false, 'tenancy' => false,
    ],
    'overrides'     => [],                                    // per-case attribute overrides without forking presets
];
```

See [`docs/configuration.md`](docs/configuration.md) for the full
reference.

## Preset enums

26 native preset enums ship under
`Simtabi\Laranail\Enumerator\Presets\Enums\` — use directly via `use`
or publish into `app/Enums/` with `--tag=enumerator-presets`:

- **Lifecycle** — `StatusEnum`, `PublicationStatusEnum`, `ApprovalStatusEnum`, `OrderStatusEnum`, `PaymentStatusEnum`, `CommentStatusEnum`, `TaskStatusEnum`
- **Severity** — `PriorityEnum`, `SeverityEnum`
- **UI** — `VisibilityEnum`, `SizeEnum`, `DirectionEnum`, `ToggleEnum`
- **HTTP** — `HttpMethodEnum`, `HttpStatusClassEnum`
- **Bitmask** — `BasicPermissionEnum`, `FeatureFlagEnum`, `NotificationOptInEnum`, `RoleFlagEnum`
- **Demographic** — `GenderEnum`, `MaritalStatusEnum`, `RaceEnum`, `ReligionEnum`
- **Calendar** — `WeekdayEnum`, `MonthEnum`
- **MIME** — `MimeTypeCategoryEnum`

## Documentation

### Getting started

- [Installation](docs/installation.md)
- [Getting started](docs/getting-started.md)
- [Configuration](docs/configuration.md)
- [Architecture overview](docs/architecture.md)
- [Release process](docs/release.md)
- [Shipping checklist](docs/shipping-checklist.md)

### Tools

- [Attributes](docs/tools/attributes.md)
- [Bitmask](docs/tools/bitmask.md)
- [Blade components](docs/tools/blade-components.md)
- [Blade directives](docs/tools/blade-directives.md)
- [Cases collection](docs/tools/cases-collection.md)
- [Eloquent casts](docs/tools/casts.md)
- [Class-const API](docs/tools/class-const-api.md)
- [Concerns](docs/tools/concerns.md)
- [Contracts](docs/tools/contracts.md)
- [Eloquent integration](docs/tools/eloquent.md)
- [Exceptions](docs/tools/exceptions.md)
- [Helpers](docs/tools/helpers.md)
- [Invokable cases](docs/tools/invokable-cases.md)
- [Magic comparisons](docs/tools/magic-comparisons.md)
- [`make:enumerator` command](docs/tools/make-enumerator.md)
- [Presets](docs/tools/presets.md)
- [Route binding](docs/tools/route-binding.md)
- [State machine](docs/tools/state-machine.md)
- [Translations](docs/tools/translations.md)
- [Validation rules](docs/tools/validation.md)
- [Views publishing](docs/tools/views-publishing.md)

### Recipes

- [API resources](docs/recipes/api-resources.md)
- [Customizing presets](docs/recipes/customizing-presets.md)
- [Filament integration](docs/recipes/filament.md)
- [Form requests](docs/recipes/form-requests.md)
- [Inertia integration](docs/recipes/inertia.md)
- [Livewire integration](docs/recipes/livewire.md)
- [Notification channel example](docs/recipes/notification-channel.md)
- [Nova integration](docs/recipes/nova.md)
- [Tailwind config](docs/recipes/tailwind-config.md)
- [Testing enums](docs/recipes/testing-enums.md)
- [TypeScript export](docs/recipes/typescript-export.md)

## Contributing

Pull requests welcome. Please run the gates before submitting:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
vendor/bin/pest
```

See [`CONTRIBUTING.md`](CONTRIBUTING.md) for coding conventions, test
patterns, and PR expectations.

## Security

Disclosure to `opensource@simtabi.com`. See [`SECURITY.md`](SECURITY.md).

## License

MIT — see [`LICENSE`](LICENSE).

Copyright © 2026 Simtabi LLC.
