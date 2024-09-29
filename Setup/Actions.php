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

namespace D3\Totp\Setup;

use D3\OxidServiceBridges\Internal\Framework\Templating\Cache\ShopTemplateCacheServiceBridge;
use D3\OxidServiceBridges\Internal\Framework\Templating\Cache\ShopTemplateCacheServiceBridgeInterface;
use Exception;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\SeoEncoder;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Actions
{
    public array $seo_de = [
        '2-faktor-authentisierung/',
    ];
    public array $seo_en = [
        'en/2-factor-authentication/',
    ];
    public array $stdClassName = [
        'd3_account_totp',
    ];

    /**
     * @return MigrationsBuilder
     */
    protected function getMigrationsBuilder(): MigrationsBuilder
    {
        return oxNew(MigrationsBuilder::class);
    }

    /**
     * @throws Exception
     */
    public function runModuleMigrations(): void
    {
        $migrationsBuilder = $this->getMigrationsBuilder();
        $migrations = $migrationsBuilder->build();
        $migrations->execute('migrations:migrate', 'd3totp');
    }

    protected function getDbMetaDataHandler(): DbMetaDataHandler
    {
        return oxNew(DbMetaDataHandler::class);
    }

    /**
     * Regenerate views for changed tables
     * @throws Exception
     */
    public function regenerateViews(): void
    {
        $oDbMetaDataHandler = $this->getDbMetaDataHandler();
        $oDbMetaDataHandler->updateViews();
    }

    protected function getUtils(): Utils
    {
        return oxNew(Utils::class);
    }

    protected function getLogger(): LoggerInterface
    {
        return Registry::getLogger();
    }

    /**
     * clear cache
     * @throws Exception
     */
    public function clearCache(): void
    {
        try {
            /** @var ShopTemplateCacheServiceBridge $templateCacheService */
            $templateCacheService = $this->getDIContainer()->get(ShopTemplateCacheServiceBridgeInterface::class);
            $templateCacheService->invalidateCache(Registry::getConfig()->getShopId());
            $oUtils = $this->getUtils();
            $oUtils->resetLanguageCache();
        } catch (ContainerExceptionInterface $e) {
            Registry::getLogger()->error($e->getMessage(), [$this]);
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function seoUrl(): void
    {
        try {
            $seoEncoder = oxNew(SeoEncoder::class);
            if (!$this->hasSeoUrls($seoEncoder)) {
                $this->createSeoUrls($seoEncoder);
            }
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage(), [$this]);
            Registry::getUtilsView()->addErrorToDisplay('error wile creating SEO URLs: ' . $e->getMessage());
        }
    }

    /**
     * @param SeoEncoder $seoEncoder
     * @return bool
     */
    public function hasSeoUrls(SeoEncoder $seoEncoder): bool
    {
        foreach ($this->stdClassName as $item) {
            foreach ([0, 1] as $lang) {
                if (false === $this->hasSeoUrl($seoEncoder, $item, $lang)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function hasSeoUrl(SeoEncoder $seoEncoder, string $item, int $langId): bool
    {
        $seoUrl = $seoEncoder->getStaticUrl(
            oxNew(FrontendController::class)->getViewConfig()->getSelfLink() .
            "cl=" . $item,
            $langId
        );
        return (bool)strlen($seoUrl);
    }

    /**
     * @param SeoEncoder $seoEncoder
     * @return void
     */
    public function createSeoUrls(SeoEncoder $seoEncoder): void
    {
        foreach (array_keys($this->stdClassName) as $id) {
            $objectid = md5(strtolower(Registry::getConfig()->getShopId() . 'index.php?cl=' . $this->stdClassName[$id]));
            if (!$this->hasSeoUrl($seoEncoder, $this->stdClassName[$id], 0)) {
                $seoEncoder->addSeoEntry(
                    $objectid,
                    Registry::getConfig()->getShopId(),
                    0,
                    'index.php?cl=' . $this->stdClassName[$id],
                    $this->seo_de[$id],
                    'static',
                    false
                );
            }
            if (!$this->hasSeoUrl($seoEncoder, $this->stdClassName[$id], 1)) {
                $seoEncoder->addSeoEntry(
                    $objectid,
                    Registry::getConfig()->getShopId(),
                    1,
                    'index.php?cl=' . $this->stdClassName[$id],
                    $this->seo_en[$id],
                    'static',
                    false
                );
            }
        }
    }

    /**
     * @return ContainerInterface|null
     */
    protected function getDIContainer(): ?ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }
}
