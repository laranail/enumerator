#!/usr/bin/env bash
# scripts/scaffold.sh
#
# One-shot boilerplate scaffolder for laranail/enumerator v0.1.0.
# Creates directories + all boilerplate files (markdown, config, attributes,
# exceptions, contracts, stubs, presets, view bundles, lang). The hand-
# written PHP logic files (concerns, casts, rules, blade components, service
# provider, console commands, integrations, tests, docs) are NOT generated
# here — they are authored individually after this script runs.
#
# Idempotent: re-running overwrites existing boilerplate files.
#
# Usage:
#   bash scripts/scaffold.sh

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Scaffolding into $ROOT"

# ----------------------------------------------------------------------------
# Directory tree
# ----------------------------------------------------------------------------
mkdir -p \
    .github/workflows \
    .github/ISSUE_TEMPLATE \
    config \
    lang/en \
    database/migrations \
    resources/stubs \
    resources/views/components/plain \
    resources/views/components/tailwind \
    resources/views/components/daisyui \
    resources/views/components/bootstrap \
    resources/views/components/bulma \
    src/Attributes \
    src/Blade/Components \
    src/Casts \
    src/Concerns \
    src/Console \
    src/Contracts \
    src/Eloquent \
    src/Exceptions \
    src/Facades \
    src/Helpers \
    src/Integrations/Filament/Columns \
    src/Integrations/Filament/Components \
    src/Integrations/Filament/Filters \
    src/Integrations/Filament/Forms \
    src/Integrations/Filament/Infolists \
    src/Integrations/Inertia \
    src/Integrations/Livewire \
    src/Integrations/Nova \
    src/PHPStan \
    src/Presets/Enums \
    src/Routing \
    src/Rules \
    src/Support \
    tests/Application/Console \
    tests/Application/Models \
    tests/Application/database/migrations \
    tests/Application/routes \
    tests/Feature/Integrations \
    tests/Fixtures/Enums \
    tests/Fixtures/lang/en \
    tests/Unit/Attributes \
    tests/Unit/Concerns \
    tests/Unit/Helpers \
    tests/Unit/Rules \
    tests/Unit/Support \
    docs/tools \
    docs/recipes

# ----------------------------------------------------------------------------
# Root config files
# ----------------------------------------------------------------------------
cat > .editorconfig <<'EOF'
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
insert_final_newline = true
trim_trailing_whitespace = true

[*.{yml,yaml,json,blade.php}]
indent_size = 2

[Makefile]
indent_style = tab
EOF

cat > .gitignore <<'EOF'
/vendor/
/node_modules/
/build/
/coverage/
/.phpunit.cache/
/.phpstan-cache/
/.idea/
/.vscode/
/.fleet/
/.zed/
.DS_Store
.phpunit.result.cache
composer.lock
_ide_helper_enumerator.php
EOF

cat > .gitattributes <<'EOF'
* text=auto eol=lf

# Files/dirs excluded from Packagist archives
/tests              export-ignore
/docs               export-ignore
/examples           export-ignore
/.github            export-ignore
/.editorconfig      export-ignore
/.gitattributes     export-ignore
/.gitignore         export-ignore
/.idea              export-ignore
/.vscode            export-ignore
/CHANGELOG.md       export-ignore
/CODE_OF_CONDUCT.md export-ignore
/CONTRIBUTING.md    export-ignore
/plans              export-ignore
/SECURITY.md        export-ignore
/phpstan.neon       export-ignore
/phpunit.xml        export-ignore
/pint.json          export-ignore
/scripts            export-ignore
/extension.neon     export-ignore

# Language detection on GitHub
*.blade.php linguist-language=Blade
*.stub      linguist-language=PHP
EOF

cat > CODE_OF_CONDUCT.md <<'EOF'
# Contributor Covenant Code of Conduct

## Our Pledge

We as members, contributors, and leaders pledge to make participation in our
community a harassment-free experience for everyone, regardless of age, body
size, visible or invisible disability, ethnicity, sex characteristics, gender
identity and expression, level of experience, education, socio-economic status,
nationality, personal appearance, race, religion, or sexual identity and
orientation.

We pledge to act and interact in ways that contribute to an open, welcoming,
diverse, inclusive, and healthy community.

## Our Standards

Examples of behavior that contributes to a positive environment for our
community include:

