# Wrapper for MailMate's emate CLI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pulli/emate.svg?style=flat-square)](https://packagist.org/packages/pulli/emate)
[![Tests](https://img.shields.io/github/actions/workflow/status/the-pulli/emate/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/the-pulli/emate/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/pulli/emate.svg?style=flat-square)](https://packagist.org/packages/pulli/emate)

## Installation

You can install the package via composer:

```bash
composer require pulli/emate
```

## Usage

```php
// First symlink emate, if not already done so
\Pulli\Emate\Emate::symlink();

// The values below are also the default values.
// No parameter in the array is actually required.
// Just set the one's you need.
$emate = \Pulli\Emate\Emate::from([
    'body' => '',
    'to' => [], // array of email addresses or Symfony\Component\Mime\Address objects or string separated by newline with email addresses
    'from' => '', // email address or Symfony\Component\Mime\Address object
    'cc' => [], // array of email addresses or Symfony\Component\Mime\Address objects or string separated by newline with email addresses
    'bcc' => [], // array of email addresses or Symfony\Component\Mime\Address objects or string separated by newline with email addresses
    'files' => [], // array of file paths or string separated by newline with file paths
    'reply_to' => '', // email address or Symfony\Component\Mime\Address object
    'markdown' => false, // boolean
    'encrypt' => false, // boolean
    'sign' => false, // boolean
    'send_now' => false, // boolean
    'encryption_mode' => 'openpgp', // string: mime or openpgp
])->mail();
```

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
