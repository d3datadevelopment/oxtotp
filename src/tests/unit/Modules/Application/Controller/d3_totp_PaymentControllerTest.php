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

namespace D3\Totp\tests\unit\Modules\Application\Controller;

use D3\Totp\Modules\Application\Controller\d3_totp_PaymentController;
use D3\Totp\tests\unit\d3TotpUnitTestCase;

class d3_totp_PaymentControllerTest extends d3TotpUnitTestCase
{
    use d3_totp_getUserTestTrait;

    /** @var d3_totp_PaymentController */
    protected $_oController;

    protected $sControllerClass = d3_totp_PaymentController::class;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oController = oxNew(d3_totp_PaymentController::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->_oController);
    }
}