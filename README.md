# oxtotp (2FA)
Modul für eine 2-Faktor-Authentisierung (2FA) zum Login in Front- und Backend zusätzlich zu Benutzername und Passwort.

- Authentisierung wird nur bei Benutzerkonten gezeigt, die dieses aktiviert haben - sonst nur Standardanmeldung die Basis der Passwortgenerierung wird für jedes Benutzerkonto individuell angelegt</li>

- Einrichtung des Zugangs in der Auth-App kann durch scanbaren QR-Code oder kopierbare Zeichenkette erfolgen

- Validierung der Einmalpassworte und Generierung der QR-Codes werden ausschließlich innerhalb des Shops durchgeführt - keine Kommunikation nach außen nötig

- statische Backupcodes ermöglichen auch eine (begrenzte) Anmeldung ohne Zugang zum Generierungstool

- optionale Verpflichtung zur 2FA-Nutzung für Adminuser