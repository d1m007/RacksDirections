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
 * @link      https://github.com/dim00z/racksdirections
 * -------------------------------------------------------------------------
 */

/**
 * Called when user clicks on Install - REQUIRED
 */
function plugin_RacksDirections_install() {
	
	// Create database table:
	$rd = new PluginRacksDirections;
    $rd->createPluginDB();
	
	// Backup original GLPI '/src/Item_Rack.php' file:
	rename(GLPI_ROOT . '/src/Item_Rack.php', GLPI_ROOT . '/src/Item_Rack.bak.php');
	
	// Replace original GLPI '/src/Item_Rack.php' file with custom file from plugin:
	copy(PLUGIN_RACKSDIRECTIONS_DIR . '/files/src/Item_Rack.reverse.php', GLPI_ROOT . '/src/Item_Rack.php');
	
	// Add custom file from plugin to GLPI '/js' dir:
	copy(PLUGIN_RACKSDIRECTIONS_DIR . '/files/js/rack.reverse.js', GLPI_ROOT . '/js/rack.reverse.js');
	
	return true;
	
}
 
/**
 * Called when user click on Uninstall - REQUIRED
 */
function plugin_RacksDirections_uninstall() { 

	// Drop database table:
	$rd = new PluginRacksDirections;
    $rd->dropPluginDB();
	
	// Reset modified SESSION parameter:
	$_SESSION['glpi_js_toload']['rack'][] = 'js/rack.js';
	
	// Delete custom '/src/Item_Rack.php' file:
	unlink(GLPI_ROOT . '/src/Item_Rack.php');
	
	// Restore original GLPI '/src/Item_Rack.php' file from backup:
	rename(GLPI_ROOT . '/src/Item_Rack.bak.php', GLPI_ROOT . '/src/Item_Rack.php');
	
	// Delete custom file '/js/rack.reverse.js' dir:
	unlink(GLPI_ROOT . '/js/rack.reverse.js');
	
	return true;
	
}

/**
 * Called when plugin is init - REQUIRED
 */
function plugin_RacksDirections_postinit() {
	
	// set default SESSION parameter:
	$_SESSION['glpi_js_toload']['rack'][] = 'js/rack.js';	
	
}
