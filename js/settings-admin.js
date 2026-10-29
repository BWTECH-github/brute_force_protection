/**
 * @copyright Copyright (c) 2018, Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
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

$(document).ready(function(){
	var msg = '#save-bfp-settings-message';
	var felder = [
		['#bfp-threshold-time', 'brute_force_protection_time_threshold'],
		['#bfp-fail-tolerance', 'brute_force_protection_fail_tolerance'],
		['#bfp-ban-period', 'brute_force_protection_ban_period']
	];

	// Nur ganze Zahlen ab 1: $.isNumeric liess "2.5" oder "0.5" durch, der
	// Server rundet per intval ab - eine Toleranz 0 sperrte schon nach dem
	// ersten Fehlversuch.
	function positiveGanzzahl(wert) {
		return /^[0-9]+$/.test(wert) && parseInt(wert, 10) > 0;
	}

	// Echtes Formular: Enter in einem Feld speichert wie der Knopf
	$('#brute-force-protection').on('submit', '#bfp-settings-form', function(event) {
		event.preventDefault();
		var ungueltig = null;
		felder.forEach(function(feld) {
			var $feld = $(feld[0]);
			var ok = positiveGanzzahl($.trim($feld.val()));
			$feld.attr('aria-invalid', ok ? null : 'true');
			if (!ok && ungueltig === null) {
				ungueltig = $feld;
			}
		});
		if (ungueltig !== null) {
			OC.msg.finishedError(msg, t('brute_force_protection', 'Inputs must be positive integers'));
			ungueltig.trigger('focus');
			return;
		}
		OC.msg.startSaving(msg);
		// Erfolg erst melden, wenn alle drei Werte wirklich gespeichert sind
		$.when.apply($, felder.map(function(feld) {
			return OC.AppConfig.setValue('brute_force_protection', feld[1], parseInt($(feld[0]).val(), 10));
		})).done(function() {
			OC.msg.finishedSuccess(msg, t('brute_force_protection', 'Preferences are saved'));
		}).fail(function() {
			OC.msg.finishedError(msg, t('core', 'Error'));
		});
	});
});