- Demonstrating empathy and kindness toward other people.
- Being respectful of differing opinions, viewpoints, and experiences.
- Giving and gracefully accepting constructive feedback.
- Accepting responsibility and apologising to those affected by our mistakes,
  and learning from the experience.
- Focusing on what is best not just for us as individuals, but for the
  overall community.

Examples of unacceptable behavior include:

- The use of sexualised language or imagery, and sexual attention or advances
  of any kind.
- Trolling, insulting or derogatory comments, and personal or political
  attacks.
- Public or private harassment.
- Publishing others' private information, such as a physical or email
  address, without their explicit permission.
- Other conduct which could reasonably be considered inappropriate in a
  professional setting.

## Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be
reported to the community leaders responsible for enforcement at
**opensource@simtabi.com**. All complaints will be reviewed and investigated
promptly and fairly.

This Code of Conduct is adapted from the [Contributor Covenant][homepage],
version 2.1.

[homepage]: https://www.contributor-covenant.org
EOF

# ----------------------------------------------------------------------------
# .github/*
# ----------------------------------------------------------------------------
cat > .github/FUNDING.yml <<'EOF'
github: [simtabi]
custom: ["https://simtabi.com/sponsor"]
EOF

cat > .github/dependabot.yml <<'EOF'
version: 2
updates:
  - package-ecosystem: composer
    directory: /
    schedule:
      interval: weekly
      day: monday
      time: "06:00"
      timezone: America/New_York
    open-pull-requests-limit: 10

  - package-ecosystem: github-actions
    directory: /
    schedule:
      interval: weekly
      day: monday
      time: "06:00"
      timezone: America/New_York
    open-pull-requests-limit: 5
EOF

cat > .github/workflows/ci.yml <<'EOF'
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

permissions:
  contents: read

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ["8.3", "8.4"]

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: xdebug
          tools: composer:v2

      - name: Cache Composer
        uses: actions/cache@v4
        with:
          path: ~/.composer/cache/files
          key: composer-${{ matrix.php }}-${{ hashFiles('composer.json') }}

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Pint (format check)
        run: vendor/bin/pint --test

      - name: PHPStan
        run: vendor/bin/phpstan analyse --no-progress

      - name: Pest (with coverage)
        run: vendor/bin/pest --coverage --min=90
EOF

cat > .github/workflows/static-analysis.yml <<'EOF'
name: Static Analysis

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

permissions:
  contents: read

jobs:
  phpstan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
          tools: composer:v2
      - run: composer install --prefer-dist --no-progress
      - run: vendor/bin/phpstan analyse --no-progress --error-format=github
EOF

cat > .github/workflows/release.yml <<'EOF'
name: Release

on:
  push:
    tags: ["v*"]

permissions:
  contents: write

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Generate release notes
        uses: softprops/action-gh-release@v2
        with:
          generate_release_notes: true
EOF

cat > .github/ISSUE_TEMPLATE/bug_report.yml <<'EOF'
name: Bug report
description: Report a defect in laranail/enumerator
labels: [bug]
body:
  - type: textarea
    id: description
    attributes:
      label: Description
      description: What went wrong?
    validations:
      required: true
  - type: textarea
    id: reproduction
    attributes:
      label: Steps to reproduce
      description: Minimal failing code, expected vs actual.
    validations:
      required: true
  - type: input
    id: package_version
    attributes:
      label: Package version
      placeholder: "0.1.0"
    validations:
      required: true
  - type: input
    id: php_version
    attributes:
      label: PHP version
      placeholder: "8.3.x"
    validations:
      required: true
  - type: input
    id: laravel_version
    attributes:
      label: Laravel version
      placeholder: "13.x"
    validations:
      required: true
EOF

cat > .github/ISSUE_TEMPLATE/feature_request.yml <<'EOF'
name: Feature request
description: Suggest an idea
labels: [enhancement]
body:
  - type: textarea
    id: motivation
    attributes:
      label: Motivation
      description: What problem does this solve?
    validations:
      required: true
  - type: textarea
    id: proposal
    attributes:
      label: Proposal
      description: What would the API look like?
    validations:
      required: true
  - type: textarea
    id: alternatives
    attributes:
      label: Alternatives considered
EOF

