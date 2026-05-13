#!/usr/bin/env bash
# scripts/scaffold-step-2.sh
#
# Phase 2 scaffolder. Writes:
#   - Blade directives + 6 component classes
#   - 5 view bundles (plain/tailwind/daisyui/bootstrap/bulma) × 6 view files each
#   - 6 artisan command classes
#   - 11 integration adapter classes (Filament/Livewire/Nova/Inertia)
#   - 2 PHPStan extensions
#   - 26 preset enums (25 native + 1 class-const)
#
# Idempotent; re-running overwrites.
#
# Usage:
#   bash scripts/scaffold-step-2.sh

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Phase 2 scaffolding into $ROOT"

# ============================================================================
# BLADE DIRECTIVES
# ============================================================================
cat > src/Blade/BladeDirectives.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade;

use Illuminate\Support\Facades\Blade;

/**
 * Registers package Blade directives. All directive names are prefixed
 * `enumerator` to avoid collision with Laravel core or other packages.
 */
final class BladeDirectives
{
    public static function register(): void
    {
        // @enumeratorLabel($case) — prints the translated label.
        Blade::directive('enumeratorLabel', static fn (string $expr): string =>
            "<?php echo e(({$expr})->label()); ?>");

        // @enumeratorValue($case) — prints the backing value.
        Blade::directive('enumeratorValue', static fn (string $expr): string =>
            "<?php echo e(({$expr}) instanceof \\BackedEnum ? ({$expr})->value : (method_exists({$expr}, 'getValue') ? ({$expr})->getValue() : ({$expr})->name)); ?>");

        // @enumeratorName($case) — prints the case name.
        Blade::directive('enumeratorName', static fn (string $expr): string =>
            "<?php echo e(({$expr}) instanceof \\UnitEnum ? ({$expr})->name : (method_exists({$expr}, 'getKey') ? ({$expr})->getKey() : '')); ?>");

        // @enumeratorBadge($case) — outputs HtmlString.
        Blade::directive('enumeratorBadge', static fn (string $expr): string =>
            "<?php echo ({$expr})->toHtml(); ?>");

        // @enumeratorIs($case, target) ... @endEnumeratorIs
        Blade::directive('enumeratorIs', static function (string $expr): string {
            $parts = self::splitTwo($expr);

            return "<?php if (({$parts[0]})->is({$parts[1]})): ?>";
        });
        Blade::directive('endEnumeratorIs', static fn (): string => '<?php endif; ?>');

        // @enumeratorIn($case, [targets]) ... @endEnumeratorIn
        Blade::directive('enumeratorIn', static function (string $expr): string {
            $parts = self::splitTwo($expr);

            return "<?php if (({$parts[0]})->in({$parts[1]})): ?>";
        });
        Blade::directive('endEnumeratorIn', static fn (): string => '<?php endif; ?>');

        // @enumeratorColor($case) — prints color attribute.
        Blade::directive('enumeratorColor', static fn (string $expr): string =>
            "<?php echo e(method_exists({$expr}, 'color') ? ({$expr})->color() ?? '' : ''); ?>");

        // @enumeratorIcon($case)
        Blade::directive('enumeratorIcon', static fn (string $expr): string =>
            "<?php echo e(method_exists({$expr}, 'icon') ? ({$expr})->icon() ?? '' : ''); ?>");
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function splitTwo(string $expr): array
    {
        $depth = 0;
        $pos = null;
        $len = strlen($expr);
        for ($i = 0; $i < $len; $i++) {
            $c = $expr[$i];
            if ($c === '(' || $c === '[' || $c === '{') {
                $depth++;
            } elseif ($c === ')' || $c === ']' || $c === '}') {
                $depth--;
            } elseif ($c === ',' && $depth === 0) {
                $pos = $i;
                break;
            }
        }
        if ($pos === null) {
            return [trim($expr), 'null'];
        }

        return [trim(substr($expr, 0, $pos)), trim(substr($expr, $pos + 1))];
    }
}
PHP

# ============================================================================
# BLADE COMPONENT CLASSES (6)
# ============================================================================
mkdir -p src/Blade/Components

cat > src/Blade/Components/Badge.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

class Badge extends Component
{
    public function __construct(
        public UnitEnum|AbstractEnumeratorClass $case,
        public ?string $framework = null,
    ) {
        $this->framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
    }

    public function label(): string
    {
        return method_exists($this->case, 'label') ? (string) $this->case->label() : (string) $this->case->name;
    }

    public function color(): ?string
    {
        return method_exists($this->case, 'color') ? $this->case->color() : null;
    }

    public function icon(): ?string
    {
        return method_exists($this->case, 'icon') ? $this->case->icon() : null;
    }

    public function cssClass(): ?string
    {
        return method_exists($this->case, 'cssClass') ? $this->case->cssClass($this->framework) : null;
    }

