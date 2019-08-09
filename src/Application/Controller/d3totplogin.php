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

namespace D3\Totp\Application\Controller;

use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;

class d3totplogin extends FrontendController
{
    protected $_sThisTemplate = 'd3totplogin.tpl';

    public function render()
    {
        if (Registry::getSession()->hasVariable(d3totp::TOTP_SESSION_VARNAME) ||
            false == Registry::getSession()->hasVariable(d3totp::TOTP_SESSION_CURRENTUSER)
        ) {
            $this->getUtils()->redirect('index.php?cl=start', true, 302);
            if (false == defined('OXID_PHP_UNIT')) {
                // @codeCoverageIgnoreStart
                exit;
                // @codeCoverageIgnoreEnd
            }
        }

        $this->addTplParam('navFormParams', Registry::getSession()->getVariable(d3totp::TOTP_SESSION_NAVFORMPARAMS));

        return parent::render();
    }

    /**
     * @return Utils
     */
    public function getUtils()
    {
        return Registry::getUtils();
    }

    /**
     * @return string|void
     * @throws DatabaseConnectionException
     */
    public function getBackupCodeCountMessage()
    {
        $oBackupCodeList = $this->getBackupCodeListObject();
        $iCount = $oBackupCodeList->getAvailableCodeCount(Registry::getSession()->getVariable(d3totp::TOTP_SESSION_CURRENTUSER));

        if ($iCount < 4) {
            return sprintf(
                Registry::getLang()->translateString('D3_TOTP_AVAILBACKUPCODECOUNT', null, true),
                $iCount
            );
        };

        return;
    }

    /**
     * @return d3backupcodelist
     */
    public function getBackupCodeListObject()
    {
        return oxNew(d3backupcodelist::class);
    }

    public function getPreviousClass()
    {
        return Registry::getSession()->getVariable(d3totp::TOTP_SESSION_CURRENTCLASS);
    }

    public function previousClassIsOrderStep()
    {
        $sClassKey = Registry::getSession()->getVariable(d3totp::TOTP_SESSION_CURRENTCLASS);
        $resolvedClass = Registry::getControllerClassNameResolver()->getClassNameById($sClassKey);
        $resolvedClass = $resolvedClass ? $resolvedClass : 'start';

        /** @var FrontendController $oController */
        $oController = oxNew($resolvedClass);
        return $oController->getIsOrderStep();
    }

    /**
     * @return bool
     */
    public function getIsOrderStep()
    {
        return $this->previousClassIsOrderStep();
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        $aPaths = [];
        $aPath = [];
        $iBaseLanguage = Registry::getLang()->getBaseLanguage();
        $aPath['title'] = Registry::getLang()->translateString('D3_TOTP_BREADCRUMB', $iBaseLanguage, false);
        $aPath['link'] = $this->getLink();

        $aPaths[] = $aPath;

        return $aPaths;
    }
}