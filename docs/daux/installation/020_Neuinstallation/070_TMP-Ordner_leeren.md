---
title: TMP-Ordner leeren
---

# mit installiertem D3 Modul-Connector

Leeren Sie das Verzeichnis `tmp` über [ Admin ] -> [ (D3) Module ] -> [ Modul-Connector ] -> [ TMP leeren ]. Markieren Sie [ komplett leeren ] und klicken auf [ TMP leeren ]. 

Sofern die Views nicht automatisch aktualisiert werden, führen Sie dies noch durch.

> [i] Erfordert Ihre Installation eine andere Vorgehensweise zum Leeren des Caches oder zum Aktualisieren der Datenbank-Viewtabellen, führen Sie diese bitte aus.

# ohne installierten D3 Modul-Connector

Verbinden Sie sich mit Hilfe Ihres FTP-Programms zu Ihrem Server und löschen Sie alle Dateien bis auf `.htaccess` in den Ordnern `source/tmp` und `source/tmp/smarty` innerhalb Ihrer Shopinstallation.