<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Blade\Components\Concerns\RoutesToFrameworkView;
use UnitEnum;

class Checkboxes extends Component
{
    use RoutesToFrameworkView;

    /** @var array<int, UnitEnum|AbstractEnumeratorClass> */
    public array $cases;

    /** @var array<int, string|int> */
    public array $selectedValues = [];

    /**
     * @param  class-string  $enum
     * @param  iterable<int|string, mixed>  $selected
     */
    public function __construct(
        public string $enum,
        public string $name,
        iterable $selected = [],
        public string $layout = 'vertical',
        public ?string $legend = null,
        public bool $disabled = false,
        public bool $required = false,
        public bool $descriptions = false,
        public ?string $framework = null,
        public ?string $classes = null,
        public ?string $itemClasses = null,
        public ?string $inputClasses = null,
        public ?string $labelClasses = null,
        public ?string $legendClasses = null,
        public ?string $rootId = null,
    ) {
        $this->cases = $enum::cases();
        foreach ($selected as $item) {
            $this->selectedValues[] = $this->scalar($item);
        }
    }

    public function render(): View
    {
        return view($this->frameworkView('checkboxes'), [
            'appendClasses' => $this->consumerClasses(),
            'cases' => $this->cases,
            'name' => $this->name,
            'selectedValues' => $this->selectedValues,
            'layout' => $this->layout,
            'legend' => $this->legend,
            'disabled' => $this->disabled,
            'required' => $this->required,
            'descriptions' => $this->descriptions,
            'overrideClasses' => $this->classes,
            'overrideItemClasses' => $this->itemClasses,
            'overrideInputClasses' => $this->inputClasses,
            'overrideLabelClasses' => $this->labelClasses,
            'overrideLegendClasses' => $this->legendClasses,
            'overrideRootId' => $this->rootId,
            'valueOf' => $this->valueOfFn(),
            'labelOf' => $this->labelOfFn(),
            'idFor' => $this->idForFn(),
            'descriptionOf' => $this->descriptionOfFn(),
        ]);
    }

    protected function scalar(mixed $value): string|int
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof UnitEnum => $value->name,
            $value instanceof AbstractEnumeratorClass => (string) $value->getValue(),
            is_scalar($value) => $value,
            default => (string) $value,
        };
    }

    protected function valueOfFn(): callable
    {
        return function (object $case): string|int {
            if ($case instanceof BackedEnum) {
                return $case->value;
            }
            if ($case instanceof UnitEnum) {
                return $case->name;
            }

            /** @var AbstractEnumeratorClass $case */
            return (string) $case->getValue();
        };
    }

    protected function labelOfFn(): callable
    {
        return static fn (object $case): string => method_exists($case, 'label')
            ? (string) $case->label()
            : (string) ($case->name ?? '');
    }

    protected function idForFn(): callable
    {
        $name = rtrim($this->name, '[]');
        $valueOf = $this->valueOfFn();

        return static fn (object $case): string => $name . '_'
            . preg_replace('/[^a-z0-9]+/i', '_', (string) $valueOf($case));
    }

    protected function descriptionOfFn(): callable
    {
        return static fn (object $case): ?string => method_exists($case, 'description')
            ? $case->description()
            : null;
    }
}