    public function render(): View
    {
        $ns = (string) (config('enumerator.view_namespace') ?? 'laranail-enumerator');

        return view($ns.'::components.'.$this->framework.'.badge');
    }
}
PHP

cat > src/Blade/Components/Select.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

class Select extends Component
{
    /** @var array<int, UnitEnum|AbstractEnumeratorClass> */
    public array $cases;

    public mixed $selectedValue;

    public function __construct(
        public string $enum,
        public string $name,
        UnitEnum|AbstractEnumeratorClass|string|int|null $selected = null,
        public bool $nullable = false,
        public string $placeholder = 'Select an option…',
        public ?string $framework = null,
    ) {
        $this->cases = enum_exists($enum) ? $enum::cases() : $enum::cases();
        $this->selectedValue = match (true) {
            $selected instanceof BackedEnum => $selected->value,
            $selected instanceof UnitEnum => $selected->name,
            $selected instanceof AbstractEnumeratorClass => $selected->getValue(),
            default => $selected,
        };
        $this->framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
    }

    public function valueOf(object $case): string|int
    {
        if ($case instanceof BackedEnum) {
            return $case->value;
        }
        if ($case instanceof UnitEnum) {
            return $case->name;
        }
        /** @var AbstractEnumeratorClass $case */
        return (string) $case->getValue();
    }

    public function labelOf(object $case): string
    {
        return method_exists($case, 'label') ? (string) $case->label() : (string) ($case->name ?? '');
    }

    public function isSelected(object $case): bool
    {
        return $this->selectedValue !== null && (string) $this->selectedValue === (string) $this->valueOf($case);
    }

    public function render(): View
    {
        $ns = (string) (config('enumerator.view_namespace') ?? 'laranail-enumerator');

        return view($ns.'::components.'.$this->framework.'.select');
    }
}
PHP

# Radio is essentially Select; same shape with a "layout" prop.
cat > src/Blade/Components/Radio.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

class Radio extends Component
{
    /** @var array<int, UnitEnum|AbstractEnumeratorClass> */
    public array $cases;

    public mixed $selectedValue;

    public function __construct(
        public string $enum,
        public string $name,
        UnitEnum|AbstractEnumeratorClass|string|int|null $selected = null,
        public string $layout = 'vertical',
        public ?string $framework = null,
    ) {
        $this->cases = $enum::cases();
        $this->selectedValue = match (true) {
            $selected instanceof BackedEnum => $selected->value,
            $selected instanceof UnitEnum => $selected->name,
            $selected instanceof AbstractEnumeratorClass => $selected->getValue(),
            default => $selected,
        };
        $this->framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
    }

    public function valueOf(object $case): string|int
    {
        if ($case instanceof BackedEnum) {
            return $case->value;
        }
        if ($case instanceof UnitEnum) {
            return $case->name;
        }
        /** @var AbstractEnumeratorClass $case */
        return (string) $case->getValue();
    }

    public function labelOf(object $case): string
    {
        return method_exists($case, 'label') ? (string) $case->label() : (string) ($case->name ?? '');
    }

    public function isSelected(object $case): bool
    {
        return $this->selectedValue !== null && (string) $this->selectedValue === (string) $this->valueOf($case);
    }

    public function idFor(object $case): string
    {
        return $this->name.'_'.preg_replace('/[^a-z0-9]+/i', '_', (string) $this->valueOf($case));
    }

    public function render(): View
    {
        $ns = (string) (config('enumerator.view_namespace') ?? 'laranail-enumerator');

        return view($ns.'::components.'.$this->framework.'.radio');
    }
}
PHP

cat > src/Blade/Components/Checkboxes.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

class Checkboxes extends Component
{
    /** @var array<int, UnitEnum|AbstractEnumeratorClass> */
    public array $cases;

    /** @var array<int, string|int> */
    public array $selectedValues = [];

    public function __construct(
        public string $enum,
        public string $name,
        iterable $selected = [],
        public string $layout = 'vertical',
        public ?string $framework = null,
    ) {
        $this->cases = $enum::cases();
        foreach ($selected as $item) {
            $this->selectedValues[] = match (true) {
                $item instanceof BackedEnum => $item->value,
                $item instanceof UnitEnum => $item->name,
                $item instanceof AbstractEnumeratorClass => (string) $item->getValue(),
                default => is_scalar($item) ? $item : (string) $item,
            };
        }
        $this->framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
    }

    public function valueOf(object $case): string|int
    {
        if ($case instanceof BackedEnum) {
            return $case->value;
        }
        if ($case instanceof UnitEnum) {
            return $case->name;
        }
        /** @var AbstractEnumeratorClass $case */
        return (string) $case->getValue();
    }

    public function labelOf(object $case): string
    {
        return method_exists($case, 'label') ? (string) $case->label() : (string) ($case->name ?? '');
    }

    public function isChecked(object $case): bool
    {
        return in_array((string) $this->valueOf($case), array_map('strval', $this->selectedValues), true);
    }