cat > .github/ISSUE_TEMPLATE/config.yml <<'EOF'
blank_issues_enabled: false
contact_links:
  - name: Documentation
    url: https://opensource.simtabi.com/documentation/laranail/enumerator
    about: Read the docs first.
  - name: Security
    url: mailto:opensource@simtabi.com
    about: Do not file public issues for security vulnerabilities.
EOF

cat > .github/PULL_REQUEST_TEMPLATE.md <<'EOF'
## Summary

<!-- 1-3 bullets explaining what changed and why. -->

## Linked issue

<!-- Closes #N -->

## Test plan

- [ ] `composer validate --strict`
- [ ] `vendor/bin/pint --test`
- [ ] `vendor/bin/phpstan analyse`
- [ ] `vendor/bin/pest --coverage --min=90`
- [ ] Added/updated tests for new behaviour
- [ ] Updated `CHANGELOG.md` under `[Unreleased]`
EOF

# ----------------------------------------------------------------------------
# composer.json (rewrite)
# ----------------------------------------------------------------------------
cat > composer.json <<'EOF'
{
    "name": "laranail/enumerator",
    "description": "Type-safe enumerator toolkit for Laravel 13+ with attributes, state machines, bitmasks, translations, validation, casts, blade components, and Filament/Livewire/Nova/Inertia integrations.",
    "keywords": [
        "laravel",
        "laravel-13",
        "php-8.3",
        "enum",
        "enums",
        "enumerator",
        "state-machine",
        "bitmask",
        "filament",
        "livewire",
        "nova",
        "inertia",
        "typescript",
        "simtabi",
        "laranail"
    ],
    "homepage": "https://opensource.simtabi.com/laranail/enumerator",
    "license": "MIT",
    "type": "library",
    "authors": [
        {
            "name": "Simtabi LLC",
            "email": "opensource@simtabi.com",
            "homepage": "https://simtabi.com",
            "role": "Maintainer"
        },
        {
            "name": "Imani Manyara",
            "email": "imani@simtabi.com",
            "role": "Lead developer"
        }
    ],
    "support": {
        "email": "opensource@simtabi.com",
        "issues": "https://github.com/laranail/enumerator/issues",
        "source": "https://github.com/laranail/enumerator",
        "docs": "https://opensource.simtabi.com/documentation/laranail/enumerator"
    },
    "require": {
        "php": "^8.3",
        "illuminate/console": "^13.0",
        "illuminate/contracts": "^13.0",
        "illuminate/database": "^13.0",
        "illuminate/support": "^13.0",
        "illuminate/validation": "^13.0",
        "illuminate/view": "^13.0"
    },
    "require-dev": {
        "filament/filament": "^4.0",
        "inertiajs/inertia-laravel": "^2.0",
        "larastan/larastan": "^3.0",
        "laravel/nova": "^5.0",
        "laravel/pint": "^1.18",
        "livewire/livewire": "^3.5",
        "orchestra/testbench": "^10.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0",
        "phpstan/extension-installer": "^1.4",
        "phpstan/phpstan": "^2.0"
    },
    "suggest": {
        "filament/filament": "Use the Filament integration (columns, form fields, filters).",
        "inertiajs/inertia-laravel": "Use the Inertia transformer for typed SPA responses.",
        "laravel/nova": "Use the Nova integration (field, filter).",
        "livewire/livewire": "Use the Livewire enum-aware property casts and rule helpers."
    },
    "autoload": {
        "psr-4": {
            "Simtabi\\Laranail\\Enumerator\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Simtabi\\Laranail\\Enumerator\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Simtabi\\Laranail\\Enumerator\\EnumeratorServiceProvider"
            ]
        },
        "phpstan": {
            "includes": [
                "extension.neon"
            ]
        }
    },
    "scripts": {
        "test": "pest",
        "test:coverage": "pest --coverage --min=90",
        "analyse": "phpstan analyse",
        "format": "pint",
        "format:check": "pint --test"
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "phpstan/extension-installer": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
EOF

# ----------------------------------------------------------------------------
# phpunit.xml + phpstan.neon + extension.neon + pint.json
# ----------------------------------------------------------------------------
cat > phpunit.xml <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         processIsolation="false"
         stopOnFailure="false"
         executionOrder="random"
         failOnRisky="true"
         failOnWarning="true"
         beStrictAboutOutputDuringTests="true"
         cacheDirectory=".phpunit.cache"
         displayDetailsOnTestsThatTriggerDeprecations="true"
         displayDetailsOnTestsThatTriggerErrors="true"
         displayDetailsOnTestsThatTriggerNotices="true"
         displayDetailsOnTestsThatTriggerWarnings="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/PHPStan</directory>
            <directory>src/Integrations</directory>
        </exclude>
    </source>
    <coverage>
        <report>
            <text outputFile="php://stdout" showOnlySummary="true"/>
        </report>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="testing"/>
    </php>
</phpunit>
EOF

cat > phpstan.neon <<'EOF'
includes:
    - vendor/larastan/larastan/extension.neon
    - extension.neon

parameters:
    level: 8
    paths:
        - src
        - tests
    excludePaths:
        - tests/Application/database/migrations/*
    treatPhpDocTypesAsCertain: false
    checkMissingIterableValueType: false
EOF

cat > extension.neon <<'EOF'
services:
    -
        class: Simtabi\Laranail\Enumerator\PHPStan\EnumeratorMethodReflectionExtension
        tags:
            - phpstan.broker.methodsClassReflectionExtension
EOF

cat > pint.json <<'EOF'
{
    "preset": "laravel",
    "rules": {
        "concat_space": { "spacing": "one" },
        "method_argument_space": { "on_multiline": "ensure_fully_multiline" },
        "no_unused_imports": true,
        "ordered_imports": { "sort_algorithm": "alpha" },
        "single_quote": true,
        "trailing_comma_in_multiline": { "elements": ["arrays", "arguments", "parameters"] }
    },
    "exclude": [
        "resources/views"
    ]
}
EOF

# ----------------------------------------------------------------------------
# config/enumerator.php
# ----------------------------------------------------------------------------
cat > config/enumerator.php <<'EOF'
<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | CSS Framework
    |--------------------------------------------------------------------------
    | Drives the default Blade component view bundle.
    | One of: plain, tailwind, daisyui, bootstrap, bulma.
    | Override per-call with <x-laranail-enumerator::badge framework="bootstrap" />.
    */
    'css_framework' => env('ENUMERATOR_CSS', 'plain'),

    /*
    |--------------------------------------------------------------------------
    | View Namespace
    |--------------------------------------------------------------------------
    | Prefix for the Blade component namespace. Tag becomes
    | <x-{view_namespace}::badge />.
    */
    'view_namespace' => 'laranail-enumerator',

    /*
    |--------------------------------------------------------------------------
    | Translation Namespace
    |--------------------------------------------------------------------------
    | Default key prefix. Full key: {namespace}::enums.{enum_slug}.{case}.
    */
    'translation_namespace' => 'enumerator',

    /*
    |--------------------------------------------------------------------------
    | Reflection cache
    |--------------------------------------------------------------------------
    | driver: memory  (per-request, default in dev)
    |         file    (bootstrap/cache/enumerator.php)
    |         layered (memory over file — recommended for prod)
    */
    'cache' => [
        'driver' => env('ENUMERATOR_CACHE_DRIVER', 'layered'),
        'file_path' => null,
        'auto_warm' => false,
        'auto_warm_classes' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | State machine
    |--------------------------------------------------------------------------
    */
    'state_machine' => [
        'table_name' => 'enumerator_state_history',
        'record_history' => true,
        'enforce_initial_state' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Magic behaviour
    |--------------------------------------------------------------------------
    */
    'magic' => [
        'case_insensitive_method_names' => true,
        'allow_invokable_cases' => false,
        'ambiguous_resolution' => 'throw', // throw | first | null
    ],

    /*
    |--------------------------------------------------------------------------
    | Attribute overrides
    |--------------------------------------------------------------------------
    | Override compile-time attributes without forking a preset.
    |
    | Example:
    |   Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum::class => [
    |       'Critical' => [
    |           'color' => 'magenta',
    |           'meta'  => ['paging' => true],
    |       ],
    |   ],
    */
    'overrides' => [],

];
EOF

# ----------------------------------------------------------------------------
# lang/en/enumerator.php — package's own strings only
# ----------------------------------------------------------------------------
cat > lang/en/enumerator.php <<'EOF'
<?php

declare(strict_types=1);

return [
    'validation' => [
        'invalid_value' => 'The :attribute must be a valid :enum value.',
        'invalid_name' => 'The :attribute must be a valid :enum case name.',
        'not_allowed' => 'The :attribute value is not in the allowed set: :values.',
        'excluded' => 'The :attribute value :value is excluded.',
        'invalid_transition' => 'Cannot transition :from to :to for :enum.',
        'invalid_enum_class' => 'The configured enum class :class is not valid.',
    ],
    'components' => [
        'select' => [
            'placeholder' => 'Select an option…',
            'empty' => 'No options available.',
        ],
        'radio' => [
            'group_label' => 'Choose one of :count options.',
        ],
        'grid' => [
            'empty' => 'No options available.',
        ],
    ],
    'aria' => [
        'badge' => 'Status: :label',
        'select' => 'Select :name',
        'radio' => 'Choose :name',
        'grid' => ':label',
    ],
    'commands' => [
        'cache' => [
            'cached' => 'Cached enumerator reflection data.',
            'cleared' => 'Cleared enumerator reflection cache.',
        ],
    ],
];
EOF

# ----------------------------------------------------------------------------
# database/migrations
# ----------------------------------------------------------------------------
cat > database/migrations/2026_05_12_000000_create_enumerator_state_history_table.php <<'EOF'
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('enumerator.state_machine.table_name', 'enumerator_state_history');

        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->morphs('subject');
            $table->string('field');
            $table->string('from')->nullable();
            $table->string('to');
            $table->string('enum_class');
            $table->json('context')->nullable();
            $table->foreignId('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->timestamps();

            $table->index(['enum_class', 'to']);
            $table->index(['causer_type', 'causer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            (string) config('enumerator.state_machine.table_name', 'enumerator_state_history')
        );
    }
};
EOF

echo "==> Root + config files written"

# ----------------------------------------------------------------------------
# Attributes (9 files)
# ----------------------------------------------------------------------------
write_attr() {
    local name="$1"
    local args="$2"
    local props="$3"
    local target="${4:-Attribute::TARGET_CLASS_CONSTANT}"
    local extra="${5:-}"
    cat > "src/Attributes/${name}.php" <<EOF
<?php

declare(strict_types=1);

namespace Simtabi\\Laranail\\Enumerator\\Attributes;

use Attribute;

#[Attribute(${target}${extra})]
final readonly class ${name}
{
    public function __construct(
${args}
    ) {${props}}
}
EOF
}

write_attr "Bit" \
"        public int \$bit," \
""

write_attr "Color" \
"        public string \$color," \
""

cat > src/Attributes/CssClass.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
final readonly class CssClass
{
    public function __construct(
        public string $classes,
        public string $framework = 'plain',
    ) {}
}
EOF

cat > src/Attributes/Description.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_CLASS_CONSTANT)]
final readonly class Description
{
    public function __construct(public string $description) {}
}
EOF

