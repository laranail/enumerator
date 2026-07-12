<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// / IdeHelperCommand emits real `@method` stubs.

afterEach(function (): void {
    // Cleanup any helper file the test wrote.
    @unlink(base_path('_ide_helper_test.php'));
});

it('emits a header explaining the file is IDE-only', function (): void {
    $this->artisan('enumerator:ide-helper', [
        'classes' => [StatusEnum::class],
        '--out' => '_ide_helper_test.php',
    ])->assertSuccessful();

    $contents = file_get_contents(base_path('_ide_helper_test.php'));
    expect($contents)
        ->toContain('NOT meant to be executed')
        ->toContain('autocomplete')
        ->toContain('<?php');
});

it('emits @method static stubs per case for native enums', function (): void {
    $this->artisan('enumerator:ide-helper', [
        'classes' => [StatusEnum::class],
        '--out' => '_ide_helper_test.php',
    ])->assertSuccessful();

    $contents = file_get_contents(base_path('_ide_helper_test.php'));
    // Case factories from HasInvokableCases.
    expect($contents)
        ->toContain('@method static StatusEnum Active()')
        ->toContain('@method static StatusEnum Inactive()')
        ->toContain('@method static StatusEnum Pending()')
        ->toContain('@method static StatusEnum Archived()');
});

it('emits @method bool predicates per case for native enums', function (): void {
    $this->artisan('enumerator:ide-helper', [
        'classes' => [StatusEnum::class],
        '--out' => '_ide_helper_test.php',
    ])->assertSuccessful();

    $contents = file_get_contents(base_path('_ide_helper_test.php'));
    // HasMagicComparisons-style predicates.
    expect($contents)
        ->toContain('@method bool isActive()')
        ->toContain('@method bool isNotActive()')
        ->toContain('@method bool isInactive()')
        ->toContain('@method bool isNotInactive()');
});

it('wraps the stub class in the real namespace', function (): void {
    $this->artisan('enumerator:ide-helper', [
        'classes' => [StatusEnum::class],
        '--out' => '_ide_helper_test.php',
    ])->assertSuccessful();

    $contents = file_get_contents(base_path('_ide_helper_test.php'));
    expect($contents)
        ->toContain('namespace Simtabi\\Laranail\\Enumerator\\Presets\\Enums {')
        ->toContain('class StatusEnum {}');
});

it('emits @method static stubs for class-const enums (AbstractEnumeratorClass)', function (): void {
    $this->artisan('enumerator:ide-helper', [
        'classes' => [LegacyStatusEnum::class],
        '--out' => '_ide_helper_test.php',
    ])->assertSuccessful();

    $contents = file_get_contents(base_path('_ide_helper_test.php'));
    expect($contents)
        ->toContain('@method static LegacyStatusEnum ACTIVE()')
        ->toContain('@method static LegacyStatusEnum INACTIVE()')
        ->toContain('@method static LegacyStatusEnum BANNED()');
});

it('falls back to enumerator.cache.auto_warm_classes when no args provided', function (): void {
    config()->set('enumerator.cache.auto_warm_classes', [StatusEnum::class]);

    $this->artisan('enumerator:ide-helper', [
        '--out' => '_ide_helper_test.php',
    ])->assertSuccessful();

    $contents = file_get_contents(base_path('_ide_helper_test.php'));
    expect($contents)->toContain('@method static StatusEnum Active()');
});

it('warns and exits cleanly when no classes are configured', function (): void {
    config()->set('enumerator.cache.auto_warm_classes', []);
    config()->set('enumerator.ide_helper.classes', []);

    $this->artisan('enumerator:ide-helper', [
        '--out' => '_ide_helper_test.php',
    ])
        ->expectsOutputToContain('No enumerator classes found')
        ->assertSuccessful();
});

it('rejects non-enumerator classes silently (filter, not error)', function (): void {
    $this->artisan('enumerator:ide-helper', [
        'classes' => [stdClass::class, StatusEnum::class],
        '--out' => '_ide_helper_test.php',
    ])->assertSuccessful();

    $contents = file_get_contents(base_path('_ide_helper_test.php'));
    expect($contents)
        ->toContain('StatusEnum')
        ->not->toContain('stdClass');
});
