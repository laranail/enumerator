<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use BackedEnum;
use ReflectionEnum;
use UnitEnum;

/**
 * Exports an enum to alternative representations: PHP array, JSON, TypeScript
 * (`as const` object + union type + Zod tuple).
 *
 * @phpstan-type CaseDescriptor array{value: int|string, name: string, label: string}
 */
final class EnumExporter
{
    /**
     * @param  class-string<UnitEnum>  $class
     * @return array<int, CaseDescriptor>
     */
    public function toArray(string $class): array
    {
        $out = [];
        foreach ($class::cases() as $case) {
            $out[] = [
                'value' => $case instanceof BackedEnum ? $case->value : $case->name,
                'name' => $case->name,
                'label' => method_exists($case, 'label') ? (string) $case->label() : $case->name,
            ];
        }

        return $out;
    }

    /**
     * @param  class-string<UnitEnum>  $class
     */
    public function toJson(string $class, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES): string
    {
        return (string) json_encode($this->toArray($class), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * @param  class-string<UnitEnum>  $class
     */
    public function toPhpFile(string $class): string
    {
        $payload = var_export($this->toArray($class), true);

        return "<?php\n\ndeclare(strict_types=1);\n\nreturn {$payload};\n";
    }

    /**
     * @param  class-string<UnitEnum>  $class
     */
    public function toTypeScript(string $class): string
    {
        $reflection = new ReflectionEnum($class);
        $shortName = $reflection->getShortName();
        $cases = $this->toArray($class);

        $constLines = [];
        $literalUnion = [];
        $tupleValues = [];
        foreach ($cases as $case) {
            $value = is_int($case['value']) ? (string) $case['value'] : "'" . addslashes($case['value']) . "'";
            $constLines[] = sprintf('    %s: %s', $case['name'], $value);
            $literalUnion[] = is_int($case['value']) ? (string) $case['value'] : "'" . addslashes($case['value']) . "'";
            $tupleValues[] = is_int($case['value']) ? (string) $case['value'] : "'" . addslashes($case['value']) . "'";
        }
        $constObject = "export const {$shortName} = {\n" . implode(",\n", $constLines) . "\n} as const;";
        $unionType = "export type {$shortName}Value = " . implode(' | ', $literalUnion) . ';';
        // / B16: emitted as a plain `as const` tuple. Suitable for
        // `z.enum(...)` consumption if a consumer wires up Zod themselves,
        // but no Zod schema or runtime dependency is emitted here.
        $constTuple = "export const {$shortName}_VALUES = [\n    " . implode(",\n    ", $tupleValues) . "\n] as const;";

        return implode("\n\n", [$constObject, $unionType, $constTuple]) . "\n";
    }
}
