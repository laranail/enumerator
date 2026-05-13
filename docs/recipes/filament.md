# Filament integration

Filament 4+ adapters live under `Integrations/Filament/`. They auto-load only when `filament/filament` is installed.

- `EnumeratorColumn` — table column auto-formatted via `->label()`.
- `EnumeratorBadge` (infolist entry).
- `EnumeratorFilter::for(MyEnum::class, 'status')` — select filter.
- `EnumeratorSelect::make('status')->enumerator(MyEnum::class)`.
- `EnumeratorRadio::make('status')->enumerator(MyEnum::class)`.
- `EnumeratorEntry` (infolist text entry).