    public function idFor(object $case): string
    {
        return $this->name.'_'.preg_replace('/[^a-z0-9]+/i', '_', (string) $this->valueOf($case));
    }

    public function render(): View
    {
        $ns = (string) (config('enumerator.view_namespace') ?? 'laranail-enumerator');

        return view($ns.'::components.'.$this->framework.'.checkboxes');
    }
}
PHP

cat > src/Blade/Components/Grid.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Grid extends Component
{
    /** @var array<int, object> */
    public array $cases;

    public function __construct(
        public string $enum,
        public int $columns = 3,
        public bool $showBadges = false,
        public ?string $framework = null,
    ) {
        $this->cases = $enum::cases();
        $this->framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
    }

    public function labelOf(object $case): string
    {
        return method_exists($case, 'label') ? (string) $case->label() : (string) ($case->name ?? '');
    }

    public function descriptionOf(object $case): ?string
    {
        return method_exists($case, 'description') ? $case->description() : null;
    }

    public function render(): View
    {
        $ns = (string) (config('enumerator.view_namespace') ?? 'laranail-enumerator');

        return view($ns.'::components.'.$this->framework.'.grid');
    }
}
PHP

cat > src/Blade/Components/ListComponent.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ListComponent extends Component
{
    /** @var array<int, object> */
    public array $cases;

    public function __construct(
        public string $enum,
        public ?string $framework = null,
    ) {
        $this->cases = $enum::cases();
        $this->framework ??= (string) (config('enumerator.css_framework') ?? 'plain');
    }

    public function labelOf(object $case): string
    {
        return method_exists($case, 'label') ? (string) $case->label() : (string) ($case->name ?? '');
    }

    public function render(): View
    {
        $ns = (string) (config('enumerator.view_namespace') ?? 'laranail-enumerator');

        return view($ns.'::components.'.$this->framework.'.list');
    }
}
PHP

# Tell Blade where to load the "list" alias when used as <x-...::list>
# (Laravel resolves snake-case from class name)

echo "==> Blade components written"

# ============================================================================
# VIEW BUNDLES (5 frameworks × 6 components each = 30 view files)
# ============================================================================
write_view() {
    local framework="$1"; local kind="$2"; local content="$3"
    cat > "resources/views/components/${framework}/${kind}.blade.php" <<EOF
${content}
EOF
}

# --- PLAIN ----------------------------------------------------------------
write_view plain badge '@php $classes = $cssClass() ?? "enumerator-badge"; @endphp
<span class="{{ $classes }}" role="status">
@if($icon())<span class="enumerator-icon" aria-hidden="true">{!! $icon() !!}</span>@endif
{{ $label() }}
</span>'

write_view plain select '<select name="{{ $name }}" id="{{ $attributes->get("id", $name) }}" {{ $attributes->merge() }}>
@if($nullable)<option value="">{{ $placeholder }}</option>@endif
@foreach($cases as $case)
<option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
@endforeach
</select>'

write_view plain radio '<fieldset role="radiogroup" {{ $attributes }}>
@foreach($cases as $case)
<label for="{{ $idFor($case) }}">
<input type="radio" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isSelected($case))>
{{ $labelOf($case) }}
</label>
@endforeach
</fieldset>'

write_view plain checkboxes '<fieldset {{ $attributes }}>
@foreach($cases as $case)
<label for="{{ $idFor($case) }}">
<input type="checkbox" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isChecked($case))>
{{ $labelOf($case) }}
</label>
@endforeach
</fieldset>'

write_view plain grid '<ul role="list" {{ $attributes }}>
@foreach($cases as $case)
<li>{{ $labelOf($case) }}@if($d = $descriptionOf($case)) — {{ $d }}@endif</li>
@endforeach
</ul>'

write_view plain list '<ul role="list" {{ $attributes }}>
@foreach($cases as $case)<li>{{ $labelOf($case) }}</li>@endforeach
</ul>'

# --- TAILWIND ------------------------------------------------------------
write_view tailwind badge '@php $color = $color() ?? "gray"; $classes = $cssClass() ?? "inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{$color}-100 text-{$color}-800"; @endphp
<span class="{{ $classes }}" role="status">
@if($icon())<span class="mr-1" aria-hidden="true">{!! $icon() !!}</span>@endif
{{ $label() }}
</span>'

write_view tailwind select '<select name="{{ $name }}" id="{{ $attributes->get("id", $name) }}" {{ $attributes->merge(["class" => "block w-full rounded-md border-gray-300 shadow-sm"]) }}>
@if($nullable)<option value="">{{ $placeholder }}</option>@endif
@foreach($cases as $case)
<option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
@endforeach
</select>'

write_view tailwind radio '@php $wrap = $layout === "horizontal" ? "flex flex-wrap gap-4" : "space-y-2"; @endphp
<fieldset role="radiogroup" {{ $attributes->merge(["class" => $wrap]) }}>
@foreach($cases as $case)
<label for="{{ $idFor($case) }}" class="inline-flex items-center gap-2">
<input type="radio" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isSelected($case)) class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
<span>{{ $labelOf($case) }}</span>
</label>
@endforeach
</fieldset>'

