<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Helpers\Humanizer;

it('humanizes constant case names', function (): void {
    expect(Humanizer::humanize('ACTIVE'))->toBe('Active');
    expect(Humanizer::humanize('IN_PROGRESS'))->toBe('In Progress');
});

it('humanizes PascalCase names', function (): void {
    expect(Humanizer::humanize('NeedsRevision'))->toBe('Needs Revision');
    expect(Humanizer::humanize('InProgress'))->toBe('In Progress');
});

it('humanizes kebab and snake', function (): void {
    expect(Humanizer::humanize('needs-revision'))->toBe('Needs Revision');
    expect(Humanizer::humanize('needs_revision'))->toBe('Needs Revision');
});

it('slugifies enum class names dropping trailing Enum', function (): void {
    expect(Humanizer::slugify('UserStatusEnum'))->toBe('user_status');
    expect(Humanizer::slugify('OrderStatus'))->toBe('order_status');
});
