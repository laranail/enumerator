# Validation

Five rules:

```php
new EnumValue($class)            // valid backed value
new EnumName($class)             // valid case name
new EnumIn($class, [...])        // allow-list
new EnumNotIn($class, [...])     // deny-list
new EnumTransition($class, $from) // legal next state
```

`EnumValue` supports `->only([...])` / `->except([...])` chaining.