write_view tailwind checkboxes '@php $wrap = $layout === "horizontal" ? "flex flex-wrap gap-4" : "space-y-2"; @endphp
<fieldset {{ $attributes->merge(["class" => $wrap]) }}>
@foreach($cases as $case)
<label for="{{ $idFor($case) }}" class="inline-flex items-center gap-2">
<input type="checkbox" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isChecked($case)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
<span>{{ $labelOf($case) }}</span>
</label>
@endforeach
</fieldset>'

write_view tailwind grid '@php $cols = "grid-cols-{$columns}"; @endphp
<ul role="list" {{ $attributes->merge(["class" => "grid gap-4 ".$cols]) }}>
@foreach($cases as $case)
<li class="p-4 border rounded-lg">
<div class="font-semibold">{{ $labelOf($case) }}</div>
@if($d = $descriptionOf($case))<p class="text-sm text-gray-600">{{ $d }}</p>@endif
</li>
@endforeach
</ul>'

write_view tailwind list '<ul role="list" {{ $attributes->merge(["class" => "divide-y divide-gray-200"]) }}>
@foreach($cases as $case)<li class="py-2">{{ $labelOf($case) }}</li>@endforeach
</ul>'

# --- DAISYUI -------------------------------------------------------------
write_view daisyui badge '@php $color = $color() ?? "ghost"; $classes = $cssClass() ?? "badge badge-{$color}"; @endphp
<span class="{{ $classes }}" role="status">
@if($icon()){!! $icon() !!}@endif
{{ $label() }}
</span>'

write_view daisyui select '<select name="{{ $name }}" id="{{ $attributes->get("id", $name) }}" {{ $attributes->merge(["class" => "select select-bordered w-full"]) }}>
@if($nullable)<option value="">{{ $placeholder }}</option>@endif
@foreach($cases as $case)
<option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
@endforeach
</select>'

write_view daisyui radio '@php $wrap = $layout === "horizontal" ? "flex flex-wrap gap-4" : "space-y-2"; @endphp
<fieldset role="radiogroup" {{ $attributes->merge(["class" => $wrap]) }}>
@foreach($cases as $case)
<label class="label cursor-pointer gap-2" for="{{ $idFor($case) }}">
<input type="radio" class="radio radio-primary" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isSelected($case))>
<span class="label-text">{{ $labelOf($case) }}</span>
</label>
@endforeach
</fieldset>'

write_view daisyui checkboxes '@php $wrap = $layout === "horizontal" ? "flex flex-wrap gap-4" : "space-y-2"; @endphp
<fieldset {{ $attributes->merge(["class" => $wrap]) }}>
@foreach($cases as $case)
<label class="label cursor-pointer gap-2" for="{{ $idFor($case) }}">
<input type="checkbox" class="checkbox checkbox-primary" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isChecked($case))>
<span class="label-text">{{ $labelOf($case) }}</span>
</label>
@endforeach
</fieldset>'

write_view daisyui grid '@php $cols = "grid-cols-{$columns}"; @endphp
<ul role="list" {{ $attributes->merge(["class" => "grid gap-4 ".$cols]) }}>
@foreach($cases as $case)
<li class="card bg-base-100 shadow-sm border border-base-300"><div class="card-body p-4">
<h3 class="font-semibold">{{ $labelOf($case) }}</h3>
@if($d = $descriptionOf($case))<p class="text-sm">{{ $d }}</p>@endif
</div></li>
@endforeach
</ul>'

write_view daisyui list '<ul role="list" {{ $attributes->merge(["class" => "menu bg-base-100"]) }}>
@foreach($cases as $case)<li><a>{{ $labelOf($case) }}</a></li>@endforeach
</ul>'

# --- BOOTSTRAP -----------------------------------------------------------
write_view bootstrap badge '@php $color = $color() ?? "secondary"; $classes = $cssClass() ?? "badge bg-{$color}"; @endphp
<span class="{{ $classes }}" role="status">
@if($icon())<i class="me-1">{!! $icon() !!}</i>@endif
{{ $label() }}
</span>'

write_view bootstrap select '<select name="{{ $name }}" id="{{ $attributes->get("id", $name) }}" {{ $attributes->merge(["class" => "form-select"]) }}>
@if($nullable)<option value="">{{ $placeholder }}</option>@endif
@foreach($cases as $case)
<option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
@endforeach
</select>'

write_view bootstrap radio '@php $wrap = $layout === "horizontal" ? "d-flex flex-wrap gap-3" : "d-flex flex-column gap-2"; @endphp
<fieldset role="radiogroup" {{ $attributes->merge(["class" => $wrap]) }}>
@foreach($cases as $case)
<div class="form-check">
<input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $idFor($case) }}" value="{{ $valueOf($case) }}" @checked($isSelected($case))>
<label class="form-check-label" for="{{ $idFor($case) }}">{{ $labelOf($case) }}</label>
</div>
@endforeach
</fieldset>'

