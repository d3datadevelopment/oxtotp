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

use Exception;

// @codeCoverageIgnoreStart
class Events
{
    /**
     * @return void
     * @throws Exception
     */
    public static function onActivate(): void
    {
        $actions = oxNew(Actions::class);
        $actions->runModuleMigrations();
        $actions->regenerateViews();
        $actions->clearCache();
        $actions->seoUrl();
    }

    /**
     * @codeCoverageIgnore
     */
    public static function onDeactivate(): void
    {
    }
}
// @codeCoverageIgnoreEnd
