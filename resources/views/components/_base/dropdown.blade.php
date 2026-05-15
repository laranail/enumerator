{{--
    Base dropdown partial — wrapper <label>/<small> trio over a select
    or an Alpine-driven listbox.

    When searchable=false AND clearable=false: emits a native <select>.
    When either is true: emits an Alpine combobox/listbox (requires
    `<x-laranail-enumerator::alpine-loader />` in the page). The native
    select path still ships data-searchable / data-clearable hooks for
    consumers using Tom Select, Choices.js, etc.

    Groups: when searchable=true the options are flattened (group
    headers ignored). Grouped + searchable is a v0.3.0 enhancement.
--}}
@php
    $selectedValue ??= null;
    $nullable ??= false;
    $placeholder ??= 'Select an option…';
    $multiple ??= false;
    $size ??= null;
    $disabled ??= false;
    $required ??= false;
    $classes ??= null;
    $optionClasses ??= null;
    $wrapperClasses ??= null;
    $labelClasses ??= null;
    $descriptionClasses ??= null;
    $groups ??= null;
    $labelText ??= null;
    $description ??= null;
    $ariaLabel ??= null;
    $searchable ??= false;
    $clearable ??= false;

    $inputId = $attributes->get('id', $name);
    $describedById = $description !== null ? $inputId . '-description' : null;
    $renderName = $multiple ? rtrim($name, '[]') . '[]' : $name;
    $isSelected = static function ($case) use ($selectedValue, $valueOf, $multiple): bool {
        if ($selectedValue === null) {
            return false;
        }
        $needle = (string) $valueOf($case);
        if ($multiple && is_iterable($selectedValue)) {
            foreach ($selectedValue as $v) {
                if ((string) $v === $needle) {
                    return true;
                }
            }
            return false;
        }
        return (string) $selectedValue === $needle;
    };

    // For the Alpine listbox path: flat list of { value, label } pairs.
    // Groups are flattened when searchable=true.
    $alpineEnhanced = ($searchable || $clearable) && ! $multiple && ! $disabled;
    $optionsList = [];
    foreach ($cases as $case) {
        $optionsList[] = ['value' => (string) $valueOf($case), 'label' => (string) $labelOf($case)];
    }
    $alpineSelectedValue = $selectedValue === null ? '' : (string) $selectedValue;
@endphp

<div {{ $attributes->only(['id'])->class(['enumerator-dropdown', $wrapperClasses]) }}>
    @if ($labelText !== null)
        <label for="{{ $inputId }}" class="{{ $labelClasses ?? 'enumerator-dropdown-label' }}">
            {{ $labelText }}
            @if ($required)<span aria-hidden="true">*</span>@endif
        </label>
    @endif

    @if ($description !== null)
        <small id="{{ $describedById }}" class="{{ $descriptionClasses ?? 'enumerator-dropdown-description' }}">
            {{ $description }}
        </small>
    @endif

