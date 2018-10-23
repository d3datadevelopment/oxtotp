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
    'D3_TOTP_CURRPWD'                                 => 'Login password of the user account',
    'D3_TOTP_CURRPWD_HELP'                            => 'This ensures that only authorized users can make changes to these settings.',
    'D3_TOTP_CURROTP'                                 => 'Confirmation with one-time password',
    'D3_TOTP_CURROTP_HELP'                            => 'If you have registered this customer account in your authentication app, you generate a one-time password, enter it here and send the form out immediately.',

    'D3_TOTP_REGISTEREXIST'                           => 'existing registration',
    'D3_TOTP_REGISTERDELETE'                          => 'Delete registration',
    'D3_TOTP_REGISTERDELETE_DESC'                     => 'To change the registration, please delete it. You can then immediately create a new registration. <br> If you delete the registration, the account is no longer protected by the two-factor authentication.',

    'D3_TOTP_SAVE'                                    => 'Save',

    'D3_TOTP_ERROR_UNVALID'                           => 'The one-time password is invalid.',
    'D3_TOTP_ERROR_PWDONTPASS'                        => 'The password does not match the selected user account.',
];
