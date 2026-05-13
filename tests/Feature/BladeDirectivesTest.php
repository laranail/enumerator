<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\RenderableStatusEnum;

// Blade directives — render each directive against an enum case in a
// throwaway Blade view and verify the output.

/**
 * @param  array<string, mixed>  $data
 */
function render(string $blade, array $data = []): string
{
    return Blade::render($blade, $data);
}

it('@enumeratorLabel prints the case label', function (): void {
    expect(render('@enumeratorLabel($case)', ['case' => StatusEnum::Active]))
        ->toBe('Active');
});

it('@enumeratorValue prints the backing value for a backed enum', function (): void {
    expect(render('@enumeratorValue($case)', ['case' => StatusEnum::Active]))
        ->toBe('active');
});

it('@enumeratorName prints the case name', function (): void {
    expect(render('@enumeratorName($case)', ['case' => StatusEnum::Active]))
        ->toBe('Active');
});

it('@enumeratorBadge renders the HtmlString output', function (): void {
    config()->set('enumerator.css_framework', 'bootstrap');
    $out = render('@enumeratorBadge($case)', ['case' => RenderableStatusEnum::Active]);
    expect($out)->toContain('Active');
});

it('@enumeratorIs ... @endEnumeratorIs gates content correctly', function (): void {
    // Spaces around inner content matter — Blade's directive regex requires
    // a non-word character before the `@` of `@endEnumeratorIs`.
    $template = '@enumeratorIs($case, $target) YES @endEnumeratorIs';
    expect(trim(render($template, ['case' => StatusEnum::Active, 'target' => StatusEnum::Active])))
        ->toBe('YES');
    expect(trim(render($template, ['case' => StatusEnum::Active, 'target' => StatusEnum::Archived])))
        ->toBe('');
});

it('@enumeratorIn ... @endEnumeratorIn gates content correctly', function (): void {
    $template = '@enumeratorIn($case, $targets) YES @endEnumeratorIn';
    expect(trim(render($template, [
        'case' => StatusEnum::Active,
        'targets' => [StatusEnum::Active, StatusEnum::Pending],
    ])))->toBe('YES');
});

it('@enumeratorColor prints the color attribute', function (): void {
    expect(render('@enumeratorColor($case)', ['case' => StatusEnum::Active]))
        ->toBe('success');
});

it('@enumeratorIcon prints the icon attribute', function (): void {
    expect(render('@enumeratorIcon($case)', ['case' => StatusEnum::Active]))
        ->toBe('check-circle');
});

it('@enumeratorDescription prints empty when no description is set', function (): void {
    expect(render('@enumeratorDescription($case)', ['case' => StatusEnum::Active]))
        ->toBe('');
});

it('@enumeratorHelp prints empty when no help is set', function (): void {
    expect(render('@enumeratorHelp($case)', ['case' => StatusEnum::Active]))
        ->toBe('');
});

it('@enumeratorMeta prints the keyed meta value when present', function (): void {
    config()->set('enumerator.overrides', [
        StatusEnum::class => ['Active' => ['meta' => ['priority' => 'high']]],
    ]);
    expect(render("@enumeratorMeta(\$case, 'priority')", ['case' => StatusEnum::Active]))
        ->toBe('high');
});
