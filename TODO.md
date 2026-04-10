# MH.Gallery Security Improvements TODO

## Höchste Priorität
- [x] CSRF-Schutz implementieren für alle Formulare
  - [x] CSRF-Token-Generierung und -Validierung-Funktionen erstellen
    - Neue Datei: `system/functions/csrf.php`
    - Funktionen: `csrf_token()` (generieren), `csrf_validate()` (validieren)
    - Token speichern in Session, nicht als Cookie (client-side nicht sichtbar)
    - Token als hidden input in Formularen einbauen
  - [x] CSRF-Token zum Admin-Login-Formular hinzufügen
    - Datei: `system/site/templates/admin.php`, Zeilen 87-98
    - Token als hidden input nach dem action-input
  - [x] CSRF-Token zum Hash-Erstellungs-Formular hinzufügen
    - Datei: `system/site/templates/admin_create-hash.php`, Zeilen 20-23
    - Token als hidden input im Formular
  - [x] CSRF-Token zum Galerie-Passwort-Formular hinzufügen
    - Datei: `system/site/templates/401-password.php`, Zeilen 36-50
    - Token als hidden input im Formular
  - [x] CSRF-Validierung zum Admin-Login-Processing hinzufügen
    - Datei: `system/classes/route.php`, Zeilen 237-242
    - Vor `admin_login()` aufrufen
  - [x] CSRF-Validierung zum Hash-Erstellungs-Processing hinzufügen
    - Hash-Erstellung erfolgt direkt im Template (Zeilen 26-28)
    - CSRF-Check vor Passwort-Hashing hinzugefügt
  - [x] CSRF-Validierung zum Galerie-Passwort-Processing hinzufügen
    - Datei: `system/classes/route.php`, Zeilen 105-118
    - Vor `check_password()` aufrufen
  - [ ] CSRF-Schutz-Implementierung testen

## Mittlere Priorität
- [ ] Rate Limiting für Authentifizierungsversuche implementieren
  - Admin-Login-Versuche begrenzen
  - Galerie-Passwort-Versuche begrenzen
- [ ] Session-Sicherheit verbessern
  - Secure, HttpOnly, SameSite Cookie-Attribute setzen
  - Session-Handhabung für Geheimnis-Handling überprüfen
- [ ] Fehlerausgaben sichern (keine Benutzer-Enumeration ermöglichen)
  - Generische Fehlermeldungen anstelle von "wrong password"
- [ ] Directory Traversal-Prüfung bei Datei-Includes verbessern
- [ ] Sicherheitsheader implementieren (X-Content-Type-Options, X-Frame-Options, etc.)
- [ ] Passwort-Richtlinien stärken
- [ ] Content Security Policy (CSP) Header hinzufügen