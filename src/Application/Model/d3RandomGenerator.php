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

use Laminas\Math\Rand;

class d3RandomGenerator extends Rand
{
    const CHAR_DIGITS    = '0123456789';

    /**
     * @return string
     */
    public static function getRandomTotpBackupCode()
    {
        return self::getString(6, self::CHAR_DIGITS);
    }
}