<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      http://www.oxidmodule.com
 */

namespace D3\Totp\Application\Controller\Admin;

use D3\Totp\Application\Model\d3totp;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;

class d3user_totp extends AdminDetailsController
{
    protected $_sThisTemplate = 'd3user_totp.tpl';

    /**
     * @return string
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function render()
    {
        parent::render();

        $soxId = $this->_aViewData["oxid"] = $this->getEditObjectId();
        if (isset($soxId) && $soxId != "-1") {
            /** @var d3totp $oTotp */
            $oTotp = oxNew(d3totp::class);
            $oTotp->loadByUserId($soxId);
            $this->_aViewData["edit"] = $oTotp;
        }

        if (!$this->_allowAdminEdit($soxId)) {
            $this->_aViewData['readonly'] = true;
        }

        return $this->_sThisTemplate;
    }
}