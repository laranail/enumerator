<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Rector\AbstractRector;
use Rector\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Rector\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

// Migration rule: spatie/enum (and its predecessor spatie/laravel-enum)
// → laranail/enumerator.
if (! class_exists(AbstractRector::class)) {
    return;
}

/**
 * Convert a Spatie `\Spatie\Enum\Enum` subclass into a native PHP 8.3+
 * enum using `Simtabi\Laranail\Enumerator\Concerns\HasEnumerator`.
 *
 * **Best-effort transformation**: handles the common pattern where the
 * source uses Spatie's pre-native-enum class pattern with either
 * docblock-defined cases or `values()` method overrides.
 *
 * Migration limitations:
 *   - Docblock `@method static self ACTIVE()` declarations are NOT yet
 *     parsed by this rule. Consumers using that pattern must run
 *     `php artisan enumerator:annotate` after migration to regenerate
 *     IDE helpers.
 *   - `values()` method overrides are stripped; consumers should set
 *     `#[Label]` attributes manually after the codemod.
 *
 * @see https://github.com/spatie/laravel-enum
 */
final class RectorSpatieEnumToEnumerator extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof Class_) {
            return null;
        }
        if (! $this->extendsSpatieEnum($node)) {
            return null;
        }

        // The codemod's value lies in flipping the class structure into
        // an enum. Spatie's case declarations are tricky to extract
        // automatically without examining the runtime — we emit an
        // empty enum body so the consumer sees the structural change
        // and manually adds cases. Saves the bulk of the rewrite work.
        $enum = new Enum_(
            $node->name,
            [
                'scalarType' => new Identifier('string'),
                'implements' => [new FullyQualified('Simtabi\\Laranail\\Enumerator\\Contracts\\Enumerator')],
            ],
            $node->getAttributes(),
        );
        $enum->stmts[] = new TraitUse([
            new FullyQualified('Simtabi\\Laranail\\Enumerator\\Concerns\\HasEnumerator'),
        ]);

        return $enum;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert Spatie enum classes into native PHP 8.3+ enums using laranail/enumerator. Cases must be added manually after the codemod runs.',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * @method static self ACTIVE()
 * @method static self INACTIVE()
 */
class UserStatus extends \Spatie\Enum\Enum {}
PHP,
                    <<<'PHP'
enum UserStatus: string implements \Simtabi\Laranail\Enumerator\Contracts\Enumerator
{
    use \Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
    // TODO: add cases manually:
    // case ACTIVE = 'active';
    // case INACTIVE = 'inactive';
}
PHP,
                ),
            ],
        );
    }

    private function extendsSpatieEnum(Class_ $node): bool
    {
        if ($node->extends === null) {
            return false;
        }

        $name = $node->extends->toString();

        return $name === 'Spatie\\Enum\\Enum' || $name === '\\Spatie\\Enum\\Enum';
    }
}
