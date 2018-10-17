<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      http://www.oxidmodule.com
 */

use D3\Extsearch\setup as ModuleSetup;
use D3\ModCfg\Application\Model\d3utils;
use OxidEsales\Eshop\Application\Controller\Admin\LoginController;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\VisualCmsModule\Application\Controller\Admin\VisualCmsAdmin as VisualCMSAdmin;
use OxidEsales\Eshop\Application\Controller as OxidController;
use OxidEsales\Eshop\Application\Model as OxidModel;
use OxidEsales\Eshop\Application\Component as OxidComponent;
use OxidEsales\Eshop\Core as OxidCore;

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

$sModuleId = 'd3totp';
/**
 * Module information
 */
$aModule = array(
    'id'          => $sModuleId,
    'title'       =>
        (class_exists(d3utils::class) ? d3utils::getInstance()->getD3Logo() : 'D&sup3;') . ' Erweiterte Suche / Extended Search',
    'description' => array(
        'de' => 'Stellt fehlertolerante Suche und weitere Filterm&ouml;glichkeiten zur Verf&uuml;gung.<br>Aktivieren Sie die Moduleintr&auml;ge bitte immer und steuern Sie die Modulaktivit&auml;t ausschlie&szlig;lich im Adminbereich des Moduls.',
        'en' => '',
    ),
    'thumbnail'   => 'picture.png',
    'version'     => '6.1.2.0',
    'author'      => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'       => 'support@shopmodule.com',
    'url'         => 'http://www.oxidmodule.com/',
    'extend'      => array(
        OxidModel\User::class              => \D3\Totp\Modules\Application\Model\d3_totp_user::class,
        LoginController::class             => \D3\Extsearch\Modules\Application\Controller\Admin\d3_article_list_extsearch::class,
            // render
        Utils::class                        => \D3\Extsearch\Modules\Application\Controller\d3_details_extsearch::class,
    ),
    'controllers'   => array(
        'd3_cfg_extsearch'                  => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch::class,
        'd3_cfg_extsearch_list'             => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch_list::class,
        'd3_cfg_extsearch_main'             => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch_main::class,
        'd3_cfg_extsearch_navigation'       => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch_navigation::class,
        'd3_cfg_extsearch_quicksearch'      => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch_quicksearch::class,
        'd3_cfg_extsearch_plugins'          => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch_plugins::class,
        'd3_cfg_extsearch_licence'          => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch_licence::class,

        'd3_cfg_extsearchstat'              => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchstat::class,
        'd3_cfg_extsearchstat_list'         => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchstat_list::class,
        'd3_cfg_extsearch_statistik'        => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearch_statistik::class,

        'd3_cfg_extsearchsyneditor'         => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchsyneditor::class,
        'd3_cfg_extsearchsyneditor_list'    => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchsyneditor_list::class,
        'd3_cfg_extsearchsyneditor_main'    => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchsyneditor_main::class,
        'd3_cfg_extsearchsyneditor_manage'  => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchsyneditor_manage::class,

        'd3_cfg_extsearchlog'               => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchlog::class,
        'd3_cfg_extsearchlog_list'          => \D3\Extsearch\Application\Controller\Admin\d3_cfg_extsearchlog_list::class,

        'd3_extsearch_response'             => \D3\Extsearch\Application\Controller\d3_extsearch_response::class,
    ),
    'templates'   => array(
        'd3_cfg_extsearch_main.tpl'                 => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearch_main.tpl',
        'd3_cfg_extsearch_main_sortanalysis.tpl'    => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearch_main_sortanalysis.tpl',
        'd3_cfg_extsearch_navigation.tpl'           => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearch_navigation.tpl',
        'd3_cfg_extsearch_plugins.tpl'              => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearch_plugins.tpl',
        'd3_cfg_extsearch_quicksearch.tpl'          => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearch_quicksearch.tpl',
        'd3_cfg_extsearch_statistik.tpl'            => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearch_statistik.tpl',
        'd3_cfg_extsearchsyneditor_list.tpl'        => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearchsyneditor_list.tpl',
        'd3_cfg_extsearchsyneditor_main.tpl'        => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearchsyneditor_main.tpl',
        'd3_cfg_extsearchsyneditor_manage.tpl'      => 'd3/extsearch/Application/views/admin/tpl/d3_cfg_extsearchsyneditor_manage.tpl',
        'd3_extsearch_report_hitless.tpl'           => 'd3/extsearch/Application/views/admin/tpl/reports/d3_extsearch_report_hitless.tpl',
        'd3_extsearch_report_mostsearches.tpl'      => 'd3/extsearch/Application/views/admin/tpl/reports/d3_extsearch_report_mostsearches.tpl',
        'd3_extsearch_plugin.tpl'                   => 'd3/extsearch/Application/views/admin/tpl/d3_extsearch_plugin.tpl',
        'd3_extsearch_popup.tpl'                    => 'd3/extsearch/Application/views/admin/tpl/d3_extsearch_popup.tpl',

        'd3_ext_search_suggestsearch.tpl'           => 'd3/extsearch/Application/views/tpl/d3_ext_search_suggestsearch.tpl',

        'd3_ext_search_highlight.tpl'               => 'd3/extsearch/Application/views/tpl/d3_ext_search_highlight.tpl',

        'd3_ext_search_filter.tpl'                  => 'd3/extsearch/Application/views/tpl/d3_ext_search_filter.tpl',
        'd3_inc_ext_search_azure.tpl'               => 'd3/extsearch/Application/views/tpl/azure/d3_inc_ext_search.tpl',
        'd3_list_filters_azure.tpl'                 => 'd3/extsearch/Application/views/tpl/azure/d3extsearch_alist_filters.tpl',
        'd3_search_contents_flow.tpl'               => 'd3/extsearch/Application/views/tpl/flow/d3_search_contents.tpl',
        'd3_search_filters_flow.tpl'                => 'd3/extsearch/Application/views/tpl/flow/d3_search_filters.tpl',
        'd3_list_filters_flow.tpl'                  => 'd3/extsearch/Application/views/tpl/flow/d3_list_filters.tpl',
        'd3_inc_ext_search_mobile.tpl'              => 'd3/extsearch/Application/views/tpl/mobile/d3_inc_ext_search.tpl',
        'd3_list_filters_mobile.tpl'                => 'd3/extsearch/Application/views/tpl/mobile/d3extsearch_alist_filters.tpl',

        'd3_ext_search_filter_category.tpl'         => 'd3/extsearch/Application/views/tpl/filterelements/category.tpl',
        'd3_ext_search_filter_vendor.tpl'           => 'd3/extsearch/Application/views/tpl/filterelements/vendor.tpl',
        'd3_ext_search_filter_manufacturer.tpl'     => 'd3/extsearch/Application/views/tpl/filterelements/manufacturer.tpl',
        'd3_ext_search_filter_attribute.tpl'        => 'd3/extsearch/Application/views/tpl/filterelements/attribute.tpl',
        'd3_ext_search_filter_priceselector.tpl'    => 'd3/extsearch/Application/views/tpl/filterelements/priceselector.tpl',
        'd3_ext_search_filter_jqslider.tpl'         => 'd3/extsearch/Application/views/tpl/filterelements/jqslider.tpl',

        'd3_ddeovisualcmsadmin_extsearch.tpl'       => 'd3/extsearch/Application/views/tpl/d3_ddoevisualcmsadmin_extsearch.tpl',
    ),
    'events'      => [
        'onActivate'    => '\D3\Extsearch\setup\Events::onActivate',
        'onDeactivate'  => '\D3\Extsearch\setup\Events::onDeactivate',
    ],
    'settings' => array(
        array(
            'group'     => 'd3thememapping_module',
            'name'      => 'd3custParentThemeMappedToFlow_'.$sModuleId,
            'type'      => 'str',
            'value'     => ''
        ),
        array(
            'group'     => 'd3thememapping_module',
            'name'      => 'd3custParentThemeMappedToMobile_'.$sModuleId,
            'type'      => 'str',
            'value'     => ''
        ),
        array(
            'group'     => 'd3thememapping_module',
            'name'      => 'd3custParentThemeMappedToAzure_'.$sModuleId,
            'type'      => 'str',
            'value'     => ''
        ),
    ),
    'blocks'      => array(
        array(
            'template'  => 'page/search/search.tpl',
            'block'     => 'search_results',
            'file'      => 'Application/views/blocks/page/search/d3_inc_ext_search.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'layout/base.tpl',
            'block'     => 'head_css',
            'file'      => 'Application/views/blocks/layout/d3_extsearch_css.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'layout/base.tpl',
            'block'     => 'base_js',
            'file'      => 'Application/views/blocks/layout/d3_extsearch_js.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/header/search.tpl',
            'block'     => 'widget_header_search_form',
            'file'      => 'Application/views/blocks/widget/header/d3_extsearch_headersearch.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/header/search.tpl',
            'block'     => 'header_search_field',
            'file'      => 'Application/views/blocks/widget/header/d3_extsearch_searchfield.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'page/list/list.tpl',
            'block'     => 'page_list_listbody',
            'file'      => 'Application/views/blocks/page/list/d3extsearch_alist_noartfilters.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'page/list/list.tpl',
            'block'     => 'page_list_listhead',
            'file'      => 'Application/views/blocks/page/list/d3extsearch_alist_filters.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/locator/attributes.tpl',
            'block'     => 'widget_locator_attributes',
            'file'      => 'Application/views/blocks/widget/locator/d3_list_disabledefaultfilters.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/product/listitem_infogrid.tpl',
            'block'     => 'widget_product_listitem_infogrid_titlebox',
            'file'      => 'Application/views/blocks/widget/product/d3_extsearch_listiteminfogrid_title.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/product/listitem_grid.tpl',
            'block'     => 'widget_product_listitem_grid',
            'file'      => 'Application/views/blocks/widget/product/d3_extsearch_listitemgrid_title.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/product/listitem_grid.tpl',
            'block'     => 'widget_product_listitem_infogrid_titlebox',
            'file'      => 'Application/views/blocks/widget/product/d3_extsearch_listitemgrid_flowtitlebox.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/product/listitem_line.tpl',
            'block'     => 'widget_product_listitem_line_titlebox',
            'file'      => 'Application/views/blocks/widget/product/d3_extsearch_listitemline_flowtitlebox.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/product/listitem_line.tpl',
            'block'     => 'widget_product_listitem_line_selections',
            'file'      => 'Application/views/blocks/widget/product/d3_extsearch_listitemline_selections.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'widget/product/listitem_line.tpl',
            'block'     => 'widget_product_listitem_line_description',
            'file'      => 'Application/views/blocks/widget/product/d3_extsearch_listitemline_description.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'content_main.tpl',
            'block'     => 'admin_content_main_form',
            'file'      => 'Application/views/admin/blocks/d3_extsearch_content_main.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'attribute_main.tpl',
            'block'     => 'admin_attribute_main_form',
            'file'      => 'Application/views/admin/blocks/d3_extsearch_attribute_main.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'include/category_main_form.tpl',
            'block'     => 'admin_category_main_form',
            'file'      => 'Application/views/admin/blocks/d3_extsearch_category_main.tpl',
            'position'  => 1,
        ),
        array(
            'template'  => 'article_extend.tpl',
            'block'     => 'admin_article_extend_form',
            'file'      => 'Application/views/admin/blocks/d3_extsearch_article_extend.tpl',
            'position'  => 1,
        ),
    ),
    'd3FileRegister'    => array(
        'd3/extsearch/IntelliSenseHelper.php',
        'd3/extsearch/metadata.php',
        'd3/extsearch/core/smarty/plugins/function.d3_extsearch_highlight.php',
        'd3/extsearch/Application/Model/d3_phonetic_de.php',
        'd3/extsearch/Application/Model/d3_phonetic_de_voc.php',
        'd3/extsearch/public/d3_extsearch_response.php',
        'd3/extsearch/Application/translations/de/d3_extsearch_lang.php',
        'd3/extsearch/Application/translations/en/d3_extsearch_lang.php',
        'd3/extsearch/Application/views/admin/de/d3_extsearch_lang.php',
        'd3/extsearch/Application/views/admin/de/module_options.php',
        'd3/extsearch/Application/views/admin/en/d3_extsearch_lang.php',
        'd3/extsearch/Application/views/admin/en/module_options.php',
        'd3/extsearch/setup/d3_extsearch_semanticstructure.php',
        'd3/extsearch/setup/d3_extsearch_semantic_synset.php',
        'd3/extsearch/setup/d3_extsearch_semantic_term.php',
        'd3/extsearch/setup/d3_extsearch_statisticlog.php',

        'd3/extsearch/Application/Controller/d3_extsearch_response.php',
        'd3/extsearch/Application/Controller/d3_xlist_extsearch.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch_navigation.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch_list.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch_main.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch_licence.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchsyneditor.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchsyneditor_list.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchlog.php',
        'd3/extsearch/Application/Controller/Admin/Reports/d3_extsearch_report_hitless.php',
        'd3/extsearch/Application/Controller/Admin/Reports/d3_extsearch_report_base.php',
        'd3/extsearch/Application/Controller/Admin/Reports/d3_extsearch_report_mostsearches.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchlog_list.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchsyneditor_manage.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchstat.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchstat_list.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch_plugins.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch_quicksearch.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearchsyneditor_main.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch_statistik.php',
        'd3/extsearch/Application/Controller/Admin/d3_cfg_extsearch.php',
        'd3/extsearch/Application/Model/d3_extsearch_statisticlog.php',
        'd3/extsearch/Application/Model/d3_oxutils_extsearch.php',
        'd3/extsearch/Application/Model/d3_semantic.php',
        'd3/extsearch/Application/Model/d3_search.php',
        'd3/extsearch/Application/Model/d3_extsearch_term.php',
        'd3/extsearch/Application/Model/d3_extsearch_synset.php',
        'd3/extsearch/Application/Model/d3_search_generator.php',
        'd3/extsearch/Application/Model/Filters/d3Filter.php',
        'd3/extsearch/Application/Model/Filters/d3FieldIsFilter.php',
        'd3/extsearch/Application/Model/Filters/d3VendorFilter.php',
        'd3/extsearch/Application/Model/Filters/d3FilterList.php',
        'd3/extsearch/Application/Model/Filters/d3IndexFilter.php',
        'd3/extsearch/Application/Model/Filters/d3AttributeFilter.php',
        'd3/extsearch/Application/Model/Filters/d3ManufacturerFilter.php',
        'd3/extsearch/Application/Model/Filters/d3CategoryFilter.php',
        'd3/extsearch/Application/Model/Filters/d3FieldLikeFilter.php',
        'd3/extsearch/Application/Model/Filters/d3PriceFilter.php',
        'd3/extsearch/Application/Model/Filters/d3FilterInterface.php',
        'd3/extsearch/setup/d3_extsearch_update.php',
        'd3/extsearch/Core/d3_extsearch_conf.php',

        'd3/extsearch/Modules/Application/Component/d3_oxcmp_basket_extsearch.php',
        'd3/extsearch/Modules/Application/Component/d3_oxcmp_utils_extsearch.php',
        'd3/extsearch/Modules/Application/Component/d3_oxwarticledetails_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/d3_manufacturerlist_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/d3_vendorlist_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/d3_oxlocator_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/d3_alist_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/d3_details_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/d3_rss_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/Admin/d3_article_list_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/Admin/d3_ddoevisualcmsadmin_extsearch.php',
        'd3/extsearch/Modules/Application/Controller/d3_ext_search.php',
        'd3/extsearch/Modules/Application/Model/d3_oxsearch_extsearch.php',
        'd3/extsearch/Modules/Application/Model/d3_oxarticlelist_extsearch.php',
        'd3/extsearch/Modules/Application/Model/d3_oxarticle_phonetic.php',
        'd3/extsearch/Modules/Application/Model/d3_oxrssfeed_extsearch.php',
        'd3/extsearch/Modules/Core/d3_oxutilsview_extsearch.php',

        'd3/extsearch/setup/Events.php',
    ),
    'd3SetupClasses'    => array(
        ModuleSetup\d3_extsearch_update::class,
    ),
);

if (class_exists(VisualCMSAdmin::class)) {
    $aModule['extend'][VisualCmsAdmin::class] = \D3\Extsearch\Modules\Application\Controller\Admin\d3_ddoevisualcmsadmin_extsearch::class;
}

if (class_exists(OeStatistics_Report_Base::class)) {
    $aModule['controllers']['d3_extsearch_report_base']         = \D3\Extsearch\Application\Controller\Admin\Reports\d3_extsearch_report_base::class;
    $aModule['controllers']['d3_extsearch_report_hitless']      = \D3\Extsearch\Application\Controller\Admin\Reports\d3_extsearch_report_hitless::class;
    $aModule['controllers']['d3_extsearch_report_mostsearches'] = \D3\Extsearch\Application\Controller\Admin\Reports\d3_extsearch_report_mostsearches::class;
}
