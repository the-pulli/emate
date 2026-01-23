# Upgrade Guide

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
