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

use D3\Totp\Application\Controller\Admin\d3user_totp;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3backupcodelist extends ListModel
{
    protected $_sObjectsInListName = d3backupcode::class;

    /** @var string */
    protected $_sCoreTable = 'd3totp_backupcodes';

    protected array $_backupCodes = [];

    /**
     * @param string $sUserId
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function generateBackupCodes(string $sUserId): void
    {
        $this->deleteAllFromUser($sUserId);

        for ($i = 1; $i <= 10; $i++) {
            $oBackupCode = $this->getD3BackupCodeObject();
            $this->_backupCodes[] = $oBackupCode->generateCode($sUserId);
            $this->offsetSet(md5((string) rand()), $oBackupCode);
        }

        /** @var d3user_totp $oActView */
        $oActView = $this->d3GetConfig()->getActiveView();
        $oActView->setBackupCodes($this->_backupCodes);
    }

    /**
     * @return d3backupcode
     */
    public function getD3BackupCodeObject(): d3backupcode
    {
        return oxNew(d3backupcode::class);
    }

    /**
     * @return Config
     */
    public function d3GetConfig(): Config
    {
        return Registry::getConfig();
    }

    /**
     * @throws Exception
     */
    public function save(): void
    {
        /** @var d3backupcode $oBackupCode */
        foreach ($this->getArray() as $oBackupCode) {
            $oBackupCode->save();
        }
    }

    /**
     * @return d3backupcode
     */
    public function getBaseObject(): d3backupcode
    {
        /** @var d3backupcode $object */
        $object = parent::getBaseObject();

        return $object;
    }

    public function verify(string $code): bool
    {
        $bc = $this->verifyArgon2($code);

        if ($bc instanceof d3backupcode) {
            $bc->delete();

            return true;
        }

        return $this->verifyLegacy($code);
    }

    protected function verifyArgon2(string $code): ?d3backupcode
    {
        $this->loadUserArgonBackupCodes();

        /** @var d3backupcode $backupCode */
        foreach ($this->getArray() as $backupCode) {
            if (password_verify($code, $backupCode->getRawFieldData('backupcode'))) {
                return $backupCode;
            }
        }

        return null;
    }

    protected function loadUserArgonBackupCodes(): void
    {
        $qb = $this->getQueryBuilder();
        $qb->select('oxid', 'backupcode')
            ->from($this->getBaseObject()->getViewName())
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'oxuserid',
                        $qb->createNamedParameter($this->d3GetUser()->getId())
                    ),
                    $qb->expr()->eq(
                        'codeversion',
                        $qb->createNamedParameter(d3backupcode::VERSION_ARGON)
                    )
                )
            );

        $this->selectString($qb->getSQL(), $qb->getParameters());
    }

    /**
     * @param string $totp
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @deprecated
     */
    protected function verifyLegacy(string $totp): bool
    {
        $qb = $this->getQueryBuilder();
        $qb->select('oxid')
            ->from($this->getBaseObject()->getViewName())
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'backupcode',
                        'BINARY MD5( CONCAT('.$qb->createNamedParameter($totp).', UNHEX('.$qb->createNamedParameter($this->d3GetUser()->getFieldData('oxpasssalt')).')))'
                    ),
                    $qb->expr()->eq(
                        'oxuserid',
                        $qb->createNamedParameter($this->d3GetUser()->getId())
                    ),
                    $qb->expr()->eq(
                        'codeversion',
                        $qb->createNamedParameter(d3backupcode::VERSION_MD5)
                    )
                )
            );

        $sVerify = $qb->execute()->fetchOne();

        if ($sVerify) {
            $this->getBaseObject()->delete($sVerify);
        }

        return (bool) $sVerify;
    }

    /**
     * @param string $sUserId
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteAllFromUser(string $sUserId): void
    {
        $qb = $this->getQueryBuilder();
        $qb->select('oxid')
            ->from($this->getBaseObject()->getCoreTableName())
            ->where(
                $qb->expr()->eq(
                    'oxuserid',
                    $qb->createNamedParameter($sUserId)
                )
            );

        $this->selectString($qb->getSQL(), $qb->getParameters());

        /** @var d3backupcode $oBackupCode */
        foreach ($this->getArray() as $oBackupCode) {
            $oBackupCode->delete();
        }
    }

    /**
     * @param string $sUserId
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function getAvailableCodeCount(string $sUserId): int
    {
        $qb = $this->getQueryBuilder();
        $qb->select('count(*)')
            ->from($this->getBaseObject()->getViewName())
            ->where(
                $qb->expr()->eq(
                    'oxuserid',
                    $qb->createNamedParameter($sUserId)
                )
            );

        return (int) $qb->execute()->fetchOne();
    }

    public function d3GetUser(): User
    {
        return $this->getBaseObject()->d3GetUser();
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
}
