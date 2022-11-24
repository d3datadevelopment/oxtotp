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

namespace D3\Totp\Application\Controller;

use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp_conf;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;

class d3totplogin extends FrontendController
{
    protected $_sThisTemplate = 'd3totplogin.tpl';

    public function render()
    {
        if (!Registry::getSession()->hasVariable(d3totp_conf::SESSION_CURRENTUSER)) {
            $this->getUtils()->redirect('index.php?cl=start');
        }

        $this->addTplParam('navFormParams', Registry::getSession()->getVariable(d3totp_conf::SESSION_NAVFORMPARAMS));

        return parent::render();
    }

    /**
     * @return Utils
     */
    public function getUtils(): Utils
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
        $userId = Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTUSER);
        $iCount = $oBackupCodeList->getAvailableCodeCount($userId);

        if ($iCount < 4) {
            return sprintf(
                Registry::getLang()->translateString('D3_TOTP_AVAILBACKUPCODECOUNT', null, true),
                $iCount
            );
        }
    }

    /**
     * @return d3backupcodelist
     */
    public function getBackupCodeListObject(): d3backupcodelist
    {
        return oxNew(d3backupcodelist::class);
    }

    public function getPreviousClass()
    {
        return Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTCLASS);
    }

    public function previousClassIsOrderStep(): bool
    {
        $sClassKey = Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTCLASS);
        $resolvedClass = Registry::getControllerClassNameResolver()->getClassNameById($sClassKey);
        $resolvedClass = $resolvedClass ?: 'start';

        /** @var FrontendController $oController */
        $oController = oxNew($resolvedClass);
        return $oController->getIsOrderStep();
    }

    /**
     * @return bool
     */
    public function getIsOrderStep(): bool
    {
        return $this->previousClassIsOrderStep();
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb(): array
    {
        $aPaths = [];
        $aPath = [];
        $iBaseLanguage = (int) Registry::getLang()->getBaseLanguage();
        $aPath['title'] = Registry::getLang()->translateString('D3_TOTP_BREADCRUMB', $iBaseLanguage, false);
        $aPath['link'] = $this->getLink();

        $aPaths[] = $aPath;

        return $aPaths;
    }
}
