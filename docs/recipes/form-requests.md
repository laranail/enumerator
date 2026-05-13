# Form requests

```php
class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', new EnumValue(UserStatusEnum::class)],
            'role'   => ['required', new EnumName(RoleEnum::class)],
        ];
    }
}
```
