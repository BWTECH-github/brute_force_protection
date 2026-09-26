<?php
/**
 * @copyright Copyright (c) 2026, BW-Tech GmbH
 * @license GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

namespace OCA\BruteForceProtection\Repair;

use OCP\IConfig;
use OCP\ILogger;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

/**
 * Übernimmt die Brute-Force-Einstellungen der Vorgänger-App "security".
 *
 * Diese App ist 2018 aus der App "security" (ownCloud bis 10.0.8) entstanden
 * und liest dieselben drei Schlüssel - nur unter ihrer eigenen App-ID. Zieht
 * ein Kunde mit einer Datenbank um, in der "security" eingestellt war, liegen
 * seine Werte unter appid=security und diese App würde mit ihren Standards
 * (3 Versuche / 60 s / 300 s) starten.
 *
 * Der Schritt läuft nur bei der Erstinstallation (repair-steps/install): War
 * brute_force_protection in der alten Datenbank schon installiert, gilt ihr
 * eigener Stand - auch wenn das die Standards sind.
 *
 * Übernommen wird nur, wenn diese App noch keinen der drei Schlüssel hat;
 * vorhandene Werte werden nie überschrieben und nie mit Altwerten gemischt.
 * Der Altbestand bleibt unverändert liegen. Ein zweiter Lauf findet die eigenen
 * Schlüssel vor und tut nichts.
 */
class ImportLegacySecuritySettings implements IRepairStep {
	public const APP = 'brute_force_protection';
	public const LEGACY_APP = 'security';

	/** Gleiche Schlüsselnamen in "security" und in dieser App */
	public const KEYS = [
		'brute_force_protection_fail_tolerance',
		'brute_force_protection_time_threshold',
		'brute_force_protection_ban_period',
	];

	/** @var IConfig */
	private $config;

	/** @var ILogger */
	private $logger;

	public function __construct(IConfig $config, ILogger $logger) {
		$this->config = $config;
		$this->logger = $logger;
	}

	public function getName() {
		return 'Import brute-force settings of the predecessor app "security"';
	}

	public function run(IOutput $output) {
		$own = \array_intersect(self::KEYS, $this->config->getAppKeys(self::APP));
		if ($own !== []) {
			// Die App ist bereits eingestellt - nichts übernehmen.
			return;
		}

		$legacy = \array_intersect(self::KEYS, $this->config->getAppKeys(self::LEGACY_APP));
		if ($legacy === []) {
			return;
		}

		$imported = [];
		foreach ($legacy as $key) {
			$raw = (string)$this->config->getAppValue(self::LEGACY_APP, $key, '');
			$value = \trim($raw);
			// "security" hat nur positive Ganzzahlen gespeichert (Prüfung im
			// Browser). Alles andere wäre hier ein kaputter Wert; dann greift
			// der Standard dieser App.
			if (!\ctype_digit($value) || (int)$value <= 0) {
				$message = \sprintf(
					'Legacy setting %s/%s has no usable value ("%s"); keeping the default of %s.',
					self::LEGACY_APP,
					$key,
					$raw,
					self::APP
				);
				$output->warning($message);
				$this->logger->warning($message, ['app' => self::APP]);
				continue;
			}
			$this->config->setAppValue(self::APP, $key, (string)(int)$value);
			$imported[] = "$key=" . (int)$value;
		}

		if ($imported !== []) {
			$message = 'Imported brute-force settings from the predecessor app "security": '
				. \implode(', ', $imported) . '. Please review them in the admin settings.';
			$output->info($message);
			// Warnstufe, damit der Hinweis auch beim Standard-Loglevel 2 im
			// Serverprotokoll landet - die Übernahme soll nachvollziehbar sein.
			$this->logger->warning($message, ['app' => self::APP]);
		}
	}
}
