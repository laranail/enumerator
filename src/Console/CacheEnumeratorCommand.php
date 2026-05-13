<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;
use Simtabi\Laranail\Enumerator\Support\ReflectionCachePersistor;

class CacheEnumeratorCommand extends Command
{
    protected $signature = 'enumerator:cache';

    protected $description = 'Warm and persist the enumerator reflection cache.';

    /**
     * fix: delegate to ReflectionCachePersistor so the persisted
     * payload actually contains the warmed attribute + case data. The
     * previous implementation called LayeredCache::persist() against an
     * empty store because AttributesCache + CasesCache use their own
     * disconnected static memos.
     */
    public function handle(ReflectionCachePersistor $persistor): int
    {
        $classes = (array) config('enumerator.cache.auto_warm_classes', []);
        $persistor->dump($classes);
        $this->info((string) __('enumerator::enumerator.commands.cache.cached'));

        return self::SUCCESS;
    }
}
