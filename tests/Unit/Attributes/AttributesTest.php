<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\CssClass;
use Simtabi\Laranail\Enumerator\Attributes\Description;
use Simtabi\Laranail\Enumerator\Attributes\Help;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Meta;
use Simtabi\Laranail\Enumerator\Attributes\Order;

// Attribute classes — constructor + property contract.

it('Label exposes the given string', function (): void {
    expect((new Label('Active'))->label)->toBe('Active');
});

it('Color exposes the given string', function (): void {
    expect((new Color('success'))->color)->toBe('success');
});

it('Icon exposes the given string', function (): void {
    expect((new Icon('check-circle'))->icon)->toBe('check-circle');
});

it('Order exposes the given int', function (): void {
    expect((new Order(42))->order)->toBe(42);
});

it('Description exposes the given string', function (): void {
    expect((new Description('A status case.'))->description)->toBe('A status case.');
});

it('Help exposes the given string', function (): void {
    expect((new Help('Pick one.'))->help)->toBe('Pick one.');
});

it('Bit exposes the given int', function (): void {
    expect((new Bit(4))->bit)->toBe(4);
});

it('CssClass exposes classes + framework, defaulting to "plain"', function (): void {
    $a = new CssClass('badge bg-success');
    expect($a->classes)->toBe('badge bg-success');
    expect($a->framework)->toBe('plain');

    $b = new CssClass('badge bg-success', 'bootstrap');
    expect($b->framework)->toBe('bootstrap');
});

it('Meta collects named arguments into a values array', function (): void {
    $meta = new Meta(featured: true, priority: 3);
    expect($meta->values)->toBe([
        'featured' => true,
        'priority' => 3,
    ]);
});

it('Meta throws if a positional (non-string-keyed) argument is supplied', function (): void {
    new Meta('positional-no-key');
})->throws(InvalidArgumentException::class);

it('Meta accepts an empty argument list', function (): void {
    expect((new Meta)->values)->toBe([]);
});