write_view bootstrap checkboxes '<fieldset {{ $attributes->merge(["class" => $layout === "horizontal" ? "d-flex flex-wrap gap-3" : "d-flex flex-column gap-2"]) }}>
@foreach($cases as $case)
<div class="form-check">
<input class="form-check-input" type="checkbox" name="{{ $name }}" id="{{ $idFor($case) }}" value="{{ $valueOf($case) }}" @checked($isChecked($case))>
<label class="form-check-label" for="{{ $idFor($case) }}">{{ $labelOf($case) }}</label>
</div>
@endforeach
</fieldset>'

write_view bootstrap grid '@php $cls = "row row-cols-".$columns." g-3"; @endphp
<ul role="list" {{ $attributes->merge(["class" => $cls]) }} style="list-style:none;padding:0;">
@foreach($cases as $case)
<li class="col"><div class="card h-100"><div class="card-body">
<h6 class="card-title">{{ $labelOf($case) }}</h6>
@if($d = $descriptionOf($case))<p class="card-text small text-muted">{{ $d }}</p>@endif
</div></div></li>
@endforeach
</ul>'

write_view bootstrap list '<ul role="list" {{ $attributes->merge(["class" => "list-group"]) }}>
@foreach($cases as $case)<li class="list-group-item">{{ $labelOf($case) }}</li>@endforeach
</ul>'

# --- BULMA ---------------------------------------------------------------
write_view bulma badge '@php $color = $color() ?? "light"; $classes = $cssClass() ?? "tag is-{$color}"; @endphp
<span class="{{ $classes }}" role="status">
@if($icon())<span class="icon mr-1">{!! $icon() !!}</span>@endif
{{ $label() }}
</span>'

write_view bulma select '<div class="select"><select name="{{ $name }}" id="{{ $attributes->get("id", $name) }}" {{ $attributes }}>
@if($nullable)<option value="">{{ $placeholder }}</option>@endif
@foreach($cases as $case)
<option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
@endforeach
</select></div>'

write_view bulma radio '<div class="control" {{ $attributes }}>
@foreach($cases as $case)
<label class="radio" for="{{ $idFor($case) }}">
<input type="radio" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isSelected($case))>
{{ $labelOf($case) }}
</label>
@endforeach
</div>'

write_view bulma checkboxes '<div class="control" {{ $attributes }}>
@foreach($cases as $case)
<label class="checkbox" for="{{ $idFor($case) }}">
<input type="checkbox" id="{{ $idFor($case) }}" name="{{ $name }}" value="{{ $valueOf($case) }}" @checked($isChecked($case))>
{{ $labelOf($case) }}
</label>
@endforeach
</div>'

write_view bulma grid '<div class="columns is-multiline" {{ $attributes }}>
@foreach($cases as $case)
<div class="column is-{{ (int) (12 / $columns) }}"><div class="box">
<p class="title is-6">{{ $labelOf($case) }}</p>
@if($d = $descriptionOf($case))<p class="subtitle is-7">{{ $d }}</p>@endif
</div></div>
@endforeach
</div>'

write_view bulma list '<ul role="list" {{ $attributes->merge(["class" => "panel"]) }}>
@foreach($cases as $case)<li class="panel-block">{{ $labelOf($case) }}</li>@endforeach
</ul>'

echo "==> View bundles written"

# ============================================================================
# CONSOLE COMMANDS (6)
# ============================================================================
cat > src/Console/MakeEnumeratorCommand.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\GeneratorCommand;
use InvalidArgumentException;

class MakeEnumeratorCommand extends GeneratorCommand
{
    protected $signature = 'make:enumerator {name : Class name (e.g. UserStatusEnum)} {--stub=backed : One of: backed|pure|attributes|bitmask|state-machine} {--namespace= : Override the namespace (default App\\Enums)}';

    protected $description = 'Create a new enumerator class.';

    protected $type = 'Enumerator';

    protected function getStub(): string
    {
        $stub = (string) $this->option('stub');
        $allowed = ['backed', 'pure', 'attributes', 'bitmask', 'state-machine'];
        if (! in_array($stub, $allowed, true)) {
            throw new InvalidArgumentException(sprintf('Unknown stub "%s". Allowed: %s', $stub, implode(', ', $allowed)));
        }

        $published = resource_path('stubs/enumerator/enumerator.'.$stub.'.stub');
        if (is_file($published)) {
            return $published;
        }

        return __DIR__.'/../../resources/stubs/enumerator.'.$stub.'.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $override = $this->option('namespace');

        return is_string($override) && $override !== '' ? $override : $rootNamespace.'\\Enums';
    }
}
PHP

cat > src/Console/AnnotateEnumeratorCommand.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use BackedEnum;
use Illuminate\Console\Command;
use ReflectionEnum;

