<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Infolists;

use Filament\Infolists\Components\TextEntry;

if (! class_exists(TextEntry::class)) {
    return;
}

final class EnumeratorEntry extends TextEntry
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
    }
}
