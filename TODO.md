# MH.Gallery Security Improvements TODO

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