write_attr "Help" \
"        public string \$help," \
""

write_attr "Icon" \
"        public string \$icon," \
""

write_attr "Label" \
"        public string \$label," \
""

cat > src/Attributes/Meta.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Attributes;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
final readonly class Meta
{
    /** @var array<string, mixed> */
    public array $values;

    public function __construct(mixed ...$values)
    {
        $clean = [];
        foreach ($values as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Meta keys must be strings.');
            }
            $clean[$key] = $value;
        }
        $this->values = $clean;
    }
}
EOF

write_attr "Order" \
"        public int \$order," \
""

echo "==> Attributes written"

# ----------------------------------------------------------------------------
# Contracts (6 files)
# ----------------------------------------------------------------------------
cat > src/Contracts/Enumerator.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

/**
 * Marker interface implemented by any enum (native or class-const) integrated
 * with this package.
 *
 * Native PHP enums implementing this contract automatically expose UnitEnum
 * / BackedEnum semantics in addition to the methods provided by
 * Concerns\HasEnumeratorBehavior.
 */
interface Enumerator
{
}
EOF

cat > src/Contracts/HtmlRenderable.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

use Illuminate\Support\HtmlString;

interface HtmlRenderable
{
    public function toHtml(): HtmlString;
}
EOF

cat > src/Contracts/Stateful.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

