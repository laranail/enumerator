# ADR-0001 — Frame this pass as a v0.2.0 audit, not a pre-release completion

- **Status:** Accepted
- **Date:** 2026-05-15
- **Deciders:** Imani Manyara (project owner)

## Context

The audit-remediate prompt was authored before v0.1.0 shipped and reads
like a pre-release completion pass. As of 2026-05-15, v0.1.0 is
publicly tagged at commit `8b772108`, `main` is 14 commits past the tag
at `e6fb99d`, 751 tests pass, coverage is 83.2% against an 80% CI gate,
and all three CI workflows are green.

If we treated this as a pre-release rewrite we would risk breaking
shipped public API for the small number of consumers who pulled
v0.1.0 between 2026-05-12 and today.

## Decision

This audit is treated as a **v0.2.0 audit + plan**. Backwards
compatibility within `^0.x` is a hard constraint: shipped public API
visible in `composer.json::autoload.psr-4` may not be removed or
renamed. Breaking changes go through a deprecation cycle and land in
v1.0.0.

## Consequences

- Phase 3's completion plan tags every work item as either preserving
  v0.1.0's surface or adding a new surface. **Net-removals are out of
  scope.**
- Cleanup candidates (Phase 7) target only files that have no public
  consumer (scripts/, design artifacts, broken docs).
- The PHPStan baseline at 393 KB stays — halving it (issue I-13) is
  deferred to v0.3.0.

## Alternatives considered

- **Pre-release rewrite** — would have required unpublishing v0.1.0 or
  jumping straight to v0.2.0 with breakage. Rejected: users pull
  packages quickly after publication.
- **Audit-only, no plan** — would have left v0.2.0 scoping for a
  future session. Rejected: pre-flight question 1 confirmed the user
  wanted a planning artifact, not just a stock-take.
