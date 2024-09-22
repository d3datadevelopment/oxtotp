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

namespace D3\Totp\Tests\Unit;

use OxidEsales\Eshop\Core\Registry as RegistryAlias;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

abstract class d3TotpUnitTestCase extends TestCase
{
    public function d3getMockBuilder($className): MockBuilder
    {
        if (strpos($className, '\\') === false) {
            $className = strtolower($className);
        }
        $editionClassName = RegistryAlias::getUtilsObject()->getClassName($className);

        return parent::getMockBuilder($editionClassName);
    }
}
