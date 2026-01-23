# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP wrapper for MailMate's `emate` CLI tool. Provides a fluent, object-oriented interface to compose and send emails via MailMate's command-line binary. Single-class library (`Pulli\Emate\Emate`) in `src/Emate.php`.

## Commands

```bash
composer test              # Run tests with Pest
composer test-coverage     # Run tests with coverage reports
composer format            # Format code with Laravel Pint

vendor/bin/pest --filter="test name"  # Run a single test
```

## Architecture

The library is a single final class `Pulli\Emate\Emate` using a builder/fluent pattern:

- **Factory method:** `Emate::from(array $options)` — private constructor forces use of this static factory
- **Output:** `mail()` executes the shell command; `debug()` returns it as a string without executing
- **Command structure:** Generates `echo 'body' | $HOME/bin/emate mailto [FLAGS] [FILES]`

Key internal flow:
1. `commandString()` orchestrates full command assembly
2. `preamble()` builds the echo pipe prefix
3. `normalFlags()` builds `--to`, `--cc`, `--bcc`, `--subject`, `--from`, `--replyto`
4. `filesFlag()` formats file paths with `escapeshellarg()`
5. `booleanFlags()` handles `--send-now`, `--encrypt`/`--noencrypt`, `--sign`/`--nosign`
6. `markdownMode()` and `encryptionMode()` add optional flags

Address handling supports: plain strings, `"Name" <email>` format, newline-separated strings, arrays, and Symfony `Address` objects. The `--from` flag extracts only the email address from Address objects.

## Testing

- Framework: Pest PHP (on top of PHPUnit)
- Tests in `tests/Unit/EmateTest.php` — uses `debug()` to assert generated command strings
- Architecture tests in `tests/ArchTest.php` — ensures no debug functions in source
- PHPUnit config: `phpunit.xml.dist` (random order, strict mode)

## Code Style

- Laravel Pint (auto-fixed in CI via GitHub Actions)
- `declare(strict_types=1)` in all PHP files
- 4-space indentation, UTF-8, LF line endings

## CI

- Tests run on PHP 8.3, 8.4, 8.5 with both `prefer-lowest` and `prefer-stable`
- Code style auto-committed on push via Pint action