interface Stateful extends Enumerator
{
    /**
     * Map of from-value => list of allowed target cases.
     *
     * @return array<int|string, array<int, static>>
     */
    public static function transitions(): array;

    /**
     * Cases allowed as the initial state.
     *
     * @return array<int, static>
     */
    public static function initialStates(): array;
}
EOF

cat > src/Contracts/Translatable.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

interface Translatable
{
    /**
     * Translation namespace (e.g. "enumerator", "app", "auth").
     */
    public static function translationNamespace(): string;

    /**
     * Slug used in the translation key. Defaults to snake-case of the class
     * basename, trailing "Enum" stripped.
     */
    public static function translationSlug(): string;
}
EOF

cat > src/Contracts/Bitwise.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

interface Bitwise extends Enumerator
{
}
EOF

cat > src/Contracts/TransitionHook.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

interface TransitionHook
{
    /**
     * Called before a transition. Return false to abort.
     *
     * @param  object  $from  the source case (Enumerator or AbstractEnumeratorClass)
     * @param  object  $to    the target case
     */
    public function before(object $from, object $to): bool;

    /**
     * Called after a successful transition.
     */
    public function after(object $from, object $to): void;
}
EOF

echo "==> Contracts written"

# ----------------------------------------------------------------------------
# Exceptions (7 files)
# ----------------------------------------------------------------------------
write_exc() {
    local name="$1"
    local parent="${2:-EnumeratorException}"
    cat > "src/Exceptions/${name}.php" <<EOF
<?php

declare(strict_types=1);

namespace Simtabi\\Laranail\\Enumerator\\Exceptions;

class ${name} extends ${parent}
{
}
EOF
}

