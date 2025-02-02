<?php

declare(strict_types=1);

use Pulli\Emate\Emate;
use Symfony\Component\Mime\Address;

function emate(array $options): string
{
    return Emate::from($options)->debug();
}

it('can send a mail with one recipient and simple body', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with one recipient and simple body and quoted name', function () {
    $options = [
        'to' => '"The PuLLi" <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"The PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with one recipient and markdown body', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello **bold**',
        'subject' => 'Test',
        'markdown' => true,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello **bold**' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign --markup 'markdown'");
});

it('can send a mail with one recipient as symfony address object with reply_to', function () {
    $options = [
        'to' => new Address('the@pulli.dev', 'PuLLi'),
        'from' => new Address('the@l33tdump.com', 'l33tdump.com'),
        'reply_to' => new Address('wtf@l33tdump.com', 'l33tdump.com'),
        'body' => 'Hello',
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --replyto '\"l33tdump.com\" <wtf@l33tdump.com>' --noencrypt --nosign");
});

it('raises an exception, if one pass an invalid argument for files', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'files' => 4,
    ];

    Emate::from($options);
})->throws(TypeError::class);

it('raises an exception, if one pass an invalid argument for encryption mode', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'encryption_mode' => 'invalid',
    ];

    Emate::from($options);
})->throws(InvalidArgumentException::class, 'Invalid encryption mode. Possible values are: openpgp, mime');

it('can send a multiline message to more than one recipient', function () {
    $options = [
        'to' => "PuLLi <the@pulli.dev>\nNotifications <notfications@pulli.dev>",
        'from' => 'the@l33tdump.com',
        'body' => "Hello\n\nWith two new lines",
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello\n\nWith two new lines' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --to '\"Notifications\" <notfications@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a multiline message to two recipients as array', function () {
    $options = [
        'to' => ['PuLLi <the@pulli.dev>', 'Notifications <notfications@pulli.dev>'],
        'from' => 'the@l33tdump.com',
        'body' => "Hello\n\nWith two new lines",
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello\n\nWith two new lines' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --to '\"Notifications\" <notfications@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a multiline message with single quotes to more than one recipient', function () {
    $options = [
        'to' => "PuLLi <the@pulli.dev>\nNotifications <notfications@pulli.dev>",
        'from' => 'the@l33tdump.com',
        'body' => "Hello\n\n'With two new lines'",
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello\n\n'\''With two new lines'\''' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --to '\"Notifications\" <notfications@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send an encrypted message to one recipient with default encryption mode (openpgp)', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'encrypt' => true,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --nosign --openpgp");
});

it('can send an encrypted message to one recipient with mime encryption', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'encrypt' => true,
        'encryption_mode' => 'mime',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --nosign --mime");
});

it('can send an encrypted message to one recipient with mime encryption as capitalized parameter', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'encrypt' => true,
        'encryption_mode' => 'MIME',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --nosign --mime");
});

it('can send a signed message to one recipient', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'sign' => true,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --sign --openpgp");
});

it('can send an encrypted and signed message to one recipient', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'encrypt' => true,
        'sign' => true,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --sign --openpgp");
});

it('can send a simple message with two files attached', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'files' => "/home/rainbow.txt\n/home/pride.txt",
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' '/home/rainbow.txt' '/home/pride.txt' --noencrypt --nosign");
});

it('can send a simple message with two files passed as array', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'files' => ['/home/rainbow.txt', '/home/pride.txt'],
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' '/home/rainbow.txt' '/home/pride.txt' --noencrypt --nosign");
});

it('can send a simple message with no files passed as an empty array', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'files' => [],
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a simple message with two files passed as associative array', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'files' => ['first' => '/home/rainbow.txt', 'second' => '/home/pride.txt'],
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' '/home/rainbow.txt' '/home/pride.txt' --noencrypt --nosign");
});
