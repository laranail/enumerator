# ADR-0002 — Reference codebases stay gitignored under `.design/references/`

- **Status:** Accepted
- **Date:** 2026-05-15
- **Deciders:** Imani Manyara

## Context

Phase 1 setup copied two reference codebases into the project so they
were stable for the audit:

- `.design/references/reference-a-botble/` — enum slices from a
  CodeCanyon-licensed CMS (Botble).
- `.design/references/reference-b-tusente/` — full enum module from
  another internal project (Tusente), MIT-licensed per its own
  `composer.json`.

Botble in particular is **redistribution-prohibited** under its
CodeCanyon licence; committing it to a public MIT repo would breach
that licence.

## Decision

`/.design/references/` is added to `.gitignore` (done in the same Phase
1 commit). Both reference subtrees remain local-only artifacts.

## Consequences

- New session resumes lose the references if Downloads is cleaned;
  re-copy from the source paths in `audit-remediate.md`'s SETUP block
  is the documented recovery path.
- Any insight from Botble must be **re-described in our own words** in
  `.design/research/landscape.md` or `feature-gap.md` — not
  cut-and-pasted from the reference.

## Alternatives considered

- **Commit the references** — rejected; licence violation for Botble,
  needless bloat for Tusente.
- **Skip both references** — rejected; Tusente's `COMPARISON.md` and
  `BOTBLE_PATTERN.md` are the most direct inputs we have for the gap
  analysis. Losing them would have weakened Phase 2.
