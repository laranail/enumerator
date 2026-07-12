<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

/**
 * Resolved attribute snapshot for a single case (or for the enum class
 * itself). All properties default to null/empty when the case carries no
 * such attribute.
 */
final class AttributeBag
{
    public ?string $label = null;

    public ?string $description = null;

    public ?string $color = null;

    public ?string $icon = null;

    public ?string $help = null;

    public ?int $order = null;

    public ?int $bit = null;

    /** @var array<string, mixed>|null */
    public ?array $meta = null;

    /** @var array<string, string> map of framework => class string */
    public array $cssClasses = [];

    public function metaValue(string $key): mixed
    {
        return $this->meta[$key] ?? null;
    }

    public function cssClassFor(string $framework): ?string
    {
        return $this->cssClasses[$framework] ?? null;
    }
}
