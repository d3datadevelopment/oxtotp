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
    'D3_TOTP_CURRPWD_HELP'                            => 'Dies stellt sicher, dass nur Berechtigte Änderungen an diesen Einstellungen vornehmen dürfen.',
    'D3_TOTP_CURROTP'                                 => 'Bestätigung mit Einmalpasswort',
    'D3_TOTP_CURROTP_HELP'                            => 'Haben Sie dieses Kundenkonto in Ihrer Authentisierungs-App registriert, generieren Sie damit ein Einmalpasswort, tragen Sie es hier ein und senden das Formular direkt darauf hin ab.',

    'D3_TOTP_REGISTEREXIST'                           => 'vorhandene Registrierung',
    'D3_TOTP_REGISTERDELETE'                          => 'Registrierung löschen',
    'D3_TOTP_REGISTERDELETE_DESC'                     => 'Um die Registrierung zu ändern, löschen Sie diese bitte vorerst. Sie können sofort im Anschluss eine neue Registrierung anlegen.<br>Wenn Sie die Registrierung löschen, ist das Konto nicht mehr durch die Zweifaktorauthentisierung geschützt.',

    'D3_TOTP_BACKUPCODES'                             => 'Backupcodes',
    'D3_TOTP_BACKUPCODES_DESC'                        => 'Mit diesen Backupcodes können Sie sich anmelden, wenn die Generierung des Einmalpasswortes nicht möglich ist (z.B. Gerät verloren oder neu installiert). Sie können dann die Einstellungen zur Verwendung der 2-Faktor-Authentisierung ändern oder einen neuen Zugang erstellen. Speichern Sie sich diese Codes bitte in diesem Moment sicher ab. Nach Verlassen dieser Seite können diese Codes nicht erneut angezeigt werden.',
    'D3_TOTP_AVAILBACKUPCODECOUNT'                    => 'noch %1$s Backupcodes verfügbar',
    'D3_TOTP_AVAILBACKUPCODECOUNT_DESC'               => 'Um neue Backupcodes zu erstellen, löschen Sie die bestehende Registrierung und legen diese bitte neu an.',

    'D3_TOTP_SAVE'                                    => 'Speichern',

    'D3_TOTP_ERROR_UNVALID'                           => 'Das Einmalpasswort ist ungültig.',
    'D3_TOTP_ERROR_PWDONTPASS'                        => 'Das Passwort passt nicht zum gewählten Benutzerkonto.',
];
