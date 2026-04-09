# MH.Gallery Security Improvements TODO

## Höchste Priorität
- [ ] CSRF-Schutz implementieren für alle Formulare
  - [ ] CSRF-Token-Generierung und -Validierung-Funktionen erstellen (neue Datei: functions/csrf.php)
  - [ ] CSRF-Token zum Admin-Login-Formular hinzufügen
  - [ ] CSRF-Token zum Hash-Erstellungs-Formular hinzufügen
  - [ ] CSRF-Token zum Galerie-Passwort-Formular hinzufügen
  - [ ] CSRF-Validierung zum Admin-Login-Processing hinzufügen
  - [ ] CSRF-Validierung zum Hash-Erstellungs-Processing hinzufügen
  - [ ] CSRF-Validierung zum Galerie-Passwort-Processing hinzufügen
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