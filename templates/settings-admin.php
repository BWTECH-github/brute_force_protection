<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH.
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
 * @copyright Copyright (c) 2026, BW-Tech GmbH
 *
 * Modified by BW-Tech GmbH on 2026-06-15.
 * Changes:
 *   - WCAG 2.1 AA fixes for bundled plugin forks
 *   - Bundle expanded SaaS apps for 11.0.3
 */

/** @var array $_ */
/** @var \OCP\IL10N $l */
script('brute_force_protection', 'settings-admin');
style('brute_force_protection', 'settings-admin');
?>
<div id="brute-force-protection" class="section">
	<h2><?php p($l->t('Brute Force Protection')); ?></h2>
	<?php /* Echtes Formular: Enter in einem Feld speichert. novalidate, weil
		js/settings-admin.js selbst prueft und die Meldung im Statusfeld zeigt
		statt der Browser-Sprechblase. */ ?>
	<form id="bfp-settings-form" class="bfp-form" novalidate>
		<div class="bfp-field">
			<label for="bfp-threshold-time"><?php p($l->t('Count failed attempts over how many seconds?')) ?></label>
			<input type="number" id="bfp-threshold-time" min="1" step="1" inputmode="numeric" required value="<?php p($_['bruteForceProtectionTimeThreshold']) ?>">
		</div>
		<div class="bfp-field">
			<label for="bfp-fail-tolerance"><?php p($l->t('Ban after how many failed attempts?')) ?></label>
			<input type="number" id="bfp-fail-tolerance" min="1" step="1" inputmode="numeric" required value="<?php p($_['bruteForceProtectionFailTolerance']) ?>">
		</div>
		<div class="bfp-field">
			<label for="bfp-ban-period"><?php p($l->t('Ban for how many seconds?')) ?></label>
			<input type="number" id="bfp-ban-period" min="1" step="1" inputmode="numeric" required value="<?php p($_['bruteForceProtectionBanPeriod']) ?>">
		</div>
		<div class="bfp-actions">
			<button id="save-bfp-settings" type="submit" class="oco-btn-primary"><?php p($l->t('Save settings'));?></button>
			<span id="save-bfp-settings-message" class="msg" role="status" aria-live="polite" aria-atomic="true"></span>
		</div>
	</form>
</div>
