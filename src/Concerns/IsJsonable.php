<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use UnitEnum;

/**
 * `JsonSerializable`-friendly representation for enum cases.
 * Outputs a stable shape: ['value', 'name', 'label'].
 *
 * The umbrella HasEnumeratorBehavior pulls this in; consumers normally don't
 * need to use it directly.
 */
trait IsJsonable
{
    /**
     * Convert to a plain array (rich form).
     *
     * @return array{value: string|int, name: string, label: string}
     */
    public function toArray(): array
    {
        /** @phpstan-var UnitEnum $self */
        $self = $this;

        return [
            'value' => $self instanceof BackedEnum ? $self->value : $self->name,
            'name' => $self->name,
            'label' => method_exists($self, 'label') ? (string) $self->label() : $self->name,
        ];
    }

    /**
     * @return array{value: string|int, name: string, label: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }
}
