# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [1.5.3] - 2026-10-07

Redesign-Linie (Zweig `redesign`): enthält main bis 1.5.2.

### Fixed

- Sprache: Das Feld „Count failed attempts over how many seconds?“ hieß in de, de_DE und de_CH „Wie viele Sekunden soll nach gescheiterten Versuchen gewartet werden bis sich User neu anmelden können?“ – das ist die Sperrdauer (drittes Feld), nicht das Zählfenster. Jetzt „Fehlversuche innerhalb wie vieler Sekunden zählen?“.
- Anrede: Der Sperrhinweis für öffentliche Links siezte im Du-Katalog (de, de_CH); beide Sperrhinweise sind jetzt gleich formuliert („Zu viele fehlgeschlagene (Anmelde-)Versuche. Versuche es in %s erneut.“).
- de_AT: sechs genutzte Texte ergänzt (Sperrhinweise, Minuten/Stunden, Admin-Beschriftungen).

## [1.5.2] - 2026-09-26

### Added

- Umzug alter Datenbanken: Wird die App auf einer Datenbank erstmals
  installiert, in der noch die Vorgänger-App `security` (ownCloud bis 10.0.8)
  ihre Brute-Force-Werte hinterlassen hat, übernimmt der Reparaturschritt
  `ImportLegacySecuritySettings` diese drei Werte (gleiche Schlüsselnamen,
  appid `security`). Nur wenn die App noch keinen eigenen Wert hat; nichts wird
  überschrieben oder gemischt, der Altbestand bleibt liegen, ein zweiter Lauf
  tut nichts. Die Übernahme steht im Serverprotokoll. War die App in der alten
  Datenbank schon installiert (Update-Weg), läuft der Schritt nicht.
- Die Altwerte werden wie in `security` per `intval` gelesen: `600.0`, `10.5`
  oder `1e3`, die deren Oberfläche zuließ, ergeben 600, 10 bzw. 1000 statt des
  Standards dieser App. Nur Werte, die dabei nicht größer als 0 sind, bleiben
  beim Standard (Warnung im Protokoll).
- Protokollhinweis und README nennen, dass die übernommenen Werte hier auch für
  falsche Kennwörter an öffentlichen Links gelten; `security` drosselte nur
  Anmeldungen. Die README nennt außerdem `occ app:enable
  brute_force_protection` nach dem Upgrade, weil die App nicht standardmäßig
  eingeschaltet ist.

## [1.5.1] - 2026-08-13

### Changed

- Produktname, Beschreibung und uebersetzte Zeichenketten nennen owncloud.online;
  Verweise auf Fehlerbereich, Repository und Dokumentation zeigen auf das eigene
  Repository. Screenshots aus fremden Repositories entfernt.

## [Unreleased] - xxxx-xx-xx

## [1.3.0] - 2024-01-09

- [#204](https://github.com/owncloud/brute_force_protection/pull/204) - Log of blocking a user


## [1.2.0] - 2023-03-13

### Added

- Apply brute login policy on failed login - [#193](https://github.com/owncloud/brute_force_protection/pull/193)


## [1.1.0] - 2020-09-17

### Added

- Add app icon - [#61](https://github.com/owncloud/brute_force_protection/issues/61)
- Added userfriendly text after too many login attempts - [#84](https://github.com/owncloud/brute_force_protection/issues/84)

### Fixed

- Internal Server Error entering a wrong password - [#134](https://github.com/owncloud/brute_force_protection/issues/134)
- Fix documentation path - [#130](https://github.com/owncloud/brute_force_protection/issues/130)
- Protect public links password page - [#90](https://github.com/owncloud/brute_force_protection/issues/90)

### Changed

- Bump libraries

## [1.0.1]

- Initial release

[Unreleased]: https://github.com/owncloud/brute_force_protection/compare/v1.3.0...master
[1.3.0]: https://github.com/owncloud/brute_force_protection/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/owncloud/brute_force_protection/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/owncloud/brute_force_protection/compare/v1.0.1...v1.1.0
