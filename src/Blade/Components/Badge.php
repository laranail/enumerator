<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Blade\Components\Concerns\RoutesToFrameworkView;
use UnitEnum;

/**
 * Renders an enum case as a badge. Routes to
 * `resources/views/components/{framework}/badge.blade.php`, which owns its
 * CSS class strings and forwards to `_base/badge.blade.php`.
 *
 * Override props (all nullable):
 *   classes        replace the root <span>/<a> class string
 *   iconClasses    replace the icon span class string
 *   labelClasses   replace the label span class string
 *   rootId         set the root element id
 */
class Badge extends Component
{
    use RoutesToFrameworkView;

    public function __construct(
        public UnitEnum|AbstractEnumeratorClass $case,
        public ?string $framework = null,
        public string $iconPosition = 'before',
        public ?string $href = null,
        public ?string $ariaLabel = null,
        public ?string $classes = null,
        public ?string $iconClasses = null,
        public ?string $labelClasses = null,
        public ?string $rootId = null,
    ) {}

    public function render(): View
    {
        return view($this->frameworkView('badge'), [
            'appendClasses' => $this->consumerClasses(),
            'case' => $this->case,
            'iconPosition' => $this->iconPosition,
            'href' => $this->href,
            'ariaLabel' => $this->ariaLabel,
            'overrideClasses' => $this->classes,
            'overrideIconClasses' => $this->iconClasses,
            'overrideLabelClasses' => $this->labelClasses,
            'overrideRootId' => $this->rootId,
        ]);
    }
}
