---
title: Paketquelle entfernen
---

Durch einen Wechsel der Paketquelle ist die veraltete Angabe nicht mehr erforderlich. Um Konflikte zu vermeiden, sollte die alte Paketquelle entfernt werden. Haben Sie diese schon früher entfernt, können Sie diesen Schritt überspringen.

Starten Sie die Konsole Ihres Webservers und wechseln in das Hauptverzeichnis Ihres Shops (oberhalb des `source`- und `vendor`-Verzeichnisses). Senden Sie dort diesen Befehl ab:

```bash
php composer config --unset repositories.d3
php composer config --unset repositories.D3modules
``` 

> [!] Achten Sie darauf, dass die Installation über Composer mit derselben PHP-Version erfolgt, in der auch Ihr Shop installiert ist. Sie erhalten sonst unpassende Modulpakete.
   
> [i] Benötigt Ihre Installation einen anderen Aufruf von Composer, ändern Sie den Befehl bitte entsprechend ab. Für weitere Optionen dieses Befehls lesen Sie bitte die [Dokumentation von Composer](https://getcomposer.org/doc/03-cli.md#require).
