<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\Totp\Application\Model;

use Random\RandomException;

class d3RandomGenerator
{
    public const CHAR_DIGITS    = '0123456789';
    public const CHAR_ALNUM     = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * @return string
     * @throws RandomException
     */
    public static function getRandomTotpBackupCode(): string
    {
        $rawCode = '';
        $alphabet = self::CHAR_ALNUM;

        for ($i = 0; $i < 12; $i++) {
            $rawCode .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return implode('-', str_split($rawCode, 4));
    }
}
