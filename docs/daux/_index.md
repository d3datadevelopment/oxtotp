Die [2-Faktor-Authentisierung](https://de.wikipedia.org/wiki/Zwei-Faktor-Authentisierung) ermöglicht es den Shopbesuchern, neben der üblichen Kombination aus Benutzername und Passwort auch ein [zeitgesteuertes Einmalpasswort](https://de.wikipedia.org/wiki/Time-based_One-time_Password_Algorithmus) zur Anmeldung abfragen zu lassen. Dies erhöht die Sicherheit im Anmeldeprozess deutlich und macht auch Anmeldungen in öffentlichen Netzwerken oder Internet-Cafés sicherer.
Das abgefragte Einmalpasswort wird z.B. durch entsprechende Apps auf dem Smartphone erzeugt. Die folgenden Apps sind Empfehlungen und können gegen andere nach dem TOTP-Standard arbeitende Apps getauscht werden.

[![Authenticator Apps bei Google Play](https://play.google.com/intl/en_us/badges/images/generic/de_badge_web_generic.png)](http://play.google.com/store/search?q=totp%20authenticator&c=apps)

[![Authenticator Apps im Apple Store](https://apps.apple.com/de/app/totp-authenticator-fast-2fa/id1404230533)](https://apps.apple.com/de/app/totp-authenticator-fast-2fa/id1404230533)

Die Einrichtung dieses 2. Faktors ist optional und lässt sich für jedes Benutzerkonto separat einrichten. Die Einrichtung erfolgt im "Mein Konto"-Bereich, über das Shopbackend kann die Einrichtung ebenfalls durchgeführt werden. 

Erst dann wird die zusätzliche Sicherheit genutzt. Sofern der Benutzer auch Zugang zum Adminbereich hat, wird das Einmalpasswort dort ebenfalls abgefragt.

Zur Einrichtung wird ein scanbarer QR-Code angeboten. Bei der Aktivierung werden statische Backup-Codes angelegt, die verwendet werden können, wenn ein Anmelden mit den Einmalpasswörtern nicht möglich ist. Die Einrichtung kann jederzeit wieder gelöscht werden.