# Prüfstand – 21.09.2026

Impressum: Marcus Knorr als Einzelunternehmer, Anschrift, Kontakte, Domain,
Handwerkskammer und Betriebsnummer. Keine erfundene Umsatzsteuer-ID.
Datenschutz: ZETTAHOST statt bisherigem Anbieter; dessen Sicherheitscookie-Angabe
entfernt. IONOS-AVV vom Inhaber bestätigt. Hosting-Logfristen bleiben ungeklärt.
Die öffentliche DPA ist ein Vertragszusatz; konkrete Konto-/Tarifunterlagen wurden
nicht eingesehen. Keine pauschale Bestätigung vollständiger DSGVO-Konformität.
Eigene AGB sind nicht allein wegen dieser Anfrageseite erforderlich.
Leistungstexte auf Holz-/Mauerschutz eingegrenzt; keine Einzelfallentscheidung über
handwerksrechtlich zulässige Tätigkeiten. Bestehendes Markenlogo unverändert.

## Quellen
- https://www.zettahost.com/privacy-policy/ (Abschnitt GDPR Data Processing Agreement)
- https://www.gesetze-im-internet.de/ddg/__5.html
- https://eur-lex.europa.eu/legal-content/DE/TXT/?uri=CELEX:32016R0679
- https://www.gesetze-im-internet.de/ttdsg/__25.html
- https://www.frankfurt-main.ihk.de/recht/uebersicht-alle-rechtsthemen/vertragsrecht-und-e-commerce-recht/agb-allgemeine-informationen-zur-verwendung-5195938
- https://www.gesetze-im-internet.de/hwo/__1.html
- https://www.ionos.de/hilfe/e-mail/allgemeine-themen/serverinformationen-fuer-imap-pop3-und-smtp/

Die folgenden PHP-Prüfungen stammen aus der vorherigen Fassung; der Servercode
wurde bei diesem Textupdate nicht geändert. Kein Zugang zum ZETTAHOST-Konto,
keine Prüfung von dessen SMTP-Freigabe, keine echte E-Mail-Zustellung.

## Durchgeführte technische Prüfung

- Alle lokalen Seiten-/Dateiverweise und eindeutigen HTML-IDs geprüft.
- Animiertes Logo mit der vorherigen Originaldatei verglichen: unverändert.
- JavaScript-Syntax mit Node geprüft.
- Alle PHP-Dateien einschließlich PHPMailer mit PHP 8.3 auf Syntax geprüft.
- PHP-Endpunkt lokal geprüft: deaktivierter Zustand, HTTPS-Anforderung,
  erlaubte Methoden und Herkunft, CSRF, ungültige Eingaben, Honeypot,
  übergroße Anfragen, erfolgreiche Antwort mit Tokenwechsel, Transportfehler
  und Versandlimit über mehrere Sitzungen hinweg.
- Erfolg/Transportfehler mit lokalem Mailtransport-Dummy geprüft, ohne Netzwerk-
  oder E-Mail-Versand. Das bestätigt keine Verbindung oder Zustellung bei IONOS.
- Keine visuelle Browserprüfung und kein Zugriff auf das ZETTAHOST-Konto.
