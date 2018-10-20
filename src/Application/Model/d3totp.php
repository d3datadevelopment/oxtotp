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
    const TOTP_SESSION_VARNAME = 'totp_auth';

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

        if (DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne("SHOW TABLES LIKE '".$this->tableName."'")) {
            $oQB->select('oxid')
                ->from($this->getViewName())
                ->where("oxuserid = " . $oQB->createNamedParameter($userId))
                ->setMaxResults(1);

            return $this->load(DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne($oQB->getSQL(), $oQB->getParameters()));
        }

        return false;
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
        return $this->getFieldData('usetotp')
            && $this->getFieldData('seed');
    }

    /**
     * @param $userId
     * @return string
     */
    public function getSavedSecret()
    {
        $seed_enc = $this->getFieldData('seed');
        $sPwd = Registry::getSession()->getVariable('pwdTransmit');

        if ($seed_enc && $sPwd) {
            $seed = $this->decrypt($seed_enc, $sPwd);
            if ($seed) {
                return $seed;
            }
        }

        return null;
    }

    /**
     * @param $seed
     * @return TOTP
     */
    public function getTotp($seed = null)
    {
        if (false == $this->totp) {

            if ($this->getTotpLibVersion() == 8) {     // version 0.8 (< PHP 7.1)
                $this->totp = oxNew(
                    TOTP::class,
                    $this->getUser()->getFieldData('oxusername')
                        ? $this->getUser()->getFieldData('oxusername')
                        : null,
                    $seed
                        ? $seed
                        : $this->getSavedSecret()
                );
            } else {                                    // version 0.9 (>= PHP 7.1)
                $this->totp = TOTP::create($seed ? $seed : $this->getSavedSecret());
                $this->totp->setLabel($this->getUser()->getFieldData('oxusername')
                    ? $this->getUser()->getFieldData('oxusername')
                    : null
                );
            }
            $this->totp->setIssuer(Registry::getConfig()->getActiveShop()->getFieldData('oxname'));
        }

        return $this->totp;
    }

    /**
     * @return int
     */
    public function getTotpLibVersion()
    {
        return method_exists(TOTP::class, 'create') ?
            9 :
            8;
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
        return trim($this->getTotp()->getSecret());
    }

    /**
     * @param $seed
     * @param $key
     */
    public function saveSecret($seed, $key)
    {
        $this->assign(
            array(
                'seed'  => $this->encrypt($seed, $key)
            )
        );
    }

    /**
     * @param $totp
     * @param $seed
     * @return string
     * @throws d3totp_wrongOtpException
     */
    public function verify($totp, $seed = null)
    {
        $blVerify = $this->getTotp($seed)->verify($totp, null, 2);
        if (false == $blVerify) {
            $oException = oxNew(d3totp_wrongOtpException::class);
            throw $oException;
        }

        return $blVerify;
    }

    /**
     * $key should have previously been generated in a cryptographically secure manner, e.g. via openssl_random_pseudo_bytes
     *
     * @param $plaintext
     * @param $key
     * @return string
     */
    public function encrypt($plaintext, $key)
    {
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        return base64_encode($iv.$hmac.$ciphertext_raw);
    }

    /**
     * $key should have previously been generated in a cryptographically secure manner, e.g. via openssl_random_pseudo_bytes
     *
     * @param $ciphertext
     * @param $key
     * @return bool|string
     */
    public function decrypt($ciphertext, $key)
    {
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        if (hash_equals($hmac, $calcmac)) { // PHP 5.6+ compute attack-safe comparison
            return $original_plaintext;
        }

        return false;
    }
}