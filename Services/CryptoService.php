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

namespace D3\Totp\Services;

use D3\Totp\Application\Model\Constants;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use RuntimeException;

class CryptoService implements CryptoServiceInterface
{
    protected const MASTER_KEY = 'D3_TOTP_MASTER_KEY';
    protected const MASTER_KEY_VERSION = 'D3_TOTP_MASTER_KEY_VERSION';

    public const KEY_VERSION_LEGACY = 1;
    public const KEY_VERSION_MASTER_KEY = 2;

    public function __construct(protected ModuleSettingServiceInterface $settings)
    {
    }

    public function getKey(int $version): string
    {
        return match ($version) {
            self::KEY_VERSION_LEGACY => $this->getLegacyKey(),
            self::KEY_VERSION_MASTER_KEY => $this->getMasterKey(),
            default => throw new RuntimeException(
                "Unknown key version {$version}"
            )
        };
    }

    public function getLegacyKey(): string
    {
        return 'fq45QS09_fqyx09239QQ';
    }

    public function getMasterKey(): string
    {
        if (!trim($this->settings->getString(
            self::MASTER_KEY,
            Constants::OXID_MODULE_ID
        )->toString())) {
            $this->initializeMasterKey();
        }

        return $this->settings
            ->getString(
                self::MASTER_KEY,
                Constants::OXID_MODULE_ID
            )
            ->toString();
    }

    protected function initializeMasterKey(): void
    {
        $this->settings->saveString(
            self::MASTER_KEY,
            base64_encode(random_bytes(32)),
            Constants::OXID_MODULE_ID
        );
        $this->settings->saveInteger(
            self::MASTER_KEY_VERSION,
            self::KEY_VERSION_MASTER_KEY,
            Constants::OXID_MODULE_ID
        );
    }
}