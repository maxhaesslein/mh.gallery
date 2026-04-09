# MH.Gallery Security Improvements TODO

## Höchste Priorität
- [ ] CSRF-Schutz implementieren für alle Formulare
  - Login-Formulare (Admin, Galerie-Passwort)
  - Aktionen im Admin-Bereich
  - Andere state-changing Operationen

## Hohe Priorität
- [x] Eingabe-Validierung für Superglobals verbessern
  - Überprüfung und Validierung von $_GET, $_POST, $_REQUEST, $_COOKIE
  - Zentrale Validierungsfunktionen implementieren
  - abgeschlossen

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