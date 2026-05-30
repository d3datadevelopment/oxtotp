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
use D3\Totp\Services\CryptoService;
use D3\Totp\Services\CryptoServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class d3totp extends BaseModel
{
    protected $_sCoreTable = 'd3totp';
    public null|string $userId = null;
    public null|TOTP $totp = null;
    protected int $timeWindow = 2;
    protected CryptoServiceInterface $crypto;

    /**
     * d3totp constructor.
     */
    public function __construct()
    {
        $this->init($this->getCoreTableName());

        $this->crypto = ContainerFactory::getInstance()->getContainer()->get(CryptoServiceInterface::class);

        parent::__construct();
    }

    /**
     * @param string $userId
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function loadByUserId(string $userId): void
    {
        $this->userId = $userId;

        if ($this->getDbConnection()
            ->prepare("SHOW TABLES LIKE ".$this->getDbConnection()->quote($this->getCoreTableName()))
            ->executeQuery()
            ->fetchOne()
        ) {
            $qb = $this->getQueryBuilder();
            $qb->select('oxid')
                ->from($this->getViewName())
                ->where(
                    $qb->expr()->eq('oxuserid', $qb->createNamedParameter($userId))
                )
                ->setMaxResults(1);

            if ($oxid = $qb->execute()->fetchOne()) {
                $this->load($oxid);
            }
        }
    }

    /**
     * @param string $userId
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function checkIfAlreadyExist(string $userId): bool
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
     * @deprecated
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
     * @param string|null $seed
     * @return TOTP
     */
    public function getTotp(User $user, string $seed = null): TOTP
    {
        if (null == $this->totp) {
            $this->totp = TOTP::create($seed ?: $this->getSavedSecret());
            $this->totp->setLabel($user->getFieldData('oxusername') ?: '');
            $this->totp->setIssuer(Registry::getConfig()->getActiveShop()->getFieldData('oxname'));
        }

        return $this->totp;
    }

    /**
     * @return string
     */
    public function getQrCodeElement(User $user): string
    {
        $renderer = BaconQrCodeFactory::renderer(200);
        $writer = $this->d3GetWriter($renderer);

        return $writer->writeString($this->getTotp($user)->getProvisioningUri());
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
    public function getSecret(User $user): string
    {
        return trim($this->getTotp($user)->getSecret());
    }

    /**
     * @param string $seed
     */
    public function saveSecret(string $seed): void
    {
        $this->assign([
            'seed'  => $this->encrypt($seed),
        ]);
    }

    /**
     * @param string $totp
     * @param string|null $seed
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws d3totp_wrongOtpException
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function verify(User $user, string $totp, string $seed = null): bool
    {
        $blNotVerified = $this->getTotp($user, $seed)->verify($totp, null, $this->timeWindow) == false;

        if ($blNotVerified && null == $seed) {
            $oBC = $this->d3GetBackupCodeListObject();
            $blNotVerified = $oBC->verify($totp) == false;

            if ($blNotVerified) {
                /** @var d3totp_wrongOtpException $exception */
                $exception = oxNew(d3totp_wrongOtpException::class);
                throw $exception;
            }
        } elseif ($blNotVerified && $seed !== null) {
            /** @var d3totp_wrongOtpException $exception */
            $exception = oxNew(d3totp_wrongOtpException::class);
            throw $exception;
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
     * @param string $plaintext
     * @return string
     */
    public function encrypt(string $plaintext): string
    {
        $key = $this->crypto->getKey(CryptoService::KEY_VERSION_MASTER_KEY);

        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $iv.$ciphertext_raw, $key, true);
        return base64_encode($iv.$hmac.$ciphertext_raw);
    }

    /**
     * @param string $ciphertext
     * @return false|string
     */
    public function decrypt(string $ciphertext): false|string
    {
        $result = $this->decryptWithKey(
            $ciphertext,
            $this->crypto->getKey(CryptoService::KEY_VERSION_MASTER_KEY)
        );

        if (null !== $result) {
            return $result;
        }

        // Legacy-Fallback
        $result = $this->decryptWithKey(
            $ciphertext,
            $this->crypto->getKey(CryptoService::KEY_VERSION_LEGACY)
        );

        if (null !== $result) {
            $this->assign(['seed' => $this->encrypt($result)]);
            $this->save();

            return $result;
        }

        return false;
    }

    protected function decryptWithKey(string $ciphertext, string $key): ?string
    {
        $payload = $this->d3Base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($payload, 0, $ivlen);
        $hmac = substr($payload, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($payload, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $iv.$ciphertext_raw, $key, true);
        if (hash_equals($hmac, $calcmac)) { // PHP 5.6+ compute attack-safe comparison
            return $original_plaintext;
        }

        return null;
    }

    /**
     * required for unit tests
     * @param string $source
     * @return string
     */
    public function d3Base64_decode(string $source): string
    {
        return base64_decode($source);
    }

    /**
     * @param string|null $oxid
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function delete($oxid = null): bool
    {
        $oBackupCodeList = $this->d3GetBackupCodeListObject();
        $oBackupCodeList->deleteAllFromUser($this->getFieldData('oxuserid'));

        return parent::delete($oxid);
    }
}
