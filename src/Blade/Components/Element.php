<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Blade\Components\Concerns\RoutesToFrameworkView;
use UnitEnum;

class Element extends Component
{
    use RoutesToFrameworkView;

    public function __construct(
        public UnitEnum|AbstractEnumeratorClass $case,
        public string $as = 'span',
        public ?string $href = null,
        public ?string $type = null,
        public ?string $for = null,
        public bool $showIcon = false,
        public bool $showLabel = true,
        public bool $showBadge = false,
        public ?string $framework = null,
        public ?string $classes = null,
        public ?string $iconClasses = null,
        public ?string $labelClasses = null,
        public ?string $badgeClasses = null,
        public ?string $rootId = null,
    ) {}

    public function render(): View
    {
        return view($this->frameworkView('element'), [
            'appendClasses' => $this->consumerClasses(),
            'case' => $this->case,
            'as' => $this->as,
            'href' => $this->href,
            'type' => $this->type,
            'for' => $this->for,
            'showIcon' => $this->showIcon,
            'showLabel' => $this->showLabel,
            'showBadge' => $this->showBadge,
            'overrideClasses' => $this->classes,
            'overrideIconClasses' => $this->iconClasses,
            'overrideLabelClasses' => $this->labelClasses,
            'overrideBadgeClasses' => $this->badgeClasses,
            'overrideRootId' => $this->rootId,
        ]);
    }
}
