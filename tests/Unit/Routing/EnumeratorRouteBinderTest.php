<?php

declare(strict_types=1);

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Routing\EnumeratorRouteBinder;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;

// EnumeratorRouteBinder — implicit-binding registrar for native + class-const.

function bindAndResolve(string $param, string $enumClass, ?object $fallback, string $urlValue): mixed
{
    EnumeratorRouteBinder::register($param, $enumClass, $fallback);

    /** @var Router $router */
    $router = app('router');
    $callback = $router->getBindingCallback($param);
    expect($callback)->not->toBeNull();

    /** @var Route $route */
    $route = new Route(['GET'], '/', fn () => null);

    return $callback($urlValue, $route);
}

it('resolves a backed-enum case from its value', function (): void {
    $case = bindAndResolve('status', StatusEnum::class, null, 'active');
    expect($case)->toBe(StatusEnum::Active);
});

it('resolves case-insensitively for backed enums', function (): void {
    $case = bindAndResolve('status2', StatusEnum::class, null, 'ACTIVE');
    expect($case)->toBe(StatusEnum::Active);
});

it('falls back to tryFromName for backed enums', function (): void {
    $case = bindAndResolve('status3', StatusEnum::class, null, 'Active');
    expect($case)->toBe(StatusEnum::Active);
});

it('resolves a pure enum case by name', function (): void {
    $case = bindAndResolve('color', PureColorEnum::class, null, 'Red');
    expect($case)->toBe(PureColorEnum::Red);
});

it('returns the fallback when no match is found', function (): void {
    $case = bindAndResolve('status4', StatusEnum::class, StatusEnum::Archived, 'nonexistent');
    expect($case)->toBe(StatusEnum::Archived);
});

it('aborts 404 when no match and no fallback', function (): void {
    bindAndResolve('status5', StatusEnum::class, null, 'nonexistent');
})->throws(HttpException::class);

it('aborts 404 for a non-enumerator class', function (): void {
    bindAndResolve('bogus', stdClass::class, null, 'anything');
})->throws(HttpException::class);

it('resolves a class-const enum case by value', function (): void {
    $case = bindAndResolve('legacy', LegacyStatusEnum::class, null, 'active');
    expect($case)->toBeInstanceOf(LegacyStatusEnum::class);
    expect($case->getValue())->toBe('active');
});

it('resolves a class-const enum case by key when value misses', function (): void {
    $case = bindAndResolve('legacy2', LegacyStatusEnum::class, null, 'ACTIVE');
    expect($case)->toBeInstanceOf(LegacyStatusEnum::class);
});
