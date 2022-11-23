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

namespace D3\Totp\Application\Model;

class d3totp_conf
{
    public const OXID_ADMIN_AUTH                = 'auth';
    public const OXID_FRONTEND_AUTH             = 'usr';
    public const SESSION_AUTH                   = 'd3Totp_auth';           // has valid totp, user is logged in completly
    public const SESSION_CURRENTUSER            = 'd3Totp_currentUser';    // oxid assigned to user from entered username
    public const SESSION_CURRENTCLASS           = 'd3Totp_currentClass';   // oxid assigned to user from entered username
    public const SESSION_NAVFORMPARAMS          = 'd3Totp_navFormParams';
    public const SESSION_ADMIN_AUTH             = 'd3Totp_auth';           // has valid totp, user is logged in completly
    public const SESSION_ADMIN_CURRENTUSER      = 'd3Totp_currentUser';    // oxid assigned to user from entered username
    public const SESSION_ADMIN_PROFILE          = 'd3Totp_currentProfile'; // selected profile
    public const SESSION_ADMIN_CHLANGUAGE       = 'd3Totp_currentChLanguage'; // selected language
}