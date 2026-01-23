# Wrapper for MailMate's emate CLI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pulli/emate.svg?style=flat-square)](https://packagist.org/packages/pulli/emate)
[![Tests](https://img.shields.io/github/actions/workflow/status/the-pulli/emate/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/the-pulli/emate/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/pulli/emate.svg?style=flat-square)](https://packagist.org/packages/pulli/emate)

A PHP wrapper for [MailMate's](https://freron.com) `emate` CLI tool. Provides a fluent, object-oriented interface to compose and send emails via MailMate's command-line binary.

## Features

- Fluent builder pattern via static factory method
- Support for TO, CC, BCC, and Reply-To recipients
- File attachments
- Markdown body formatting
- OpenPGP and S/MIME encryption and signing
- Send-now mode for immediate delivery
- Flexible address formats: plain strings, `"Name" <email>` format, arrays, and Symfony `Address` objects
- Shell-safe command generation with proper argument escaping

## Installation

```bash
composer require pulli/emate
```

## Symlink Setup

MailMate ships the `emate` binary inside its application bundle. Create a symlink to make it available in your `$HOME/bin`:

```php
use Pulli\Emate\Emate;

// Creates symlink at $HOME/bin/emate (default)
Emate::symlink();

// Or specify a custom directory
Emate::symlink('/usr/local/bin');
```

## Usage

### Basic Email

```php
use Pulli\Emate\Emate;

Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Hello',
    'body' => 'This is the email body.',
])->mail();
```

### With Named Recipients

```php
Emate::from([
    'to' => 'John Doe <john@example.com>',
    'from' => 'sender@example.com',
    'subject' => 'Hello John',
    'body' => 'Hi there!',
])->mail();
```

### Multiple Recipients

```php
// As an array
Emate::from([
    'to' => ['alice@example.com', 'Bob <bob@example.com>'],
    'from' => 'sender@example.com',
    'subject' => 'Group message',
    'body' => 'Hello everyone!',
])->mail();

// As a newline-separated string
Emate::from([
    'to' => "alice@example.com\nBob <bob@example.com>",
    'from' => 'sender@example.com',
    'subject' => 'Group message',
    'body' => 'Hello everyone!',
])->mail();
```

### CC and BCC

```php
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'cc' => ['manager@example.com', 'team@example.com'],
    'bcc' => 'archive@example.com',
    'subject' => 'Update',
    'body' => 'Please see the update.',
])->mail();
```

### Reply-To

```php
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'reply_to' => 'replies@example.com',
    'subject' => 'Hello',
    'body' => 'Please reply to the other address.',
])->mail();
```

### Symfony Address Objects

```php
use Symfony\Component\Mime\Address;

Emate::from([
    'to' => new Address('recipient@example.com', 'Recipient'),
    'from' => new Address('sender@example.com', 'Sender'),
    'reply_to' => new Address('replies@example.com', 'Reply Handler'),
    'subject' => 'Hello',
    'body' => 'Using Address objects.',
])->mail();
```

### File Attachments

```php
// As an array
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Files attached',
    'body' => 'See attached.',
    'files' => ['/path/to/report.pdf', '/path/to/data.csv'],
])->mail();

// As a newline-separated string
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'File attached',
    'body' => 'See attached.',
    'files' => '/path/to/report.pdf',
])->mail();
```

### Markdown Body

```php
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Formatted email',
    'body' => "# Heading\n\nThis is **bold** and this is *italic*.",
    'markdown' => true,
])->mail();
```

### Encryption and Signing

```php
use Pulli\Emate\EncryptionMode;

// Encrypt with OpenPGP (default)
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Secret',
    'body' => 'Encrypted content.',
    'encrypt' => true,
])->mail();

// Sign with S/MIME
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Signed',
    'body' => 'Verified content.',
    'sign' => true,
    'encryption_mode' => 'mime',
])->mail();

// Encrypt and sign, using the enum directly
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Secure',
    'body' => 'Encrypted and signed.',
    'encrypt' => true,
    'sign' => true,
    'encryption_mode' => EncryptionMode::MIME,
])->mail();
```

### Send Immediately

```php
Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Urgent',
    'body' => 'This sends immediately without queuing in drafts.',
    'send_now' => true,
])->mail();
```

### Debugging

Use `debug()` instead of `mail()` to inspect the generated shell command without executing it:

```php
$command = Emate::from([
    'to' => 'recipient@example.com',
    'from' => 'sender@example.com',
    'subject' => 'Test',
    'body' => 'Hello',
])->debug();

echo $command;
// echo 'Hello' | $HOME/bin/emate mailto --to 'recipient@example.com' --subject 'Test' --from 'sender@example.com' --noencrypt --nosign
```

## Options Reference

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `body` | `string` | `''` | Email body text |
| `to` | `string\|array\|Address` | `[]` | Recipient(s) |
| `from` | `string\|Address` | `''` | Sender address |
| `subject` | `string` | `''` | Email subject |
| `cc` | `string\|array\|Address` | `[]` | CC recipient(s) |
| `bcc` | `string\|array\|Address` | `[]` | BCC recipient(s) |
| `reply_to` | `string\|Address` | `''` | Reply-to address |
| `files` | `string\|array` | `[]` | File path(s) to attach |
| `markdown` | `bool` | `false` | Render body as Markdown |
| `encrypt` | `bool\|string` | `false` | Encrypt the message (`true`, `'yes'`, `'true'`) |
| `sign` | `bool\|string` | `false` | Sign the message (`true`, `'yes'`, `'true'`) |
| `send_now` | `bool\|string` | `false` | Send immediately (`true`, `'yes'`, `'true'`) |
| `encryption_mode` | `string\|EncryptionMode` | `'openpgp'` | Encryption mode: `'openpgp'` or `'mime'` |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [PuLLi](https://github.com/the-pulli)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
