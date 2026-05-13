# Class-const API

For rare scenarios (legacy migration, mixed backing types within one enum), extend `AbstractEnumeratorClass`:

```php
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Attributes\{Color, Label};

class UserStatusEnum extends AbstractEnumeratorClass
{
    #[Label('Active'), Color('success')] public const ACTIVE = 'active';
    #[Label('Banned'), Color('danger')]  public const BANNED = 'banned';
}

$s = UserStatusEnum::ACTIVE();
$s->label(); // 'Active'
```

Identical surface to the native-enum API except where PHP itself differs (no `->value`/`->name` magic constants; use `->getValue()`/`->getKey()`).
