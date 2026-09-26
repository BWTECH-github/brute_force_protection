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

namespace OCA\BruteForceProtection\Tests\Repair;

use OCA\BruteForceProtection\Repair\ImportLegacySecuritySettings;
use OCP\IConfig;
use OCP\ILogger;
use OCP\Migration\IOutput;
use Test\TestCase;

/**
 * Prüft die Übernahme der Brute-Force-Einstellungen aus der Vorgänger-App
 * "security" gegen die echte App-Konfiguration (oc_appconfig), so wie sie
 * nach einem Umzug aus der alten Datenbank vorliegt.
 *
 * @group DB
 */
class ImportLegacySecuritySettingsTest extends TestCase {
	private const KEYS = [
		'brute_force_protection_fail_tolerance',
		'brute_force_protection_time_threshold',
		'brute_force_protection_ban_period',
	];

	/** @var IConfig */
	private $config;

	/** @var array<string, array<string, string>> Stand vor dem Test, wird in tearDown zurückgeschrieben */
	private $backup = [];

	public function setUp(): void {
		parent::setUp();
		$this->config = \OC::$server->getConfig();
		foreach (['security', 'brute_force_protection'] as $app) {
			foreach (self::KEYS as $key) {
				$value = $this->config->getAppValue($app, $key, null);
				if ($value !== null) {
					$this->backup[$app][$key] = $value;
				}
				$this->config->deleteAppValue($app, $key);
			}
		}
	}

	public function tearDown(): void {
		foreach (['security', 'brute_force_protection'] as $app) {
			foreach (self::KEYS as $key) {
				$this->config->deleteAppValue($app, $key);
				if (isset($this->backup[$app][$key])) {
					$this->config->setAppValue($app, $key, $this->backup[$app][$key]);
				}
			}
		}
		parent::tearDown();
	}

	private function runStep(): void {
		$step = new ImportLegacySecuritySettings(
			$this->config,
			$this->createMock(ILogger::class)
		);
		$step->run($this->createMock(IOutput::class));
	}

	/**
	 * @return array<string, string|null>
	 */
	private function currentSettings(): array {
		$result = [];
		foreach (self::KEYS as $key) {
			$result[$key] = $this->config->getAppValue('brute_force_protection', $key, null);
		}
		return $result;
	}

	public function testImportsLegacyValuesWhenAppHasNoOwnSettings(): void {
		$this->config->setAppValue('security', 'brute_force_protection_fail_tolerance', '5');
		$this->config->setAppValue('security', 'brute_force_protection_time_threshold', '600');
		$this->config->setAppValue('security', 'brute_force_protection_ban_period', '900');

		$this->runStep();

		$this->assertSame([
			'brute_force_protection_fail_tolerance' => '5',
			'brute_force_protection_time_threshold' => '600',
			'brute_force_protection_ban_period' => '900',
		], $this->currentSettings());
	}

	public function testLeavesLegacyValuesInPlace(): void {
		$this->config->setAppValue('security', 'brute_force_protection_fail_tolerance', '5');

		$this->runStep();

		$this->assertSame('5', $this->config->getAppValue('security', 'brute_force_protection_fail_tolerance', null));
	}

	public function testKeepsExistingSettingsUntouched(): void {
		$this->config->setAppValue('brute_force_protection', 'brute_force_protection_fail_tolerance', '10');
		$this->config->setAppValue('security', 'brute_force_protection_fail_tolerance', '5');
		$this->config->setAppValue('security', 'brute_force_protection_time_threshold', '600');
		$this->config->setAppValue('security', 'brute_force_protection_ban_period', '900');

		$this->runStep();

		// Schon ein eigener Wert heißt: hier wurde die neue App eingestellt,
		// also wird gar nichts aus "security" gemischt.
		$this->assertSame([
			'brute_force_protection_fail_tolerance' => '10',
			'brute_force_protection_time_threshold' => null,
			'brute_force_protection_ban_period' => null,
		], $this->currentSettings());
	}

	public function testSecondRunIsNoop(): void {
		$this->config->setAppValue('security', 'brute_force_protection_fail_tolerance', '5');
		$this->config->setAppValue('security', 'brute_force_protection_time_threshold', '600');
		$this->config->setAppValue('security', 'brute_force_protection_ban_period', '900');
		$this->runStep();
		$afterFirstRun = $this->currentSettings();

		// Ändert sich der Altbestand danach, darf ein zweiter Lauf nichts mehr tun.
		$this->config->setAppValue('security', 'brute_force_protection_fail_tolerance', '99');
		$this->runStep();

		$this->assertSame($afterFirstRun, $this->currentSettings());
	}

	public function testDoesNothingWithoutLegacyData(): void {
		$this->runStep();

		$this->assertSame([
			'brute_force_protection_fail_tolerance' => null,
			'brute_force_protection_time_threshold' => null,
			'brute_force_protection_ban_period' => null,
		], $this->currentSettings());
	}

	public function testSkipsLegacyValuesThatAreNoPositiveInteger(): void {
		$this->config->setAppValue('security', 'brute_force_protection_fail_tolerance', 'abc');
		$this->config->setAppValue('security', 'brute_force_protection_time_threshold', '0');
		$this->config->setAppValue('security', 'brute_force_protection_ban_period', ' 120 ');

		$this->runStep();

		$this->assertSame([
			'brute_force_protection_fail_tolerance' => null,
			'brute_force_protection_time_threshold' => null,
			'brute_force_protection_ban_period' => '120',
		], $this->currentSettings());
	}

	public function testStepIsRegisteredAsInstallRepairStep(): void {
		$info = \OC::$server->getAppManager()->getAppInfo('brute_force_protection');

		$this->assertContains(ImportLegacySecuritySettings::class, $info['repair-steps']['install']);
	}

	public function testStepIsNotRunOnUpdates(): void {
		$info = \OC::$server->getAppManager()->getAppInfo('brute_force_protection');

		// Beim Update war die App schon da; ihr eigener Stand (auch der
		// Standard) gilt dann und wird nicht nachträglich durch "security" ersetzt.
		$this->assertNotContains(ImportLegacySecuritySettings::class, $info['repair-steps']['post-migration']);
	}
}
