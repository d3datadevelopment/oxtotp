---
title: Schnellstart
---

Aktivieren Sie die 2-Faktor-Authentisierung im Backend unter [ Benutzer verwalten ] -> [ Benutzer ] -> [ 2-Faktor-Authentisierung ] oder im "Mein Konto" des Shopfrontends.

Ab dann erfordert das Login im Shop (Front- und Backend) für das jeweilige Benutzerkonto neben Benutzername und Passwort auch die Angabe eines Einmalpasswortes.

Diese zusätzliche Absicherung kann im Adminbereich jederzeit wieder entfernt werden. 

## zusätzliche Konfigurationsparameter in der Datei `config.inc.php` möglich:

`blDisableTotpGlobally` => true: deaktiviert diese zusätzlichen Sicherung shopweit für alle Benutzer. Diese Option ist für den Fall vorbehalten, wenn der Adminbereich aus einem technischen Grund und von diesem Modul verursacht nicht mehr aufgerufen werden kann.