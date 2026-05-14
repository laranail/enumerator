<?php

declare(strict_types=1);

use Illuminate\View\ComponentAttributeBag;
use Simtabi\Laranail\Enumerator\Blade\Components\Badge;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\RenderableStatusEnum;

// RoutesToFrameworkView — framework resolution + attribute extraction.

it('frameworkView() composes the namespaced view path for the configured framework', function (): void {
    config()->set('enumerator.css_framework', 'bootstrap');
    config()->set('enumerator.view_namespace', 'laranail-enumerator');

    $component = new Badge(RenderableStatusEnum::Active);
    $method = (new ReflectionMethod($component, 'frameworkView'))->setAccessible(true) ?? null;
    $r = new ReflectionMethod($component, 'frameworkView');
    $r->setAccessible(true);
    expect($r->invoke($component, 'badge'))->toBe('laranail-enumerator::components.bootstrap.badge');
});

it('frameworkView() respects the per-component framework prop over config', function (): void {
    config()->set('enumerator.css_framework', 'bootstrap');

    $component = new Badge(RenderableStatusEnum::Active, framework: 'tailwind');
    $r = new ReflectionMethod($component, 'frameworkView');
    $r->setAccessible(true);
    expect($r->invoke($component, 'badge'))->toBe('laranail-enumerator::components.tailwind.badge');
});

it('frameworkView() falls back to plain for unknown frameworks', function (): void {
    $component = new Badge(RenderableStatusEnum::Active, framework: 'no-such-framework');
    $r = new ReflectionMethod($component, 'frameworkView');
    $r->setAccessible(true);
    expect($r->invoke($component, 'badge'))->toBe('laranail-enumerator::components.plain.badge');
});

it('consumerClasses() returns the class string from the attribute bag', function (): void {
    $component = new Badge(RenderableStatusEnum::Active);
    $component->attributes = new ComponentAttributeBag(['class' => 'my-extra-class']);

    $r = new ReflectionMethod($component, 'consumerClasses');
    $r->setAccessible(true);
    expect($r->invoke($component))->toBe('my-extra-class');
});

it('consumerClasses() returns empty string when no class= attribute is set', function (): void {
    $component = new Badge(RenderableStatusEnum::Active);
    $component->attributes = new ComponentAttributeBag([]);

    $r = new ReflectionMethod($component, 'consumerClasses');
    $r->setAccessible(true);
    expect($r->invoke($component))->toBe('');
});

it('consumerClasses() returns empty when $attributes is null', function (): void {
    $component = new Badge(RenderableStatusEnum::Active);
    // Laravel sets $attributes after construction; before it, it's null.

    $r = new ReflectionMethod($component, 'consumerClasses');
    $r->setAccessible(true);
    expect($r->invoke($component))->toBe('');
});

it('consumerId() returns the id from the attribute bag', function (): void {
    $component = new Badge(RenderableStatusEnum::Active);
    $component->attributes = new ComponentAttributeBag(['id' => 'badge-1']);

    $r = new ReflectionMethod($component, 'consumerId');
    $r->setAccessible(true);
    expect($r->invoke($component))->toBe('badge-1');
});

it('consumerId() returns null when no id= attribute is set', function (): void {
    $component = new Badge(RenderableStatusEnum::Active);
    $component->attributes = new ComponentAttributeBag([]);

    $r = new ReflectionMethod($component, 'consumerId');
    $r->setAccessible(true);
    expect($r->invoke($component))->toBeNull();
});

it('consumerId() returns null when $attributes is null', function (): void {
    $component = new Badge(RenderableStatusEnum::Active);

    $r = new ReflectionMethod($component, 'consumerId');
    $r->setAccessible(true);
    expect($r->invoke($component))->toBeNull();
});

it('consumerId() returns null when id= is an empty string', function (): void {
    $component = new Badge(RenderableStatusEnum::Active);
    $component->attributes = new ComponentAttributeBag(['id' => '']);

    $r = new ReflectionMethod($component, 'consumerId');
    $r->setAccessible(true);
    expect($r->invoke($component))->toBeNull();
});
