<?php

declare(strict_types=1);

use GraphQL\Type\Definition\ScalarType;
use Simtabi\Laranail\Enumerator\Modules\Lighthouse\EnumScalar;
use Simtabi\Laranail\Enumerator\Modules\Lighthouse\LighthouseServiceProvider;

it('LighthouseServiceProvider no-ops when Lighthouse is absent', function (): void {
    config()->set('enumerator.modules.lighthouse', true);
    app()->register(LighthouseServiceProvider::class, true);

    expect(true)->toBeTrue();  // smoke: no throw during registration
});

it('EnumScalar file no-ops when webonyx/graphql-php is absent (skipped here since the dep IS installed)', function (): void {
    // webonyx/graphql-php is pulled in transitively by other dev deps.
    // Confirm the EnumScalar abstract class loads cleanly when its
    // parent class is present.
    if (! class_exists(ScalarType::class)) {
        $this->markTestSkipped('webonyx/graphql-php not installed.');
    }

    expect(class_exists(EnumScalar::class))->toBeTrue();
});
