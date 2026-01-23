# Upgrade Guide

## From v2.0 to v2.1

### Breaking Changes

#### 1. `illuminate/collections` removed

The `illuminate/collections` package has been removed as a dependency. All internal usage has been replaced with plain PHP array functions. This has no impact on the public API, but if your project relied on `illuminate/collections` as a transitive dependency from this package, you will need to require it directly in your own `composer.json`.

#### 2. `mail()` now throws `RuntimeException` on failure

Previously, `mail()` used `system()` and silently discarded the exit code. It now uses `passthru()` and throws a `RuntimeException` if the `emate` command exits with a non-zero status.

```php
// v2.0 — failure was silent
Emate::from([...])->mail();

// v2.1 — throws RuntimeException on failure
try {
    Emate::from([...])->mail();
} catch (\RuntimeException $e) {
    // handle failure
}
```

#### 3. `symlink()` default path now correctly resolves `$HOME`

Previously, the default symlink destination was the literal string `'$HOME/bin'`, which PHP cannot expand. It now uses `getenv('HOME').'/bin'` to correctly resolve the user's home directory at runtime.

### New Features

#### Fluent Builder API

A new `Emate::compose()` static factory method provides a fluent builder API as an alternative to passing an options array:

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
    ->encryptionMode(EncryptionMode::MIME)
    ->mail();
```

Use `sender()` for the from-address (to avoid conflict with the `Emate::from()` static factory method).

#### `markdown` option now accepts truthy strings

The `markdown` option now accepts `bool|string` and recognizes the same truthy values as other flags (`true`, `'yes'`, `'true'`).

## From v1.x to v2.0

### Breaking Changes

#### 1. Shell escaping of email body (Security Fix)

The email body is now properly escaped using `escapeshellarg()` before being passed to the shell command. Previously, the body was interpolated directly into single quotes, which allowed shell injection if the body contained single quotes or other special characters.

**Impact:** The output of `debug()` will differ for bodies containing single quotes.

```php
// v1.x (broken shell syntax)
Emate::from(['body' => "Hello 'world'", ...])->debug();
// echo 'Hello 'world'' | $HOME/bin/emate mailto ...

// v2.0 (properly escaped)
Emate::from(['body' => "Hello 'world'", ...])->debug();
// echo 'Hello'\''world'\''' | $HOME/bin/emate mailto ...
```

If you are comparing `debug()` output in your own tests, update the expected strings accordingly. The actual mail-sending behavior via `mail()` is now correct for all body content.

#### 2. `subject` option no longer accepts Address objects

The `subject` option is now strictly typed as `string`. Passing a `Symfony\Component\Mime\Address` object as the subject will throw a `TypeError`.

```php
// v1.x (worked but was semantically wrong)
Emate::from(['subject' => new Address('foo@example.com', 'Name'), ...]);

// v2.0 (throws TypeError)
Emate::from(['subject' => 'Name', ...]);
```

### New Features

#### EncryptionMode Enum

You can now pass the `EncryptionMode` enum directly instead of a string:

```php
use Pulli\Emate\EncryptionMode;

Emate::from([
    'encryption_mode' => EncryptionMode::MIME,    // instead of 'mime'
    'encryption_mode' => EncryptionMode::OpenPGP, // instead of 'openpgp'
    ...
]);
```

String values (`'openpgp'`, `'mime'`) continue to work as before.
