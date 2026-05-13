<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Blade\Components\Concerns\RoutesToFrameworkView;
use UnitEnum;

class Listing extends Component
{
    use RoutesToFrameworkView;

    /** @var array<int, UnitEnum|AbstractEnumeratorClass> */
    public array $cases;

    /**
     * @param  class-string  $enum
     */
    public function __construct(
        public string $enum,
        public ?string $framework = null,
        public ?string $classes = null,
        public ?string $itemClasses = null,
        public ?string $rootId = null,
    ) {
        $this->cases = $enum::cases();
    }

    public function render(): View
    {
        return view($this->frameworkView('listing'), [
            'appendClasses' => $this->consumerClasses(),
            'cases' => $this->cases,
            'overrideClasses' => $this->classes,
            'overrideItemClasses' => $this->itemClasses,
            'overrideRootId' => $this->rootId,
            'labelOf' => static fn (object $case): string => method_exists($case, 'label')
                ? (string) $case->label()
                : (string) ($case->name ?? ''),
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
