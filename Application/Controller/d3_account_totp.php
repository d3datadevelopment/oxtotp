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

use Assert\Assert;
use D3\Totp\Application\Model\Constants;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Exception;
use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3_account_totp extends AccountController
{
    use OtpManagementControllerTrait;

    protected $_sThisTemplate = '@'.Constants::OXID_MODULE_ID.'/tpl/d3_account_totp';

    public array $aBackupCodes = [];

    public function render(): string
    {
        $sRet = parent::render();

        $this->addTplParam('user', $this->getUser());

        return $sRet;
    }

    public function getCurrentUserId(): string
    {
        return $this->getUser()->getId();
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function create(): void
    {
        if (Registry::getRequest()->getRequestEscapedParameter('totp_use') === '1') {
            try {
                /** @var d3_totp_user $user */
                $user = $this->getUser();
                $oTotp = $this->getTotpObject();
                Assert::that($oTotp->checkIfAlreadyExist($this->getCurrentUserId()))->false('D3_TOTP_ALREADY_EXIST');

                $oTotpBackupCodes = $this->getBackupCodeListObject();
                $aParams = [
                    'd3totp__usetotp' => 1,
                    'd3totp__oxuserid'  => $user->getId(),
                ];
                $secret = Registry::getSession()->getVariable(d3totp_conf::OTP_SECRET_SESSION_VARNAME);
                $label = Registry::getSession()->getVariable(d3totp_conf::OTP_LABEL_SESSION_VARNAME);
                $init = oxNew(d3totp::class);
                $init->getTotp($user)->setSecret($secret);
                $init->getTotp($user)->setLabel($label);
                $seed = $init->getSecret($user);
                $otp = Registry::getRequest()->getRequestEscapedParameter("otp");

                Assert::that($seed)->notBlank('D3_TOTP_EMPTY_SEED');
                Assert::that($otp)
                    ->integerish('D3_TOTP_MISSING_VALIDATION')
                    ->length(6, 'D3_TOTP_MISSING_VALIDATION');

                $oTotp->setSecret($seed);
                $oTotp->assign($aParams);
                $oTotp->setId();
                $oTotp->verify($user, $otp, '', $seed);
                $oTotpBackupCodes->generateBackupCodes($user->getId());
                $oTotp->save();
                $oTotpBackupCodes->save();
            } catch (Exception $oExcp) {
                Registry::get(UtilsView::class)->addErrorToDisplay($oExcp->getMessage());
            } finally {
                Registry::getSession()->deleteVariable(d3totp_conf::OTP_SECRET_SESSION_VARNAME);
                Registry::getSession()->deleteVariable(d3totp_conf::OTP_LABEL_SESSION_VARNAME);
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function delete(): void
    {
        try {
            if ( Registry::getRequest()->getRequestEscapedParameter( 'totp_use' ) !== '1' ) {
                $user  = $this->getUser();
                $oTotp = $this->getTotpObject();
                if ( $user instanceof User && $user->getId() ) {
                    $oTotp->loadByUserId( $user->getId() );
                    $this->verifyPassword(
                        $user,
                        trim( Registry::getRequest()->getRequestEscapedParameter('password'))
                    );
                    $oTotp->delete();
                }
            }
        } catch ( Exception $oExcp ) {
            Registry::get( UtilsView::class )->addErrorToDisplay( $oExcp->getMessage() );
        }
    }

    /**
     * @param User   $user
     * @param string $password
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function verifyPassword(User $user, string $password): void
    {
        $container = ContainerFactory::getInstance()->getContainer();

        Assert::that(
            $container->get(PasswordServiceBridgeInterface::class)
                ->verifyPassword($password, $user->getFieldData('oxpassword'))
        )->true(Registry::getLang()->translateString('D3_TOTP_ACCOUNT_PASSWORD_ERR'));
    }
}
