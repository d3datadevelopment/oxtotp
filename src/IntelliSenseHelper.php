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

    use OxidEsales\Eshop\Application\Component\UserComponent;

    class d3_totp_UserComponent_parent extends UserComponent { }
}

namespace D3\Totp\Modules\Application\Controller
{

    use OxidEsales\Eshop\Application\Controller\OrderController;
    use OxidEsales\Eshop\Application\Controller\PaymentController;
    use OxidEsales\Eshop\Application\Controller\UserController;

    class d3_totp_UserController_parent extends UserController { }

    class d3_totp_PaymentController_parent extends PaymentController { }

    class d3_totp_OrderController_parent extends OrderController { }
}

namespace D3\Totp\Modules\Application\Controller\Admin
{

    use OxidEsales\Eshop\Application\Controller\Admin\LoginController;

    class d3_totp_LoginController_parent extends LoginController { }
}

namespace D3\Totp\Modules\Application\Model
{

    use OxidEsales\Eshop\Application\Model\User;

    class d3_totp_user_parent extends User { }
}

namespace D3\Totp\Modules\Core
{

    use OxidEsales\Eshop\Core\Utils;

    class d3_totp_utils_parent extends Utils { }
}