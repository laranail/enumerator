# Scripts

Historical one-time scaffolds used while bootstrapping the package.
**None of these run in CI**, and **none are part of the day-to-day
contributor workflow**. They're kept in tree as an audit trail of how
the initial v0.1.0 surface was generated.

If you want to know how a particular layer (docs, presets, framework
view bundles) was first laid down, the matching script below is the
authoritative answer.

## What each script did

| Script | Purpose | Last run (context) |
|---|---|---|
| `scaffold.sh` | Bootstrap the empty package skeleton: top-level files (`LICENSE`, `composer.json`, `phpstan.neon`, etc.), `src/` namespace tree, an empty `tests/` directory. | Pre-v0.1.0, during initial commit |
| `scaffold-step-2.sh` | Second-pass scaffolding for the `src/Concerns/` tree, the `src/Casts/` shells, and the initial Pest base. | Pre-v0.1.0 |
| `scaffold-docs.sh` | Generate the `docs/tools/*.md` and `docs/recipes/*.md` skeletons. Each generated file had a `# Title` line, an intro paragraph, and `## Sections` headings — manually filled in afterwards. | Pre-v0.1.0 |
| `scaffold-framework-views.sh` | Lay down the `resources/views/components/_base/*.blade.php` files (the framework-agnostic Blade source-of-truth). | Pre-v0.1.0 |
| `scaffold-framework-variants.sh` | Fork each `_base` view into the five framework-specific variants under `bootstrap/`, `bulma/`, `daisyui/`, `plain/`, `tailwind/`. Each variant kept the same shape, with framework-specific class names. | Pre-v0.1.0 |
| `scaffold-presets.sh` | Generate the 26 preset enums under `src/Presets/Enums/`. The script emitted skeletons with `#[Label]` / `#[Color]` placeholders; each preset's case set was hand-curated afterwards. | Pre-v0.1.0 |
| `refactor-facades.py` | Mass-rewrite raw function calls (`array_map`, `sprintf`-of-`trans`, etc.) to Laravel facade calls (`Arr::`, `Lang::`) where the facade reads more naturally. Conservative rewrite with manual review afterwards. | Pre-v0.1.0 |

## Why they're kept

A future maintainer who wants to add (say) a sixth Blade framework
variant has two options: hand-copy `_base/*` into the new directory, or
adapt `scaffold-framework-variants.sh` to do the bulk of it.

If at any point these become genuinely dead weight, see
`.design/plans/cleanup-candidates.md` for the staged-disposition plan.

## Not part of the public package surface

These scripts are NOT shipped to consumers. They exist only in the
repo. `composer install` does not copy `scripts/` into `vendor/`.
