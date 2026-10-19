<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Architecture;

use SplFileInfo;
use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;

/**
 * Keeps literal credentials out of the tests, so secret scanning has nothing to report.
 *
 * A secret scanner cannot tell a test password from a leaked one and reports both: a literal of
 * any strength under a name such as `password`, `secret`, `token` or `api_key`, a strong-looking
 * literal passed to `new Password(...)`, and the password in a URL. Tests generate such values at
 * run time (`bin2hex(random_bytes(16))`) instead. A second check keeps every purpose passed to a
 * `TestCredentials` generator a single lowercase word, because a scanner reads a compound purpose
 * as the password itself.
 *
 * A minimal local copy of laranail/package-tools' `src/Testing/CredentialLiterals.php` and
 * `src/Testing/AssertsNoLiteralCredentials.php`, at v0.1.10, rules and false-positive exclusions
 * unchanged. It is a copy because this package supports PHP ^8.3, and its CI tests 8.3, while
 * package-tools requires ^8.4.1: depending on it, even in require-dev, would break the 8.3 leg.
 * Keep it in step with those files by hand.
 *
 * Mark a deliberate fixed value on its line, or the line above, with
 * `// @credential-literal-exempt: <reason>`.
 */
final class NoLiteralCredentialsTest extends TestCase
{
    private const string EXEMPTION = '@credential-literal-exempt';

    /**
     * A floor, so a path that stops matching fails instead of reporting a clean suite.
     */
    private const int MIN_FILES = 120;

    public function test_the_tests_hold_no_literal_credentials(): void
    {
        [$scanned, $offenders] = self::scan(self::dirs());

        self::assertGreaterThanOrEqual(self::MIN_FILES, $scanned, sprintf(
            'Only %d PHP file(s) were scanned, fewer than the floor of %d. Check the directories scanned.',
            $scanned,
            self::MIN_FILES,
        ));
        self::assertSame([], $offenders, sprintf(
            "These tests hold a literal credential, which secret scanning reports as a leak. Generate it at run time, or mark a deliberate fixed value with %s and a reason:\n  %s",
            self::EXEMPTION,
            implode("\n  ", $offenders),
        ));
    }

    public function test_every_credential_purpose_is_a_single_word(): void
    {
        [$scanned, $offenders] = self::scanPurposes(self::dirs());

        self::assertGreaterThanOrEqual(self::MIN_FILES, $scanned, sprintf(
            'Only %d PHP file(s) were scanned, fewer than the floor of %d. Check the directories scanned.',
            $scanned,
            self::MIN_FILES,
        ));
        self::assertSame([], $offenders, sprintf(
            "These TestCredentials purposes are not single lowercase words, and secret scanning reads a compound one as the password itself. Use one plain word ('user', 'wrong', 'renewed'):\n  %s",
            implode("\n  ", $offenders),
        ));
    }

    public function test_the_scan_recognises_what_it_guards(): void
    {
        $name = 'api' . '_key';
        $value = 'abcd' . '1234';
        $call = 'TestCredentials::' . 'password(';

        self::assertCount(1, self::literalsIn("<?php ['{$name}' => '{$value}'];"));
        self::assertCount(1, self::literalsIn(sprintf('<?php $x = "https://u:%s@example.test";', $value)));
        self::assertSame([], self::literalsIn("<?php ['{$name}' => \$generated];"));
        self::assertSame([], self::literalsIn("<?php ['{$name}' => 'required'];"));
        self::assertSame([], self::literalsIn("<?php ['{$name}_type' => '{$value}'];"));

        self::assertCount(1, self::purposesIn("<?php {$call}'smtp-new');"));
        self::assertSame([], self::purposesIn("<?php {$call}'user');"));
    }

    /**
     * @return list<string>
     */
    private static function dirs(): array
    {
        return [dirname(__DIR__)];
    }

