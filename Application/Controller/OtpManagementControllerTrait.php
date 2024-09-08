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

namespace D3\Totp\Application\Controller;

use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;

trait OtpManagementControllerTrait
{
    /**
     * @param array $aCodes
     */
    public function setBackupCodes(array $aCodes): void
    {
        $this->aBackupCodes = $aCodes;
    }

    /**
     * @return string
     */
    public function getBackupCodes(): string
    {
        return implode(PHP_EOL, $this->aBackupCodes);
    }

    /**
     * @return d3backupcodelist
     */
    public function getBackupCodeListObject(): d3backupcodelist
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @return int
     * @throws DatabaseConnectionException
     */
    public function getAvailableBackupCodeCount(): int
    {
        $oBackupCodeList = $this->getBackupCodeListObject();
        return $oBackupCodeList->getAvailableCodeCount($this->getCurrentUserId());
    }

    /**
     * @return d3totp
     */
    public function getTotpObject(): d3totp
    {
        return oxNew(d3totp::class);
    }
}