<?php

declare(strict_types=1);

namespace Pulli\Emate;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Mime\Address;

final class Emate
{
    private static array $encryptionModes = ['openpgp', 'mime'];

    private static array $truthyValues = ['yes', 'true', true];

    private static string $symlinkTarget = '/Applications/MailMate.app/Contents/Resources/emate';

    private static string $symlinkDestination = '$HOME/bin';

    private string $commandString;

    private string $body;

    private Address|array|string $to;

    private Address|string $from;

    private Address|string $subject;

    private Address|array|string $cc;

    private Address|array|string $bcc;

    private array|string $files;

    private Address|string $replyTo;

    private bool $markdown;

    private bool $encrypt;

    private bool $sign;

    private bool $sendNow;

    private string $encryptionMode;

    /**
     * @throws InvalidArgumentException
     */
    private function __construct(array $options = [])
    {
        $options = new Collection($options);

        $this->body = $options->get('body', '');
        $this->to = $options->get('to', []);
        $this->from = $options->get('from', '');
        $this->subject = $options->get('subject', '');
        $this->cc = $options->get('cc', []);
        $this->bcc = $options->get('bcc', []);
        $this->files = $options->get('files', []);
        $this->replyTo = $options->get('reply_to', '');
        $this->markdown = $options->get('markdown', false);
        $this->encrypt = $options->get('encrypt', false);
        $this->sign = $options->get('sign', false);
        $this->sendNow = $options->get('send_now', false);
        $this->encryptionMode = mb_strtolower($options->get('encryption_mode', 'openpgp'));

        $this->checkForEncryptionMode();
        $this->commandString = $this->commandString();
    }

    public static function from(array $options = []): self
    {
        return new self($options);
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

        $destination = $folder ?? self::$symlinkDestination;
        $link = $destination.'/emate';

        if (file_exists($link)) {
            return true;
        }

        if (! file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        return symlink($target, $link);
    }

    public function mail(): void
    {
        system($this->commandString);
    }

    public function debug(): string
    {
        return $this->commandString;
    }

    private function commandString(): string
    {
        return implode('', array_merge(
            $this->preamble(),
            $this->normalFlags(),
            $this->filesFlag(),
            $this->booleanFlags(),
            $this->markdownMode(),
            $this->encryptionMode(),
        ));
    }

    private function preamble(): array
    {
        return ['preamble' => "echo '$this->body' | \$HOME/bin/emate mailto"];
    }

    private function normalFlags(): array
    {
        return Collection::make([
            ['value' => $this->to, 'flag' => '--to'],
            ['value' => $this->cc, 'flag' => '--cc'],
            ['value' => $this->bcc, 'flag' => '--bcc'],
            ['value' => $this->subject, 'flag' => '--subject'],
            ['value' => $this->from, 'flag' => '--from'],
            ['value' => $this->replyTo, 'flag' => '--replyto'],
        ])
            ->map(fn (array $input) => $this->buildConsoleStatement($input['value'], $input['flag']))
            ->toArray();
    }

    private function filesFlag(): array
    {
        return [
            'files' => $this->convert($this->files)
                ->map(fn (string $file) => mb_strlen($file) > 0 ? ' '.escapeshellarg($file) : '')
                ->join(''),
        ];
    }

    private function booleanFlags(): array
    {
        return Collection::make([
            ['value' => $this->sendNow, 'truthy_flag' => ' --send-now', 'falsy_flag' => ''],
            ['value' => $this->encrypt, 'truthy_flag' => ' --encrypt', 'falsy_flag' => ' --noencrypt'],
            ['value' => $this->sign, 'truthy_flag' => ' --sign', 'falsy_flag' => ' --nosign'],
        ])
            ->map(fn (array $value) => in_array($value['value'], self::$truthyValues) ? $value['truthy_flag'] : $value['falsy_flag'])
            ->toArray();
    }

    private function markdownMode(): array
    {
        return ['markdown_mode' => $this->markdown ? " --markup 'markdown'" : ''];
    }

    private function encryptionMode(): array
    {
        return ['encryption_mode' => $this->encrypt || $this->sign ? " --$this->encryptionMode" : ''];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function checkForEncryptionMode(): void
    {
        if (in_array($this->encryptionMode, self::$encryptionModes)) {
            return;
        }

        throw new InvalidArgumentException('Invalid encryption mode. Possible values are: '.implode(', ', self::$encryptionModes));
    }

    private function buildConsoleStatement(mixed $address, string $kind): string
    {
        // If from is passed as Address object, return just the address.
        // Otherwise, MailMate won't recognize the proper account.
        if ($kind === '--from' && $address instanceof Address) {
            $address = $address->getAddress();
        }

        return $this->convert($address)
            ->map(fn (string $address) => mb_strlen($address) > 0 ? implode('', [' ', $kind, ' ']).escapeshellarg($address) : '')
            ->join('');
    }

    /**
     * @throws InvalidArgumentException
     */
    private function convert(mixed $value): Collection
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
        return Collection::make($value)
            ->map(function (Address|string $address): string {
                if ($address instanceof Address) {
                    return $address->toString();
                }

                return $this->splitString($address)
                    ->map(function (string $line) {
                        preg_match('/"?([^"]+)"? <(.+)>/', $line, $match);

                        if ($match) {
                            $name = $match[1];
                            $mailAddress = $match[2];

                            return "\"$name\" <$mailAddress>";
                        }

                        return $line;
                    })
                    ->implode(PHP_EOL);
            })
            ->implode(PHP_EOL);
    }

    private function splitString(string $string): Collection
    {
        return Collection::make(explode(PHP_EOL, $string));
    }
}
