<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Illuminate\Support\HtmlString;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorNameException;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;
use Simtabi\Laranail\Enumerator\Helpers\Humanizer;
use Simtabi\Laranail\Enumerator\Support\AttributeBag;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use Simtabi\Laranail\Enumerator\Support\EnumeratorRegistry;

/**
 * Class-constant enumerator engine. Brings native-enum-style behaviour to a
 * `class` with public `const` declarations.
 *
 * Usage:
 *
 *     class UserStatusEnum extends AbstractEnumeratorClass
 *     {
 *         #[Label('Active'), Color('success')]
 *         public const ACTIVE = 'active';
 *     }
 *
 *     $s = UserStatusEnum::ACTIVE();
 *     $s->label();                 // "Active"
 *     $s->equals(UserStatusEnum::ACTIVE()); // true
 *     UserStatusEnum::isValid('active');     // true
 *     UserStatusEnum::cases();               // array<UserStatusEnum>
 *
 * Mutually exclusive with `HasEnumeratorBehavior` — pick one.
 */
trait HasClassEnumBehavior
{
    private string|int|null $value = null;

    private ?string $key = null;

    final public function __construct() {}

    /**
     * Build an instance for the given backed value.
     */
    public function make(string|int $value): static
    {
        $constants = CasesCache::classConstants(static::class);
        $key = array_search($value, $constants, true);
        if ($key === false) {
            throw new InvalidEnumeratorValueException(sprintf(
                'Value "%s" is not a constant on %s.',
                (string) $value,
                static::class,
            ));
        }

        $this->value = $value;
        $this->key = $key;

        return $this;
    }

    public static function fromValue(string|int $value): static
    {
        return (new static)->make($value);
    }

    public static function tryFromValue(string|int $value): ?static
    {
        try {
            return static::fromValue($value);
        } catch (InvalidEnumeratorValueException) {
            return null;
        }
    }

    public static function fromKey(string $key): static
    {
        $constants = CasesCache::classConstants(static::class);
        if (! array_key_exists($key, $constants)) {
            throw new InvalidEnumeratorNameException(sprintf(
                'Constant "%s" not declared on %s.',
                $key,
                static::class,
            ));
        }

        return (new static)->make($constants[$key]);
    }

    public static function tryFromKey(string $key): ?static
    {
        try {
            return static::fromKey($key);
        } catch (InvalidEnumeratorNameException) {
            return null;
        }
    }

    /**
     * Magic case access: UserStatus::ACTIVE() → instance.
     *
     * @param  array<int, mixed>  $arguments  (ignored)
     */
    public static function __callStatic(string $name, array $arguments): static
    {
        return static::fromKey($name);
    }

