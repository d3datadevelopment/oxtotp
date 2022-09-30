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

namespace D3\Totp\tests\unit\Modules\Application\Controller;

use D3\Totp\Modules\Application\Controller\d3_totp_UserController;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Controller\UserController;

class d3_totp_UserControllerTest extends d3TotpUnitTestCase
{
    use d3_totp_getUserTestTrait;

    /** @var d3_totp_UserController */
    protected $_oController;

    protected $sControllerClass = UserController::class;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(UserController::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }
}