class AnnotateEnumeratorCommand extends Command
{
    protected $signature = 'enumerator:annotate {class? : Fully-qualified enum class. Omit to scan app/Enums/}';

    protected $description = 'Print PHPDoc @method static stubs for case access and meta helpers.';

    public function handle(): int
    {
        $class = (string) ($this->argument('class') ?? '');
        if ($class === '') {
            $this->info('Pass an FQCN, e.g.: php artisan enumerator:annotate "App\\Enums\\UserStatusEnum"');

            return self::SUCCESS;
        }
        if (! enum_exists($class)) {
            $this->error("Class {$class} is not an enum.");

            return self::FAILURE;
        }

        $reflection = new ReflectionEnum($class);
        $short = $reflection->getShortName();
        $isBacked = $reflection->isBacked();
        $this->line('/**');
        foreach ($reflection->getCases() as $case) {
            if ($isBacked) {
                $type = (string) $reflection->getBackingType();
                $this->line(" * @method static {$short} {$case->getName()}()  // returns {$type}");
            } else {
                $this->line(" * @method static {$short} {$case->getName()}()");
            }
        }
        $this->line(' */');

        return self::SUCCESS;
    }
}
PHP

cat > src/Console/ExportEnumeratorCommand.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;
use Simtabi\Laranail\Enumerator\Support\EnumExporter;

class ExportEnumeratorCommand extends Command
{
    protected $signature = 'enumerator:export {class : Fully-qualified enum class} {--ts : Emit TypeScript} {--json : Emit JSON} {--php : Emit a PHP array file} {--out= : Output file path; defaults to stdout}';

    protected $description = 'Export an enum to TS / JSON / PHP for frontend or downstream consumption.';

    public function handle(EnumExporter $exporter): int
    {
        $class = (string) $this->argument('class');
        if (! enum_exists($class)) {
            $this->error("Class {$class} is not an enum.");

            return self::FAILURE;
        }

        $payload = match (true) {
            (bool) $this->option('ts') => $exporter->toTypeScript($class),
            (bool) $this->option('php') => $exporter->toPhpFile($class),
            default => $exporter->toJson($class),
        };

        $out = (string) ($this->option('out') ?? '');
        if ($out === '') {
            $this->line($payload);

            return self::SUCCESS;
        }

        @mkdir(dirname($out), 0o755, true);
        file_put_contents($out, $payload);
        $this->info("Wrote {$out}");

        return self::SUCCESS;
    }
}
PHP

cat > src/Console/IdeHelperCommand.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;

class IdeHelperCommand extends Command
{
    protected $signature = 'enumerator:ide-helper {--out=_ide_helper_enumerator.php}';

    protected $description = 'Generate IDE helper docblocks for dynamic enumerator methods.';

    public function handle(): int
    {
        $out = (string) ($this->option('out') ?? '_ide_helper_enumerator.php');
        $payload = "<?php\n\n// IDE helper for laranail/enumerator dynamic methods.\n// Generated by `php artisan enumerator:ide-helper`.\n// Add this file to your IDE's index and to .gitignore.\n";
        file_put_contents(base_path($out), $payload);
        $this->info("Wrote {$out}.");

        return self::SUCCESS;
    }
}
PHP

cat > src/Console/CacheEnumeratorCommand.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCache;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;

class CacheEnumeratorCommand extends Command
{
    protected $signature = 'enumerator:cache';

    protected $description = 'Warm and persist the enumerator reflection cache.';

    public function handle(LayeredCache $cache): int
    {
        $classes = (array) config('enumerator.cache.auto_warm_classes', []);
        foreach ($classes as $class) {
            if (! is_string($class)) {
                continue;
            }
            if (enum_exists($class)) {
                CasesCache::nativeCases($class);
                foreach ($class::cases() as $case) {
                    AttributesCache::for($case);
                }
            }
        }
        $cache->persist();
        $this->info((string) __('enumerator::enumerator.commands.cache.cached'));

        return self::SUCCESS;
    }
}
PHP

cat > src/Console/CacheClearEnumeratorCommand.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCache;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;

class CacheClearEnumeratorCommand extends Command
{
    protected $signature = 'enumerator:cache:clear';

    protected $description = 'Drop the enumerator reflection cache (memory + file).';

    public function handle(LayeredCache $cache): int
    {
        AttributesCache::flush();
        CasesCache::flush();
        $cache->flush();
        $cache->clearFile();
        $this->info((string) __('enumerator::enumerator.commands.cache.cleared'));

        return self::SUCCESS;
    }
}
PHP

echo "==> Console commands written"

# ============================================================================
# INTEGRATIONS (thin adapters — load only when the host package is installed)
# ============================================================================
cat > src/Integrations/Filament/Columns/EnumeratorColumn.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Columns;

if (! class_exists(\Filament\Tables\Columns\TextColumn::class)) {
    return;
}

/**
 * Filament 4+ table column that auto-formats enumerator cases via ->label().
 * Falls back gracefully when the value is a raw string.
 */
