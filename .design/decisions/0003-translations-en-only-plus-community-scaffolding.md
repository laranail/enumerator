# ADR-0003 — v0.2.0 ships `en` only with community-translation scaffolding

- **Status:** Accepted
- **Date:** 2026-05-15
- **Deciders:** Imani Manyara

## Context

Reference codebase A (Botble) ships ~40 locales in its enum translation
tree. laranail/enumerator v0.1.0 ships only `lang/en/enumerator.php`.

Three options surfaced during Phase 1.1 pre-flight (translation scope):

1. `en` only + scaffolding for community contribution
2. `en` + top-5 (`es`, `fr`, `de`, `pt_BR`, `zh`)
3. Mirror Botble's ~40 locales

## Decision

**Option 1.** Ship `en` only. Document a "contribute a locale" recipe
in `docs/recipes/contributing-translations.md`. The translator surface
is already pluggable (`TranslatorAdapter`, D36); consumers who need
non-English labels today can swap in a `DatabaseTranslatorAdapter` or
hand-published `lang/vendor/enumerator/{locale}/enumerator.php`.

## Consequences

- v0.2.0 maintenance surface stays small.
- A "Contributing translations" recipe must be added (work item F-26
  in `feature-gap.md`-adjacent — to be tracked in the completion plan).
- Future locale additions arrive via community PRs; CHANGELOG entries
  for them belong under `Added` not `Changed`.

## Alternatives considered

- Option 2 (top-5): Multiplies maintenance surface 6×; risks stale
  translations as v0.2.0 evolves. Rejected.
- Option 3 (Botble mirror): Largest reach but biggest burden. The
  Botble translations are also CodeCanyon-licensed and cannot be
  copy-pasted; we would have to commission them. Rejected.
