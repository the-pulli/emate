# Changelog

All notable changes to `emate` will be documented in this file.

## v2.1.1 - 2026-03-09

### Fix non-ASCII character stripping in shell arguments

- Add UTF-8 safe `escapeshellarg()` wrapper to preserve umlauts and other non-ASCII characters (e.g. `ö`, `ü`, `ä`) in recipient names, subjects, body text, signatures, headers, and file paths
- PHP's `escapeshellarg()` strips non-ASCII chars when `LC_CTYPE` is `"C"` or `"POSIX"` (default on macOS) — the new `escapeArg()` method temporarily sets the locale to `en_US.UTF-8` before escaping

## v2.1.0 - 2026-01-24

### Breaking Changes

- **Removed `illuminate/collections` dependency** — all internal usage replaced with plain PHP array functions. If you relied on this as a transitive dependency, add it to your own `composer.json`.
- **`mail()` now throws `RuntimeException` on failure** — previously exit codes were silently discarded.
- **`EncryptionMode::MIME` renamed to `EncryptionMode::SMIME`** — the CLI flag is `--smime`, not `--mime`. String `'mime'` is still accepted as an alias.
- **`symlink()` default path fixed** — now correctly resolves `$HOME` via `getenv('HOME')` instead of using an unexpandable literal string.

### New Features

- **Fluent Builder API** — new `Emate::compose()` static factory with chainable setters:
  ```php
  Emate::compose()
      ->to('recipient@example.com')
      ->sender('sender@example.com')
      ->subject('Hello')
      ->body('Email body')
      ->markdown()
      ->encrypt()
      ->sign()
      ->sendNow()
      ->encryptionMode(EncryptionMode::SMIME)
      ->signature('Best regards')
      ->header('X-Priority', '1')
      ->mail();
  
  ```
- **Signature support** — set text or reference existing signature by UUID via `'signature'` option or `->signature()` fluent setter.
- **Custom headers** — add arbitrary email headers via `'headers'` option or `->header('Name', 'Value')` fluent setter.
- **`markdown` option now accepts truthy strings** — recognizes `true`, `'yes'`, and `'true'` (same as `encrypt`, `sign`, `send_now`).

### Fixes

- **S/MIME encryption mode** — now generates correct `--smime` flag instead of invalid `--mime`.
- **Changelog workflow** — added concurrency group and `git pull --rebase` to prevent failures from simultaneous release events.

### Internal

- Simplified all command-building methods to return strings directly instead of keyed arrays.
- Replaced `Collection`-based data flow with `array_map`/`implode`/`explode`.

See [UPGRADE.md](UPGRADE.md) for migration details.

## v1.1.0 - 2026-01-23

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/the-pulli/emate/pull/3
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/the-pulli/emate/pull/4
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/the-pulli/emate/pull/5
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/the-pulli/emate/pull/6
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/the-pulli/emate/pull/7
* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/the-pulli/emate/pull/8

**Full Changelog**: https://github.com/the-pulli/emate/compare/v1.0.6...v1.1.0

## v1.0.6 - 2026-01-23

**Full Changelog**: https://github.com/the-pulli/emate/compare/v1.0.5...v1.0.6

## v1.0.5 - 2026-01-23

**Full Changelog**: https://github.com/the-pulli/emate/compare/v1.0.4...v1.0.5

## v1.0.3 - 2026-01-23

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot[bot] in https://github.com/the-pulli/emate/pull/2

**Full Changelog**: https://github.com/the-pulli/emate/compare/v1.0.2...v1.0.3

## v1.0.4 - 2026-01-23

**Full Changelog**: https://github.com/the-pulli/emate/compare/v1.0.3...v1.0.4

## v1.0.0 - 2026-01-23

**Full Changelog**: https://github.com/the-pulli/emate/commits/v1.0.0

## 1.0

* Initial release
