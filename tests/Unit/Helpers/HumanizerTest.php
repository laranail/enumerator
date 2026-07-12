<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Helpers\Humanizer;

// Humanizer — humanize() + slugify() correctness across input shapes.

it('humanize() title-cases all-uppercase constants', function (): void {
    expect(Humanizer::humanize('ACTIVE'))->toBe('Active');
    expect(Humanizer::humanize('IN_PROGRESS'))->toBe('In Progress');
});

it('humanize() handles PascalCase', function (): void {
    expect(Humanizer::humanize('NeedsRevision'))->toBe('Needs Revision');
});

it('humanize() handles camelCase', function (): void {
    expect(Humanizer::humanize('needsRevision'))->toBe('Needs Revision');
});

it('humanize() handles kebab-case', function (): void {
    expect(Humanizer::humanize('needs-revision'))->toBe('Needs Revision');
});

it('humanize() preserves already-titled phrases', function (): void {
    expect(Humanizer::humanize('Already Titled'))->toBe('Already Titled');
});

it('humanize() handles acronym-PascalCase', function (): void {
    expect(Humanizer::humanize('HTTPMethod'))->toBe('Http Method');
    expect(Humanizer::humanize('XMLHttpRequest'))->toBe('Xml Http Request');
});

it('slugify() snake-cases a class basename and strips trailing Enum', function (): void {
    expect(Humanizer::slugify('UserStatusEnum'))->toBe('user_status');
    expect(Humanizer::slugify('OrderStatusEnum'))->toBe('order_status');
});

it('slugify() snake-cases without an Enum suffix', function (): void {
    expect(Humanizer::slugify('UserStatus'))->toBe('user_status');
});

it('slugify() handles all-uppercase acronyms', function (): void {
    expect(Humanizer::slugify('HTTPMethod'))->toBe('h_t_t_p_method');
});
