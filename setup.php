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
 * @link      https://github.com/dim00z/RacksDirections
 * -------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;

define('PLUGIN_RACKSDIRECTIONS_VERSION', '1.0.0');

// Minimal GLPI version, inclusive
define('PLUGIN_RACKSDIRECTIONS_MIN_GLPI', '10.0.0');

// Maximum GLPI version, exclusive
define('PLUGIN_RACKSDIRECTIONS_MAX_GLPI', '10.0.11');

if (!defined("PLUGIN_RACKSDIRECTIONS_DIR")) {
    define("PLUGIN_RACKSDIRECTIONS_DIR", Plugin::getPhpDir("RacksDirections"));
}

/**
 * Get the name and the version of the plugin - REQUIRED
 */
function plugin_version_RacksDirections() {
	
	return [
		'name'           => __("Racks Directions", "RacksDirections"),
		'version'        => PLUGIN_RACKSDIRECTIONS_VERSION,
		'author'         => 'Dimitri Mestdagh',
		'license'        => 'GPLv3.0',
		'homepage'       => 'https://github.com/dim00z/RacksDirections',
		'requirements'   => [
			'glpi' => [
				'min' => PLUGIN_RACKSDIRECTIONS_MIN_GLPI,
				'max' => PLUGIN_RACKSDIRECTIONS_MAX_GLPI,
			]
		]
	];
}

/**
 *  Check if the config is ok - REQUIRED
 */
function plugin_RacksDirections_check_config() {
	
    return true;
}

/**
 * Check if the prerequisites of the plugin are satisfied - REQUIRED
 */
function plugin_RacksDirections_check_prerequisites() {
 
    // Check that the GLPI version is compatible:
    if (version_compare(GLPI_VERSION, PLUGIN_RACKSDIRECTIONS_MIN_GLPI, 'lt') || version_compare(GLPI_VERSION, PLUGIN_RACKSDIRECTIONS_MAX_GLPI, 'gt')) {
        echo __('This plugin requires GLPI', 'RacksDirections') . ' >= ' . PLUGIN_RACKSDIRECTIONS_MIN_GLPI . ' ' .__('and GLPI', 'RacksDirections') . ' < ' . PLUGIN_RACKSDIRECTIONS_MAX_GLPI;
        return false;
    }
 
    return true;
}


/**
 * Init hooks of the plugin - REQUIRED
 *
 * @return void
 */
function plugin_init_RacksDirections() {
   
	global $PLUGIN_HOOKS,$CFG_GLPI;
	
	$PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['RacksDirections'] = true;
	
    // Load plugin custom class:
	include_once(PLUGIN_RACKSDIRECTIONS_DIR . "/inc/RacksDirections.class.php");
	
	// Add tab on rack admin page:
	Plugin::registerClass('PluginRacksDirections', array('addtabon' => array('Rack')));
	
	// Add plugin config page:
	if (Session::haveRight('config', UPDATE)) {
		$PLUGIN_HOOKS['config_page']['RacksDirections'] = 'front/config.form.php';
	}

	return;
   
}
