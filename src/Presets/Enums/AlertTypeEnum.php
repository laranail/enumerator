<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Concerns\HasGrouping;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;

/**
 * The styling of a flash message or inline notice.
 *
 * Distinct from {@see SeverityEnum}, which classifies a log record on the
 * RFC 5424 scale. This one is presentational: `Primary` and `Mono` are not
 * severities, and nothing here maps to a syslog level.
 *
 * ## `color()` is a palette token, `value` is the CSS token
 *
 * They differ for two cases, deliberately. The backing values are the strings a
 * front end already puts in a class name — `default` and `mono` among them —
 * while `color()` answers with this package's palette, where those two are
 * `secondary` and `ghost`. Consumers wanting their own vocabulary should read
 * `->value`; consumers wanting a shared one should read `color()`.
 */
enum AlertTypeEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;

    #[Label('Notice'),  Color('secondary'), Icon('info'),           Order(10)] case Default = 'default';
    #[Label('Primary'), Color('primary'),   Icon('zap'),            Order(20)] case Primary = 'primary';
    #[Label('Success'), Color('success'),   Icon('check-circle'),   Order(30)] case Success = 'success';
    #[Label('Info'),    Color('info'),      Icon('alert-circle'),   Order(40)] case Info = 'info';
    #[Label('Warning'), Color('warning'),   Icon('alert-triangle'), Order(50)] case Warning = 'warning';
    #[Label('Error'),   Color('danger'),    Icon('x-octagon'),      Order(60)] case Error = 'error';
    #[Label('Muted'),   Color('ghost'),     Icon('minus'),          Order(70)] case Mono = 'mono';

    /**
     * @return array<string, list<self>>
     */
    public static function groups(): array
    {
        return [
            'positive'  => [self::Success],
            'negative'  => [self::Error],
            'attention' => [self::Warning],
            'neutral'   => [self::Default, self::Info, self::Mono],
            'branded'   => [self::Primary],
        ];
    }
}
