<?php

declare(strict_types=1);

namespace Pulli\Emate;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Mime\Address;

final class Emate
{
    private static array $truthyValues = ['yes', 'true', true];

    private static string $symlinkTarget = '/Applications/MailMate.app/Contents/Resources/emate';

    private string $body = '';

    private Address|array|string $to = [];

    private Address|string $from = '';

    private string $subject = '';

    private Address|array|string $cc = [];

    private Address|array|string $bcc = [];

    private array|string $files = [];

    private Address|string $replyTo = '';

    private bool|string $markdown = false;

    private bool|string $encrypt = false;

    private bool|string $sign = false;

    private bool|string $sendNow = false;

    private EncryptionMode $encryptionMode = EncryptionMode::OpenPGP;

    /**
     * @throws InvalidArgumentException
     */
    private function __construct(array $options = [])
    {
        $this->body = $options['body'] ?? '';
        $this->to = $options['to'] ?? [];
        $this->from = $options['from'] ?? '';
        $this->subject = $options['subject'] ?? '';
        $this->cc = $options['cc'] ?? [];
        $this->bcc = $options['bcc'] ?? [];
        $this->files = $options['files'] ?? [];
        $this->replyTo = $options['reply_to'] ?? '';
        $this->markdown = $options['markdown'] ?? false;
        $this->encrypt = $options['encrypt'] ?? false;
        $this->sign = $options['sign'] ?? false;
        $this->sendNow = $options['send_now'] ?? false;
        $encryptionMode = $options['encryption_mode'] ?? 'openpgp';
        $this->encryptionMode = $encryptionMode instanceof EncryptionMode
            ? $encryptionMode
            : EncryptionMode::tryFrom(mb_strtolower($encryptionMode))
                ?? throw new InvalidArgumentException('Invalid encryption mode. Possible values are: openpgp, mime');
    }

    public static function from(array $options = []): self
    {
        return new self($options);
    }

    public static function compose(): self
    {
        return new self;
    }

    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function to(Address|array|string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function sender(Address|string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function cc(Address|array|string $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    public function bcc(Address|array|string $bcc): self
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function files(array|string $files): self
    {
        $this->files = $files;

        return $this;
    }

    public function replyTo(Address|string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function markdown(bool|string $markdown = true): self
    {
        $this->markdown = $markdown;

        return $this;
    }

    public function encrypt(bool|string $encrypt = true): self
    {
        $this->encrypt = $encrypt;

        return $this;
    }

    public function sign(bool|string $sign = true): self
    {
        $this->sign = $sign;

        return $this;
    }

    public function sendNow(bool|string $sendNow = true): self
    {
        $this->sendNow = $sendNow;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function encryptionMode(EncryptionMode|string $mode): self
    {
        $this->encryptionMode = $mode instanceof EncryptionMode
            ? $mode
            : EncryptionMode::tryFrom(mb_strtolower($mode))
                ?? throw new InvalidArgumentException('Invalid encryption mode. Possible values are: openpgp, mime');

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public static function symlink(?string $folder = null): bool
    {
        $target = self::$symlinkTarget;

        if (! file_exists($target)) {
            throw new RuntimeException("MailMate is not installed. The emate command could not be found here: '$target'.");
        }

        $destination = $folder ?? getenv('HOME').'/bin';
        $link = $destination.'/emate';

        if (file_exists($link)) {
            return true;
        }

        if (! file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        return symlink($target, $link);
    }

    /**
     * @throws RuntimeException
     */
    public function mail(): void
    {
        passthru($this->commandString(), $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException("emate command failed with exit code $exitCode.");
        }
    }

    public function debug(): string
    {
        return $this->commandString();
    }

    private function commandString(): string
    {
        return $this->preamble()
            .$this->normalFlags()
            .$this->filesFlag()
            .$this->booleanFlags()
            .$this->markdownFlag()
            .$this->encryptionModeFlag();
    }

    private function preamble(): string
    {
        return 'echo '.escapeshellarg($this->body).' | $HOME/bin/emate mailto';
    }

    private function normalFlags(): string
    {
        $flags = [
            ['value' => $this->to, 'flag' => '--to'],
            ['value' => $this->cc, 'flag' => '--cc'],
            ['value' => $this->bcc, 'flag' => '--bcc'],
            ['value' => $this->subject, 'flag' => '--subject'],
            ['value' => $this->from, 'flag' => '--from'],
            ['value' => $this->replyTo, 'flag' => '--replyto'],
        ];

        return implode('', array_map(
            fn (array $input) => $this->buildConsoleStatement($input['value'], $input['flag']),
            $flags,
        ));
    }

    private function filesFlag(): string
    {
        $files = $this->convert($this->files);

        return implode('', array_map(
            fn (string $file) => mb_strlen($file) > 0 ? ' '.escapeshellarg($file) : '',
            $files,
        ));
    }

    private function booleanFlags(): string
    {
        $flags = [
            ['value' => $this->sendNow, 'truthy_flag' => ' --send-now', 'falsy_flag' => ''],
            ['value' => $this->encrypt, 'truthy_flag' => ' --encrypt', 'falsy_flag' => ' --noencrypt'],
            ['value' => $this->sign, 'truthy_flag' => ' --sign', 'falsy_flag' => ' --nosign'],
        ];

        return implode('', array_map(
            fn (array $value) => in_array($value['value'], self::$truthyValues) ? $value['truthy_flag'] : $value['falsy_flag'],
            $flags,
        ));
    }

    private function markdownFlag(): string
    {
        return in_array($this->markdown, self::$truthyValues) ? " --markup 'markdown'" : '';
    }

    private function encryptionModeFlag(): string
    {
        return $this->encrypt || $this->sign ? " --{$this->encryptionMode->value}" : '';
    }

    private function buildConsoleStatement(mixed $address, string $kind): string
    {
        if ($kind === '--from' && $address instanceof Address) {
            $address = $address->getAddress();
        }

        $parts = $this->convert($address);

        return implode('', array_map(
            fn (string $address) => mb_strlen($address) > 0 ? implode('', [' ', $kind, ' ']).escapeshellarg($address) : '',
            $parts,
        ));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function convert(mixed $value): array
    {
        return $this->splitString(match (true) {
            $value instanceof Address => $value->toString(),
            is_string($value), is_array($value) => $this->checkForAddress($value),
            default => throw new InvalidArgumentException('Invalid format.'),
        });
    }

    /**
     * @param  array<Address|string>|string  $value
     */
    private function checkForAddress(array|string $value): string
    {
        $values = is_array($value) ? $value : [$value];

        return implode(PHP_EOL, array_map(function (Address|string $address): string {
            if ($address instanceof Address) {
                return $address->toString();
            }

            $lines = $this->splitString($address);

            return implode(PHP_EOL, array_map(function (string $line): string {
                preg_match('/"?([^"]+)"? <(.+)>/', $line, $match);

                if ($match) {
                    $name = $match[1];
                    $mailAddress = $match[2];

                    return "\"$name\" <$mailAddress>";
                }

                return $line;
            }, $lines));
        }, $values));
    }

    private function splitString(string $string): array
    {
        return explode(PHP_EOL, $string);
    }
}
