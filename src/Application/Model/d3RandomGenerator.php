<?php

namespace D3\Totp\Application\Model;

use Laminas\Math\Rand;

class d3RandomGenerator extends Rand
{
    const CHAR_UPPER     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHAR_LOWER     = 'abcdefghijklmnopqrstuvwxyz';
    const CHAR_DIGITS    = '0123456789';
    const CHAR_UPPER_HEX = 'ABCDEF';
    const CHAR_LOWER_HEX = 'abcdef';
    const CHAR_BASE64    = '+/';
    const CHAR_SYMBOLS   = '!"#$%&\'()* +,-./:;<=>?@[\]^_`{|}~';
    const CHAR_BRACKETS  = '()[]{}<>';
    const CHAR_PUNCT     = ',.;:';

    /**
     * @return string
     */
    public static function getRandomTotpBackupCode()
    {
        return self::getString(6, self::CHAR_DIGITS);
    }
}