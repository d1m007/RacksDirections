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

class PluginRacksDirections extends CommonGLPI
{
	/**
     * This function is called to create the required tables
	 *  in the GLPI database
     */
	function createPluginDB(){
		
		// Table to store racks directions:
		$DB = new DB;
		$table = "glpi_plugin_racksdirections_racksdirections";
		$query = "CREATE TABLE `glpi`.`".$table."` (`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
			`rack_id` INT(10) UNSIGNED NOT NULL ,
			`is_reversed` INT(1) NOT NULL DEFAULT '0' ,
			PRIMARY KEY (`id`) USING BTREE) ENGINE = InnoDB;";
		$result = $DB->query($query) or die($DB->error());
		
		// Table to store profiles rights on plugin:
		$table = "glpi_plugin_racksdirections_profiles";
		$query = "CREATE TABLE `glpi`.`".$table."` (`id` INT(10) UNSIGNED NOT NULL ,
			`profile_right` INT(1) UNSIGNED NOT NULL ,
			PRIMARY KEY (`id`) USING BTREE) ENGINE = InnoDB;";
		$result = $DB->query($query) or die($DB->error());
		
		return;
		
	}
	
	/**
     * This function is called to drop the plugin table
	 *  from the GLPI database
     */
	 function dropPluginDB(){
		
		// Drop table of racks directions:
		$DB = new DB;
		$table = "glpi_plugin_racksdirections_racksdirections";
		$query = "DROP TABLE `glpi`.`".$table."`;";
		$result = $DB->query($query) or die($DB->error());
		
		// Drop table of plugin profiles rights:
		$DB = new DB;
		$table = "glpi_plugin_racksdirections_profiles";
		$query = "DROP TABLE `glpi`.`".$table."`;";
		$result = $DB->query($query) or die($DB->error());
		
		return;
		
	}
	
	/**
     * This function is called from GLPI to allow the plugin to insert one or more item
     *  inside the left menu of an Itemtype
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
    {
		
		switch ($item::getType()) {
			case Rack::getType():
				// Adjust rack direction in rack view according to info set in db:
				self::checkRackDirection($item->getID());
				// Display plugin tab according to active user profile:
				$profile_access = self::getPluginProfile($_SESSION['glpiactiveprofile']['id']);
				if($profile_access == 1) return __('Rack direction', 'racksdirections');
				break;
			default:
		}
   
		return '';
    }
	
    /**
     * This function is called from GLPI to render the form when the user clicks
     *  on the menu item generated from getTabNameForItem()
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0)
    {
		$out = "\n";
		$out .= "	<div style=\"margin-top:10px\">\n		<table class=\"tab_cadre_fixe\">\n";
		$out .= "		<tbody>\n";
		$out .= "		<tr><th>" . __('Change slots numbering direction in rack view', 'racksdirections') . "</th></tr>\n";
        $out .= "			<tr>\n";
        $out .= "				<td>\n";
		$out .= "				<form action='../plugins/racksdirections/front/saverackdirection.php' method='post'>\n";
        $out .= "					" . Html::hidden('rack_id', array('value' => $item->getID()));
		$out .= "\n";
        $out .= "					" . Html::hidden('_glpi_csrf_token', array('value' => Session::getNewCSRFToken()));
		$out .= "\n";
		$out .= "					<label for=\"rackdirection\">" . __('Direction to apply to this rack', 'racksdirections') . ":</label>\n";
        $out .= "					<select name=\"rackdirection\" id=\"rackdirection\">\n";
		$out .= "						<option value=\"0\"";
		if(isset($_SESSION['glpi_js_toload']['rack']) && $_SESSION['glpi_js_toload']['rack'][0] == 'js/rack.js') $out .= (" selected");
		$out .= ">" . __('Default', 'racksdirections') . "</option>\n";
		$out .= "						<option value=\"1\"";
		if(isset($_SESSION['glpi_js_toload']['rack']) && $_SESSION['glpi_js_toload']['rack'][0] == 'js/rack.reverse.js') $out .= (" selected");
		$out .= ">" . __('Reversed', 'racksdirections') . "</option>\n";
		$out .= "					</select>\n";
		$out .= "					<input type=\"submit\" class=\"submit\" value=\"" . __('Save', 'racksdirections') . "\" name=\"save\"/>\n";
        $out .= "				</form>\n";
        $out .= "				</td>\n";
        $out .= "			</tr>\n";
		$out .= "			<tr><td style=\"padding-top:30px\">" . __('Information', 'racksdirections') . ":</td></tr>\n";
		$out .= "			<tr><td style=\"padding-left:30px\"><b>" . __('Default numbering', 'racksdirections') . ":</b> " . __('slots in this rack are numbered from bottom to top', 'racksdirections') . "</td></tr>\n";
		$out .= "			<tr><td style=\"padding-left:30px\"><b>" . __('Reversed numbering', 'racksdirections') . ":</b> " . __('slots in this rack are numbered from top to bottom', 'racksdirections') . "</td></tr>\n";
        $out .= "		</tbody>\n";
		$out .= "		</table>\n";
		$out .= "	</div>\n";
		
        echo ($out);
        return true;

    }
	
	/**
     * This function is called to save profile access rights on
     *  the rackdirection plugin
     */
	function savePluginProfile($profile_id, $profile_right){
		
		$DB = new DB;
	
		// Check if profile has already been set (at least once):
		$profile_set = 0;
		$table = "glpi_plugin_racksdirections_profiles";
		$query = "SELECT `profile_right` FROM `".$table."` WHERE `".$table."`.`id`=".$profile_id.";";
		$result = $DB->query($query) or die($DB->error());
		
		// Do we need to insert or update profile access right?
		$profile_set = mysqli_num_rows($result);	
		if($profile_set == 0){
			// Insert profile access right information:
			$query = "INSERT INTO `".$table."` (id, profile_right) VALUES (".$profile_id.", '".$profile_right."')";
		}
		else{
			// Update "glpi_rackdirections" table:
			$query = "UPDATE `".$table."` SET `profile_right`='".$profile_right."' WHERE `".$table."`.`id`=".$profile_id.";";
			
		}
		$result = $DB->query($query) or die($DB->error());
		
		return true;
		
	}
	
	/**
     * This function is called to get all available GLPI profiles in DB
     */
	function getGlpiProfiles(){
		
		// Get rack direction information from DB:
		$DB = new DB;		
		$table = "glpi_profiles";
		$query = "SELECT `id`,`name` FROM `".$table."` WHERE `id`!=0 ORDER by `id` ASC;";
		$result = $DB->query($query) or die($DB->error());
		if(mysqli_num_rows($result) > 0) return ($result);
		else return 0;
		
	}
	
	/**
     * This function is called to get the plugin profiles in DB
     */
	function getPluginProfile($profile_id){
		
		// Get plugin information from DB:
		$DB = new DB;		
		$table = "glpi_plugin_racksdirections_profiles";
		$query = "SELECT `id`,`profile_right` FROM `".$table."` WHERE `id`=".$profile_id.";";
		$result = $DB->query($query) or die($DB->error());
		$row = $result->fetch_assoc();
		if(mysqli_num_rows($result) > 0) return ($row['profile_right']);
		else return 0;
		
	}
	
	/**
     * This function is called to save user's choice about
     *  the slot numbering direction for a rack
     */
	function saveRackDirection($rack_id, $is_reversed){
		
		$DB = new DB;
	
		// Check if rack direction has already been set (at least once):
		$direction_set = 0;
		$table = "glpi_plugin_racksdirections_racksdirections";
		$query = "SELECT `id` FROM `".$table."` WHERE `".$table."`.`rack_id`=".$rack_id.";";
		$result = $DB->query($query) or die($DB->error());
		
		// Do we need to insert or update rack direction info?
		$direction_set = mysqli_num_rows($result);	
		if($direction_set == 0){
			// Insert rack direction information:
			$query = "INSERT INTO `".$table."` (rack_id, is_reversed) VALUES (".$rack_id.", '".$is_reversed."')";
		}
		else{
			// Update "glpi_rackdirections" table:
			$query = "UPDATE `".$table."` SET `is_reversed`='".$is_reversed."' WHERE `".$table."`.`rack_id`=".$rack_id.";";
			
		}
		$result = $DB->query($query) or die($DB->error());
		
		return true;
		
	}
	
	/**
     * This function is called to get the rack direction set in DB
	 *  for a rack
     */
	function getRackDirection($rack_id){
		
		// Get rack direction information from DB:
		$DB = new DB;		
		$table = "glpi_plugin_racksdirections_racksdirections";
		$query = "SELECT `is_reversed` FROM `".$table."` WHERE `rack_id`='".$rack_id."';";
		$result = $DB->query($query) or die($DB->error());
		$row = $result->fetch_assoc();
		if(mysqli_num_rows($result) > 0) return ($row['is_reversed']);
		else return 0;
		
	}
	
	/**
     * This function is called to set the required SESSION parameter
     *  according to the rack direction set in database
     */
	 function checkRackDirection($rack_id){
		
		// If rack_id=0 then user is adding new rack, exit this script
		if(isset($rack_id) && !empty($rack_id)){	
			
			// Check slot numbering direction for given rack:
			$reversed_order = PluginRacksDirections::getRackDirection($rack_id);

			// Set the SESSION parameter about javascript to load according to the rack direction:
			if($reversed_order == 1) $_SESSION['glpi_js_toload']['rack'][] = 'js/rack.reverse.js';
			else $_SESSION['glpi_js_toload']['rack'][] = 'js/rack.js';
			
		}
		
		return;
		
	}
	
}
