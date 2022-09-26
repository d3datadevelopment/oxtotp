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

use BaconQrCode\Renderer\RendererInterface;
use BaconQrCode\Writer;
use D3\Totp\Application\Factory\BaconQrCodeFactory;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use Doctrine\DBAL\DBALException;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;

class d3totp extends BaseModel
{
    const TOTP_SESSION_VARNAME          = 'totp_auth';
    const TOTP_SESSION_CURRENTUSER      = 'd3totpCurrentUser';
    const TOTP_SESSION_CURRENTCLASS     = 'd3totpCurrentClass';
    const TOTP_SESSION_NAVFORMPARAMS    = 'd3totpNavFormParams';

    public $tableName = 'd3totp';
    public $userId;
    public $totp;
    protected $timeWindow = 2;

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
     * @throws DatabaseConnectionException
     */
    public function loadByUserId($userId)
    {
        $this->userId = $userId;
        $oDb = $this->d3GetDb();

        if ($oDb->getOne("SHOW TABLES LIKE '".$this->tableName."'")) {
            $query = "SELECT oxid FROM ".$this->getViewName().' WHERE oxuserid = '.$oDb->quote($userId).' LIMIT 1';
            $this->load($oDb->getOne($query));
        }
    }

    /**
     * @param $userId
     * @return bool
     * @throws DatabaseConnectionException
     */
    public function checkIfAlreadyExist($userId)
    {
        $oDb = $this->d3GetDb();
        $query = "SELECT 1 FROM ".$this->getViewName().' WHERE oxuserid = '.$oDb->quote($userId).' LIMIT 1';
        return (bool) $oDb->getOne($query);
    }

    /**
     * @return DatabaseInterface
     * @throws DatabaseConnectionException
     */
    public function d3GetDb()
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        $userId = $this->userId ? $this->userId : $this->getFieldData('oxuserid');

        $user = $this->d3GetUser();
        $user->load($userId);
        return $user;
    }

    /**
     * @return User
     */
    public function d3GetUser()
    {
        return oxNew(User::class);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return false == Registry::getConfig()->getConfigParam('blDisableTotpGlobally')
            &&  $this->UserUseTotp();
    }

    /**
     * @return bool
     */
    public function UserUseTotp()
    {
        return $this->getFieldData('usetotp')
            && $this->getFieldData('seed');
    }

    /**
     * @return string
     */
    public function getSavedSecret()
    {
        $seed_enc = $this->getFieldData('seed');

        if ($seed_enc) {
            $seed = $this->decrypt($seed_enc);
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

            $this->totp = oxNew(
                TOTP::class,
                $this->getUser()->getFieldData('oxusername')
                    ? $this->getUser()->getFieldData('oxusername')
                    : null,
                $seed
                    ? $seed
                    : $this->getSavedSecret()
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
    public function getQrCodeElement()
    {
        $renderer = BaconQrCodeFactory::renderer(200);
        $writer = $this->d3GetWriter($renderer);
        return $writer->writeString($this->getTotp()->getProvisioningUri());
    }

    /**
     * @param RendererInterface $renderer
     * @return Writer
     */
    public function d3GetWriter(RendererInterface $renderer)
    {
        return oxNew(Writer::class, $renderer);
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
     */
    public function saveSecret($seed)
    {
        $this->assign(
            [
                'seed'  => $this->encrypt($seed)
            ]
        );
    }

    /**
     * @param $totp
     * @param $seed
     * @return string
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function verify($totp, $seed = null)
    {
        $blVerify = $this->getTotp($seed)->verify($totp, null, $this->timeWindow);

        if (false == $blVerify && null == $seed) {
            $oBC = $this->d3GetBackupCodeListObject();
            $blVerify = $oBC->verify($totp);

            if (false == $blVerify) {
                $oException = oxNew(d3totp_wrongOtpException::class);
                throw $oException;
            }
        } elseif (false == $blVerify && $seed) {
            $oException = oxNew(d3totp_wrongOtpException::class);
            throw $oException;
        }

        return $blVerify;
    }

    /**
     * @return d3backupcodelist
     */
    public function d3GetBackupCodeListObject()
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @param $plaintext
     * @return string
     */
    public function encrypt($plaintext)
    {
        $key = Registry::getConfig()->getConfigParam('sConfigKey');
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        return base64_encode($iv.$hmac.$ciphertext_raw);
    }

    /**
     * @param $ciphertext
     * @return bool|string
     */
    public function decrypt($ciphertext)
    {
        $key = Registry::getConfig()->getConfigParam('sConfigKey');
        $c = $this->d3Base64_decode($ciphertext);
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

    /**
     * required for unit tests
     * @param $source
     * @return bool|string
     */
    public function d3Base64_decode($source)
    {
        return base64_decode($source);
    }

    /**
     * @param null $oxid
     * @return bool
     * @throws DatabaseConnectionException
     */
    public function delete($oxid = null)
    {
        $oBackupCodeList = $this->d3GetBackupCodeListObject();
        $oBackupCodeList->deleteAllFromUser($this->getFieldData('oxuserid'));

        $blDelete = parent::delete();

        return $blDelete;
    }
}
