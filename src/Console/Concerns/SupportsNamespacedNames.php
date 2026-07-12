<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console\Concerns;

use ReflectionProperty;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Lets a command use the laranail naming shape `laranail::enumerator.<command>`.
 *
 * Symfony's {@see SymfonyCommand::validateName()} regex (`^[^:]++(:[^:]++)*$`)
 * rejects the empty segment in `::`, so this trait sets the name (and aliases)
 * past that validator by writing the private property directly. Dispatch still
 * works because Symfony resolves an exact command name (and its registered
 * aliases) before its `:`-splitting namespace lookup runs — so both the
 * `laranail::enumerator.cache` primary and the familiar `enumerator:cache`
 * alias resolve.
 *
 * The trait also applies an optional `$commandAliases` list during construction
 * (Laravel invokes {@see setName()} while building the command from its
 * signature), keeping the old, non-namespaced names working as aliases with no
 * per-command constructor boilerplate. Declare the list on the command itself —
 * `protected array $commandAliases = ['enumerator:cache'];` — rather than on this
 * trait, since PHP forbids a class from overriding a trait property's default.
 *
 * Self-contained: this is a local copy of the canonical laranail trait, so the
 * package takes on no new dependency. Kept on PHP 8.3-safe syntax to match the
 * package floor.
 *
 * @api Stable extension point (SemVer-covered).
 */
trait SupportsNamespacedNames
{
    public function setName(string $name): static
    {
        $this->writeCommandName('name', $name);

        // The list is declared on the consuming command (see class docblock).
        if ($this->commandAliases !== []) {
            $this->setAliases($this->commandAliases);
        }

        return $this;
    }

    /**
     * @param  iterable<int, string>  $aliases
     */
    public function setAliases(iterable $aliases): static
    {
        $this->writeCommandName('aliases', is_array($aliases) ? $aliases : iterator_to_array($aliases));

        return $this;
    }

    private function writeCommandName(string $property, mixed $value): void
    {
        // The name/aliases are private on Symfony's base Command; writing them
        // directly bypasses validateName()'s rejection of the `::` separator.
        (new ReflectionProperty(SymfonyCommand::class, $property))->setValue($this, $value);
    }
}