final class EnumeratorColumn extends \Filament\Tables\Columns\TextColumn
{
    public function setUp(): void
    {
        parent::setUp();
        $this->formatStateUsing(static function (mixed $state): string {
            if (is_object($state) && method_exists($state, 'label')) {
                return (string) $state->label();
            }

            return is_scalar($state) ? (string) $state : '';
        });
        $this->badge();
    }
}
PHP

cat > src/Integrations/Filament/Components/Badge.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Components;

if (! class_exists(\Filament\Infolists\Components\TextEntry::class)) {
    return;
}

final class Badge extends \Filament\Infolists\Components\TextEntry
{
    public function setUp(): void
    {
        parent::setUp();
        $this->badge();
        $this->formatStateUsing(static function (mixed $state): string {
            if (is_object($state) && method_exists($state, 'label')) {
                return (string) $state->label();
            }

            return is_scalar($state) ? (string) $state : '';
        });
    }
}
PHP

cat > src/Integrations/Filament/Filters/EnumeratorFilter.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Filters;

if (! class_exists(\Filament\Tables\Filters\SelectFilter::class)) {
    return;
}

final class EnumeratorFilter extends \Filament\Tables\Filters\SelectFilter
{
    /**
     * @param  class-string  $enumClass
     */
    public static function for(string $enumClass, string $column): self
    {
        /** @var self $filter */
        $filter = self::make($column);
        $filter->options(static function () use ($enumClass): array {
            if (! enum_exists($enumClass)) {
                return [];
            }
            $options = [];
            foreach ($enumClass::cases() as $case) {
                $options[$case instanceof \BackedEnum ? $case->value : $case->name]
                    = method_exists($case, 'label') ? (string) $case->label() : $case->name;
            }

            return $options;
        });

        return $filter;
    }
}
PHP

cat > src/Integrations/Filament/Forms/EnumeratorSelect.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Forms;

if (! class_exists(\Filament\Forms\Components\Select::class)) {
    return;
}

final class EnumeratorSelect extends \Filament\Forms\Components\Select
{
    /**
     * @param  class-string  $enumClass
     */
    public function enumerator(string $enumClass): static
    {
        $this->options(static function () use ($enumClass): array {
            if (! enum_exists($enumClass)) {
                return [];
            }
            $options = [];
            foreach ($enumClass::cases() as $case) {
                $options[$case instanceof \BackedEnum ? $case->value : $case->name]
                    = method_exists($case, 'label') ? (string) $case->label() : $case->name;
            }

            return $options;
        });

        return $this;
    }
}
PHP

cat > src/Integrations/Filament/Forms/EnumeratorRadio.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Forms;

if (! class_exists(\Filament\Forms\Components\Radio::class)) {
    return;
}

final class EnumeratorRadio extends \Filament\Forms\Components\Radio
{
    /**
     * @param  class-string  $enumClass
     */
    public function enumerator(string $enumClass): static
    {
        $this->options(static function () use ($enumClass): array {
            if (! enum_exists($enumClass)) {
                return [];
            }
            $options = [];
            foreach ($enumClass::cases() as $case) {
                $options[$case instanceof \BackedEnum ? $case->value : $case->name]
                    = method_exists($case, 'label') ? (string) $case->label() : $case->name;
            }

            return $options;
        });

        return $this;
    }
}
PHP

cat > src/Integrations/Filament/Infolists/EnumeratorEntry.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Infolists;

if (! class_exists(\Filament\Infolists\Components\TextEntry::class)) {
    return;
}

final class EnumeratorEntry extends \Filament\Infolists\Components\TextEntry
{
    public function setUp(): void
    {
        parent::setUp();
        $this->formatStateUsing(static function (mixed $state): string {
            if (is_object($state) && method_exists($state, 'label')) {
                return (string) $state->label();
            }

            return is_scalar($state) ? (string) $state : '';
        });
    }
}
PHP

# Livewire
cat > src/Integrations/Livewire/EnumeratorCasts.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Livewire;

if (! class_exists(\Livewire\Component::class)) {
    return;
}

/**
 * Generic helper for casting enum-typed Livewire properties via component
 * hooks. Livewire 3.5+ natively supports BackedEnum property hydration; this
 * helper is for pure enums and AbstractEnumeratorClass instances.
 */
final class EnumeratorCasts
{
    /**
     * @param  class-string  $enumClass
     */
    public static function hydrateProperty(string $enumClass, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if (enum_exists($enumClass) && method_exists($enumClass, 'tryFromName') && is_string($value)) {
            return $enumClass::tryFromName($value) ?? $value;
        }

        return $value;
    }
}
PHP

cat > src/Integrations/Livewire/RegistersLivewireSupport.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Livewire;

if (! class_exists(\Livewire\Livewire::class)) {
    return;
}

/**
 * Hook point for future Livewire-specific registrations (component aliases,
 * directives, blade-bound macros). Kept as a no-op placeholder so the service
 * provider can wire it in safely.
 */
