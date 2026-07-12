<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Concerns\ResolvesMagicCalls;
use Simtabi\Laranail\Enumerator\Exceptions\AmbiguousMagicCallException;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\MultiHandlerEnum;

// ResolvesMagicCalls — multi-handler dispatch branches.

it('throws AmbiguousMagicCallException by default on a multi-hit call', function (): void {
    // The default config value of ambiguous_resolution is 'throw'.
    config()->set('enumerator.magic.ambiguous_resolution', 'throw');
    MultiHandlerEnum::Alpha->isAlpha();
})->throws(AmbiguousMagicCallException::class);

it('returns the first handler hit when ambiguous_resolution is "first"', function (): void {
    config()->set('enumerator.magic.ambiguous_resolution', 'first');
    // magicCompare runs first, returns `true` (Alpha->isAlpha).
    expect(MultiHandlerEnum::Alpha->isAlpha())->toBeTrue();
});

it('returns null when ambiguous_resolution is "null"', function (): void {
    config()->set('enumerator.magic.ambiguous_resolution', 'null');
    expect(MultiHandlerEnum::Alpha->isAlpha())->toBeNull();
});

it('throws BadMethodCallException when no handler returns a hit', function (): void {
    // `someBogusMethod()` matches neither handler's prefix.
    MultiHandlerEnum::Alpha->someBogusMethod();
})->throws(BadMethodCallException::class);

it('skips handlers whose method does not exist on the receiver', function (): void {
    // Cover the `method_exists` continue branch by adding a fake handler
    // name that no method satisfies. Use an inline fixture so we don't
    // pollute the main MultiHandlerEnum.
    $enum = new class
    {
        use ResolvesMagicCalls;

        public string $name = 'Anon';

        /** @return list<string> */
        protected static function magicCallHandlers(): array
        {
            return ['magicDoesNotExist', 'magicCompare'];
        }

        /**
         * @param  array<int, mixed>  $arguments
         * @return array{0: mixed}|null
         */
        protected function magicCompare(string $method, array $arguments): ?array
        {
            return $method === 'isFoo' ? [true] : null;
        }
    };

    expect($enum->isFoo())->toBeTrue();
});
