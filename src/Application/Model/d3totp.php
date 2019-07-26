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

use BaconQrCode\Renderer\Image\Svg;
use BaconQrCode\Writer;
use D3\ModCfg\Application\Model\d3database;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use Doctrine\DBAL\DBALException;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use RandomLib\Factory;
use RandomLib\Generator;

class d3totp extends BaseModel
{
    const TOTP_SESSION_VARNAME = 'totp_auth';

    public $tableName = 'd3totp';
    public $userId;
    public $totp;
    protected $timeWindow = 2;
    protected $_backupCodes = array();

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
     * @return array
     */
    public function generateBackupCodes()
    {
        $factory = new Factory();
        $generator = $factory->getLowStrengthGenerator();

        for ($i = 0; $i < 10; $i++) {
            $this->_backupCodes[] = $generator->generateString(6, Generator::CHAR_DIGITS);
        }

        return $this->_backupCodes;
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
        $renderer = oxNew(Svg::class);
        $renderer->setHeight(200);
        $renderer->setWidth(200);

        /** @var Writer $writer */
        $writer = oxNew(Writer::class, $renderer);
        return $writer->writeString($this->getTotp()->getProvisioningUri());
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
            array(
                'seed'  => $this->encrypt($seed)
            )
        );
    }

    /**
     * @param $code
     * @return false|string
     * @throws DatabaseConnectionException
     */
    public function d3EncodeBC($code)
    {
        $oDb = DatabaseProvider::getDb();
        $salt = $this->getUser()->getFieldData('oxpasssalt');
        $sSelect = "SELECT BINARY MD5( CONCAT( " . $oDb->quote($code) . ", UNHEX( ".$oDb->quote($salt)." ) ) )";

        return $oDb->getOne($sSelect);
    }

    public function save()
    {
        $this->assign(
            array(
                'bc1'   => $this->d3EncodeBC($this->_backupCodes[0]),
                'bc2'   => $this->d3EncodeBC($this->_backupCodes[1]),
                'bc3'   => $this->d3EncodeBC($this->_backupCodes[2]),
                'bc4'   => $this->d3EncodeBC($this->_backupCodes[3]),
                'bc5'   => $this->d3EncodeBC($this->_backupCodes[4]),
                'bc6'   => $this->d3EncodeBC($this->_backupCodes[5]),
                'bc7'   => $this->d3EncodeBC($this->_backupCodes[6]),
                'bc8'   => $this->d3EncodeBC($this->_backupCodes[7]),
                'bc9'   => $this->d3EncodeBC($this->_backupCodes[8]),
                'bc10'   => $this->d3EncodeBC($this->_backupCodes[9]),
            )
        );

        return parent::save();
    }

    /**
     * @param $totp
     * @param $seed
     * @return string
     * @throws d3totp_wrongOtpException
     */
    public function verify($totp, $seed = null)
    {
        $blVerify = $this->getTotp($seed)->verify($totp, null, $this->timeWindow);
        if (false == $blVerify) {
            $oException = oxNew(d3totp_wrongOtpException::class);
            throw $oException;
        }

        return $blVerify;
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