<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Columns;

use Filament\Tables\Columns\TextColumn;

if (! class_exists(TextColumn::class)) {
    return;
}

/**
 * Filament 4+ table column that auto-formats enumerator cases via ->label().
 * Falls back gracefully when the value is a raw string.
 */
final class EnumeratorColumn extends TextColumn
{
    public function setUp(): void
    {
        parent::setUp();
        $this->formatStateUsing(static function (mixed $state): string {
            if (is_object($state) && method_exists($state, 'label')) {
                return (string) $state->label();
            }

            return is_scalar($state) ? (string) $state : '';
        });
        $this->badge();
    }
}
