# Blade components

Six components: `badge`, `select`, `radio`, `checkboxes`, `grid`, `list`.

```blade
<x-laranail-enumerator::badge :case="\$user->status" />
<x-laranail-enumerator::select :enum="UserStatusEnum::class" name="status" />
<x-laranail-enumerator::radio :enum="UserStatusEnum::class" name="status" layout="horizontal" />
<x-laranail-enumerator::checkboxes :enum="PermissionEnum::class" name="permissions[]" :selected="\$selected" />
<x-laranail-enumerator::grid :enum="UserStatusEnum::class" :columns="3" />
<x-laranail-enumerator::list :enum="UserStatusEnum::class" />
```

Each component picks the view bundle based on `config('enumerator.css_framework')` (overridable via `framework=` prop).
