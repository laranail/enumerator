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
        Blade::directive('enumeratorLabel', static fn (string $expr): string => "<?php echo e(({$expr})->label()); ?>");

        // @enumeratorValue($case) — prints the backing value.
        Blade::directive('enumeratorValue', static fn (string $expr): string => "<?php echo e(({$expr}) instanceof \\BackedEnum ? ({$expr})->value : (method_exists({$expr}, 'getValue') ? ({$expr})->getValue() : ({$expr})->name)); ?>");

        // @enumeratorName($case) — prints the case name.
        Blade::directive('enumeratorName', static fn (string $expr): string => "<?php echo e(({$expr}) instanceof \\UnitEnum ? ({$expr})->name : (method_exists({$expr}, 'getKey') ? ({$expr})->getKey() : '')); ?>");

        // @enumeratorBadge($case) — outputs HtmlString.
        Blade::directive('enumeratorBadge', static fn (string $expr): string => "<?php echo ({$expr})->toHtml(); ?>");

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
        Blade::directive('enumeratorColor', static fn (string $expr): string => "<?php echo e(method_exists({$expr}, 'color') ? ({$expr})->color() ?? '' : ''); ?>");

        // @enumeratorIcon($case)
        Blade::directive('enumeratorIcon', static fn (string $expr): string => "<?php echo e(method_exists({$expr}, 'icon') ? ({$expr})->icon() ?? '' : ''); ?>");

        // parity directives for description / help / meta.
        // @enumeratorDescription($case)
        Blade::directive('enumeratorDescription', static fn (string $expr): string => "<?php echo e(method_exists({$expr}, 'description') ? ({$expr})->description() ?? '' : ''); ?>");

        // @enumeratorHelp($case)
        Blade::directive('enumeratorHelp', static fn (string $expr): string => "<?php echo e(method_exists({$expr}, 'help') ? ({$expr})->help() ?? '' : ''); ?>");

        // @enumeratorMeta($case, 'key')
        Blade::directive('enumeratorMeta', static function (string $expr): string {
            $parts = self::splitTwo($expr);

            return "<?php echo e(method_exists({$parts[0]}, 'meta') ? ({$parts[0]})->meta({$parts[1]}) ?? '' : ''); ?>";
        });
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
