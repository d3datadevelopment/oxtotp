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

namespace D3\Totp\Modules\Core;

use D3\Totp\Application\Model\Constants;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleSettingNotFountException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3_totp_utils extends d3_totp_utils_parent
{
    /**
     * @return bool
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function checkAccessRights()
    {
        $blAuth = parent::checkAccessRights();
        $blAuth = $this->d3AuthHook($blAuth);
        $userID = $this->d3TotpGetSessionObject()->getVariable(d3totp_conf::OXID_ADMIN_AUTH);
        $totpAuth = (bool) $this->d3TotpGetSessionObject()->getVariable(d3totp_conf::SESSION_ADMIN_AUTH);
        /** @var d3totp $totp */
        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId($userID);

        //check forced 2FA for all admin users
        if (
            $this->d3IsAdminForce2FA()
            && $blAuth
            && $totp->isActive() === false
        ) {
            $this->redirect('index.php?cl=d3force_2fa');
        }

        //staten der prüfung vom einmalpasswort
        if ($blAuth && $totp->isActive() && false === $totpAuth) {
            $this->redirect('index.php?cl=d3totpadminlogin', false);
        }

        return $blAuth;
    }

    /**
     * @return Session
     */
    public function d3TotpGetSessionObject(): Session
    {
        return Registry::getSession();
    }

    /**
     * @return d3totp
     */
    public function d3GetTotpObject(): d3totp
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return Config
     */
    public function d3GetConfig(): Config
    {
        return Registry::getConfig();
    }

    /**
     * @return bool
     * @throws ModuleSettingNotFountException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function d3IsAdminForce2FA(): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }

        $container = ContainerFactory::getInstance()->getContainer();
        $moduleConfigurationBridge = $container->get(ModuleConfigurationDaoBridgeInterface::class);
        /** @var ModuleConfiguration $moduleConfiguration */
        $moduleConfiguration = $moduleConfigurationBridge->get(Constants::OXID_MODULE_ID);
        return (bool) $moduleConfiguration->getModuleSetting('D3_TOTP_ADMIN_FORCE_2FA')->getValue();
    }

    /**
     * @param bool $blAuth
     * @return bool
     */
    protected function d3AuthHook(bool $blAuth): bool
    {
        return $blAuth;
    }
}
