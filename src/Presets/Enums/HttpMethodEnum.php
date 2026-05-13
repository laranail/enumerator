<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum HttpMethodEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('GET')] case GET = 'GET';
    #[Label('POST')] case POST = 'POST';
    #[Label('PUT')] case PUT = 'PUT';
    #[Label('PATCH')] case PATCH = 'PATCH';
    #[Label('DELETE')] case DELETE = 'DELETE';
    #[Label('HEAD')] case HEAD = 'HEAD';
    #[Label('OPTIONS')] case OPTIONS = 'OPTIONS';

    public function isSafe(): bool
    {
        return in_array($this, [self::GET, self::HEAD, self::OPTIONS], true);
    }

    public function isIdempotent(): bool
    {
        return $this !== self::POST && $this !== self::PATCH;
    }
}
