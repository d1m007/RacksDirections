<?php

/**
 * -------------------------------------------------------------------------
 * RacksDirections plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of the RacksDirections plugin for GLPI.
 *
 * RacksDirections is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * RacksDirections is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with RacksDirections. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2023 by Dimitri Mestdagh.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/d1m007/RacksDirections
 * -------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

// Check GLPI profile rights:
Session::checkRight("config", UPDATE);

// To be available when plugin in not activated:
Plugin::load('RacksDirections');

Html::header(__('Plugin Racks Directions - Configuration', 'RacksDirections'), $_SERVER['PHP_SELF'], "config", "plugins");

// Get available profiles in GLPI config :
$rd = new PluginRacksDirections;
$glpi_profiles = $rd->getGlpiProfiles();

$out  = ("<div class=\"tab-content p-2 flex-grow-1 card border-start-0\" style=\"margin-left:30%;max-width:400px;min-width: 150px; margin-bottom:100px;\">");
$out .= ("<table style=\"\">\n<tbody>\n");
$out .= ("<form action='./saveconfig.php' method='post'>\n");
$out .= ("	" . Html::hidden('_glpi_csrf_token', array('value' => Session::getNewCSRFToken())));
$out .= ("\n");
$out .= ("	<tr><th colspan=\"2\" style=\"text-align:left;font-size:16px;\"><h3>" . __("Plugin Racks Directions - Configuration", 'RacksDirections') . "</h3></th></tr>\n");
$out .= ("	<tr><td colspan=\"2\" style=\"text-align:left;padding-top:10px;\">" . __("Select which profile can access the plugin tab to change racks directions", 'RacksDirections') . ":</td></tr>\n");

foreach($glpi_profiles as $profile) {
	
	$profile_right = $rd->getPluginSetting('profile_right_'.$profile['id']);
	$out .= ("	<tr>\n");
	$out .= ("		<td style=\"padding-left:50px;text-align:center;\"><label for=\"profile_right_" . $profile['id'] . "\">" . $profile['name'] . "</label></td>\n");
	$out .= ("		<td style=\"padding:5px;\">\n");
	$out .= ("			<select name=\"profile_right_" . $profile['id'] . "\" id=\"profile_right_" . $profile['id'] . "\">\n");
	$out .= ("				<option value=\"0\"");
	if($profile_right == 1) $out .= (" selected");	
	$out .= (">" . __('No access', 'RacksDirections') . "</option>\n");
	$out .= ("				<option value=\"1\"");
	if($profile_right == 1) $out .= (" selected");	
	$out .= (">" . __('Write', 'RacksDirections') . "</option>\n");
	$out .= ("			</select>\n");
	$out .= ("		</td>\n");
	$out .= ("	</tr>\n");
	

	

}

$out .= ("	<tr><td colspan=\"2\" style=\"text-align: left;padding-top:20px;\">" . __("At plugin uninstall", 'RacksDirections') . ":</td></tr>\n");
$out .= ("	<tr><td colspan=\"2\" style=\"white-space:nowrap;\">");
$out .= ("		<div class=\"form-check form-switch\">");
$out .= ("		<input class=\"form-check-input\" type=\"checkbox\" id=\"preservePluginDB\" name=\"preservePluginDB\" value=\"1\"");
if($rd->getPluginSetting("preservePluginDB") == 1) $out .= "checked";
$out .= (">");
$out .= ("		<label for=\"preservePluginDB\" style=\"width:\">".__('Preserve plugin database in GLPI', 'RacksDirections')."</label>");
$out .= ("	<br/><i>" . __('Recommended in case of upgrade', 'RacksDirections') . "</i>\n");
$out .= ("	</div></td></tr>\n");
$out .= ("</td></tr>\n");
$out .= ("	<tr><td colspan=\"2\" style=\"padding-top: 10px; text-align:center\">\n");
$out .= ("	<button type=\"submit\" class=\"btn btn-primary\">\n");
$out .= ("		<i class=\"far fa-save\"></i>\n");
$out .= ("		<span>" . __('Save', 'RacksDirections') . "</span>\n");
$out .= ("	</button></td>\n");
$out .= ("</form>\n</tbody>\n</table>\n</div>\n");

echo($out);

Html::footer();
