<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use Closure;
use Illuminate\Contracts\View\View;

/**
 * Higher-level dropdown — wraps Select with label/description and
 * JS-friendly `data-searchable` / `data-clearable` hooks for Alpine, Tom
 * Select, Choices.js, Select2, etc. Routes to `{framework}/dropdown.blade.php`.
 *
 * Per-element overrides (in addition to those inherited from Select):
 *   wrapperClasses, labelClasses, descriptionClasses
 *   wrapperId, labelId
 */
class Dropdown extends Select
{
    /**
     * @param  class-string  $enum
     * @param  array<string, string>  $groupLabels
     * @param  array<string, array<int, mixed>>|null  $groups
     */
    public function __construct(
        string $enum,
        string $name,
        mixed $selected = null,
        bool $nullable = true,
        string $placeholder = 'Select an option…',
        bool $multiple = false,
        ?int $size = null,
        bool $disabled = false,
        bool $required = false,
        ?string $framework = null,
        string|Closure|null $groupsBy = null,
        ?array $groups = null,
        array $groupLabels = [],
        ?string $ariaLabel = null,
        ?string $classes = null,
        ?string $optionClasses = null,
        ?string $rootId = null,
        public ?string $labelText = null,
        public ?string $description = null,
        public bool $searchable = false,
        public bool $clearable = false,
        public ?string $wrapperClasses = null,
        public ?string $labelClasses = null,
        public ?string $descriptionClasses = null,
        public ?string $wrapperId = null,
        public ?string $labelId = null,
    ) {
        parent::__construct(
            enum: $enum,
            name: $name,
            selected: $selected,
            nullable: $nullable,
            placeholder: $placeholder,
            multiple: $multiple,
            size: $size,
            disabled: $disabled,
            required: $required,
            framework: $framework,
            groupsBy: $groupsBy,
            groups: $groups,
            groupLabels: $groupLabels,
            ariaLabel: $ariaLabel,
            classes: $classes,
            optionClasses: $optionClasses,
            rootId: $rootId,
        );
    }

    public function render(): View
    {
        $this->groups = $this->groups
            ?? $this->buildGroups($this->cases, $this->groupsBy, $this->groupLabels);

        return view($this->frameworkView('dropdown'), [
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
            'labelText' => $this->labelText,
            'description' => $this->description,
            'searchable' => $this->searchable,
            'clearable' => $this->clearable,
            'overrideWrapperClasses' => $this->wrapperClasses,
            'overrideLabelClasses' => $this->labelClasses,
            'overrideDescriptionClasses' => $this->descriptionClasses,
            'overrideClasses' => $this->classes,
            'overrideOptionClasses' => $this->optionClasses,
            'overrideRootId' => $this->rootId,
            'overrideWrapperId' => $this->wrapperId,
            'overrideLabelId' => $this->labelId,
            'valueOf' => $this->valueOfFn(),
            'labelOf' => $this->labelOfFn(),
        ]);
    }
}
