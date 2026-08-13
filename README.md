# Brute-Force-Schutz

Sperrt die Anmeldung vorübergehend, wenn von derselben IP-Adresse zu viele
Fehlversuche für ein Konto kommen. Damit läuft ein Angriff, der Passwörter
durchprobiert, nach wenigen Versuchen ins Leere.

## Wie es wirkt

Die App zählt fehlgeschlagene Anmeldungen je Kombination aus Benutzername und
IP-Adresse. Überschreitet die Zahl innerhalb eines Zeitfensters die erlaubte
Toleranz, wird die Anmeldung für diese Kombination für die eingestellte Dauer
abgewiesen — mit einem Hinweis, wie lange noch. Andere Nutzer und andere
Adressen sind davon nicht betroffen.

Ein Hintergrundauftrag (`OCA\BruteForceProtection\Jobs\ExpireOldAttempts`)
räumt abgelaufene Einträge auf. Er läuft mit dem normalen Cron des Servers;
ohne funktionierenden Cron wächst die Tabelle.

**Zusammenspiel mit owncloud.online:** Ist diese App aktiv, übernimmt sie die
Anmeldebremse vollständig. Der im Server eingebaute Schutz tritt dann zurück
und zählt nicht doppelt mit. Ist die App deaktiviert, greift wieder der Server.
Beide Wege zeigen dieselbe Meldung samt Link zum Zurücksetzen des Passworts.

## Voraussetzungen

* owncloud.online 11.x
* PHP 8.4
* ein laufender Cron (`occ background:cron` empfohlen)

## Installation

Über den Market in den Server-Einstellungen, oder von Hand:

```bash
cd /var/www/owncloud.online/apps
git clone https://github.com/BWTECH-github/brute_force_protection.git
chown -R www-data:www-data brute_force_protection
sudo -u www-data php8.4 ../occ app:enable brute_force_protection
```

## Einstellungen

Einstellungen → Sicherheit. Drei Werte:

| Einstellung | Schlüssel | Standard | Bedeutung |
| --- | --- | --- | --- |
| Erlaubte Fehlversuche | `brute_force_protection_fail_tolerance` | `3` | Ab dem wievielten Fehlversuch gesperrt wird |
| Zeitfenster | `brute_force_protection_time_threshold` | `60` | Über wie viele Sekunden die Fehlversuche gezählt werden |
| Sperrdauer | `brute_force_protection_ban_period` | `300` | Wie lange die Sperre gilt, in Sekunden |

Auch per Kommandozeile setzbar:

```bash
sudo -u www-data php8.4 occ config:app:set brute_force_protection \
  brute_force_protection_fail_tolerance --value=5
```

## Hinweise zum Betrieb

**Hinter einem Reverse-Proxy** sieht der Server als Absender jeder Anfrage den
Proxy. Ohne korrekt gesetztes `trusted_proxies` in der `config.php` landen alle
Fehlversuche auf einer einzigen Adresse — dann sperrt die App entweder alle
gemeinsam oder gar nicht. Das ist die häufigste Fehlkonfiguration.

**Die Sperre ersetzt keine starken Passwörter.** Sinnvoll ergänzt wird sie
durch die Passwortrichtlinie und eine Zwei-Faktor-Anmeldung.

## Fehlersuche

| Symptom | Ursache | Abhilfe |
| --- | --- | --- |
| Es wird nie gesperrt | Cron läuft nicht, oder alle Anfragen kommen mit derselben Proxy-Adresse an | `occ background:cron` prüfen, `trusted_proxies` setzen |
| Alle Nutzer gleichzeitig gesperrt | `trusted_proxies` fehlt, alle teilen sich eine Adresse | `trusted_proxies` auf die Proxy-Adresse setzen |
| Sperre bleibt nach Ablauf bestehen | Aufräumauftrag läuft nicht | Cron prüfen; notfalls `occ background:queue:execute` |

## Herkunft

Fork der gleichnamigen ownCloud-App, gepflegt von der BW-Tech GmbH für
owncloud.online und PHP 8.4. Lizenz: AGPLv3.
