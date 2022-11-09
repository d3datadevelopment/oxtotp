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

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;

class d3backupcode extends BaseModel
{
    protected $_sCoreTable = 'd3totp_backupcodes';

    /**
     * @param $sUserId
     * @return string
     * @throws DatabaseConnectionException
     */
    public function generateCode($sUserId)
    {
        $sCode = $this->getRandomTotpBackupCode();
        $this->assign(
            [
                'oxuserid'    => $sUserId,
                'backupcode' => $this->d3EncodeBC($sCode, $sUserId),
            ]
        );

        return $sCode;
    }

    public function getRandomTotpBackupCode()
    {
        return d3RandomGenerator::getRandomTotpBackupCode();
    }

    /**
     * @param $code
     * @param $sUserId
     * @return false|string
     * @throws DatabaseConnectionException
     */
    public function d3EncodeBC($code, $sUserId)
    {
        $oDb = DatabaseProvider::getDb();
        $oUser = $this->d3TotpGetUserObject();
        $oUser->load($sUserId);
        $salt = $oUser->getFieldData('oxpasssalt');
        $sSelect = "SELECT BINARY MD5( CONCAT( " . $oDb->quote($code) . ", UNHEX( ".$oDb->quote($salt)." ) ) )";

        return $oDb->getOne($sSelect);
    }

    public function d3GetUser()
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if ($user instanceof User) {
            return $this->getUser();
        }

        $sUserId = Registry::getSession()->getVariable(d3totp::TOTP_SESSION_CURRENTUSER);
        $oUser = oxNew(User::class);
        $oUser->load($sUserId);
        return $oUser;
    }

    /**
     * @return User
     */
    public function d3TotpGetUserObject()
    {
        return oxNew(User::class);
    }
}
