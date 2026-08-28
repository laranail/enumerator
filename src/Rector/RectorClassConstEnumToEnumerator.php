<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Rector\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;

// Migration rule: any legacy class-constant enum base → laranail/enumerator's
// AbstractEnumeratorClass.
if (! class_exists(AbstractRector::class)) {
    return;
}

/**
 * Rebase a legacy class-constant enum onto `AbstractEnumeratorClass`.
 *
 * ## Why this converts class → class, not class → native enum
 *
 * The other two migration rules in this package flip their source into a native
 * PHP enum, because that is what those libraries were emulating. A
 * class-constant enum is different: its cases **are** string constants, and its
 * methods are full of `match ($this->value) { self::ACTIVE => … }`. Turning
 * `self::ACTIVE` from a string constant into an enum case silently changes every
 * one of those comparisons from "matches" to "never matches" — the class still
 * compiles, the tests still run, and every `match` falls to its default.
 *
 * `AbstractEnumeratorClass` exists for exactly this case, so the rule rebases
 * onto it and leaves the constants alone. Moving on to a native enum afterwards
 * is a separate, deliberate step.
 *
 * ## What it changes
 *
 * | Before | After |
 * |---|---|
 * | `extends <configured base>` | `extends AbstractEnumeratorClass` |
 * | `$this->value` | `$this->getValue()` |
 * | `Foo::from($v)` / `Foo::of($v)` | `Foo::fromValue($v)` |
 * | `Foo::tryFrom($v)` | `Foo::tryFromValue($v)` |
 * | `protected static $langPath = …` | removed |
 *
 * The `$value` property on `AbstractEnumeratorClass` is **private**, so
 * `$this->value` is not merely a rename — left alone it is a fatal error on
 * first use. That single rewrite is most of this rule's value: in the codebase
 * it was written for, `$this->value` appears 69 times across 21 enums.
 *
 * ## What it deliberately does not change
 *
 * `$langPath` is removed rather than translated. It pointed at a translation
 * namespace whose keys this rule cannot see, and enumerator resolves labels
 * through `#[Label]` attributes plus `Concerns\IsTranslatable`. Guessing the
 * mapping would produce labels that are wrong rather than absent, and an absent
 * label is visible — `label()` falls back to humanising the case name.
 *
 * Model casts are not touched either: a legacy base usually implements
 * `Castable` on the enum itself, while enumerator uses `Casts\AsEnum` on the
 * model. That edit belongs where the model is, not where the enum is.
 *
 * @see AbstractEnumeratorClass
 */
final class RectorClassConstEnumToEnumerator extends AbstractRector implements ConfigurableRectorInterface
{
    private const string TARGET = 'Simtabi\\Laranail\\Enumerator\\AbstractEnumeratorClass';

    /**
     * Fully-qualified legacy base classes to rebase, without a leading slash.
     *
     * Configurable because every application invents its own base — this rule
     * is not about one vendor package, it is about a shape.
     *
     * @var list<string>
     */
    private array $baseClasses = [];

    /**
     * @param array<int|string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $bases = [];

        foreach ($configuration as $value) {
            if (is_string($value) && $value !== '') {
                $bases[] = ltrim($value, '\\');
            }
        }

        $this->baseClasses = $bases;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof Class_ || ! $this->extendsLegacyBase($node)) {
            return null;
        }

        $node->extends = new FullyQualified(self::TARGET);

        $changed = $this->removeLangPath($node);
        $changed = $this->rewriteValueFetches($node) || $changed;
        $changed = $this->rewriteStaticFactories($node) || $changed;

        // The extends rewrite alone is a change, so the node is always
        // returned; $changed exists to keep each helper honest about whether it
        // did anything, which the tests assert individually.
        unset($changed);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rebase a legacy class-constant enum onto AbstractEnumeratorClass, rewriting $this->value to $this->getValue() and the from/of/tryFrom factories.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
class Currency extends \Acme\Enum\Enum
{
    public const USD = 'USD';

    protected static string $langPath = 'acme::enums.currency';

    public function locale(): string
    {
        return match ($this->value) {
            self::USD => 'en_US',
            default => 'en_US',
        };
    }
}
PHP,
                    <<<'PHP'
class Currency extends \Simtabi\Laranail\Enumerator\AbstractEnumeratorClass
{
    public const USD = 'USD';

    public function locale(): string
    {
        return match ($this->getValue()) {
            self::USD => 'en_US',
            default => 'en_US',
        };
    }
}
PHP,
                    ['Acme\\Enum\\Enum'],
                ),
            ],
        );
    }

    private function extendsLegacyBase(Class_ $node): bool
    {
        if ($node->extends === null || $this->baseClasses === []) {
            return false;
        }

        return in_array(ltrim($node->extends->toString(), '\\'), $this->baseClasses, true);
    }

    /**
     * `$this->value` → `$this->getValue()`.
     *
     * The rewrite that makes the migration work at all: the property is private
     * on the new base, so leaving these alone turns every one into a fatal
     * error the first time that branch runs.
     */
    private function rewriteValueFetches(Class_ $node): bool
    {
        $changed = false;

        $this->traverseNodesWithCallable($node, static function (Node $subNode) use (&$changed): ?Node {
            if (! $subNode instanceof PropertyFetch) {
                return null;
            }

            if (! $subNode->var instanceof Variable || $subNode->var->name !== 'this') {
                return null;
            }

            if (! $subNode->name instanceof Identifier || $subNode->name->toString() !== 'value') {
                return null;
            }

            $changed = true;

            return new MethodCall($subNode->var, new Identifier('getValue'));
        });

        return $changed;
    }

    /**
     * `from`/`of` → `fromValue`, `tryFrom` → `tryFromValue`.
     *
     * Scoped to `self::`, `static::` and the class's own name, so a `from()` on
     * some unrelated collaborator inside a method body is left alone.
     */
    private function rewriteStaticFactories(Class_ $node): bool
    {
        $ownName = $node->name?->toString();
        $changed = false;

        $this->traverseNodesWithCallable($node, static function (Node $subNode) use ($ownName, &$changed): ?Node {
            if (! $subNode instanceof StaticCall || ! $subNode->name instanceof Identifier) {
                return null;
            }

            $target = $subNode->class instanceof Node\Name ? $subNode->class->toString() : null;

            if ($target === null) {
                return null;
            }

            $isOwn = in_array($target, ['self', 'static', $ownName], true);

            if (! $isOwn) {
                return null;
            }

            $replacement = match ($subNode->name->toString()) {
                'from', 'of' => 'fromValue',
                'tryFrom'    => 'tryFromValue',
                default      => null,
            };

            if ($replacement === null) {
                return null;
            }

            $changed = true;
            $subNode->name = new Identifier($replacement);

            return $subNode;
        });

        return $changed;
    }

    /**
     * Drop `$langPath`. See the class docblock for why it is not translated.
     */
    private function removeLangPath(Class_ $node): bool
    {
        $changed = false;

        foreach ($node->stmts as $index => $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            foreach ($stmt->props as $prop) {
                if ($prop->name->toString() === 'langPath') {
                    unset($node->stmts[$index]);
                    $changed = true;

                    break 2;
                }
            }
        }

        if ($changed) {
            $node->stmts = array_values($node->stmts);
        }

        return $changed;
    }
}
