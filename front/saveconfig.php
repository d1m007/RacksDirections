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

include('../../../inc/includes.php');

// Check GLPI profile rights on this page:
Session::checkRight("config", UPDATE);

/**
 * Save users profiles rights in GLPI database
 */
if ($_POST && isset($_POST['save'])) {
	
	$rd = new PluginRacksDirections;
	
	foreach($_POST as $key => $val){
		
		if(preg_match("/profile_right_/", $key)) {
			
			$profile_id = (int)preg_split("/profile_right_/", $key)[1];	// for security purposes
			$val = (int)$val;											// for security purposes
			$rd->savePluginProfile($profile_id, $val);
			
		}
		
	}

	// Redirect the user to previous page:
    Html::back();
 
}
