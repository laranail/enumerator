<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use BackedEnum;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Blade\Components\Concerns\GroupsCases;
use Simtabi\Laranail\Enumerator\Blade\Components\Concerns\RoutesToFrameworkView;
use UnitEnum;

class Select extends Component
{
    use GroupsCases;
    use RoutesToFrameworkView;

    /** @var array<int, UnitEnum|AbstractEnumeratorClass> */
    public array $cases;

    public mixed $selectedValue;

    /**
     * @param  class-string  $enum
     * @param  UnitEnum|AbstractEnumeratorClass|iterable<int|string, mixed>|string|int|null  $selected
     * @param  array<string, string>  $groupLabels
     * @param  array<string, array<int, UnitEnum|AbstractEnumeratorClass>>|null  $groups
     */
    public function __construct(
        public string $enum,
        public string $name,
        UnitEnum|AbstractEnumeratorClass|iterable|string|int|null $selected = null,
        public bool $nullable = false,
        public ?string $placeholder = null,
        public bool $multiple = false,
        public ?int $size = null,
        public bool $disabled = false,
        public bool $required = false,
        public ?string $framework = null,
        public string|Closure|null $groupsBy = null,
        public ?array $groups = null,
        public array $groupLabels = [],
        public ?string $ariaLabel = null,
        public ?string $classes = null,
        public ?string $optionClasses = null,
        public ?string $rootId = null,
    ) {
        $this->cases = $enum::cases();
        $this->selectedValue = $this->normalizeSelected($selected);
        $this->placeholder ??= (string) __('enumerator::enumerator.components.select.placeholder');
    }

    public function render(): View
    {
        $this->groups = $this->groups
            ?? $this->buildGroups($this->cases, $this->groupsBy, $this->groupLabels);

        return view($this->frameworkView('select'), [
            'appendClasses' => $this->consumerClasses(),
            'cases' => $this->cases,
            'name' => $this->name,
            'selectedValue' => $this->selectedValue,
            'nullable' => $this->nullable,
            'placeholder' => $this->placeholder,
            'multiple' => $this->multiple,
            'size' => $this->size,
            'disabled' => $this->disabled,
            'required' => $this->required,
            'groups' => $this->groups,
            'ariaLabel' => $this->ariaLabel,
            'overrideClasses' => $this->classes,
            'overrideOptionClasses' => $this->optionClasses,
            'overrideRootId' => $this->rootId,
            'valueOf' => $this->valueOfFn(),
            'labelOf' => $this->labelOfFn(),
        ]);
    }

    private function normalizeSelected(mixed $selected): mixed
    {
        if ($selected === null) {
            return null;
        }
        if (is_iterable($selected) && ! ($selected instanceof UnitEnum) && ! ($selected instanceof AbstractEnumeratorClass)) {
            $out = [];
            foreach ($selected as $item) {
                $out[] = $this->scalarOf($item);
            }

            return $out;
        }

        return $this->scalarOf($selected);
    }

    private function scalarOf(mixed $value): string|int
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof UnitEnum) {
            return $value->name;
        }
        if ($value instanceof AbstractEnumeratorClass) {
            return (string) $value->getValue();
        }

        return is_scalar($value) ? $value : (string) $value;
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
}