    public function getValue(): string|int|null
    {
        return $this->value;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equals(?object $other): bool
    {
        if ($other === null) {
            return false;
        }

        return $other::class === static::class
            && method_exists($other, 'getValue')
            && $other->getValue() === $this->value;
    }

    public function is(mixed $target): bool
    {
        if ($target instanceof static) {
            return $this->equals($target);
        }

        return $target === $this->value || $target === $this->key;
    }

    public function isNot(mixed $target): bool
    {
        return ! $this->is($target);
    }

    /**
     * @param  iterable<int, mixed>  $targets
     */
    public function in(iterable $targets): bool
    {
        foreach ($targets as $target) {
            if ($this->is($target)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  iterable<int, mixed>  $targets
     */
    public function notIn(iterable $targets): bool
    {
        return ! $this->in($targets);
    }

    /**
     * @return array<string, string|int>
     */
    public static function toArrayMap(): array
    {
        return CasesCache::classConstants(static::class);
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(CasesCache::classConstants(static::class));
    }

    /**
     * @return array<int, string|int>
     */
    public static function values(): array
    {
        return array_values(CasesCache::classConstants(static::class));
    }

    /**
     * @return array<int, static>
     */
    public static function cases(): array
    {
        $out = [];
        foreach (CasesCache::classConstants(static::class) as $value) {
            $out[] = (new static)->make($value);
        }

        return $out;
    }

    /**
     * @return CasesCollection<int, static>
     */
    public static function collect(): CasesCollection
    {
        return new CasesCollection(static::cases());
    }

    public static function count(): int
    {
        return count(CasesCache::classConstants(static::class));
    }

    public static function random(): static
    {
        $values = array_values(CasesCache::classConstants(static::class));

        return (new static)->make($values[array_rand($values)]);
    }

    public static function hasCase(string $key): bool
    {
        return array_key_exists($key, CasesCache::classConstants(static::class));
    }

    public static function isValid(string|int $value): bool
    {
        return in_array($value, CasesCache::classConstants(static::class), true);
    }

    /**
     * @return array<int|string, string>
     */
    public static function labels(): array
    {
        $out = [];
        foreach (static::cases() as $case) {
            /** @var static $case */
            $out[(string) $case->getValue()] = (string) $case->label();
        }

        return $out;
    }

    /**
     * @return array<int|string, string>
     */
    public static function options(?string $placeholder = null): array
    {
        $opts = $placeholder !== null ? ['' => $placeholder] : [];

        return $opts + static::labels();
    }

    public function label(?string $locale = null): string
    {
        return $this->resolvedAttribute('label')
            ?? Humanizer::humanize($this->key ?? (string) $this->value);
    }

    public function description(?string $locale = null): ?string
    {
        return $this->resolvedAttribute('description');
    }

    public function color(): ?string
    {
        return $this->resolvedAttribute('color');
    }

    public function icon(): ?string
    {
        return $this->resolvedAttribute('icon');
    }

    public function help(): ?string
    {
        return $this->resolvedAttribute('help');
    }

    public function order(): ?int
    {
        return $this->attributeBag()->order;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(?string $key = null): mixed
    {
        $resolver = EnumeratorRegistry::instance()?->overrides;
        $merged = $resolver?->mergeMeta($this, $this->attributeBag()->meta)
            ?? ($this->attributeBag()->meta ?? []);

        return $key === null ? $merged : ($merged[$key] ?? null);
    }

    public function toHtml(?string $framework = null): HtmlString
    {
        $framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
        $classes = $this->cssClass($framework) ?? sprintf('enumerator-badge enumerator-%s', $this->color() ?? 'default');
        $icon = $this->icon();
        $iconHtml = $icon !== null
            ? sprintf('<span class="enumerator-icon" aria-hidden="true">%s</span> ', e($icon))
            : '';

        return new HtmlString(sprintf(
            '<span class="%s" role="status">%s%s</span>',
            e($classes),
            $iconHtml,
            e($this->label()),
        ));
    }

    public function cssClass(?string $framework = null): ?string
    {
        $framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
        $override = EnumeratorRegistry::instance()?->overrides->resolve($this, 'css_class.' . $framework);
        if (is_string($override)) {
            return $override;
        }

        return $this->attributeBag()->cssClassFor($framework);
    }

    /**
     * @return array{value: string|int|null, key: string|null, label: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'key' => $this->key,
            'label' => $this->label(),
        ];
    }

    /**
     * @return array{value: string|int|null, key: string|null, label: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    protected function resolvedAttribute(string $field): ?string
    {
        $registry = EnumeratorRegistry::instance();
        $override = $registry?->overrides->resolve($this, $field);
        if (is_string($override) && $override !== '') {
            return $override;
        }
        $bag = $this->attributeBag();

        return match ($field) {
            'label' => $bag->label,
            'description' => $bag->description,
            'color' => $bag->color,
            'icon' => $bag->icon,
            'help' => $bag->help,
            default => null,
        };
    }

    private function attributeBag(): AttributeBag
    {
        return AttributesCache::for($this);
    }
}
