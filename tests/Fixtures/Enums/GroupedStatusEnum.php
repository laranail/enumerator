<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Grouped fixture — exercises HasGrouping's positive/negative/pending
 * convenience predicates and the `<x-laranail-enumerator::select
 * groupsBy="groups" />` component path.
 */
enum GroupedStatusEnum: string implements Enumerator
{
    use HasEnumerator;

    #[Label('Active')] case Active = 'active';
    #[Label('Approved')] case Approved = 'approved';
    #[Label('Pending')] case Pending = 'pending';
    #[Label('Review')] case Review = 'review';
    #[Label('Rejected')] case Rejected = 'rejected';
    #[Label('Banned')] case Banned = 'banned';

    /**
     * @return array<string, array<int, self>>
     */
    public static function groups(): array
    {
        return [
            'positive' => [self::Active, self::Approved],
            'pending' => [self::Pending, self::Review],
            'negative' => [self::Rejected, self::Banned],
        ];
    }
}
