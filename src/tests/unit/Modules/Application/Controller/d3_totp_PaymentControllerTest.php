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

namespace D3\Totp\tests\unit\Modules\Application\Controller;

use D3\Totp\Modules\Application\Controller\d3_totp_PaymentController;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Controller\PaymentController;

class d3_totp_PaymentControllerTest extends d3TotpUnitTestCase
{
    use d3_totp_getUserTestTrait;

    /** @var d3_totp_PaymentController */
    protected $_oController;

    protected $sControllerClass = d3_totp_PaymentController::class;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(PaymentController::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }
}
