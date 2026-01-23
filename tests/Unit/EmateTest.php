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

it('can send a mail with a recipient with spaces and dot and simple body', function () {
    $options = [
        'to' => 'Dr. PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"Dr. PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
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

it('can send a mail with umlaute in body', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello äüö',
        'subject' => 'Test',
        'markdown' => true,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello äüö' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign --markup 'markdown'");
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
        ->toBe("echo 'Hello\n\n'\\''With two new lines'\\''' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --to '\"Notifications\" <notfications@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
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

it('can send a mail with cc recipients as string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'cc' => 'cc@example.com',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --cc 'cc@example.com' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with cc recipients as array', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'cc' => ['cc1@example.com', 'cc2@example.com'],
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --cc 'cc1@example.com' --cc 'cc2@example.com' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with cc recipients as Address objects', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'cc' => [new Address('cc@example.com', 'CC User')],
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --cc '\"CC User\" <cc@example.com>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with bcc recipients as string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'bcc' => 'bcc@example.com',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --bcc 'bcc@example.com' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with bcc recipients as array', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'bcc' => ['bcc1@example.com', 'bcc2@example.com'],
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --bcc 'bcc1@example.com' --bcc 'bcc2@example.com' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with bcc recipients as Address objects', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'bcc' => [new Address('bcc@example.com', 'BCC User')],
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --bcc '\"BCC User\" <bcc@example.com>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with send_now flag as true', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'send_now' => true,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --send-now --noencrypt --nosign");
});

it('can send a mail with send_now flag as yes string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'send_now' => 'yes',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --send-now --noencrypt --nosign");
});

it('can send a mail with send_now flag as true string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'send_now' => 'true',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --send-now --noencrypt --nosign");
});

it('does not send now when send_now is false', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'send_now' => false,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can send a mail with reply_to as plain string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'reply_to' => 'reply@example.com',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --replyto 'reply@example.com' --noencrypt --nosign");
});

it('can send a signed message with mime encryption mode', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'sign' => true,
        'encryption_mode' => 'mime',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --sign --mime");
});

it('can send an encrypted message with encrypt as yes string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'encrypt' => 'yes',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --nosign --openpgp");
});

it('can send a signed message with sign as true string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'sign' => 'true',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --sign --openpgp");
});

it('can send a mail with a single file as string', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'files' => '/home/rainbow.txt',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' '/home/rainbow.txt' --noencrypt --nosign");
});

it('properly escapes shell special characters in body', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello $USER `whoami` $(id)',
        'subject' => 'Test',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello \$USER `whoami` \$(id)' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can use EncryptionMode enum directly', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello',
        'subject' => 'Test',
        'encrypt' => true,
        'encryption_mode' => \Pulli\Emate\EncryptionMode::MIME,
    ];

    expect(emate($options))
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --nosign --mime");
});

it('can send a mail with markdown as truthy string yes', function () {
    $options = [
        'to' => 'PuLLi <the@pulli.dev>',
        'from' => 'the@l33tdump.com',
        'body' => 'Hello **bold**',
        'subject' => 'Test',
        'markdown' => 'yes',
    ];

    expect(emate($options))
        ->toBe("echo 'Hello **bold**' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign --markup 'markdown'");
});

it('can compose a mail using fluent builder', function () {
    $command = Emate::compose()
        ->to('PuLLi <the@pulli.dev>')
        ->sender('the@l33tdump.com')
        ->subject('Test')
        ->body('Hello')
        ->debug();

    expect($command)
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign");
});

it('can compose a mail with encrypt using fluent builder', function () {
    $command = Emate::compose()
        ->to('PuLLi <the@pulli.dev>')
        ->sender('the@l33tdump.com')
        ->subject('Test')
        ->body('Hello')
        ->encrypt()
        ->debug();

    expect($command)
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --nosign --openpgp");
});

it('can compose a mail with sign using fluent builder', function () {
    $command = Emate::compose()
        ->to('PuLLi <the@pulli.dev>')
        ->sender('the@l33tdump.com')
        ->subject('Test')
        ->body('Hello')
        ->sign()
        ->debug();

    expect($command)
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --sign --openpgp");
});

it('can compose a mail with sendNow using fluent builder', function () {
    $command = Emate::compose()
        ->to('PuLLi <the@pulli.dev>')
        ->sender('the@l33tdump.com')
        ->subject('Test')
        ->body('Hello')
        ->sendNow()
        ->debug();

    expect($command)
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --send-now --noencrypt --nosign");
});

it('can compose a mail with markdown using fluent builder', function () {
    $command = Emate::compose()
        ->to('PuLLi <the@pulli.dev>')
        ->sender('the@l33tdump.com')
        ->subject('Test')
        ->body('Hello **bold**')
        ->markdown()
        ->debug();

    expect($command)
        ->toBe("echo 'Hello **bold**' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --noencrypt --nosign --markup 'markdown'");
});

it('can compose a mail with encryption mode using fluent builder', function () {
    $command = Emate::compose()
        ->to('PuLLi <the@pulli.dev>')
        ->sender('the@l33tdump.com')
        ->subject('Test')
        ->body('Hello')
        ->encrypt()
        ->encryptionMode(\Pulli\Emate\EncryptionMode::MIME)
        ->debug();

    expect($command)
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --subject 'Test' --from 'the@l33tdump.com' --encrypt --nosign --mime");
});

it('can compose a mail with cc, bcc, files, and replyTo using fluent builder', function () {
    $command = Emate::compose()
        ->to('PuLLi <the@pulli.dev>')
        ->sender('the@l33tdump.com')
        ->subject('Test')
        ->body('Hello')
        ->cc('cc@example.com')
        ->bcc('bcc@example.com')
        ->replyTo('reply@example.com')
        ->files('/home/rainbow.txt')
        ->debug();

    expect($command)
        ->toBe("echo 'Hello' | \$HOME/bin/emate mailto --to '\"PuLLi\" <the@pulli.dev>' --cc 'cc@example.com' --bcc 'bcc@example.com' --subject 'Test' --from 'the@l33tdump.com' --replyto 'reply@example.com' '/home/rainbow.txt' --noencrypt --nosign");
});