    /**
     * Every literal credential in $source, as [byte offset, rule, literal].
     *
     * @return list<array{int, string, string}>
     */
    private static function literalsIn(string $source): array
    {
        $name = '(?<name>[\w.-]*(?:password|passwd|secret|token|api_?key|private_?key)[\w.-]*)';
        $literal = '(?:\'(?<single>(?:[^\'\\\\]|\\\\.)*)\'|"(?<double>(?:[^"\\\\]|\\\\.)*)")';

        $named = [
            '/[\'"]' . $name . '[\'"]\s*=>\s*' . $literal . '/i',
            '/\$(?:this->)?' . $name . '\s*=\s*' . $literal . '/i',
            '/\bconst\s+(?:\??\w+\s+)?' . $name . '\s*=\s*' . $literal . '/i',
            '/\bdefine\(\s*[\'"]' . $name . '[\'"]\s*,\s*' . $literal . '/i',
            '/[(,]\s*' . $name . '\s*:\s*' . $literal . '/i',
            '/(?:->|::)(?:set|assertSet)\(\s*[\'"]' . $name . '[\'"]\s*,\s*' . $literal . '/i',
        ];

        $found = [];

        foreach ($named as $pattern) {
            foreach (self::literalMatches($pattern, $source) as [$offset, $value, $key]) {
                if (self::isCredentialShaped($value) && self::namesACredential((string) $key)) {
                    $found[] = [$offset, 'named', $value];
                }
            }
        }

        foreach (self::literalMatches('/\bnew\s+(?:\\\\?[\w\\\\]*\\\\)?Password\(\s*' . $literal . '/', $source) as [$offset, $value]) {
            if (self::looksLikeAStrongPassword($value)) {
                $found[] = [$offset, 'strong', $value];
            }
        }

        preg_match_all('#\b[a-z][a-z0-9+.-]*://[^\s:/@\'"]+:([^\s@/\'"$%{]+)@#i', $source, $urls, PREG_OFFSET_CAPTURE);

        foreach ($urls[1] as [$value, $offset]) {
            $found[] = [$offset, 'url', $value];
        }

        usort($found, static fn (array $a, array $b): int => $a[0] <=> $b[0]);

        return $found;
    }

    /**
     * Every purpose passed to a TestCredentials generator that is not a single plain lowercase
     * word, as [byte offset, purpose].
     *
     * @return list<array{int, string}>
     */
    private static function purposesIn(string $source): array
    {
        preg_match_all('/\bTestCredentials::(?:password|secret|key)\(\s*(?:purpose:\s*)?([\'"])(.*?)\1/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $found = [];

        foreach ($matches as $match) {
            [$purpose, $offset] = $match[2];

            if (preg_match('/^[a-z]+$/', $purpose) !== 1) {
                $found[] = [$offset, $purpose];
            }
        }

        return $found;
    }

    /**
     * @param list<string> $dirs
     *
     * @return array{int, list<string>}
     */
    private static function scan(array $dirs): array
    {
        $scanned = 0;
        $offenders = [];

        foreach (self::phpFiles($dirs) as $path) {
            $scanned++;
            $source = (string) file_get_contents($path);
            $lines = explode("\n", $source);

            foreach (self::literalsIn($source) as [$offset, $rule, $value]) {
                $line = substr_count($source, "\n", 0, $offset) + 1;

                if (str_contains($lines[$line - 1] ?? '', self::EXEMPTION) || str_contains($lines[$line - 2] ?? '', self::EXEMPTION)) {
                    continue;
                }

                // Never the value itself: the message lands in CI logs.
                $offenders[] = sprintf('%s:%d (%s) %s…', $path, $line, $rule, substr($value, 0, 2));
            }
        }

        return [$scanned, $offenders];
    }

    /**
     * @param list<string> $dirs
     *
     * @return array{int, list<string>}
     */
    private static function scanPurposes(array $dirs): array
    {
        $scanned = 0;
        $offenders = [];

        foreach (self::phpFiles($dirs) as $path) {
            $scanned++;
            $source = (string) file_get_contents($path);

            foreach (self::purposesIn($source) as [$offset, $purpose]) {
                $offenders[] = sprintf("%s:%d '%s'", $path, substr_count($source, "\n", 0, $offset) + 1, $purpose);
            }
        }

        return [$scanned, $offenders];
    }

    /**
     * @param list<string> $dirs
     *
     * @return list<string>
     */
    private static function phpFiles(array $dirs): array
    {
        $paths = [];

        foreach ($dirs as $dir) {
            self::assertDirectoryExists($dir);

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                    $paths[] = $file->getPathname();
                }
            }
        }

