<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns\Core;

use Simtabi\Laranail\Enumerator\Concerns\HasAttributes;
use Simtabi\Laranail\Enumerator\Concerns\HasEquality;
use Simtabi\Laranail\Enumerator\Concerns\HasFromHelpers;
use Simtabi\Laranail\Enumerator\Concerns\IsJsonable;
use Simtabi\Laranail\Enumerator\Concerns\IsTranslatable;
use Simtabi\Laranail\Enumerator\Concerns\RendersHtml;
use Simtabi\Laranail\Enumerator\Concerns\ResolvesMagicCalls;

/**
 * Core behaviour composition for native PHP 8.3+ enums.
 *
 * Bundles the seven always-on concerns so a downstream umbrella can
 * `use BehaviorCore` in one line instead of seven. Composes:
 *
 *   - HasAttributes      attribute lookup with config-override layer
 *   - HasEquality        is / isNot / in / notIn / equals
 *   - HasFromHelpers     fromName / tryFromName / fromMeta / tryFromMeta / coerce
 *   - IsJsonable         toArray / toJson / jsonSerialize
 *   - IsTranslatable     label / description / help / placeholder
 *   - RendersHtml        toHtml (also delivered separately via HtmlRenderable)
 *   - ResolvesMagicCalls central __call dispatcher for opt-in magic concerns
 *
 * Most consumers should reach for `HasEnumerator` (the full umbrella) or
 * `HasEnumeratorBehavior` (core + static collection helpers) rather than
 * using this trait directly. It exists for the rare case where you want
 * the behaviour delegations without the collection helpers attached.
 *
 * The seven composed traits remain individually `public` so advanced
 * consumers can compose à la carte; this trait is purely a convenience.
 */
trait BehaviorCore
{
    use HasAttributes;
    use HasEquality;
    use HasFromHelpers;
    use IsJsonable;
    use IsTranslatable;
    use RendersHtml;
    use ResolvesMagicCalls;
}