@if ($alpineEnhanced)
    <div
        x-data="{
            open: false,
            filter: '',
            activeIndex: -1,
            selectedValue: {{ json_encode($alpineSelectedValue, JSON_UNESCAPED_SLASHES) }},
            selectedLabel: '',
            options: {{ json_encode($optionsList, JSON_UNESCAPED_SLASHES) }},
            init() {
                const found = this.options.find(o => String(o.value) === String(this.selectedValue));
                this.selectedLabel = found ? found.label : '';
            },
            get filtered() {
                if (!this.filter) return this.options;
                const f = String(this.filter).toLowerCase();
                return this.options.filter(o => String(o.label).toLowerCase().includes(f));
            },
            commitSelection(opt) {
                this.selectedValue = String(opt.value);
                this.selectedLabel = String(opt.label);
                this.open = false;
                this.filter = '';
                this.activeIndex = -1;
                this.$dispatch('change', { value: this.selectedValue });
            },
            clearSelection() {
                this.selectedValue = '';
                this.selectedLabel = '';
                this.$dispatch('change', { value: '' });
            },
            toggleOpen() {
                this.open = !this.open;
                if (this.open) {
                    this.activeIndex = -1;
                    this.$nextTick(() => {
                        if (this.$refs.filter) this.$refs.filter.focus();
                    });
                }
            },
            moveDown() {
                if (!this.open) { this.open = true; this.activeIndex = 0; return; }
                this.activeIndex = Math.min(this.activeIndex + 1, this.filtered.length - 1);
            },
            moveUp() {
                this.activeIndex = Math.max(this.activeIndex - 1, 0);
            },
            commitActive() {
                if (this.activeIndex >= 0 && this.filtered[this.activeIndex]) {
                    this.commitSelection(this.filtered[this.activeIndex]);
                }
            },
        }"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        class="enumerator-dropdown-combobox"
        data-enhancement="alpine"
        data-searchable="{{ $searchable ? 'true' : 'false' }}"
        data-clearable="{{ $clearable ? 'true' : 'false' }}"
    >
        <input type="hidden" name="{{ $renderName }}" :value="selectedValue" @if ($required) required @endif>

        <button
            type="button"
            id="{{ $inputId }}"
            @click="toggleOpen()"
            @keydown.arrow-down.prevent="moveDown()"
            @keydown.enter.prevent="open ? commitActive() : toggleOpen()"
            :aria-expanded="open ? 'true' : 'false'"
            aria-haspopup="listbox"
            @if ($describedById) aria-describedby="{{ $describedById }}" @endif
            @if ($ariaLabel ?? $labelText) aria-label="{{ $ariaLabel ?? $labelText }}" @endif
            class="enumerator-dropdown-button {{ $classes }}"
        >
            <span x-text="selectedLabel || {{ json_encode($placeholder, JSON_UNESCAPED_SLASHES) }}" class="enumerator-dropdown-label-text"></span>
        </button>

        @if ($clearable)
        <button
            type="button"
            x-show="selectedValue !== ''"
            @click.stop="clearSelection()"
            class="enumerator-dropdown-clear"
            aria-label="Clear selection"
            style="display:none"
        >&times;</button>
        @endif

        <div x-show="open" x-cloak class="enumerator-dropdown-panel" style="display:none">
            @if ($searchable)
            <input
                type="text"
                x-ref="filter"
                x-model="filter"
                @keydown.arrow-down.prevent="moveDown()"
                @keydown.arrow-up.prevent="moveUp()"
                @keydown.enter.prevent="commitActive()"
                @keydown.escape.prevent="open = false; filter = ''"
                placeholder="Search…"
                class="enumerator-dropdown-search"
                aria-label="Search options"
                autocomplete="off"
            >
            @endif

            <ul role="listbox" class="enumerator-dropdown-list">
                <template x-for="(opt, idx) in filtered" :key="String(opt.value)">
                    <li
                        role="option"
                        :aria-selected="String(opt.value) === String(selectedValue) ? 'true' : 'false'"
                        :class="{ 'enumerator-dropdown-active': idx === activeIndex }"
                        @click="commitSelection(opt)"
                        @mouseenter="activeIndex = idx"
                        class="enumerator-dropdown-option"
                    >
                        <span x-text="opt.label"></span>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="enumerator-dropdown-empty">
                    No matches.
                </li>
            </ul>
        </div>
    </div>
@else
    <select
        name="{{ $renderName }}"
        id="{{ $inputId }}"
        @if ($multiple) multiple @endif
        @if ($size) size="{{ $size }}" @endif
        @disabled($disabled)
        @required($required)
        @if ($ariaLabel ?? $labelText) aria-label="{{ $ariaLabel ?? $labelText }}" @endif
        @if ($describedById) aria-describedby="{{ $describedById }}" @endif
        data-searchable="{{ $searchable ? 'true' : 'false' }}"
        data-clearable="{{ $clearable ? 'true' : 'false' }}"
        class="enumerator-select {{ $classes }}"
    >
        @if ($nullable && ! $multiple)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if ($groups !== null)
            @foreach ($groups as $groupLabel => $groupCases)
                <optgroup label="{{ $groupLabel === '' ? '—' : $groupLabel }}">
                    @foreach ($groupCases as $case)
                        <option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        @else
            @foreach ($cases as $case)
                <option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
            @endforeach
        @endif
    </select>
@endif
</div>
