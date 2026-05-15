<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use Illuminate\Support\HtmlString;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Casts\AsEnum;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;
use UnitEnum;

/**
 * Consumer-side trait. Use on **Eloquent Models**,
 * **Livewire Components**, or **FormRequests** to wire enum attributes
 * declaratively. Distinct from `HasEnumerator` (which is for DEFINING
 * native enums).
 *
 * Usage:
 *
 *     class User extends Model
 *     {
 *         use HasEnumAttributes;
 *
 *         protected function enumAttributes(): array
 *         {
 *             return [
 *                 'status' => UserStatusEnum::class,
 *                 'role'   => ['enum' => RoleEnum::class, 'cast' => AsEnum::class],
 *             ];
 *         }
 *     }
 *
 *     // Generated accessors (no manual code):
 *     $user->status_label;        // "Active"
 *     $user->status_color;        // "success"
 *     $user->status_icon;         // "check-circle"
 *     $user->status_description;  // long-form
 *     $user->status_help;         // help text
 *     $user->status_value;        // backing value
 *     $user->status_name;         // case name
 *     $user->status_badge;        // HtmlString
 *     $user->status_meta;         // array
 *     $user->statusIs($x);        // bool
 *     $user->statusIn([...]);     // bool
 *     $user->statusEquals($x);    // bool
 *
 * Host detection uses `method_exists` sniffs (NOT `instanceof` against
 * framework classes) so the trait has no hard dependency on Eloquent,
 * Livewire, or FormRequest. On Eloquent, casts auto-register at boot.
 */
trait HasEnumAttributes
{
    /**
     * Consumer overrides this to declare the enum-typed attributes.
     *
     * @return array<string, class-string|array{enum: class-string, cast?: class-string}>
     */
    protected function enumAttributes(): array
    {
        return [];
    }

    /**
     * Eloquent `bootTraits()` calls this automatically (per Laravel's
     * convention). Livewire / FormRequest hosts call manually if they
     * want auto-registration; otherwise the trait still provides the
     * accessor + `__call` surface passively.
     */
    public static function bootHasEnumAttributes(): void
    {
        // Static boot hook — no-op by design; per-instance registration
        // happens in initializeHasEnumAttributes (Eloquent's per-instance
        // initializer) and via __get / __call below for non-Eloquent hosts.
    }

    /**
     * Eloquent `initializeTraits()` calls this on every model instance.
     * Auto-registers casts for declared enum attributes.
     *
     * Uses `mergeCasts()` — the Laravel 11+/13 method that merges into
     * `$this->casts`. Falls back to direct property manipulation if the
     * method isn't available (older Laravel or non-Model hosts).
     */
    public function initializeHasEnumAttributes(): void
    {
        $casts = [];
        $existingCasts = method_exists($this, 'getCasts') ? $this->getCasts() : [];
        foreach ($this->resolveEnumAttributesConfig() as $attribute => $config) {
            // Skip if the consumer already declared a cast for this attribute.
            if (isset($existingCasts[$attribute])) {
                continue;
            }
            $castClass = $config['cast'] ?? AsEnum::class;
            $casts[$attribute] = $castClass . ':' . $config['enum'];
        }

        if ($casts === []) {
            return;
        }

        if (method_exists($this, 'mergeCasts')) {
            $this->mergeCasts($casts);

            return;
        }

        // Fallback for non-Eloquent hosts or older Laravel: stash on the
        // property directly if it exists.
        if (property_exists($this, 'casts') && is_array($this->casts)) {
            $this->casts = array_merge($this->casts, $casts);
        }
    }

    /**
     * Magic accessor: `$model->status_label`, `$model->status_color`, etc.
     * Delegates to the underlying enum instance if it's hydrated.
     *
     * Untyped `$key` matches Eloquent\Model::__get's signature so this
     * trait composes cleanly on Models. Other hosts (Livewire, FormRequest)
     * pass strings here too in practice.
     */
    public function __get($key)
    {
        $derived = is_string($key) ? $this->resolveEnumDerivedAttribute($key) : null;
        if ($derived !== null) {
            return $derived['value'];
        }

        // Defer to parent `__get` (Eloquent's) when not an enum-derived
        // accessor. Parent classes typically provide their own implementation.
        if (is_callable(['parent', '__get'])) {
            return parent::__get($key);
        }

        return $this->{$key} ?? null;
    }

