<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      http://www.oxidmodule.com
 */

namespace D3\Totp\Application\Model\Exceptions;

use OxidEsales\Eshop\Core\Exception\StandardException;

class d3totp_wrongOtpException extends StandardException
{
    /**
     * Default constructor
     *
     * @param string          $sMessage exception message
     * @param integer         $iCode    exception code
     * @param \Exception|null $previous previous exception
     */
    public function __construct($sMessage = "D3_TOTP_ERROR_UNVALID", $iCode = 0, \Exception $previous = null)
    {
        parent::__construct($sMessage, $iCode, $previous);
    }
}
