<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Illuminate\Support\HtmlString;

/**
 * Render the case as an HTML badge. Picks the CSS class set from the
 * configured framework (or the per-call override) and falls back to a plain
 * neutral wrapper when nothing is registered.
 *
 * Requires HasAttributes for cssClass()/label()/icon() helpers.
 */
trait RendersHtml
{
    public function toHtml(?string $framework = null): HtmlString
    {
        if ($framework === null) {
            $framework = function_exists('config')
                ? (string) (config('enumerator.css_framework') ?? 'plain')
                : 'plain';
        }
        $classes = $this->cssClass($framework) ?? $this->fallbackClasses($framework);
        $label = method_exists($this, 'label') ? (string) $this->label() : (string) $this->name;
        $icon = method_exists($this, 'icon') ? $this->icon() : null;

        $escape = function_exists('e') ? 'e' : 'htmlspecialchars';

        $iconHtml = $icon !== null
            ? sprintf('<span class="enumerator-icon" aria-hidden="true">%s</span> ', $escape($icon))
            : '';

        return new HtmlString(sprintf(
            '<span class="%s" role="status">%s%s</span>',
            $escape($classes),
            $iconHtml,
            $escape($label),
        ));
    }

    private function fallbackClasses(string $framework): string
    {
        $color = method_exists($this, 'color') ? ($this->color() ?? 'default') : 'default';

        return match ($framework) {
            'tailwind' => "inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{$color}-100 text-{$color}-800",
            'daisyui' => "badge badge-{$color}",
            'bootstrap' => "badge bg-{$color}",
            'bulma' => "tag is-{$color}",
            default => sprintf('enumerator-badge enumerator-%s', $color),
        };
    }
}