cat > src/Exceptions/EnumeratorException.php <<'EOF'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Exceptions;

use RuntimeException;

class EnumeratorException extends RuntimeException
{
}
EOF

write_exc "AmbiguousMagicCallException"
write_exc "InvalidBitmaskException"
write_exc "InvalidEnumeratorNameException"
write_exc "InvalidEnumeratorValueException"
write_exc "InvalidTransitionException"
write_exc "TranslationMissingException"

echo "==> Exceptions written"

# ----------------------------------------------------------------------------
# Stubs for make:enumerator
# ----------------------------------------------------------------------------
cat > resources/stubs/enumerator.backed.stub <<'EOF'
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum {{ class }}: string implements Enumerator
{
    use HasEnumeratorBehavior;

    case Active = 'active';
    case Inactive = 'inactive';
}
EOF

cat > resources/stubs/enumerator.pure.stub <<'EOF'
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum {{ class }} implements Enumerator
{
    use HasEnumeratorBehavior;

    case Active;
    case Inactive;
}
EOF

cat > resources/stubs/enumerator.attributes.stub <<'EOF'
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Simtabi\Laranail\Enumerator\Attributes\{Color, Description, Icon, Label, Meta, Order};
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

#[Description('{{ class }} cases')]
enum {{ class }}: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Active'), Color('success'), Icon('check-circle'), Order(10)]
    case Active = 'active';

    #[Label('Inactive'), Color('ghost'), Icon('pause-circle'), Order(20)]
    case Inactive = 'inactive';
}
EOF

cat > resources/stubs/enumerator.bitmask.stub <<'EOF'
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Concerns\{HasBitmask, HasEnumeratorBehavior};
use Simtabi\Laranail\Enumerator\Contracts\{Bitwise, Enumerator};

enum {{ class }}: int implements Enumerator, Bitwise
{
    use HasEnumeratorBehavior;
    use HasBitmask;

    #[Bit(1)] case Read = 1;
    #[Bit(2)] case Write = 2;
    #[Bit(4)] case Delete = 4;
    #[Bit(8)] case Admin = 8;
}
EOF

cat > resources/stubs/enumerator.state-machine.stub <<'EOF'
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasTransitions};
use Simtabi\Laranail\Enumerator\Contracts\{Enumerator, Stateful};

enum {{ class }}: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasTransitions;

    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value => [self::Active, self::Cancelled],
            self::Active->value => [self::Completed, self::Cancelled],
            self::Completed->value => [],
            self::Cancelled->value => [],
        ];
    }
}
EOF

echo "==> Stubs written"

echo
echo "Scaffold complete. Boilerplate files written. Logic files (concerns,"
echo "casts, rules, blade components, service provider, console commands,"
echo "integrations, tests, docs, presets, view bundles) are authored"
echo "individually after this script."
