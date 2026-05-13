# API resources

Serialize an enum case in an Eloquent API Resource:

```php
public function toArray($request): array {
    return [
        'status' => $this->status->toArray(), // ['value'=>..., 'name'=>..., 'label'=>...]
    ];
}
```
