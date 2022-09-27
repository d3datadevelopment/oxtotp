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

namespace D3\Totp\Modules\Application\Controller\Admin;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsView;

class d3_totp_LoginController extends d3_totp_LoginController_parent
{
    /**
     * @return string
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function render()
    {
        $auth = $this->d3GetSession()->getVariable("auth");

        $return = parent::render();

        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId($auth);

        if ($auth
            && $totp->isActive()
            && !$this->d3GetSession()->getVariable(d3totp::TOTP_SESSION_VARNAME)
        ) {
            // set auth as secured parameter;
            $this->d3GetSession()->setVariable("auth", $auth);
            $this->addTplParam('request_totp', true);
        }

        return $return;
    }

    /**
     * @return d3totp
     */
    public function d3GetTotpObject()
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return d3backupcodelist
     */
    public function d3GetBackupCodeListObject()
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @return UtilsView
     */
    public function d3GetUtilsView()
    {
        return Registry::getUtilsView();
    }

    /**
     * @return Session
     */
    public function d3GetSession()
    {
        return Registry::getSession();
    }

    /**
     * @return mixed|string
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function checklogin()
    {
        $sTotp = Registry::getRequest()->getRequestEscapedParameter('d3totp', true);

        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId(Registry::getSession()->getVariable("auth"));

        $return = 'login';

        try {
            if ($this->isNoTotpOrNoLogin($totp) && $this->hasLoginCredentials()) {
                $return = parent::checklogin();
            } elseif ($this->hasValidTotp($sTotp, $totp)) {
                $this->d3GetSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, $sTotp);
                $return = "admin_start";
            }
        } catch (d3totp_wrongOtpException $oEx) {
            $this->d3GetUtilsView()->addErrorToDisplay($oEx);
        }

        return $return;
    }

    /**
     * @return string|void
     * @throws DatabaseConnectionException
     */
    public function getBackupCodeCountMessage()
    {
        $oBackupCodeList = $this->d3GetBackupCodeListObject();
        $iCount = $oBackupCodeList->getAvailableCodeCount(Registry::getSession()->getVariable("auth"));

        if ($iCount < 4) {
            return sprintf(
                Registry::getLang()->translateString('D3_TOTP_AVAILBACKUPCODECOUNT'),
                $iCount
            );
        }
    }

    /**
     * @param d3totp $totp
     * @return bool
     */
    public function isNoTotpOrNoLogin($totp)
    {
        return false == $this->d3GetSession()->getVariable("auth")
        || false == $totp->isActive();
    }

    protected function hasLoginCredentials()
    {
        return Registry::getRequest()->getRequestEscapedParameter( 'user') &&
               Registry::getRequest()->getRequestEscapedParameter('pwd');
    }

    /**
     * @param string $sTotp
     * @param d3totp $totp
     * @return bool
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function hasValidTotp($sTotp, $totp)
    {
        return Registry::getSession()->getVariable(d3totp::TOTP_SESSION_VARNAME) ||
        (
            $sTotp && $totp->verify($sTotp)
        );
    }

    public function d3CancelLogin()
    {
        $oUser = $this->d3GetUserObject();
        $oUser->logout();
    }

    /**
     * @return User
     */
    public function d3GetUserObject()
    {
        return oxNew(User::class);
    }
}