        return $paths;
    }

    /**
     * Twelve or more characters mixing upper case, lower case, a digit and a symbol, and not a
     * path, namespace, URL or identifier.
     */
    private static function looksLikeAStrongPassword(string $value): bool
    {
        return strlen($value) >= 12
            && preg_match('/[A-Z]/', $value) === 1
            && preg_match('/[a-z]/', $value) === 1
            && preg_match('/\d/', $value) === 1
            && preg_match('/[^A-Za-z0-9\s]/', $value) === 1
            && preg_match('/\s/', $value) === 0
            && preg_match('#^[\\\\/]|\\\\|://|^[\w.-]+$#', $value) === 0;
    }

    /**
     * @return list<array{int, string, ?string}> [offset, value, name]
     */
    private static function literalMatches(string $pattern, string $source): array
    {
        preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);

        $found = [];

        foreach ($matches as $match) {
            $double = $match['double'][0] !== null;
            [$value, $offset] = $double ? $match['double'] : $match['single'];

            if ($double && preg_match('/(?<!\\\\)\$/', (string) $value) === 1) {
                continue; // interpolated: the value comes from code
            }

            $found[] = [(int) $offset, (string) $value, $match['name'][0] ?? null];
        }

        return $found;
    }

    /**
     * Not a credential: short placeholders, prose, validation rules, masks, a package name or
     * path, a dotted route or translation name, an Eloquent cast, and a single validation rule.
     */
    private static function isCredentialShaped(string $value): bool
    {
        return strlen($value) >= 4
            && preg_match('/[A-Za-z0-9]/', $value) === 1
            && preg_match('/\s/', $value) === 0
            && ! str_contains($value, '|')
            && preg_match('#^[\w.-]+/[\w./-]+$#', $value) === 0
            && preg_match('/^[a-z][a-z0-9_-]*(?:\.[a-z][a-z0-9_-]*)+$/', $value) === 0
            && preg_match('/^(?:hashed|encrypted(?::[\w\\\\:]+)?)$/', $value) === 0
            && preg_match('/^(?:required|nullable|sometimes|confirmed|string|present|filled|accepted|current_password)$|^(?:min|max|size|between|digits|same|different|regex|in|not_in|exists|unique):/', $value) === 0;
    }

    /**
     * A dotted key is judged by its last segment, so `laranail.password-history.on_store_error`
     * is not a credential while `services.stripe.secret` is.
     */
    private static function namesACredential(string $name): bool
    {
        $segments = explode('.', $name);
        $last = end($segments);

        return preg_match('/password|passwd|secret|token|api_?key|private_?key/i', $last) === 1
            && ! self::namesMetadata($last);
    }

    /**
     * A name that holds something about a credential rather than the credential: `token_type`,
     * `password_field`, `api_key_header`, `secret_ref`, or Laravel's `passwords` broker name.
     */
    private static function namesMetadata(string $name): bool
    {
        return strcasecmp($name, 'passwords') === 0
            || preg_match('/[_.-](?:type|name|field|column|header|driver|table|broker|length|ttl|lifetime|expires?|url|uri|route|prefix|label|format|algo|algorithm|cost|rounds|rules?|message|mode|path|endpoint|placeholder|ref|reference|provider|id)$/i', $name) === 1;
    }
}
