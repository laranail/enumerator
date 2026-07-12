{{--
    Base dropdown partial — wrapper <label>/<small> trio over a select
    or an Alpine-driven listbox.

    When searchable=false AND clearable=false: emits a native <select>.
    When either is true: emits an Alpine combobox/listbox (requires
    `<x-laranail-enumerator::alpine-loader />` in the page). The native
    select path still ships data-searchable / data-clearable hooks for
    consumers using Tom Select, Choices.js, etc.

    multiple=true is supported in BOTH paths:
      - Alpine path: multi-select listbox with pill UI for selected
        values, click-to-deselect on each pill, Enter toggles
        selection without closing the panel.
      - Native path: <select multiple> falls through.

    Groups: when searchable=true the options are flattened (group
    headers ignored). Grouped + searchable is a v0.3.x enhancement.

    disabled=true always falls through to the native path; the Alpine
    listbox doesn't run.
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
    $wireModel ??= null;
    $wireModelModifier ??= null;
    $announceChanges ??= false;
    // Build wire:model[.modifier]="..." once, both fragments
    // HTML-escaped per D75 (attribute-string concatenation discipline).
    $wireModelAttr = $wireModel !== null
        ? 'wire:model'
            . ($wireModelModifier !== null
                ? '.' . htmlspecialchars((string) $wireModelModifier, ENT_QUOTES, 'UTF-8', false)
                : '')
            . '="' . htmlspecialchars((string) $wireModel, ENT_QUOTES, 'UTF-8', false) . '"'
        : '';

    $inputId = $attributes->get('id', $name);
    // a11y wiring (PR-ο):
    //   - $listboxId IDs the <ul role="listbox">; the trigger / search
    //     <input> bind aria-controls to it.
    //   - $optionIdPrefix is the per-option id seed; bound dynamically
    //     by Alpine to aria-activedescendant when an option is active.
    //   - $announceId is the polite live region id, only emitted when
    //     announceChanges=true.
    $listboxId = $inputId . '-listbox';
    $optionIdPrefix = $inputId . '-opt-';
    $announceId = $inputId . '-announce';

    // PR-ρ: translator-resolved strings for the Alpine surface.
    // Falls back to English literals when the consumer hasn't shipped a
    // locale file. `prefix` variants are the static head of an
    // `:label`-bearing pattern (e.g., "Added "); the trailing label
    // is concatenated in JS.
    $strings ??= [
        'search_placeholder' => 'Search…',
        'search_label' => 'Search options',
        'no_matches' => 'No matches.',
        'clear_selection' => 'Clear selection',
        'remove_value_prefix' => 'Remove ',
        'announce_added_prefix' => 'Added ',
        'announce_removed_prefix' => 'Removed ',
        'announce_selected_prefix' => 'Selected ',
        'announce_cleared' => 'Selection cleared',
    ];
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

    // Alpine path engaged when interactivity is requested AND the
    // component isn't disabled. multiple=true now stays in the Alpine
    // branch — handled as a multi-select listbox in the x-data state
    // below.
    $alpineEnhanced = ($searchable || $clearable) && ! $disabled;

    // For the Alpine listbox path: flat list of { value, label } pairs.
    $optionsList = [];
    foreach ($cases as $case) {
        $optionsList[] = ['value' => (string) $valueOf($case), 'label' => (string) $labelOf($case)];
    }

    // Initial state — single-select gets a scalar, multi-select an
    // array. Single-mode's $alpineSelectedValue stays empty in
    // multi-mode (we use $alpineSelectedValues instead). Casting an
    // array to (string) emits a PHP warning + 'Array' literal, so
    // skip the cast when $selectedValue is already an iterable.
    $alpineSelectedValue = '';
    $alpineSelectedValues = [];
    if ($multiple && is_iterable($selectedValue)) {
        foreach ($selectedValue as $v) {
            $alpineSelectedValues[] = (string) $v;
        }
    } elseif ($selectedValue !== null && ! is_iterable($selectedValue)) {
        $alpineSelectedValue = (string) $selectedValue;
    }
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
            multiple: {{ $multiple ? 'true' : 'false' }},
            selectedValue: {{ json_encode($alpineSelectedValue, JSON_UNESCAPED_SLASHES) }},
            selectedValues: {{ json_encode($alpineSelectedValues, JSON_UNESCAPED_SLASHES) }},
            selectedLabel: '',
            selectedLabels: [],
            announcement: '',
            options: {{ json_encode($optionsList, JSON_UNESCAPED_SLASHES) }},
            init() {
                if (this.multiple) {
                    this.refreshSelectedLabels();
                } else {
                    const found = this.options.find(o => String(o.value) === String(this.selectedValue));
                    this.selectedLabel = found ? found.label : '';
                }
            },
            get filtered() {
                if (!this.filter) return this.options;
                const f = String(this.filter).toLowerCase();
                return this.options.filter(o => String(o.label).toLowerCase().includes(f));
            },
            refreshSelectedLabels() {
                this.selectedLabels = this.selectedValues
                    .map(v => {
                        const o = this.options.find(o => String(o.value) === String(v));
                        return o ? { value: String(v), label: o.label } : null;
                    })
                    .filter(o => o !== null);
            },
            isSelected(opt) {
                if (this.multiple) {
                    return this.selectedValues.some(v => String(v) === String(opt.value));
                }
                return String(opt.value) === String(this.selectedValue);
            },
            hasSelection() {
                return this.multiple ? this.selectedValues.length > 0 : this.selectedValue !== '';
            },
            commitSelection(opt) {
                if (this.multiple) {
                    const v = String(opt.value);
                    const idx = this.selectedValues.findIndex(x => String(x) === v);
                    let verbPrefix;
                    if (idx >= 0) {
                        this.selectedValues.splice(idx, 1);
                        verbPrefix = {{ json_encode($strings['announce_removed_prefix'].' ', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }};
                    } else {
                        this.selectedValues.push(v);
                        verbPrefix = {{ json_encode($strings['announce_added_prefix'].' ', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }};
                    }
                    this.refreshSelectedLabels();
                    this.announcement = verbPrefix + String(opt.label);
                    this.$dispatch('change', { values: this.selectedValues });
                    // Multi mode keeps the panel open so multiple
                    // selections can land in a row. Esc / click-outside
                    // closes.
                } else {
                    this.selectedValue = String(opt.value);
                    this.selectedLabel = String(opt.label);
                    this.announcement = {{ json_encode($strings['announce_selected_prefix'].' ', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }} + String(opt.label);
                    this.open = false;
                    this.filter = '';
                    this.activeIndex = -1;
                    this.$dispatch('change', { value: this.selectedValue });
                }
            },
            removeValue(value) {
                if (!this.multiple) return;
                const v = String(value);
                const idx = this.selectedValues.findIndex(x => String(x) === v);
                if (idx >= 0) {
                    const removed = this.selectedLabels[idx] ? this.selectedLabels[idx].label : '';
                    this.selectedValues.splice(idx, 1);
                    this.refreshSelectedLabels();
                    this.announcement = {{ json_encode($strings['announce_removed_prefix'].' ', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }} + removed;
                    this.$dispatch('change', { values: this.selectedValues });
                }
            },
            clearSelection() {
                if (this.multiple) {
                    this.selectedValues = [];
                    this.selectedLabels = [];
                    this.announcement = {{ json_encode($strings['announce_cleared'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }};
                    this.$dispatch('change', { values: [] });
                } else {
                    this.selectedValue = '';
                    this.selectedLabel = '';
                    this.announcement = {{ json_encode($strings['announce_cleared'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }};
                    this.$dispatch('change', { value: '' });
                }
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
        :class="{ 'enumerator-dropdown-multiple': multiple }"
        data-enhancement="alpine"
        data-multiple="{{ $multiple ? 'true' : 'false' }}"
        data-searchable="{{ $searchable ? 'true' : 'false' }}"
        data-clearable="{{ $clearable ? 'true' : 'false' }}"
    >
        @if ($announceChanges)
        {{-- Polite live region for screen-reader announcements (PR-ο, v0.4.0). --}}
        <span id="{{ $announceId }}" class="enumerator-dropdown-sr-only" aria-live="polite" aria-atomic="true" x-text="announcement"></span>
        @endif

        @if ($multiple)
        <template x-for="entry in selectedLabels" :key="entry.value">
            <input type="hidden" name="{{ $renderName }}" :value="entry.value" {!! $wireModelAttr !!}>
        </template>
        @else
        <input type="hidden" name="{{ $renderName }}" :value="selectedValue" @if ($required) required @endif {!! $wireModelAttr !!}>
        @endif

        <button
            type="button"
            id="{{ $inputId }}"
            @click="toggleOpen()"
            @keydown.arrow-down.prevent="moveDown()"
            @keydown.enter.prevent="open ? commitActive() : toggleOpen()"
            :aria-expanded="open ? 'true' : 'false'"
            aria-haspopup="listbox"
            aria-controls="{{ $listboxId }}"
            :aria-activedescendant="activeIndex >= 0 ? {{ json_encode($optionIdPrefix, JSON_UNESCAPED_SLASHES) }} + activeIndex : null"
            @if ($describedById) aria-describedby="{{ $describedById }}" @endif
            @if ($ariaLabel ?? $labelText) aria-label="{{ $ariaLabel ?? $labelText }}" @endif
            class="enumerator-dropdown-button {{ $classes }}"
        >
            <template x-if="multiple">
                <span class="enumerator-dropdown-pills">
                    <span x-show="selectedLabels.length === 0" x-text="{{ json_encode($placeholder, JSON_UNESCAPED_SLASHES) }}" class="enumerator-dropdown-label-text"></span>
                    <template x-for="entry in selectedLabels" :key="entry.value">
                        <span class="enumerator-dropdown-pill">
                            <span x-text="entry.label"></span>
                            <button type="button"
                                    @click.stop="removeValue(entry.value)"
                                    class="enumerator-dropdown-pill-remove"
                                    :aria-label="{{ json_encode($strings['remove_value_prefix'].' ', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }} + entry.label"
                            >&times;</button>
                        </span>
                    </template>
                </span>
            </template>
            <template x-if="!multiple">
                <span x-text="selectedLabel || {{ json_encode($placeholder, JSON_UNESCAPED_SLASHES) }}" class="enumerator-dropdown-label-text"></span>
            </template>
        </button>

        @if ($clearable)
        <button
            type="button"
            x-show="hasSelection()"
            @click.stop="clearSelection()"
            class="enumerator-dropdown-clear"
            aria-label="{{ $strings['clear_selection'] }}"
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
                placeholder="{{ $strings['search_placeholder'] }}"
                class="enumerator-dropdown-search"
                aria-label="{{ $strings['search_label'] }}"
                aria-controls="{{ $listboxId }}"
                :aria-activedescendant="activeIndex >= 0 ? {{ json_encode($optionIdPrefix, JSON_UNESCAPED_SLASHES) }} + activeIndex : null"
                autocomplete="off"
            >
            @endif

            <ul id="{{ $listboxId }}" role="listbox" @if ($multiple) aria-multiselectable="true" @endif class="enumerator-dropdown-list">
                <template x-for="(opt, idx) in filtered" :key="String(opt.value)">
                    <li
                        :id="{{ json_encode($optionIdPrefix, JSON_UNESCAPED_SLASHES) }} + idx"
                        role="option"
                        :aria-selected="isSelected(opt) ? 'true' : 'false'"
                        :class="{
                            'enumerator-dropdown-active': idx === activeIndex,
                            'enumerator-dropdown-selected': isSelected(opt),
                        }"
                        @click="commitSelection(opt)"
                        @mouseenter="activeIndex = idx"
                        class="enumerator-dropdown-option"
                    >
                        <span x-text="opt.label"></span>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="enumerator-dropdown-empty">
                    {{ $strings['no_matches'] }}
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
        {!! $wireModelAttr !!}
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
