#!/usr/bin/env bash
# scripts/scaffold-docs.sh
#
# Creates the remaining docs/tools/ and docs/recipes/ pages with skeleton
# bodies. Each page contains enough working content to be linked from the
# index without being empty. Detailed expansion happens incrementally; the
# v0.1.0 release ships these as a navigable scaffold.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

mkdir -p docs/tools docs/recipes

write_stub() {
    local path="$1"; local title="$2"; local body="$3"
    cat > "$path" <<EOF
# ${title}

${body}
EOF
}

# Top-level
write_stub docs/release.md "Release process" "See [docs/shipping-checklist.md](shipping-checklist.md). Tag-driven (\`vX.Y.Z\`), Keep-a-Changelog, semantic versioning. Tags trigger GitHub Actions release notes."
write_stub docs/shipping-checklist.md "Shipping checklist" "1. \`composer validate --strict\`
2. \`vendor/bin/pint --test\`
3. \`vendor/bin/phpstan analyse\`
4. \`vendor/bin/pest --coverage --min=90\`
5. Update \`CHANGELOG.md\` (\`[Unreleased]\` → \`[X.Y.Z]\`).
6. Tag \`vX.Y.Z\` on the main branch.
7. Push the tag — GitHub Actions \`release.yml\` generates release notes.
8. Verify on Packagist."
write_stub docs/upgrade-guide.md "Upgrade guide" "No breaking changes from \`v0.1.0\` yet. Track per-version upgrade notes in \`CHANGELOG.md\`."

# Tools
write_stub docs/tools/attributes.md "Attributes" "Nine attributes ship with the package: \`Bit\`, \`Color\`, \`CssClass\`, \`Description\`, \`Help\`, \`Icon\`, \`Label\`, \`Meta\`, \`Order\`. Apply them on enum cases (or the enum class itself for \`Description\`).

Read with \`\$case->color()\`, \`->icon()\`, \`->label()\`, \`->help()\`, \`->order()\`, \`->meta(\$key)\`, \`->cssClass(\$framework)\`.

Overrides flow through \`config('enumerator.overrides')\` — see [configuration.md](../configuration.md)."

write_stub docs/tools/bitmask.md "Bitmask" "Mark cases with \`#[Bit(N)]\` (positive power of two) and \`use HasBitmask\`. Backing type is irrelevant — int, string, or pure enums all work.

\`\`\`php
\$mask = FeatureFlagEnum::mask(FeatureFlagEnum::DarkMode, FeatureFlagEnum::BetaUI);
\$mask->toInt();   // 3
\$mask->has(...); \$mask->add(...); \$mask->remove(...);
FeatureFlagEnum::fromMask(3);
\`\`\`

Persist via \`AsBitmask::of(FeatureFlagEnum::class)\` — see [casts.md](casts.md)."

write_stub docs/tools/blade-components.md "Blade components" "Six components: \`badge\`, \`select\`, \`radio\`, \`checkboxes\`, \`grid\`, \`list\`.

\`\`\`blade
<x-laranail-enumerator::badge :case=\"\\\$user->status\" />
<x-laranail-enumerator::select :enum=\"UserStatusEnum::class\" name=\"status\" />
<x-laranail-enumerator::radio :enum=\"UserStatusEnum::class\" name=\"status\" layout=\"horizontal\" />
<x-laranail-enumerator::checkboxes :enum=\"PermissionEnum::class\" name=\"permissions[]\" :selected=\"\\\$selected\" />
<x-laranail-enumerator::grid :enum=\"UserStatusEnum::class\" :columns=\"3\" />
<x-laranail-enumerator::list :enum=\"UserStatusEnum::class\" />
\`\`\`

Each component picks the view bundle based on \`config('enumerator.css_framework')\` (overridable via \`framework=\` prop)."

write_stub docs/tools/blade-directives.md "Blade directives" "Package-prefixed directives:

| Directive | Use |
|---|---|
| \`@enumeratorLabel(\$case)\`  | Translated label |
| \`@enumeratorValue(\$case)\`  | Backing value |
| \`@enumeratorName(\$case)\`   | Case name |
| \`@enumeratorBadge(\$case)\`  | \`toHtml()\` output |
| \`@enumeratorColor(\$case)\`  | Color attribute |
| \`@enumeratorIcon(\$case)\`   | Icon attribute |
| \`@enumeratorIs(\$case, \$target) ... @endEnumeratorIs\` | Conditional |
| \`@enumeratorIn(\$case, [..])  ... @endEnumeratorIn\` | In-list conditional |"

write_stub docs/tools/cases-collection.md "Cases collection" "\`YourEnum::collect()\` returns a \`Support\\CasesCollection\` that extends Laravel's \`Illuminate\\Support\\Collection\` with enum-aware helpers: \`names()\`, \`values()\`, \`labels()\`, \`whereName()\`, \`whereValue()\`, \`whereMeta()\`, \`only()\`, \`except()\`, \`keyByName()\`, \`keyByValue()\`, \`pluck()\`, \`toRichArray()\`."

write_stub docs/tools/casts.md "Eloquent casts" "Four casts:

- \`AsEnum::of(MyEnum::class)\` — single enum, throws on invalid.
- \`AsNullableEnum::of(MyEnum::class)\` — single enum, null on invalid.
- \`AsEnumeratorCollection::of(MyEnum::class)\` — JSON column of enums.
- \`AsBitmask::of(FeatureFlagEnum::class)\` — int column ↔ \`Bitmask\` value object.

Native Laravel enum casts also work (\`'status' => UserStatusEnum::class\`) for backed enums."

write_stub docs/tools/class-const-api.md "Class-const API" "For rare scenarios (legacy migration, mixed backing types within one enum), extend \`AbstractEnumeratorClass\`:

\`\`\`php
use Simtabi\\Laranail\\Enumerator\\AbstractEnumeratorClass;
use Simtabi\\Laranail\\Enumerator\\Attributes\\{Color, Label};

class UserStatusEnum extends AbstractEnumeratorClass
{
    #[Label('Active'), Color('success')] public const ACTIVE = 'active';
    #[Label('Banned'), Color('danger')]  public const BANNED = 'banned';
}

\$s = UserStatusEnum::ACTIVE();
\$s->label(); // 'Active'
\`\`\`

Identical surface to the native-enum API except where PHP itself differs (no \`->value\`/\`->name\` magic constants; use \`->getValue()\`/\`->getKey()\`)."

write_stub docs/tools/concerns.md "Concerns" "Sixteen traits ship in \`src/Concerns/\`:

**Always-on (composed by \`HasEnumeratorBehavior\`)**: \`HasAttributes\`, \`HasEquality\`, \`HasFromHelpers\`, \`IsJsonable\`, \`IsTranslatable\`, \`RendersHtml\`, \`ResolvesMagicCalls\`.

**Feature traits (opt-in)**: \`HasBitmask\`, \`HasTransitions\`, \`HasGrouping\`, \`HasOrder\`, \`HasLifecycle\`, \`HasMagicComparisons\`, \`HasInvokableCases\`.

**Class-const path**: \`HasClassEnumBehavior\` (used by \`AbstractEnumeratorClass\` — mutually exclusive with \`HasEnumeratorBehavior\`)."

write_stub docs/tools/contracts.md "Contracts" "Six interfaces in \`src/Contracts/\`:

- \`Enumerator\` — marker interface, implemented by every enum.
- \`HtmlRenderable\` — \`toHtml()\`.
- \`Stateful\` — state-machine enums (\`transitions()\`, \`initialStates()\`).
- \`Translatable\` — overrides for translation key shape.
- \`Bitwise\` — bitmask enums (use with \`#[Bit]\`).
- \`TransitionHook\` — before/after hooks for state transitions."

write_stub docs/tools/eloquent.md "Eloquent integration" "Casts: see [casts.md](casts.md).

\`HasEnumeratorScopes\` adds query scopes: \`whereEnum()\`, \`whereEnumNot()\`, \`whereEnumIn()\`, \`whereEnumNotIn()\`, \`whereEnumMeta()\`.

\`HasEnumeratorStateMachine\` enforces transitions and (optionally) records history to the \`enumerator_state_history\` table."

write_stub docs/tools/exceptions.md "Exceptions" "All exceptions extend \`EnumeratorException\` (a \`RuntimeException\`):

- \`AmbiguousMagicCallException\`
- \`InvalidBitmaskException\`
- \`InvalidEnumeratorNameException\`
- \`InvalidEnumeratorValueException\`
- \`InvalidTransitionException\`
- \`TranslationMissingException\`"

write_stub docs/tools/helpers.md "Helpers" "Two utility classes in \`src/Helpers/\`:

- \`Bitmask\` — value object built by \`HasBitmask::mask()\` / \`fromMask()\`.
- \`Humanizer\` — pure string helpers used internally (\`humanize\`, \`slugify\`)."

write_stub docs/tools/invokable-cases.md "Invokable cases" "Opt-in via \`use HasInvokableCases;\`. Once added, you can call cases statically:

\`\`\`php
Color::Red();         // 'red' (alias for Color::Red->value)
Color::Red('label');  // 'Red'
\`\`\`

Override per-enum by implementing \`whenInvoked(\$case, ...\$args)\` for custom return shape."

write_stub docs/tools/magic-comparisons.md "Magic comparisons" "Opt-in via \`use HasMagicComparisons;\`:

\`\`\`php
\$status->isActive();         // true|false
\$status->isNotBanned();      // true|false
\$status->isOneOf(\$a, \$b);    // bool
\$status->isNoneOf(\$a, \$b);   // bool
\`\`\`

Case-insensitive by default. Ambiguity resolution via \`config('enumerator.magic.ambiguous_resolution')\`."

write_stub docs/tools/make-enumerator.md "make:enumerator command" "Generate a new enum from a publishable stub:

\`\`\`bash
php artisan make:enumerator UserStatusEnum
php artisan make:enumerator UserStatusEnum --stub=attributes
php artisan make:enumerator OrderStatusEnum --stub=state-machine
php artisan make:enumerator FeatureFlagEnum --stub=bitmask
php artisan make:enumerator Color --stub=pure
\`\`\`

Customize stubs:

\`\`\`bash
php artisan vendor:publish --tag=enumerator-stubs
# edit resources/stubs/enumerator/enumerator.*.stub
\`\`\`"

write_stub docs/tools/presets.md "Presets" "Twenty-six preset enums ship under \`Simtabi\\Laranail\\Enumerator\\Presets\\\` — 25 native + 1 class-const.

**Lifecycle:** \`StatusEnum\`, \`PublicationStatusEnum\`, \`ApprovalStatusEnum\`, \`OrderStatusEnum\`, \`PaymentStatusEnum\`, \`CommentStatusEnum\`, \`TaskStatusEnum\`.

**Severity:** \`PriorityEnum\`, \`SeverityEnum\`.

**UI:** \`VisibilityEnum\`, \`SizeEnum\`, \`DirectionEnum\`, \`ToggleEnum\`.

**HTTP:** \`HttpMethodEnum\`, \`HttpStatusClassEnum\`.

**Bitmask demos:** \`BasicPermissionEnum\` (int), \`FeatureFlagEnum\` (string), \`NotificationOptInEnum\` (pure).

**Demographic:** \`GenderEnum\`, \`MaritalStatusEnum\`, \`RaceEnum\`, \`ReligionEnum\`.

**Calendar:** \`WeekdayEnum\` (Sunday-first), \`MonthEnum\` (ISO).

**MIME:** \`MimeTypeCategoryEnum\`.

Use directly via \`use\`, or copy into your app:

\`\`\`bash
php artisan vendor:publish --tag=enumerator-presets
\`\`\`"

write_stub docs/tools/route-binding.md "Route binding" "Native Laravel 13 enum binding works out of the box for backed enums. For case-insensitive matching with a fallback:

\`\`\`php
use Simtabi\\Laranail\\Enumerator\\Routing\\EnumeratorRouteBinder;

EnumeratorRouteBinder::register('status', UserStatusEnum::class);
EnumeratorRouteBinder::register('status', UserStatusEnum::class, fallback: UserStatusEnum::Inactive);
\`\`\`"

write_stub docs/tools/state-machine.md "State machine" "Implement \`Contracts\\Stateful\` and \`use HasTransitions\`:

\`\`\`php
enum OrderStatusEnum: string implements Enumerator, Stateful {
    use HasEnumeratorBehavior, HasTransitions;
    case Pending = 'pending'; case Paid = 'paid'; /* ... */

    public static function initialStates(): array { return [self::Pending]; }
    public static function transitions(): array {
        return [
            self::Pending->value => [self::Paid, self::Cancelled],
            self::Paid->value    => [self::Shipped],
            /* ... */
        ];
    }
}
\`\`\`

Enforce on the model via \`use HasEnumeratorStateMachine;\` and \`protected array \$stateMachines = ['status'];\`.

History records in \`enumerator_state_history\` (publish migration first)."

write_stub docs/tools/translations.md "Translations" "Key shape: \`{namespace}::enums.{slug}.{case}\`.

Defaults:
- namespace = \`enumerator\` (from config).
- slug = \`Str::snake(class_basename())\` minus trailing \`Enum\`.

Override per-enum by implementing \`Contracts\\Translatable\` (override \`translationNamespace()\` and/or \`translationSlug()\`).

Lookup priority: translation file → \`#[Label]\` attribute → humanised name."

write_stub docs/tools/validation.md "Validation" "Five rules:

\`\`\`php
new EnumValue(\$class)            // valid backed value
new EnumName(\$class)             // valid case name
new EnumIn(\$class, [...])        // allow-list
new EnumNotIn(\$class, [...])     // deny-list
new EnumTransition(\$class, \$from) // legal next state
\`\`\`

\`EnumValue\` supports \`->only([...])\` / \`->except([...])\` chaining."

write_stub docs/tools/views-publishing.md "Views publishing" "Each CSS-framework view bundle is independently publishable:

\`\`\`bash
php artisan vendor:publish --tag=enumerator-views                 # all bundles
php artisan vendor:publish --tag=enumerator-views-plain
php artisan vendor:publish --tag=enumerator-views-tailwind
php artisan vendor:publish --tag=enumerator-views-daisyui
php artisan vendor:publish --tag=enumerator-views-bootstrap
php artisan vendor:publish --tag=enumerator-views-bulma
\`\`\`

Published views land under \`resources/views/vendor/laranail-enumerator/components/{framework}/\` — edit freely; they override package defaults."

# Recipes
write_stub docs/recipes/api-resources.md "API resources" "Serialize an enum case in an Eloquent API Resource:

\`\`\`php
public function toArray(\$request): array {
    return [
        'status' => \$this->status->toArray(), // ['value'=>..., 'name'=>..., 'label'=>...]
    ];
}
\`\`\`"

write_stub docs/recipes/customizing-presets.md "Customizing presets" "Two paths:

**1. Override in config** (preferred, no code copy):

\`\`\`php
// config/enumerator.php
'overrides' => [
    Simtabi\\Laranail\\Enumerator\\Presets\\Enums\\PriorityEnum::class => [
        'Critical' => ['color' => 'magenta', 'meta' => ['paging' => true]],
    ],
],
\`\`\`

**2. Copy and own** (when you need structural changes):

\`\`\`bash
php artisan vendor:publish --tag=enumerator-presets
# Files copied into app/Enums/ — edit freely.
\`\`\`"

write_stub docs/recipes/filament.md "Filament integration" "Filament 4+ adapters live under \`Integrations/Filament/\`. They auto-load only when \`filament/filament\` is installed.

- \`EnumeratorColumn\` — table column auto-formatted via \`->label()\`.
- \`EnumeratorBadge\` (infolist entry).
- \`EnumeratorFilter::for(MyEnum::class, 'status')\` — select filter.
- \`EnumeratorSelect::make('status')->enumerator(MyEnum::class)\`.
- \`EnumeratorRadio::make('status')->enumerator(MyEnum::class)\`.
- \`EnumeratorEntry\` (infolist text entry)."

write_stub docs/recipes/form-requests.md "Form requests" "\`\`\`php
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
\`\`\`"

write_stub docs/recipes/inertia.md "Inertia integration" "Shape enums for SPA consumption with \`EnumeratorTransformer\`:

\`\`\`php
return inertia('User', [
    'user' => \$user,
    'status' => EnumeratorTransformer::case(\$user->status),
    'statusOptions' => EnumeratorTransformer::options(UserStatusEnum::class),
]);
\`\`\`"

write_stub docs/recipes/livewire.md "Livewire integration" "Backed-enum properties work out of the box in Livewire 3.5+ via Laravel's native enum casting.

For pure enums or \`AbstractEnumeratorClass\` instances, use \`EnumeratorCasts::hydrateProperty()\` in your component's \`hydrate{Prop}\` hook."

write_stub docs/recipes/notification-channel.md "Notification channel example" "Pattern for a rich domain enum using attributes + grouping. See \`laranail/laranail\`'s \`NotificationChannelEnum\` for a real-world implementation (15 channels grouped into core/messaging/mobile/integration buckets)."

write_stub docs/recipes/nova.md "Nova integration" "Nova 5+ adapters live under \`Integrations/Nova/\`. Auto-load when \`laravel/nova\` is installed.

\`\`\`php
EnumeratorField::make('Status')->forEnumerator(UserStatusEnum::class);
\`\`\`"

write_stub docs/recipes/tailwind-config.md "Tailwind config" "Add the Tailwind view bundle path to your \`tailwind.config.js\`:

\`\`\`js
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './vendor/laranail/enumerator/resources/views/components/tailwind/**/*.blade.php',
  ],
};
\`\`\`"

write_stub docs/recipes/testing-enums.md "Testing enums" "Pest is configured to use \`Simtabi\\Laranail\\Enumerator\\Tests\\TestCase\` for the \`Feature\` directory. For consumer tests:

\`\`\`php
use Simtabi\\Laranail\\Enumerator\\EnumeratorServiceProvider;
use Orchestra\\Testbench\\TestCase;

class MyEnumTest extends TestCase {
    protected function getPackageProviders(\$app): array {
        return [EnumeratorServiceProvider::class];
    }
}
\`\`\`"

write_stub docs/recipes/typescript-export.md "TypeScript export" "\`\`\`bash
php artisan enumerator:export \"App\\Enums\\UserStatusEnum\" --ts --out=resources/js/types/enums/UserStatus.ts
\`\`\`

Emits an \`as const\` object + derived union type + Zod-friendly tuple — three idioms in one file."

echo "==> Docs scaffold complete."
ls docs/tools/ | wc -l | xargs -I N echo "Tools docs: N"
ls docs/recipes/ | wc -l | xargs -I N echo "Recipes:    N"
