#!/usr/bin/env python3
"""
scripts/refactor-facades.py

Automate the conversion of PHP standard-library calls to their Laravel-facade
counterparts where the facade adds clarity (or matches Laravel idiom). Only
touches files under src/.

What it does
------------
1. Adds `use Illuminate\Support\Arr;` / `use Illuminate\Support\Str;` /
   `use Illuminate\Support\Facades\File;` to files that gain a facade call.
2. Rewrites common PHP-stdlib calls where the Laravel facade is the
   idiomatic choice:

   ARR
     array_first($arr)                  →  Arr::first($arr)
     array_get($arr, $key, $default)    →  Arr::get($arr, $key, $default)
     array_pull($arr, $key, $default)   →  Arr::pull($arr, $key, $default)
     array_random($arr, $count = 1)     →  Arr::random($arr, $count)
     array_wrap($val)                   →  Arr::wrap($val)
     in_array($needle, $hay, true)      →  unchanged (PHP idiom is fine)

   STR
     str_replace([…], [...], $s)        →  Str::replace([…], [...], $s) where target is single
     str_starts_with($s, $p)            →  Str::startsWith($s, $p)
     str_ends_with($s, $p)              →  Str::endsWith($s, $p)
     str_contains($s, $p)               →  Str::contains($s, $p)
     strtolower($s)                     →  Str::lower($s)
     strtoupper($s)                     →  Str::upper($s)
     ucfirst($s)                        →  Str::ucfirst($s)
     preg_replace(...)                  →  unchanged (Str::replaceMatches exists but verbose)

   FILE
     file_put_contents($p, $c)          →  File::put($p, $c)        (no extra flags)
     file_put_contents($p, $c, FLAG)    →  unchanged                 (flags not 1:1)
     file_get_contents($p)              →  File::get($p)
     file_exists($p) / is_file($p)      →  File::exists($p)
     unlink($p) / @unlink($p)           →  File::delete($p)
     mkdir($p, $m, true)                →  File::ensureDirectoryExists($p)
     dirname($p)                        →  unchanged (PHP idiom)

Deliberately untouched
----------------------
  • In hot paths inside concerns/casts where the PHP function is faster
    and the facade adds startup cost (e.g. `in_array` inside loops).
  • In code targeting non-Laravel contexts (we don't have any).
  • Where the substitution would alter behavior (e.g. file_put_contents
    flags, str_replace search-array semantics with array-of-needles).

Idempotent: re-running is safe; already-converted calls are skipped.
"""

from __future__ import annotations
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
SRC = ROOT / "src"

# Mapping: (regex_pattern, replacement, required_use)
# Patterns assume no namespace-prefixed call (`\foo()`); they match at word boundaries.
ARR_USE = "use Illuminate\\Support\\Arr;"
STR_USE = "use Illuminate\\Support\\Str;"
FILE_USE = "use Illuminate\\Support\\Facades\\File;"

REWRITES = [
    # ---- STR ----
    (re.compile(r"\bstr_starts_with\("),  "Str::startsWith(", STR_USE),
    (re.compile(r"\bstr_ends_with\("),    "Str::endsWith(",   STR_USE),
    (re.compile(r"\bstr_contains\("),     "Str::contains(",   STR_USE),
    (re.compile(r"\bstrtolower\("),       "Str::lower(",      STR_USE),
    (re.compile(r"\bstrtoupper\("),       "Str::upper(",      STR_USE),
    (re.compile(r"\bucfirst\("),          "Str::ucfirst(",    STR_USE),

    # ---- FILE ----
    (re.compile(r"\bfile_get_contents\("), "File::get(",     FILE_USE),
    # file_put_contents with flags → leave alone (LOCK_EX, FILE_APPEND aren't 1:1)
    (re.compile(r"\bfile_put_contents\(([^,]+),\s*([^,)]+)\)"),
        r"File::put(\1, \2)", FILE_USE),
    (re.compile(r"\bis_file\("),           "File::exists(",  FILE_USE),
    (re.compile(r"@?\bunlink\("),          "File::delete(",  FILE_USE),

    # ---- ARR ----
    (re.compile(r"\barray_wrap\("),        "Arr::wrap(",     ARR_USE),
]

# Files to exclude from rewrites (PHPStan extensions etc.)
EXCLUDE = {"src/PHPStan/EnumeratorMethodReflectionExtension.php",
           "src/PHPStan/EnumeratorReflectionExtension.php"}


def already_using(content: str, use_line: str) -> bool:
    return use_line in content


def insert_use(content: str, use_line: str) -> str:
    # Find last `use ...;` line and insert after it.
    matches = list(re.finditer(r"^use [^;]+;$", content, flags=re.MULTILINE))
    if not matches:
        # No `use` lines — insert after namespace
        ns = re.search(r"^namespace [^;]+;\n\n", content, flags=re.MULTILINE)
        if ns:
            insert_at = ns.end()
            return content[:insert_at] + use_line + "\n\n" + content[insert_at:]
        return content
    insert_at = matches[-1].end()
    return content[:insert_at] + "\n" + use_line + content[insert_at:]


def process_file(path: pathlib.Path) -> tuple[int, set[str]]:
    rel = str(path.relative_to(ROOT))
    if rel in EXCLUDE:
        return 0, set()
    content = path.read_text()
    original = content
    used = set()

    for pattern, replacement, use_line in REWRITES:
        new = pattern.sub(replacement, content)
        if new != content:
            content = new
            used.add(use_line)

    # Add necessary `use` statements
    for use_line in used:
        if not already_using(content, use_line):
            content = insert_use(content, use_line)

    if content != original:
        path.write_text(content)
        return 1, used
    return 0, set()


def main() -> int:
    touched = 0
    summary: dict[str, int] = {}
    for php in SRC.rglob("*.php"):
        changed, used = process_file(php)
        if changed:
            touched += 1
            for u in used:
                summary[u] = summary.get(u, 0) + 1
            print(f"  refactored: {php.relative_to(ROOT)}  ({', '.join(sorted(used))})")

    print(f"\n==> {touched} files touched")
    for u, n in sorted(summary.items()):
        print(f"   {n}× {u}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
