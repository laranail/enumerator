<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

interface Translatable
{
    /**
     * Translation namespace (e.g. "enumerator", "app", "auth").
     */
    public static function translationNamespace(): string;

    /**
     * Slug used in the translation key. Defaults to snake-case of the class
     * basename, trailing "Enum" stripped.
     */
    public static function translationSlug(): string;
}
