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

namespace D3\Totp\Application\Controller\Admin;

use D3\Totp\Application\Model\Constants;
use D3\Totp\Application\Model\d3totp_conf;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleSettingNotFountException;

class d3force_2fa extends d3user_totp
{
    public function render(): string
    {
        $this->addTplParam('force2FA', true);

        $userID = $this->d3TotpGetSessionObject()->getVariable(d3totp_conf::OXID_ADMIN_AUTH);
        $this->_sEditObjectId = $userID;

        return parent::render();
    }

    /**
     * @return bool
     * @throws ModuleSettingNotFountException
     */
    protected function authorize(): bool
    {
        $userID = $this->d3TotpGetSessionObject()->getVariable(d3totp_conf::OXID_ADMIN_AUTH);

        return ($this->d3IsAdminForce2FA() && !empty($userID));
    }

    /**
     * @return Session
     */
    private function d3TotpGetSessionObject(): Session
    {
        return Registry::getSession();
    }

    /**
     * @return bool
     * @throws ModuleSettingNotFountException
     */
    private function d3IsAdminForce2FA(): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }

        return (bool) $this->getModuleConfiguration()->getModuleSetting('D3_TOTP_ADMIN_FORCE_2FA')->getValue();
    }

    protected function getModuleConfiguration(): ModuleConfiguration
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $moduleConfigurationBridge = $container->get(ModuleConfigurationDaoBridgeInterface::class);
        /** @var ModuleConfiguration $moduleConfiguration */
        return $moduleConfigurationBridge->get(Constants::OXID_MODULE_ID);
    }
}
