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
