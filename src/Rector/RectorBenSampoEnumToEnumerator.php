<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rector;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Rector\AbstractRector;
use Rector\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Rector\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

// Migration rule: BenSampo/laravel-enum → laranail/enumerator.
// File-level guard so the rule class is only defined
// when Rector itself is installed — otherwise consumers loading this
// file via autoload would fatal.
if (! class_exists(AbstractRector::class)) {
    return;
}

/**
 * Convert a BenSampo `\BenSampo\Enum\Enum` subclass into a native PHP
 * 8.3+ enum using `Simtabi\Laranail\Enumerator\Concerns\HasEnumerator`.
 *
 * **Best-effort transformation**: handles the common pattern where the
 * source class is a final class extending `BenSampo\Enum\Enum` with
 * scalar (`string|int`) public constants. Subclasses with method
 * overrides (`getDescription`, `getValues`, etc.) need manual review
 * after the codemod runs — the rule strips those methods.
 *
 * @see https://github.com/BenSampo/laravel-enum
 */
final class RectorBenSampoEnumToEnumerator extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * Transform a Class_ node into an Enum_ node when the source is a
     * BenSampo enum.
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof Class_) {
            return null;
        }
        if (! $this->extendsBenSampoEnum($node)) {
            return null;
        }

        $cases = $this->extractCases($node);
        if ($cases === []) {
            return null;
        }

        $backingType = $this->detectBackingType($cases);

        $enum = new Enum_(
            $node->name,
            [
                'scalarType' => $backingType !== null ? new Identifier($backingType) : null,
                'implements' => [new FullyQualified('Simtabi\\Laranail\\Enumerator\\Contracts\\Enumerator')],
            ],
            $node->getAttributes(),
        );

        // Add `use HasEnumerator;` inside the enum body.
        $enum->stmts[] = new TraitUse([
            new FullyQualified('Simtabi\\Laranail\\Enumerator\\Concerns\\HasEnumerator'),
        ]);

        // Convert each constant to an EnumCase.
        foreach ($cases as $caseName => $caseValue) {
            $enum->stmts[] = new EnumCase($caseName, $caseValue);
        }

        return $enum;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert BenSampo enum classes into native PHP 8.3+ enums using laranail/enumerator.',
            [
                new CodeSample(
                    <<<'PHP'
final class UserStatus extends \BenSampo\Enum\Enum
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
}
PHP,
                    <<<'PHP'
enum UserStatus: string implements \Simtabi\Laranail\Enumerator\Contracts\Enumerator
{
    use \Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
PHP,
                ),
            ],
        );
    }

    private function extendsBenSampoEnum(Class_ $node): bool
    {
        if ($node->extends === null) {
            return false;
        }

        $name = $node->extends->toString();

        return $name === 'BenSampo\\Enum\\Enum' || $name === '\\BenSampo\\Enum\\Enum';
    }

    /**
     * @return array<string, Node\Expr> case name → value expression
     */
    private function extractCases(Class_ $node): array
    {
        $out = [];
        foreach ($node->stmts as $stmt) {
            if (! $stmt instanceof ClassConst) {
                continue;
            }
            if (! $stmt->isPublic()) {
                continue;
            }
            foreach ($stmt->consts as $const) {
                if ($const instanceof Const_) {
                    $out[$const->name->toString()] = $const->value;
                }
            }
        }

        return $out;
    }

    /**
     * @param  array<string, Node\Expr>  $cases
     */
    private function detectBackingType(array $cases): ?string
    {
        $sawInt = false;
        $sawString = false;
        foreach ($cases as $value) {
            if ($value instanceof Node\Scalar\Int_) {
                $sawInt = true;
            } elseif ($value instanceof Node\Scalar\String_) {
                $sawString = true;
            } else {
                // Mixed / dynamic expressions — fall back to no backing
                // (consumers can adjust manually).
                return null;
            }
        }
        if ($sawInt && ! $sawString) {
            return 'int';
        }
        if ($sawString && ! $sawInt) {
            return 'string';
        }

        return null;
    }
}
