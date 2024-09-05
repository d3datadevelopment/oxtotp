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

namespace D3\Totp\Application\Model;

use BaconQrCode\Renderer\RendererInterface;
use BaconQrCode\Writer;
use D3\Totp\Application\Factory\BaconQrCodeFactory;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;

class d3totp extends BaseModel
{
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

        parent::__construct();
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
        $userId = $this->userId ?: $this->getFieldData('oxuserid');

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
     * @return string|null
     */
    public function getSavedSecret(): ?string
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
            $this->totp = TOTP::create($seed ?: $this->getSavedSecret());
            $this->totp->setLabel($this->getUser()->getFieldData('oxusername') ?: '');
            $this->totp->setIssuer(Registry::getConfig()->getActiveShop()->getFieldData('oxname'));
        }

        return $this->totp;
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
                'seed'  => $this->encrypt($seed),
            ]
        );
    }

    /**
     * @param $totp
     * @param $seed
     * @return bool
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function verify($totp, $seed = null)
    {
        $blNotVerified = $this->getTotp($seed)->verify($totp, null, $this->timeWindow) == false;

        if ($blNotVerified && null == $seed) {
            $oBC = $this->d3GetBackupCodeListObject();
            $blNotVerified = $oBC->verify($totp) == false;

            if ($blNotVerified) {
                throw oxNew(d3totp_wrongOtpException::class);
            }
        } elseif ($blNotVerified && $seed !== null) {
            throw oxNew(d3totp_wrongOtpException::class);
        }

        return !$blNotVerified;
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
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
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
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
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
     * @param null|string $oxid
     * @return bool
     * @throws DatabaseConnectionException
     */
    public function delete($oxid = null)
    {
        $oBackupCodeList = $this->d3GetBackupCodeListObject();
        $oBackupCodeList->deleteAllFromUser($this->getFieldData('oxuserid'));

        return parent::delete($oxid);
    }
}
