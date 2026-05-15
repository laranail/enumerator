# Factories — random enum values in Eloquent factories

Generating a random enum value in a factory is a one-liner — there's
no need for a dedicated faker provider, contrary to what some other
enum packages ship. The native PHP enum API plus Laravel's
`Arr::random` (or even plain PHP `array_rand`) does the job.

## Recipe

```php
use App\Enums\UserStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'   => $this->faker->name(),
            'email'  => $this->faker->safeEmail(),
            'status' => Arr::random(UserStatusEnum::cases()),
        ];
    }
}
```

`Arr::random` returns a single random element. For a weighted
distribution, fall back to plain PHP:

```php
'status' => $this->faker->randomElement([
    UserStatusEnum::Active,
    UserStatusEnum::Active,
    UserStatusEnum::Active,
    UserStatusEnum::Inactive,
    UserStatusEnum::Banned,
]),
// ~60% Active, ~20% Inactive, ~20% Banned
```

## Random bitmask

For an enum implementing `Bitwise` (a `#[Bit]`-tagged set):

```php
use Simtabi\Laranail\Enumerator\Helpers\Bitmask;
use App\Enums\FeatureFlagEnum;

'flags' => Bitmask::of(
    FeatureFlagEnum::class,
    ...Arr::random(
        FeatureFlagEnum::cases(),
        $this->faker->numberBetween(0, count(FeatureFlagEnum::cases()))
    )
),
```

Picks a random number of bits (0 through all). `Bitmask::of` accepts
zero cases (returns the empty mask).

## Constrained subset

Restrict to a documented subset — useful for factory states:

```php
public function active(): self
{
    return $this->state(fn () => [
        'status' => Arr::random([
            UserStatusEnum::Active,
            UserStatusEnum::Inactive,
        ]),
    ]);
}

public function banned(): self
{
    return $this->state(fn () => [
        'status' => UserStatusEnum::Banned,
    ]);
}
```

Then in tests:

```php
$user = User::factory()->active()->create();
$banned = User::factory()->banned()->count(3)->create();
```

## Why no faker provider

Other enum packages ship `$faker->status()` syntax via a faker
provider. We don't, because:

- `Arr::random(UserStatusEnum::cases())` is already a one-liner.
- A faker-provider approach means a new dependency (the provider's
  service provider, the provider's binding, the documented use
  surface). All for a single function call.
- The native PHP `cases()` method is already the source-of-truth; a
  faker provider would just wrap it.

If you find yourself writing this enough that you want a shorthand,
add a one-line trait to your own factory base class:

```php
trait UsesEnumStates
{
    protected function randomCase(string $enumClass): \UnitEnum
    {
        /** @var array<int, \UnitEnum> $cases */
        $cases = $enumClass::cases();

        return Arr::random($cases);
    }
}
```

Then:

```php
return ['status' => $this->randomCase(UserStatusEnum::class)];
```

Same call shape as a faker provider, zero new package surface.
