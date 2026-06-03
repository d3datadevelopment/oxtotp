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

return [
    'charset'                                         => 'UTF-8',

    'D3_TOTP_INPUT'                                   => 'authentication code',
    'D3_TOTP_INPUT_HELP'                              => 'You get the one-time password from the two-factor authentication app on your device.',
    'D3_TOTP_INPUT_BCHELP'                            => 'Use a backup code from your list',
    'D3_TOTP_MODE_AUTH'                               => 'Having trouble with your authenticator app?',
    'D3_TOTP_MODE_BC'                                 => 'Back to entering the authenticator code.',
    'D3_TOTP_SUBMIT_LOGIN'                            => 'Log in',
    'D3_TOTP_CANCEL_LOGIN'                            => 'Cancel login',
    'D3_TOTP_BREADCRUMB'                              => 'one-time password login',
    'D3_TOTP_ERROR_UNVALID'                           => 'The one-time password is invalid.',
    'D3_TOTP_ERROR_REPLAY'                            => 'The one-time password cannot be used again. Please wait a moment.',
    'D3_TOTP_ERROR_LOCKOUT'                           => 'Too many login attempts. Please try again later.',
    'D3_TOTP_ACCOUNT'                                 => '2-factor authentication',
    'D3_TOTP_ACCOUNT_DESC'                            => 'Secure your account login with a second factor.',

    'D3_TOTP_REGISTERNEW'                             => 'create a new registration',
    'D3_TOTP_QRCODE'                                  => 'QR code',
    'D3_TOTP_QRCODE_HELP'                             => 'Scan this QR code with your authentication app to store this user account there.',
    'D3_TOTP_SECRET'                                  => 'QR code cannot be scanned?',
    'D3_TOTP_SECRET_HELP'                             => 'If you do not use an app that can scan the QR code, you can also copy this string into your authentication tool. Please set the password length to 6 characters and the time interval to 30 seconds.',
    'D3_TOTP_CURROTP'                                 => 'Confirmation with one-time password',
    'D3_TOTP_CURROTP_HELP'                            => 'If you have registered this customer account in your authentication app, use it to generate a one-time password, enter it here and send the form directly afterwards.',

    'D3_TOTP_STATUS'                                  => 'Status',
    'D3_TOTP_ACCOUNT_USE'                             => 'use 2-factor authentication',

    'D3_TOTP_REGISTEREXIST'                           => 'Existing registration',
    'D3_TOTP_REGISTERDELETE_DESC'                     => 'To change the registration, please delete it first. You can then create a new registration immediately. <br>If you delete the registration, the account is no longer protected by two-factor authentication.',
    'D3_TOTP_REGISTERDELETE_CONFIRM'                  => 'Should the existing 2-factor authentication be deleted?',

    'D3_TOTP_BACKUPCODES'                             => 'Backup codes',
    'D3_TOTP_BACKUPCODES_DESC'                        => 'You can use these backup codes to log on if it is not possible to generate the one-time password (e.g. device lost or newly installed). You can then change the settings to use 2-factor authentication or create a new account. Please save these codes securely at this moment. After leaving this page, these codes cannot be displayed again.',
    'D3_TOTP_AVAILBACKUPCODECOUNT'                    => 'still %1$s backup code(s) available',
    'D3_TOTP_AVAILBACKUPCODECOUNT_DESC'               => 'To create new backup codes, delete the existing registry and create a new one.',

    'D3_TOTP_INPUT_FIRST'                             => 'first TOTP digit',
    'D3_TOTP_INPUT_SECOND'                            => 'second TOTP digit',
    'D3_TOTP_INPUT_THIRD'                             => 'third TOTP digit',
    'D3_TOTP_INPUT_FOURTH'                            => 'fourth TOTP digit',
    'D3_TOTP_INPUT_FIFTH'                             => 'fifth TOTP digit',
    'D3_TOTP_INPUT_SIXTH'                             => 'sixth TOTP digit',

    'D3_TOTP_ACCOUNT_SAVE'                            => 'Confirm settings',

];
