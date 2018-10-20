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

$sLangName = "Deutsch";

$aLang = [
    'charset'                                         => 'UTF-8',

    'TOTP_INPUT'                                      => 'Authentisierungscode',
    'TOTP_INPUT_HELP'                                 => 'Das Einmalpasswort erhalten Sie von der Zweifaktorauthentisierung-App auf Ihrem Gerät.',
    'TOTP_CANCEL_LOGIN'                               => 'Anmeldung abbrechen',

    'd3mxuser_totp'                                   => '2-Faktor-Authentisierung',

    'D3_TOTP_REGISTERNEW'                             => 'neue Registrierung erstellen',
    'D3_TOTP_QRCODE'                                  => 'QR-Code',
    'D3_TOTP_QRCODE_HELP'                             => 'Scannen Sie diesen QR-Code mit Ihrer Authentisierungs-App, um dieses Benutzerkonto dort zu hinterlegen.',
    'D3_TOTP_SECRET'                                  => 'QR-Code kann nicht gescannt werden?',
    'D3_TOTP_SECRET_HELP'                             => 'Setzen Sie keine App ein, die den QR-Code scannen kann, können Sie diese Zeichenkette auch in Ihr Authentisierungstool kopieren. Stellen Sie bitte zusätzlich die Passwortlänge auf 6 Zeichen und das Zeitinterval auf 30 Sekunden ein.',
    'D3_TOTP_CURRPWD'                                 => 'Anmeldepasswort des Benutzerkontos',
    'D3_TOTP_CURRPWD_HELP'                            => 'Die Zeichenkette wird verschlüsselt im Shop abgelegt. Zum Verschlüsseln wird das Passwort des ausgewählten Kundenkontos benötigt. Zugleich stellt dies sicher, dass nur Berechtigte Änderungen an diesen Einstellungen vornehmen dürfen.',
    'D3_TOTP_CURROTP'                                 => 'Bestätigung mit Einmalpasswort',
    'D3_TOTP_CURROTP_HELP'                            => 'Haben Sie dieses Kundenkonto in Ihrer Authentisierungs-App registriert, generieren Sie damit ein Einmalpasswort, tragen Sie es hier ein und senden das Formular direkt darauf hin ab.',

    'D3_TOTP_REGISTEREXIST'                           => 'vorhandene Registrierung',
    'D3_TOTP_REGISTERDELETE'                          => 'Registrierung löschen',
    'D3_TOTP_REGISTERDELETE_DESC'                     => 'Das Konto ist dann nicht mehr durch die Zweifaktorauthtisierung geschützt.',

    'D3_TOTP_ERROR_UNVALID'                           => 'Das Einmalpasswort ist ungültig.',
    'D3_TOTP_ERROR_PWDONTPASS'                        => 'Das Passwort passt nicht zum gewählten Benutzerkonto.',
];
