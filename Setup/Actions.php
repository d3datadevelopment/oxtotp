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

use Doctrine\DBAL\Driver\Exception as DoctrineDriverException;
use Exception;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\SeoEncoder;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

class Actions
{
    public array $seo_de = [
        '2-faktor-authentisierung/'
    ];
    public array $seo_en = [
        'en/2-factor-authentication/'
    ];
    public array $stdClassName = [
        'd3_account_totp'
    ];

    /**
     * @throws Exception
     */
    public function runModuleMigrations(): void
    {
        /** @var MigrationsBuilder $migrationsBuilder */
        $migrationsBuilder = oxNew(MigrationsBuilder::class);
        $migrations = $migrationsBuilder->build();
        $migrations->execute('migrations:migrate', 'd3totp');
    }

    /**
     * Regenerate views for changed tables
     * @throws Exception
     */
    public function regenerateViews(): void
    {
        $oDbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $oDbMetaDataHandler->updateViews();
    }

    /**
     * clear cache
     * @throws Exception
     */
    public function clearCache(): void
    {
        try {
            $oUtils = oxNew(Utils::class);
            $oUtils->resetTemplateCache($this->getModuleTemplates());
            $oUtils->resetLanguageCache();
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface|ModuleConfigurationNotFoundException $e) {
            oxNew(LoggerInterface::class)->error($e->getMessage(), [$this]);
            oxNew(UtilsView::class)->addErrorToDisplay($e->getMessage());
        }
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ModuleConfigurationNotFoundException
     */
    protected function getModuleTemplates(): array
    {
        $container = $this->getDIContainer();
        $shopConfiguration = $container->get(ShopConfigurationDaoBridgeInterface::class)->get();
        $moduleConfiguration = $shopConfiguration->getModuleConfiguration('d3totp');

        return array_unique(array_merge(
            $this->getModuleTemplatesFromTemplates($moduleConfiguration),
            $this->getModuleTemplatesFromBlocks($moduleConfiguration)
        ));
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     *
     * @return array
     */
    protected function getModuleTemplatesFromTemplates(ModuleConfiguration $moduleConfiguration): array
    {
        /** @var $template ModuleConfiguration\Template */
        return array_map(
            function ($template) {
                return $template->getTemplateKey();
            },
            $moduleConfiguration->getTemplates()
        );
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     *
     * @return array
     */
    protected function getModuleTemplatesFromBlocks(ModuleConfiguration $moduleConfiguration): array
    {
        /** @var $templateBlock ModuleConfiguration\TemplateBlock */
        return array_map(
            function ($templateBlock) {
                return basename($templateBlock->getShopTemplatePath());
            },
            $moduleConfiguration->getTemplateBlocks()
        );
    }

    /**
     * @return void
     */
    public function seoUrl()
    {
        try {
            if (!$this->hasSeoUrls()) {
                $this->createSeoUrls();
            }
        } catch (Exception|NotFoundExceptionInterface|DoctrineDriverException|ContainerExceptionInterface $e) {
            Registry::getLogger()->error($e->getMessage(), [$this]);
            Registry::getUtilsView()->addErrorToDisplay('error wile creating SEO URLs: ' . $e->getMessage());
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasSeoUrls(): bool
    {
        foreach ($this->stdClassName as $item) {
            foreach ([0, 1] as $lang) {
                if (false === $this->hasSeoUrl($item, $lang)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function hasSeoUrl($item, $langId): bool
    {
        $seoEncoder = oxNew(SeoEncoder::class);
        $seoUrl = $seoEncoder->getStaticUrl(
            oxNew(FrontendController::class)->getViewConfig()->getSelfLink() .
            "cl=" . $item,
            $langId
        );

        return (bool)strlen($seoUrl);
    }

    /**
     * @return void
     */
    public function createSeoUrls()
    {
        foreach (array_keys($this->stdClassName) as $id) {
            $seoEncoder = oxNew(SeoEncoder::class);
            $objectid = md5(strtolower(Registry::getConfig()->getShopId() . $this->seo_de[$id]));
            if (!$this->hasSeoUrl($this->stdClassName[$id], 0)) {
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
            if (!$this->hasSeoUrl($this->stdClassName[$id], 0)) {
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
