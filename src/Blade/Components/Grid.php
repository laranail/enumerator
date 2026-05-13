<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Blade\Components\Concerns\RoutesToFrameworkView;
use UnitEnum;

class Grid extends Component
{
    use RoutesToFrameworkView;

    /** @var array<int, UnitEnum|AbstractEnumeratorClass> */
    public array $cases;

    /**
     * @param  class-string  $enum
     */
    public function __construct(
        public string $enum,
        public int $columns = 3,
        public bool $showBadges = false,
        public ?string $framework = null,
        public ?string $classes = null,
        public ?string $itemClasses = null,
        public ?string $labelClasses = null,
        public ?string $descriptionClasses = null,
        public ?string $rootId = null,
    ) {
        $this->cases = $enum::cases();
    }

    public function render(): View
    {
        return view($this->frameworkView('grid'), [
            'appendClasses' => $this->consumerClasses(),
            'cases' => $this->cases,
            'columns' => $this->columns,
            'showBadges' => $this->showBadges,
            'overrideClasses' => $this->classes,
            'overrideItemClasses' => $this->itemClasses,
            'overrideLabelClasses' => $this->labelClasses,
            'overrideDescriptionClasses' => $this->descriptionClasses,
            'overrideRootId' => $this->rootId,
            'labelOf' => static fn (object $case): string => method_exists($case, 'label')
                ? (string) $case->label()
                : (string) ($case->name ?? ''),
            'descriptionOf' => static fn (object $case): ?string => method_exists($case, 'description')
                ? $case->description()
                : null,
            'valueOf' => static function (object $case): string|int {
                if ($case instanceof BackedEnum) {
                    return $case->value;
                }
                if ($case instanceof UnitEnum) {
                    return $case->name;
                }

                /** @var AbstractEnumeratorClass $case */
                return (string) $case->getValue();
            },
        ]);
    }
}
