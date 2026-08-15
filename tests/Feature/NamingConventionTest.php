<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;
use Symfony\Component\Console\Command\Command;

/**
 * Every name this package registers into a framework-owned registry.
 *
 * They are flat maps keyed by the name, so a second package claiming one does
 * not collide loudly — it silently replaces the first, and the damage surfaces
 * far away as a missing translation or a command that runs someone else's code.
 * `enumerator` is a plausible key for an application's own.
 *
 * Read from the booted application rather than the provider's source, so the
 * guard survives a refactor of the registration code.
 */
it('registers every command under the laranail::<slug>.<command> shape', function (): void {
    $names = array_keys(app(Kernel::class)->all());

    $ours = array_values(array_filter(
        $names,
        static fn (string $name): bool => str_contains($name, 'enumerator'),
    ));

    expect($ours)->not->toBeEmpty();

    foreach ($ours as $name) {
        expect($name)->toStartWith('laranail::enumerator.');
    }
});

it('claims no generic short alias', function (): void {
    // Rule 3: an alias of `enumerator:cache` hands back exactly the collision
    // the namespaced name exists to avoid, and makes the convention decorative.
    // `make:enumerator` is worse — it colonises Laravel's own make: namespace.
    $names = array_keys(app(Kernel::class)->all());

    foreach (['make:enumerator', 'enumerator:cache', 'enumerator:cache:clear', 'enumerator:annotate', 'enumerator:export', 'enumerator:ide-helper'] as $generic) {
        expect($names)->not->toContain($generic);
    }

    /** @var array<string, Command> $commands */
    $commands = app(Kernel::class)->all();

    foreach ($commands as $name => $command) {
        if (! str_contains($name, 'enumerator')) {
            continue;
        }

        foreach ($command->getAliases() as $alias) {
            expect($alias)->toStartWith('laranail');
        }
    }
});

it('reads its config from the vendor-namespaced key', function (): void {
    expect(config('laranail.enumerator'))->not->toBeNull()
        ->and(config('enumerator'))->toBeNull();
});

it('registers its translations under vendor and slug', function (): void {
    $namespaces = Lang::getLoader()->namespaces();

    expect($namespaces)->toHaveKey('laranail-enumerator')
        ->and($namespaces)->not->toHaveKey('enumerator');
});

it('registers its views under vendor and slug', function (): void {
    // Through the factory rather than app('view'), which resolves as mixed:
    // getHints() is on FileViewFinder, not on the ViewFinderInterface that
    // getFinder() advertises.
    $finder = ViewFacade::getFinder();
    \assert($finder instanceof FileViewFinder);

    $hints = array_keys($finder->getHints());

    expect($hints)->toContain('laranail-enumerator')
        ->and($hints)->not->toContain('enumerator');
});

it('publishes under vendor-scoped tags and claims no bare one', function (): void {
    $groups = ServiceProvider::publishableGroups();

    $ours = array_values(array_filter(
        $groups,
        static fn (mixed $group): bool => is_string($group) && str_contains($group, 'enumerator'),
    ));

    expect($ours)->not->toBeEmpty();

    foreach ($ours as $group) {
        expect($group)->toStartWith('laranail::enumerator-');
    }
});
