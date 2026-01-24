<?php

declare(strict_types=1);

namespace Pulli\Emate;

enum EncryptionMode: string
{
    case OpenPGP = 'openpgp';
    case SMIME = 'smime';
}
