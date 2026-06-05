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
use D3\Totp\Application\Model\Exceptions\tooManyAttemptsException;
use D3\Totp\Application\Model\Exceptions\replayException;
use D3\Totp\Application\Model\Exceptions\totpExceptionInterface;
use D3\Totp\Application\Model\Exceptions\wrongOtpException;
use D3\Totp\Services\CryptoService;
use D3\Totp\Services\CryptoServiceInterface;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use Lcobucci\Clock\SystemClock;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3totp extends BaseModel
{
    public const LOCK_THRESHOLD = 5;
    public const LOCKTIME_IN_SECONDS = 60;

    protected $_sCoreTable = 'd3totp';
    public null|string $userId = null;
    public null|TOTP $totp = null;
    protected int $timeWindow = 2;
    protected CryptoServiceInterface $crypto;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->init($this->getCoreTableName());

        $this->crypto = ContainerFactory::getInstance()->getContainer()->get(CryptoServiceInterface::class);

        parent::__construct();
    }

    /**
     * @param string $userId
     *
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws DBALDriverException
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
     *
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws DBALDriverException
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
     * @param User        $user
     * @param string|null $seed
     *
     * @return TOTP
     * @throws Exception
     */
    public function getTotp(User $user, string $seed = null): TOTP
    {
        if (null == $this->totp) {
            $this->totp = TOTP::create(
                $seed ?: $this->getSavedSecret(),
                TOTP::DEFAULT_PERIOD,
                TOTP::DEFAULT_DIGEST,
                TOTP::DEFAULT_DIGITS,
                TOTP::DEFAULT_EPOCH,
                $this->getClock()
            );
            $this->totp->setLabel($user->getFieldData('oxusername') ?: '');
            $this->totp->setIssuer(Registry::getConfig()->getActiveShop()->getFieldData('oxname'));
        }

        return $this->totp;
    }

    /**
     * @return ClockInterface
     * @throws Exception
     */
    protected function getClock(): ClockInterface
    {
        return new SystemClock(new DateTimeZone('UTC'));
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
    public function setSecret(string $seed): void
    {
        $this->assign([
            'seed'  => $this->encrypt($seed),
        ]);
    }

    /**
     * @param string|null $seed
     *
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     * @throws totpExceptionInterface
     * @throws Exception
     */
    public function verify(User $user, string $totp, string $totpBc, string $seed = null): bool
    {
        $clock = $this->getClock();
        $timestamp = $clock->now()->getTimestamp();
        $acceptedSlice = floor($timestamp / 30);

        $this->assertReplayProtection($acceptedSlice);
        $this->assertNotLocked();

        $verified = $this->getTotp($user, $seed)->verify($totp, $timestamp, $this->timeWindow);

        if ($verified) {
            $this->assign([
                'lastacceptedtimeslice' => $acceptedSlice,
            ]);
            $this->resetFailedAttempts();

            return true;
        }

        if (null == $seed) {
            $verified = $this->d3GetBackupCodeListObject()->verify($totpBc);

            if ($verified) {
                $this->resetFailedAttempts();
                return true;
            }
        }

        $this->registerFailedAttempt();

        throw oxNew(wrongOtpException::class);
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
            $this->setSecret($result);
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
        $legacyCalcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);

        if (hash_equals($hmac, $calcmac) || hash_equals($hmac, $legacyCalcmac)) {
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

    protected function assertReplayProtection(float $currentSlice): void
    {
        $lastAcceptedTimeSlice = $this->getFieldData('lastacceptedtimeslice');

        if ($lastAcceptedTimeSlice && $currentSlice <= $lastAcceptedTimeSlice) {
            throw oxNew(replayException::class);
        }
    }

    protected function assertNotLocked(): void
    {
        $lockedUntil = $this->getFieldData('lockeduntil');

        if ($lockedUntil && strtotime($lockedUntil) > time()) {
            throw oxNew(tooManyAttemptsException::class);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function resetFailedAttempts(): void
    {
        if (!$this->getId()) return;

        $this->assign([
            'failedattempts' => 0,
            'lockeduntil' => null,
        ]);

        $this->save();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function registerFailedAttempt(): void
    {
        if (!$this->getId()) return;

        $failedAttempts =
            (int) $this->getFieldData('failedattempts') + 1;

        $data = [
            'failedattempts' => $failedAttempts,
        ];

        if ($failedAttempts >= self::LOCK_THRESHOLD) {
            $data['lockeduntil'] = date(
                'Y-m-d H:i:s',
                time() + self::LOCKTIME_IN_SECONDS
            );
        }

        $this->assign($data);
        $this->save();
    }
}
