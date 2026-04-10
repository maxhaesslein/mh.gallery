# MH.Gallery Security Improvements TODO

## Mittlere Priorität
- [ ] Rate Limiting für Authentifizierungsversuche implementieren
  - Admin-Login-Versuche begrenzen
  - Galerie-Passwort-Versuche begrenzen

  ### Teilaufgaben:
  1. Rate-Limiting-Hilfsfunktion erstellen (system/functions/rate_limit.php)
     - IP-Adresse + Zeitstempel in Datei speichern
     - Funktion: `check_rate_limit($type, $ip, $max_attempts, $time_window)`
     - Funktion: `record_failed_attempt($type, $ip)`
  2. Admin-Login in route.php (Zeile ~250) um Rate-Limit erweitern
     - Vor `admin_login()` aufrufen
     - Bei Überschreitung: Umleitung mit Fehlermeldung
  3. Galerie-Passwort in route.php (Zeile ~113) um Rate-Limit erweitern
     - Vor `check_password()` aufrufen
     - Bei Überschreitung: Umleitung mit Fehlermeldung
  4. Fehlermeldungen in Sprachdateien ergänzen
- [ ] Session-Sicherheit verbessern
  - Secure, HttpOnly, SameSite Cookie-Attribute setzen
  - Session-Handhabung für Geheimnis-Handling überprüfen
- [ ] Fehlerausgaben sichern (keine Benutzer-Enumeration ermöglichen)
  - Generische Fehlermeldungen anstelle von "wrong password"
- [ ] Directory Traversal-Prüfung bei Datei-Includes verbessern