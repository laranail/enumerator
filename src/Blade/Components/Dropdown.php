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
 *   wireModel, wireModelModifier — Livewire `wire:model[.modifier]`
 *   attribute emitted on the hidden `<input>` (Alpine path) or on the
 *   `<select>` element (native fallback path). Mirrors the per-input
 *   shape PR-γ added to radio + checkboxes.
 *   announceChanges — opt-in polite live region under the wrapper
 *   that announces "Added / Removed <label>" to assistive tech when
 *   selections change. Off by default so consumers who already supply
 *   their own status region don't get double-announcements.
 */
class Dropdown extends Select
{
    /**
     * @param  class-string  $enum
     * @param  array<string, string>  $groupLabels
     * @param  array<string, array<int, mixed>>|null  $groups
     * @param  string|null  $wireModel  Livewire `wire:model` attribute
     *                                  value (e.g. `'status'`).
     * @param  string|null  $wireModelModifier  Livewire wire:model modifier
     *                                          (`live`, `blur`, `defer`,
     *                                          `debounce.500ms`, …). No-op
     *                                          unless `wireModel` is set.
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
        public ?string $wireModel = null,
        public ?string $wireModelModifier = null,
        public bool $announceChanges = false,
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
            'wireModel' => $this->wireModel,
            'wireModelModifier' => $this->wireModelModifier,
            'announceChanges' => $this->announceChanges,
            'strings' => $this->resolveDropdownStrings(),
            'valueOf' => $this->valueOfFn(),
            'labelOf' => $this->labelOfFn(),
        ]);
    }

    /**
     * Resolve the dropdown's user-facing strings through the Laravel
     * translator (PR-ρ, v0.4.0). Returns the resolved English strings
     * when no locale override exists. Consumers ship custom locales
     * under `lang/vendor/enumerator/{locale}/enumerator.php` — see
     * `docs/recipes/contributing-translations.md`.
     *
     * The Alpine x-data needs `announce_*` patterns broken into a
     * static prefix + the `:label` placeholder so JS can substitute
     * the option label at runtime. Other keys are full strings.
     *
     * @return array{
     *     search_placeholder: string,
     *     search_label: string,
     *     no_matches: string,
     *     clear_selection: string,
     *     remove_value_prefix: string,
     *     announce_added_prefix: string,
     *     announce_removed_prefix: string,
     *     announce_selected_prefix: string,
     *     announce_cleared: string,
     * }
     */
    private function resolveDropdownStrings(): array
    {
        $base = 'enumerator::enumerator.components.dropdown';

        // For placeholder-bearing strings, resolve with `:label` then
        // split — JS will tack on the actual label. Same approach
        // Botble uses for runtime-localised JS strings.
        $splitOnLabel = static function (string $key, string $fallback): string {
            $resolved = (string) __($key);
            // If the translator returns the key as-is (no translation
            // registered), fall back to the English literal.
            if ($resolved === $key) {
                $resolved = $fallback;
            }

            // Strip the `:label` placeholder + any trailing whitespace.
            return rtrim(str_replace(':label', '', $resolved));
        };

        $resolve = static function (string $key, string $fallback): string {
            $resolved = (string) __($key);

            return $resolved === $key ? $fallback : $resolved;
        };

        return [
            'search_placeholder' => $resolve($base . '.search_placeholder', 'Search…'),
            'search_label' => $resolve($base . '.search_label', 'Search options'),
            'no_matches' => $resolve($base . '.no_matches', 'No matches.'),
            'clear_selection' => $resolve($base . '.clear_selection', 'Clear selection'),
            'remove_value_prefix' => $splitOnLabel($base . '.remove_value', 'Remove '),
            'announce_added_prefix' => $splitOnLabel($base . '.announce_added', 'Added '),
            'announce_removed_prefix' => $splitOnLabel($base . '.announce_removed', 'Removed '),
            'announce_selected_prefix' => $splitOnLabel($base . '.announce_selected', 'Selected '),
            'announce_cleared' => $resolve($base . '.announce_cleared', 'Selection cleared'),
        ];
    }
}