    /**
     * Magic method: `$model->statusIs($x)`, `$model->statusIn([...])`, etc.
     *
     * Untyped to match Eloquent\Model::__call.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call($method, $arguments)
    {
        $resolved = is_string($method) ? $this->resolveEnumPredicate($method, (array) $arguments) : null;
        if ($resolved !== null) {
            return $resolved['result'];
        }

        if (is_callable(['parent', '__call'])) {
            return parent::__call($method, $arguments);
        }

        throw new \BadMethodCallException(sprintf(
            'Undefined method %s::%s()',
            static::class,
            $method,
        ));
    }

    /**
     * Internal: normalize the consumer's `enumAttributes()` return shape
     * into a stable `[attribute => ['enum' => class, 'cast' => class]]`.
     *
     * @return array<string, array{enum: class-string, cast?: class-string}>
     */
    private function resolveEnumAttributesConfig(): array
    {
        $raw = $this->enumAttributes();
        $resolved = [];
        foreach ($raw as $attribute => $config) {
            if (is_string($config)) {
                $resolved[$attribute] = ['enum' => $config];
            } elseif (is_array($config) && isset($config['enum'])) {
                $resolved[$attribute] = $config;
            }
        }

        return $resolved;
    }

    /**
     * Resolve `$model->status_label` and friends.
     * Returns `['value' => mixed]` on a hit, `null` to defer to parent.
     *
     * @return array{value: mixed}|null
     */
    private function resolveEnumDerivedAttribute(string $key): ?array
    {
        foreach ($this->resolveEnumAttributesConfig() as $attribute => $config) {
            foreach (self::accessorSuffixes() as $suffix => $resolver) {
                if ($key !== $attribute . '_' . $suffix) {
                    continue;
                }
                $case = $this->getEnumCase($attribute);

                return ['value' => $resolver($case)];
            }
        }

        return null;
    }

    /**
     * Resolve `$model->statusIs(...)` / `statusIn(...)` / `statusEquals(...)`.
     *
     * @param  array<int, mixed>  $arguments
     * @return array{result: bool}|null
     */
    private function resolveEnumPredicate(string $method, array $arguments): ?array
    {
        $predicates = ['Is' => 'is', 'In' => 'in', 'Equals' => 'equals'];
        foreach ($this->resolveEnumAttributesConfig() as $attribute => $config) {
            foreach ($predicates as $suffix => $caseMethod) {
                if ($method !== $attribute . $suffix) {
                    continue;
                }
                $case = $this->getEnumCase($attribute);
                if ($case === null) {
                    return ['result' => false];
                }
                if (! method_exists($case, $caseMethod)) {
                    return ['result' => false];
                }

                return ['result' => (bool) $case->{$caseMethod}(...$arguments)];
            }
        }

        return null;
    }

    /**
     * Get the enum case instance for the given attribute. Tries the
     * Eloquent attribute API first, falls back to direct property
     * access for non-Eloquent hosts.
     */
    private function getEnumCase(string $attribute): ?object
    {
        if (method_exists($this, 'getAttribute')) {
            $value = $this->getAttribute($attribute);
        } else {
            $value = $this->{$attribute} ?? null;
        }

        if ($value === null) {
            return null;
        }

        if (
            $value instanceof UnitEnum
            || $value instanceof AbstractEnumeratorClass
            || (is_object($value) && IsEnumeratorClass::check($value::class))
        ) {
            return $value;
        }

        return null;
    }

    /**
     * Suffix → projection callable map. Used by `__get` to resolve
     * `$model->{attribute}_{suffix}`. Every projection handles a null
     * case (when the underlying attribute is null) by returning the
     * empty/null/default for its return type.
     *
     * @return array<string, callable(object|null): mixed>
     */
    private static function accessorSuffixes(): array
    {
        return [
            'label' => static fn (?object $c): string => $c !== null && method_exists($c, 'label') ? (string) $c->label() : '',
            'value' => static fn (?object $c): mixed => match (true) {
                $c instanceof BackedEnum => $c->value,
                $c instanceof UnitEnum => $c->name,
                $c !== null && method_exists($c, 'getValue') => $c->getValue(),
                default => null,
            },
            'name' => static fn (?object $c): string => match (true) {
                $c instanceof UnitEnum => $c->name,
                $c !== null && method_exists($c, 'getKey') => (string) $c->getKey(),
                default => '',
            },
            'color' => static fn (?object $c): ?string => $c !== null && method_exists($c, 'color') ? $c->color() : null,
            'icon' => static fn (?object $c): ?string => $c !== null && method_exists($c, 'icon') ? $c->icon() : null,
            'description' => static fn (?object $c): ?string => $c !== null && method_exists($c, 'description') ? $c->description() : null,
            'help' => static fn (?object $c): ?string => $c !== null && method_exists($c, 'help') ? $c->help() : null,
            'badge' => static fn (?object $c): ?HtmlString => $c !== null && method_exists($c, 'toHtml') ? $c->toHtml() : null,
            'meta' => static fn (?object $c): array => $c !== null && method_exists($c, 'metaAll') ? $c->metaAll() : [],
        ];
    }
}
