# Cleanup candidates — laranail/enumerator

**Nothing on this list is deleted autonomously.** This file is the
proposal; the user must explicitly approve specific items before any
removal lands in Phase 7.

For each candidate: what it is, why it's a candidate, the proposed
disposition, and the recovery path if the call turns out wrong.

---

## Candidate 1 — `scripts/refactor-facades.py`

- **Type:** One-time Python migration script.
- **Last known purpose:** Mass-rewrite raw-function calls to Laravel
  facade calls (`array_map(...)` → `Arr::map(...)`, etc.) during
  initial scaffolding.
- **Status today:** Not referenced by CI, README, CONTRIBUTING, or
  any docs/.
- **Proposed disposition:** Move into
  `.design/scaffold-history/refactor-facades.py` (gitignored). Keep
  the file for the historical record — it's small and useful to know
  what convention was applied wholesale.
- **Recovery if wrong:** `git log -- scripts/refactor-facades.py` to
  retrieve.

## Candidates 2-7 — `scripts/scaffold*.sh` (six files)

- `scripts/scaffold.sh`
- `scripts/scaffold-docs.sh`
- `scripts/scaffold-framework-variants.sh`
- `scripts/scaffold-framework-views.sh`
- `scripts/scaffold-presets.sh`
- `scripts/scaffold-step-2.sh`

- **Type:** Bash one-time generators for the docs tree, the per-
  framework view bundles, and the 26 preset enums.
- **Status today:** Not referenced anywhere in the package, in CI, or
  in published docs.
- **Proposed disposition:** Same as candidate 1 — move into
  `.design/scaffold-history/` (gitignored).
- **Recovery if wrong:** `git log -- scripts/` to retrieve.

Note: `scripts/README.md` (PR-A10 in `completion-plan.md`) will get
written BEFORE these are moved. That doc captures the scaffolds'
intent in narrative form so the historical script archive is not
needed for understanding — only for replay.

## Candidate 8 — `composer.lock`

- **Type:** Locked dependency tree.
- **Status today:** Tracked in git despite `.gitignore` listing it.
- **Proposed disposition:** `git rm --cached composer.lock` so the
  ignore takes effect. Do NOT delete the file from the working tree.
- **Rationale:** Library projects don't ship `composer.lock` — consumers
  need flexibility on dep versions; CI tests against a fresh
  `composer install`. Already covered as **PR-A8** in the completion plan.
- **Recovery if wrong:** `git checkout HEAD~1 -- composer.lock`.

## Candidate 9 — `.DS_Store` files

- **Type:** macOS Finder metadata.
- **Status today:** `find . -name '.DS_Store' -not -path './vendor/*'`
  returns 4 hits (`.design/.DS_Store`, `.design/plans/.DS_Store`(?),
  `.github/.DS_Store`, root `.DS_Store`).
- **Proposed disposition:**
  `find . -name '.DS_Store' -not -path './vendor/*' -delete` (with
  approval), and confirm `.DS_Store` line is in `.gitignore` (it is —
  line 13).
- **Recovery if wrong:** They regenerate as soon as Finder visits the
  directory. Zero-cost deletion.

## Candidate 10 — `_ide_helper_enumerator.php` (any orphaned copy)

- **Type:** Generated IDE helper output.
- **Status today:** Listed in `.gitignore` line 16. Should not exist
  in tree today.
- **Proposed disposition:** Verify no copy exists at repo root or
  under `tests/Application/`. If found, delete. (Will be re-emitted
  on `php artisan enumerator:ide-helper` whenever needed.)
- **Recovery if wrong:** `php artisan enumerator:ide-helper` regenerates.

---

## Items deliberately NOT on this list

- **`scripts/README.md`** — being CREATED, not deleted.
- **`.design/plans/NEXT-SESSION.md`** — append-only handoff doc; the
  memory-pointer and global anti-hallucination protocol both rely on
  it. Will be moved into `.design/_private/` (PR-A2) for gitignore
  correctness, never deleted.
- **`.design/plans/audit-remediate.md`** — reusable v0.2.0 prompt
  template; the audit-remediate memory pointer references it. Moves
  with `NEXT-SESSION.md` into `.design/_private/`.
- **`phpstan-baseline.neon`** — 393 KB, kept verbatim. The slimming
  exercise (issue I-13) is a v0.3.0 task, not a deletion.
- **`scripts/scaffold-step-2.sh`** if it differs from the others —
  treat the same way as candidates 2-7 unless a `scripts/README.md`
  reveals it has ongoing duties.

## Approval process

When ready to execute Phase 7, the user signs off in one of two forms:

- "Approve all cleanup candidates" — every item above proceeds.
- "Approve candidates 1, 2, 8, 9" — only those proceed; others
  remain.

The Phase 7 commit message format will be exactly:
**`chore: remove deprecated files per cleanup-candidates.md`**
(per the audit-remediate brief), and will reference this file and
the validation report by relative path.

## Final disposition log

Each row records the candidate ID, the approval choice the user
selected, the disposition that landed, and the date. Commit SHA for
each row is the SHA of the chore commit referenced in the
"Disposition" column — discoverable via `git log -- .design/plans/cleanup-candidates.md`.

| Cand. | Approval | Disposition | Date |
|---|---|---|---|
| 1 — `scripts/refactor-facades.py` | "Move to .design/scaffold-history/" | Moved (gitignored). `git log --follow scripts/refactor-facades.py` for history. | 2026-05-15 |
| 2 — `scripts/scaffold.sh` | "Move to .design/scaffold-history/" | Moved (gitignored). | 2026-05-15 |
| 3 — `scripts/scaffold-docs.sh` | "Move to .design/scaffold-history/" | Moved (gitignored). | 2026-05-15 |
| 4 — `scripts/scaffold-framework-variants.sh` | "Move to .design/scaffold-history/" | Moved (gitignored). | 2026-05-15 |
| 5 — `scripts/scaffold-framework-views.sh` | "Move to .design/scaffold-history/" | Moved (gitignored). | 2026-05-15 |
| 6 — `scripts/scaffold-presets.sh` | "Move to .design/scaffold-history/" | Moved (gitignored). | 2026-05-15 |
| 7 — `scripts/scaffold-step-2.sh` | "Move to .design/scaffold-history/" | Moved (gitignored). | 2026-05-15 |
| 8 — `composer.lock` untrack | n/a — verified no-op | File was never tracked (gitignored at line 15). I-6 verified-false-finding. | 2026-05-15 |
| 9 — `.DS_Store` files (13 instances) | "Run the full sweep now" | Deleted from disk. Will regenerate when Finder visits dirs; gitignore line 13 keeps them untracked. | 2026-05-15 |
| 10 — `_ide_helper_enumerator.php` | n/a — verified absent | File not present on disk. No action needed. | 2026-05-15 |
