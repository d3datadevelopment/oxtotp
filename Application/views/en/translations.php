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

$sLangName = "English";

$aLang = [
    'charset'                                         => 'UTF-8',

    'TOTP_INPUT'                                      => 'authentication code',
    'TOTP_INPUT_HELP'                                 => 'The authentication code is available from the Two-Factor Authentication app on your device.',
    'TOTP_CANCEL_LOGIN'                               => 'Cancel login',

    'd3mxuser_totp'                                   => 'Two-factor authentication',

    'D3_TOTP_REGISTERNEW'                             => 'create new registration',
    'D3_TOTP_QRCODE'                                  => 'QR code',
    'D3_TOTP_QRCODE_HELP'                             => 'Scan this QR code with your authentication app to deposit this user account.',
    'D3_TOTP_SECRET'                                  => 'Can not scan QR code?',
    'D3_TOTP_SECRET_HELP'                             => 'If you do not use an app that can scan the QR code, you can also copy this string into your authentication tool. Please also set the password length to 6 characters and the time interval to 30 seconds.',
    'D3_TOTP_CURROTP'                                 => 'Confirmation with one-time password',
    'D3_TOTP_CURROTP_HELP'                            => 'If you have registered this customer account in your authentication app, you generate a one-time password, enter it here and send the form out immediately.',

    'SHOP_MODULE_GROUP_d3totp_main'                   => 'Basic settings',
    'D3_TOTP_FORCE2FATITLE'                           => 'Mandates two-factor authentication',
    'D3_TOTP_FORCE2FASUB'                             => 'All administrators need to activate it',
    'D3_TOTP_ADMINBACKEND'                            => 'Admin-Backend',
    'D3_TOTP_ADMINCONTINUE'                           => 'continue',

    'D3_TOTP_REGISTEREXIST'                           => 'existing registration',
    'D3_TOTP_REGISTERDELETE'                          => 'Delete registration',
    'D3_TOTP_REGISTERDELETE_DESC'                     => 'To change the registration, please delete it. You can then immediately create a new registration. <br> If you delete the registration, the account is no longer protected by the two-factor authentication.',
    'D3_TOTP_REGISTERDELETED'                         => 'The registration has been deleted.',

    'D3_TOTP_CONFIRMATION'                            => 'confirmation',
    'D3_TOTP_BACKUPCODES'                             => 'backup codes',
    'D3_TOTP_BACKUPCODES_DESC'                        => 'You can use these backup codes to log on if it is not possible to generate the one-time password (e.g. device lost or newly installed). You can then change the settings to use 2-factor authentication or create a new 2FA login. Please save these codes safely at this moment. After leaving this page, these codes cannot be displayed again.',
    'D3_TOTP_AVAILBACKUPCODECOUNT'                    => '%1$s backup code(s) still available',
    'D3_TOTP_AVAILBACKUPCODECOUNT_DESC'               => 'To create new backup codes, delete the existing registry and create a new one.',

    'D3_TOTP_SAVE'                                    => 'Save',

    'D3_TOTP_ERROR_UNVALID'                           => 'The one-time password is invalid.',
    'D3_TOTP_ALREADY_EXIST'                           => 'The registration has already been saved.',

    'SHOP_MODULE_D3_TOTP_ADMIN_FORCE_2FA'             => 'Administrators are required to activate 2FA',
];
