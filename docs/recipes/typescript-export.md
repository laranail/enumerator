# TypeScript export

```bash
php artisan enumerator:export "App\Enums\UserStatusEnum" --ts --out=resources/js/types/enums/UserStatus.ts
```

Emits an `as const` object + derived union type + Zod-friendly tuple — three idioms in one file.

---

[← Docs index](../../README.md#documentation)
