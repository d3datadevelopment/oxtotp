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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3totp extends BaseModel
{
    protected const ENC_KEY = 'fq45QS09_fqyx09239QQ';

    public string $tableName = 'd3totp';
    public null|string $userId = null;
    public null|TOTP $totp = null;
    protected int $timeWindow = 2;

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws DBALException
     */
    public function loadByUserId($userId): void
    {
        $this->userId = $userId;

        if ($this->getDbConnection()
            ->prepare("SHOW TABLES LIKE ".$this->getDbConnection()->quote($this->tableName))
            ->executeQuery()
            ->fetchOne()
        ) {
            /** @var QueryBuilder $qb */
            $qb = $this->getQueryBuilder();
            $qb->select('oxid')
                ->from($this->getViewName())
                ->where(
                    $qb->expr()->eq('oxuserid', $qb->createNamedParameter($userId))
                )
                ->setMaxResults(1);
            $this->load($qb->execute()->fetchOne());
        }
    }

    /**
     * @param $userId
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function checkIfAlreadyExist($userId): bool
    {
        $qb = $this->getQueryBuilder();
        $qb->select('1')
            ->from($this->getViewName())
            ->where(
                $qb->expr()->eq('oxuserid', $qb->createNamedParameter($userId))
            )
            ->setMaxResults(1);
        return (bool) $qb->execute()->fetchOne();
    }

    /**
     * @return Connection
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDbConnection(): Connection
    {
        return ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get();
    }

    /**
     * @return QueryBuilder
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        $userId = $this->userId ?? $this->getFieldData('oxuserid');

        $user = $this->d3GetUser();
        $user->load($userId);
        return $user;
    }

    /**
     * @return User
     */
    public function d3GetUser(): User
    {
        return oxNew(User::class);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return false == Registry::getConfig()->getConfigParam('blDisableTotpGlobally')
            && $this->UserUseTotp();
    }

    /**
     * @return bool
     */
    public function UserUseTotp(): bool
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
    public function getTotp($seed = null): TOTP
    {
        if (null == $this->totp) {
            $this->totp = TOTP::create($seed ?: $this->getSavedSecret());
            $this->totp->setLabel($this->getUser()->getFieldData('oxusername') ?: '');
            $this->totp->setIssuer(Registry::getConfig()->getActiveShop()->getFieldData('oxname'));
        }

        return $this->totp;
    }

    /**
     * @return string
     */
    public function getQrCodeElement(): string
    {
        $renderer = BaconQrCodeFactory::renderer(200);
        $writer = $this->d3GetWriter($renderer);

        return $writer->writeString($this->getTotp()->getProvisioningUri());
    }

    /**
     * @param RendererInterface $renderer
     * @return Writer
     */
    public function d3GetWriter(RendererInterface $renderer): Writer
    {
        return oxNew(Writer::class, $renderer);
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return trim($this->getTotp()->getSecret());
    }

    /**
     * @param $seed
     */
    public function saveSecret($seed): void
    {
        $this->assign([
            'seed'  => $this->encrypt($seed),
        ]);
    }

    /**
     * @param $totp
     * @param $seed
     * @return bool
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function verify($totp, $seed = null): bool
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
    public function d3GetBackupCodeListObject(): d3backupcodelist
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @param $plaintext
     * @return string
     */
    public function encrypt($plaintext): string
    {
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, self::ENC_KEY, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, self::ENC_KEY, true);
        return base64_encode($iv.$hmac.$ciphertext_raw);
    }

    /**
     * @param $ciphertext
     * @return false|string
     */
    public function decrypt($ciphertext): false|string
    {
        $c = $this->d3Base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, self::ENC_KEY, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, self::ENC_KEY, true);
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
    public function d3Base64_decode($source): bool|string
    {
        return base64_decode($source);
    }

    /**
     * @param null|string $oxid
     * @return bool
     * @throws DatabaseConnectionException
     */
    public function delete($oxid = null): bool
    {
        $oBackupCodeList = $this->d3GetBackupCodeListObject();
        $oBackupCodeList->deleteAllFromUser($this->getFieldData('oxuserid'));

        return parent::delete($oxid);
    }
}
