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

// Check if user's profile has write access to this plugin:
$rd = new PluginRacksDirections;
$profile_right = $rd->getPluginProfile($_SESSION['glpiactiveprofile']['id']);
if($profile_right != 1) Html::displayErrorAndDie(__("You don't have permission to perform this action.", 'racksdirections'));

/**
 * Save rack direction parameter in DB according to user's choice:
 */
if ($_POST && isset($_POST['save']) && isset($_POST['rack_id'])) {
	
	// Check that a rack direction has been passed:
    if (!isset($_POST['rackdirection']) || ($_POST['rackdirection']!=0 && $_POST['rackdirection']!=1)) {
        Html::displayErrorAndDie(__('Please specify the slots numbering direction for the rack.', 'racksdirections'));
    }

	$rack_id = (int)$_POST['rack_id'];				// for security purposes
	$rack_direction = (int)$_POST['rackdirection'];	// for security purposes

    // Save rack direction in the DataBase:
	$rd = new PluginRacksDirections;
    $rd->saveRackDirection($rack_id, $rack_direction);

    // Redirect the user to previous page:
    $url = explode("?", $_SERVER['HTTP_REFERER']);
    Html::redirect($url[0] . "?id=" . $rack_id);
 
}
