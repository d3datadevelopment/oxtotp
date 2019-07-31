<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      http://www.oxidmodule.com
 */

namespace D3\Totp\Modules\Application\Component
{
    class d3_totp_UserComponent_parent extends \OxidEsales\Eshop\Application\Component\UserComponent { }
}

namespace D3\Totp\Modules\Application\Controller
{
    class d3_totp_UserController_parent extends \OxidEsales\Eshop\Application\Controller\UserController { }

    class d3_totp_PaymentController_parent extends \OxidEsales\Eshop\Application\Controller\PaymentController { }

    class d3_totp_OrderController_parent extends \OxidEsales\Eshop\Application\Controller\OrderController { }
}

namespace D3\Totp\Modules\Application\Controller\Admin
{
    class d3_totp_LoginController_parent extends \OxidEsales\Eshop\Application\Controller\Admin\LoginController { }
}

namespace D3\Totp\Modules\Application\Model
{
    class d3_totp_user_parent extends \OxidEsales\Eshop\Application\Model\User { }
}

namespace D3\Totp\Modules\Core
{
    class d3_totp_utils_parent extends \OxidEsales\Eshop\Core\Utils { }
}