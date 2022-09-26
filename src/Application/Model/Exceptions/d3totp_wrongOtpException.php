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

namespace D3\Totp\Application\Model\Exceptions;

use Exception;
use OxidEsales\Eshop\Core\Exception\StandardException;

class d3totp_wrongOtpException extends StandardException
{
    /**
     * Default constructor
     *
     * @param string          $sMessage exception message
     * @param integer         $iCode    exception code
     * @param Exception|null $previous previous exception
     */
    public function __construct($sMessage = "D3_TOTP_ERROR_UNVALID", $iCode = 0, Exception $previous = null)
    {
        parent::__construct($sMessage, $iCode, $previous);
    }
}
