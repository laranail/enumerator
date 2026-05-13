# Inertia integration

Shape enums for SPA consumption with `EnumeratorTransformer`:

```php
return inertia('User', [
    'user' => $user,
    'status' => EnumeratorTransformer::case($user->status),
    'statusOptions' => EnumeratorTransformer::options(UserStatusEnum::class),
]);
```
