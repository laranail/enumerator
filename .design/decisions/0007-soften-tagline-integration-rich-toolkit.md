# ADR-0007 — Tagline softened to "integration-rich Laravel enum toolkit"

- **Status:** Accepted
- **Date:** 2026-05-15
- **Deciders:** Imani Manyara

## Context

The audit-remediate prompt opens with "the Godfather Laravel Enum
Package." Phase 2 research found that the breadth claim is real (every
feature column in the surveyed landscape is covered) but the depth in
each integration axis (Filament, Nova, Livewire, Inertia) is not
exercised by CI today. Calling a package the "godfather" makes a
breadth-AND-depth claim; we can defend breadth alone.

Honesty is a load-bearing principle of this codebase (see
`.design/principles.md` §12, and the global CLAUDE.md "Anti-
hallucination" rules).

## Decision

The public tagline (README hero, `composer.json::description`, OSS
portal landing copy) becomes:

> **"The integration-rich Laravel enum toolkit."**

Followed by a short feature list and a link to `docs/comparison.md`
(a new v0.2.0 deliverable, see feature-gap F-27, to be tracked in the
completion plan) which honestly lays out which packages laranail/
enumerator displaces on which axis and which it does NOT.

Internal docs and marketing may still use "godfather" colloquially —
but it does not appear in any file that ships in `composer require`.

## Consequences

- README hero changes (line 3 of `README.md`).
- `composer.json::description` changes.
- A new `docs/comparison.md` ships with the landscape matrix from
  `.design/research/landscape.md`, reframed for a public audience.
- The CHANGELOG gets a `Changed` entry under `[Unreleased]`:
  "Softened tagline from 'godfather' to 'integration-rich Laravel
  enum toolkit' to match the v0.1.0 depth profile."

## Alternatives considered

- **Keep "godfather"**: Sets an expectation we cannot honestly meet on
  every integration axis today. Rejected.
- **No tagline change yet; ship `docs/comparison.md` as the qualifier**:
  Half-measure. Anyone who reads only the README hero would still get
  the overstatement. Rejected.
