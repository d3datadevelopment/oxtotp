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

class Constants
{
    public const OXID_MODULE_ID = 'd3totp';
    public const CONFIG_LOGLEVEL = 'D3_TOTP_setting_logLevel';
    public const CONFIG_KEPTLOGFILES = 'D3_TOTP_setting_keptlogfiles';
    public const CONFIG_LOGERRORSONLY = 'D3_TOTP_setting_logOnErrorsOnly';
}
