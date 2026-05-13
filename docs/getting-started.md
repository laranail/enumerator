# Getting started

## Define an enum

Minimum viable:

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
```

You immediately get:

```php
UserStatusEnum::Active->label();        // "Active"
UserStatusEnum::Active->value;          // 'active'
UserStatusEnum::Active->name;           // 'Active'
UserStatusEnum::values();               // ['active','inactive','banned']
UserStatusEnum::labels();               // ['active' => 'Active', ...]
UserStatusEnum::options();              // ['active' => 'Active', ...] for <select>
UserStatusEnum::Active->is('active');   // true
UserStatusEnum::tryFromName('Active');  // case|null
UserStatusEnum::collect();              // CasesCollection
```

## Add metadata via attributes

```php
use Simtabi\Laranail\Enumerator\Attributes\{Color, Description, Icon, Label, Meta, Order};

#[Description('Lifecycle states for a user account')]
enum UserStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Active'), Color('success'), Icon('check'), Order(10)]
    case Active = 'active';

    #[Label('Banned'), Color('danger'), Icon('x'), Order(30), Meta(notify: true)]
    case Banned = 'banned';
}

UserStatusEnum::Banned->color();         // "danger"
UserStatusEnum::Banned->icon();          // "x"
UserStatusEnum::Banned->meta('notify');  // true
```

## Use in Eloquent

```php
use Simtabi\Laranail\Enumerator\Casts\AsEnum;

class User extends Model {
    protected function casts(): array {
        return [
            'status' => UserStatusEnum::class,           // native cast
            // or
            'status' => AsEnum::of(UserStatusEnum::class), // null-aware + name lookup
        ];
    }
}
```

## Use in Blade

```blade
<x-laranail-enumerator::badge :case="$user->status" />
<x-laranail-enumerator::select :enum="UserStatusEnum::class" name="status" />

@enumeratorLabel($user->status)
@enumeratorBadge($user->status)
@enumeratorIs($user->status, UserStatusEnum::Active)
    Welcome back.
@endEnumeratorIs
```

## Validate

```php
use Simtabi\Laranail\Enumerator\Rules\EnumValue;

$request->validate([
    'status' => ['required', new EnumValue(UserStatusEnum::class)],
]);
```

## Next steps

- Lay out your enums by area: see [`tools/presets.md`](tools/presets.md).
- Add transitions: see [`tools/state-machine.md`](tools/state-machine.md).
- Combine flags via bitmask: see [`tools/bitmask.md`](tools/bitmask.md).
