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

namespace D3\Totp\Application\Model;

use D3\ModCfg\Application\Model\d3database;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use Doctrine\DBAL\DBALException;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;

class d3totp extends BaseModel
{
    public $tableName = 'd3totp';
    public $userId;
    public $totp;

    /**
     * d3totp constructor.
     */
    public function __construct()
    {
        $this->init($this->tableName);

        return parent::__construct();
    }

    /**
     * @param $userId
     * @return bool
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function loadByUserId($userId)
    {
        $this->userId = $userId;
        $oQB = d3database::getInstance()->getQueryBuilder();
        $oQB->select('oxid')
            ->from($this->getViewName())
            ->where("oxuserid = ".$oQB->createNamedParameter($userId))
            ->setMaxResults(1);

        return $this->load(DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne($oQB->getSQL(), $oQB->getParameters()));
    }

    /**
     * @return User
     */
    public function getUser()
    {
        $userId = $this->userId ? $this->userId : $this->getFieldData('oxuserid');

        $user = oxNew(User::class);
        $user->load($userId);
        return $user;
    }



    /**
     * @param $userId
     * @return bool
     */
    public function UserUseTotp()
    {
        return $this->getFieldData('usetotp');
    }

    /**
     * @param $userId
     * @return string
     */
    public function getSavedSecret()
    {
        $secret = $this->getFieldData('seed');

        if ($secret) {
            return $secret;
        }

        return null;
    }

    /**
     * @return TOTP
     */
    public function getTotp()
    {
        if (false == $this->totp) {
            $this->totp = oxNew(
                TOTP::class,
                $this->getUser()->getFieldData('oxusername')
                    ? $this->getUser()->getFieldData('oxusername')
                    : null,
                $this->getSavedSecret()
            );
            $this->totp->setIssuer(Registry::getConfig()->getActiveShop()->getFieldData('oxname'));
        }

        return $this->totp;
    }

    /**
     * @return string
     */
    public function getQrCodeUri()
    {
        return $this->getTotp()->getQrCodeUri();
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->getTotp()->getSecret();
    }

    /**
     * @param $totp
     * @return string
     * @throws d3totp_wrongOtpException
     */
    public function verify($totp)
    {
        $blVerify = $this->getTotp()->verify($totp, null, 2);
        if (false == $blVerify) {
            $oException = oxNew(d3totp_wrongOtpException::class, 'unvalid TOTP');
            throw $oException;
        }

        return $blVerify;
    }
}