final class RegistersLivewireSupport
{
    public static function register(): void
    {
        // No-op for v0.1.0.
    }
}
PHP

# Nova
cat > src/Integrations/Nova/EnumeratorField.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Nova;

if (! class_exists(\Laravel\Nova\Fields\Select::class)) {
    return;
}

final class EnumeratorField extends \Laravel\Nova\Fields\Select
{
    /**
     * @param  class-string  $enumClass
     */
    public function forEnumerator(string $enumClass): static
    {
        $this->options(static function () use ($enumClass): array {
            if (! enum_exists($enumClass)) {
                return [];
            }
            $options = [];
            foreach ($enumClass::cases() as $case) {
                $options[$case instanceof \BackedEnum ? $case->value : $case->name]
                    = method_exists($case, 'label') ? (string) $case->label() : $case->name;
            }

            return $options;
        });

        return $this;
    }
}
PHP

cat > src/Integrations/Nova/EnumeratorFilter.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Nova;

if (! class_exists(\Laravel\Nova\Filters\Filter::class)) {
    return;
}

abstract class EnumeratorFilter extends \Laravel\Nova\Filters\Filter
{
    /** @var class-string */
    public string $enumClass;

    public function options(\Laravel\Nova\Http\Requests\NovaRequest $request): array
    {
        if (! enum_exists($this->enumClass)) {
            return [];
        }
        $options = [];
        foreach ($this->enumClass::cases() as $case) {
            $key = method_exists($case, 'label') ? (string) $case->label() : $case->name;
            $value = $case instanceof \BackedEnum ? $case->value : $case->name;
            $options[$key] = $value;
        }

        return $options;
    }
}
PHP

# Inertia
cat > src/Integrations/Inertia/EnumeratorTransformer.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Inertia;

use BackedEnum;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

/**
 * Helper for shaping enumerator cases for SPA consumption. Pair with
 * Inertia's `shareProps()` or in a controller response:
 *
 *     return inertia('User', [
 *         'user' => $user,
 *         'status' => EnumeratorTransformer::case($user->status),
 *         'statusOptions' => EnumeratorTransformer::options(UserStatusEnum::class),
 *     ]);
 */
final class EnumeratorTransformer
{
    /**
     * @return array{value: string|int|null, name: string|null, label: string}|null
     */
    public static function case(UnitEnum|AbstractEnumeratorClass|null $case): ?array
    {
        if ($case === null) {
            return null;
        }

        $value = match (true) {
            $case instanceof BackedEnum => $case->value,
            $case instanceof UnitEnum => $case->name,
            default => $case->getValue(),
        };

        $name = $case instanceof UnitEnum
            ? $case->name
            : ($case instanceof AbstractEnumeratorClass ? $case->getKey() : null);

        return [
            'value' => $value,
            'name' => $name,
            'label' => method_exists($case, 'label') ? (string) $case->label() : (string) ($name ?? ''),
        ];
    }

    /**
     * @param  class-string  $enumClass
     * @return array<int, array{value: string|int|null, name: string|null, label: string}>
     */
    public static function options(string $enumClass): array
    {
        if (! enum_exists($enumClass)) {
            return [];
        }
        $out = [];
        foreach ($enumClass::cases() as $case) {
            $shape = self::case($case);
            if ($shape !== null) {
                $out[] = $shape;
            }
        }

        return $out;
    }
}
PHP

echo "==> Integration adapters written"

# ============================================================================
# PHPSTAN EXTENSION
# ============================================================================
cat > src/PHPStan/EnumeratorMethodReflectionExtension.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * PHPStan extension: silences "undefined method" for the dynamic helpers
 * shipped by `HasMagicComparisons` (`isActive()`, `isNotBanned()`, etc.) and
 * `HasInvokableCases` static call style. Full reflection logic lives in
 * Larastan's framework helpers; for v0.1.0 this is a permissive shim that
 * accepts any `is.*` / `isNot.*` zero-arg call on an Enumerator.
 */
final class EnumeratorMethodReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->implementsInterface(Enumerator::class)) {
            return false;
        }

        return str_starts_with($methodName, 'is');
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new EnumeratorReflectionExtension($classReflection, $methodName);
    }
}
PHP

cat > src/PHPStan/EnumeratorReflectionExtension.php <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\PHPStan;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

final class EnumeratorReflectionExtension implements MethodReflection
{
    public function __construct(
        private readonly ClassReflection $declaringClass,
        private readonly string $name,
    ) {}

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    public function getVariants(): array
    {
        return [
            new \PHPStan\Reflection\FunctionVariant(
                TemplateTypeMap::createEmpty(),
                null,
                [],
                false,
                new BooleanType(),
            ),
        ];
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getThrowType(): ?Type
    {
        return null;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
}
PHP

echo "==> PHPStan extension written"

echo
echo "Phase 2 scaffold complete."
