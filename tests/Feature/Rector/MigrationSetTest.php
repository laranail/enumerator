<?php

declare(strict_types=1);

use Rector\Rector\AbstractRector;
use Simtabi\Laranail\Enumerator\Rector\RectorBenSampoEnumToEnumerator;
use Simtabi\Laranail\Enumerator\Rector\RectorSpatieEnumToEnumerator;
use Simtabi\Laranail\Enumerator\Rector\Sets\MigrationSet;

// Rector migration rules.
//
// Rector itself isn't a dev-dep (consumers add it themselves when
// migrating). The rule classes use file-level `class_exists` guards
// against `\Rector\Rector\AbstractRector`, so they only define the
// class when Rector is installed. Without Rector, the file loads
// cleanly and defines nothing.

it('MigrationSet::rules() lists both migration rule classes', function (): void {
    $rules = MigrationSet::rules();

    expect($rules)->toBe([
        RectorBenSampoEnumToEnumerator::class,
        RectorSpatieEnumToEnumerator::class,
    ]);
});

it('rule files load cleanly when Rector is absent (no fatal at autoload)', function (): void {
    // Triggering the autoloader for the file should not throw.
    // The file-level guard early-returns if AbstractRector is missing,
    // and the autoloader treats the missing-class case as "class not
    // found" rather than a fatal.
    $found = class_exists(RectorBenSampoEnumToEnumerator::class);
    $expected = class_exists(AbstractRector::class);

    // When Rector IS installed → class_exists returns true.
    // When Rector is NOT installed → class_exists returns false (graceful skip via the file guard).
    expect($found)->toBe($expected);
});

it('the BenSampo rule class loads when Rector is installed (skipped here since Rector is not a dev-dep)', function (): void {
    if (! class_exists(AbstractRector::class)) {
        $this->markTestSkipped('rectorphp/rector not installed — the rule class file-guard correctly skips defining the class.');
    }
    expect(class_exists(RectorBenSampoEnumToEnumerator::class))->toBeTrue();
});
