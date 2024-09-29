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

use D3\Totp\Modules\Application\Model\d3_totp_user;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3backupcode extends BaseModel
{
    protected $_sCoreTable = 'd3totp_backupcodes';

    /**
     * @param string $sUserId
     * @return string
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function generateCode(string $sUserId): string
    {
        $sCode = $this->getRandomTotpBackupCode();
        $this->assign([
            'oxuserid'    => $sUserId,
            'backupcode' => $this->d3EncodeBC($sCode, $sUserId),
        ]);

        return $sCode;
    }

    public function getRandomTotpBackupCode(): string
    {
        return d3RandomGenerator::getRandomTotpBackupCode();
    }

    /**
     * @param string $code
     * @param string $sUserId
     * @return string
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function d3EncodeBC(string $code, string $sUserId): string
    {
        $oUser = $this->d3TotpGetUserObject();
        $oUser->load($sUserId);
        $salt = $oUser->getFieldData('oxpasssalt');

        $qb = $this->getQueryBuilder();
        $qb->select('BINARY MD5( CONCAT('.$qb->createNamedParameter($code).', UNHEX('.$qb->createNamedParameter($salt).')))');

        return $qb->execute()->fetchOne();
    }

    public function d3GetUser(): User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if ($user instanceof User) {
            return $user;
        }

        /** @var d3_totp_user $oUser */
        $oUser = $this->d3TotpGetUserObject();
        $sUserId = $oUser->d3TotpGetCurrentUser();
        $oUser->load($sUserId);
        return $oUser;
    }

    /**
     * @return User
     */
    public function d3TotpGetUserObject(): User
    {
        return oxNew(User::class);
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
