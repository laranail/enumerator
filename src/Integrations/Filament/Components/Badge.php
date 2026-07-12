<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Components;

use Filament\Infolists\Components\TextEntry;

if (! class_exists(TextEntry::class)) {
    return;
}

final class Badge extends TextEntry
{
    public function setUp(): void
    {
        parent::setUp();
        $this->badge();
        $this->formatStateUsing(static function (mixed $state): string {
            if (is_object($state) && method_exists($state, 'label')) {
                return (string) $state->label();
            }

            return is_scalar($state) ? (string) $state : '';
        });
    }
}
