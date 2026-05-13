# Testing enums

Pest is configured to use `Simtabi\Laranail\Enumerator\Tests\TestCase` for the `Feature` directory. For consumer tests:

```php
use Simtabi\Laranail\Enumerator\EnumeratorServiceProvider;
use Orchestra\Testbench\TestCase;

class MyEnumTest extends TestCase {
    protected function getPackageProviders($app): array {
        return [EnumeratorServiceProvider::class];
    }
}
```
