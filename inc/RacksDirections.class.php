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

class PluginRacksDirections extends CommonGLPI
{
	/**
     * This function is called to create the required tables
	 *  in the GLPI database
     */
	function createPluginDB(){
		
		$DB = new DB;
		
		// Table to store racks directions:
		$table = "glpi_plugin_racksdirections";
		$query = "CREATE TABLE IF NOT EXISTS `".$table."` (
			`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`rack_id` INT(10) UNSIGNED NOT NULL,
			`is_reversed` INT(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`) USING BTREE) ENGINE = InnoDB;";
		$result = $DB->query($query) or die($DB->error());
		
		// Table to store plugin settings:
		$table = "glpi_plugin_racksdirections_config";
		$query = "CREATE TABLE IF NOT EXISTS `".$table."` (
			`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`setting` VARCHAR(32),
			`value` INT(1) UNSIGNED NOT NULL,
			PRIMARY KEY (`id`) USING BTREE) ENGINE = InnoDB;";
		$result = $DB->query($query) or die($DB->error());
		
		return;
		
	}
	
	/**
     * This function is called to install plugin configuration
	 *  in the GLPI database
     */
	function installPluginDB(){
		
		$DB = new DB;

		// If old plugin database exists:
		$table = "glpi_plugin_racksdirections_racksdirections";
		$query = "SHOW TABLES LIKE '".$table."';";
		if(mysqli_num_rows($DB->query($query)) > 0) {
			$query = "RENAME TABLE `".$table."` TO `glpi_plugin_racksdirections`;";
			$result = $DB->query($query);
		}
		
		self::createPluginDB();
		
		// If old plugin database exists:
		$table = "glpi_plugin_racksdirections_profiles";
		$query = "SHOW TABLES LIKE '".$table."';";
		if(mysqli_num_rows($DB->query($query)) > 0) {
			$query = "SELECT `id`,`profile_right` FROM `".$table."`;";
			$result = $DB->query($query) or die($DB->error());
			// Migrate data to new database:
			foreach($result as $row) self::savePluginSetting('profile_right_' . $row['id'], $row['profile_right']);
			// Drop old plugin database:
			$query = "DROP TABLE `".$table."`;";
			$result = $DB->query($query) or die($DB->error());
		}

	}
		
	
	/**
     * This function is called to drop the plugin tables
	 *  from the GLPI database
     */
	 function dropPluginDB(){
		
		$DB = new DB;
		
		// Drop table of racks directions:
		$table = "glpi_plugin_racksdirections";
		$query = "DROP TABLE IF EXISTS `".$table."`;";
		$result = $DB->query($query);
		
		// Drop table of plugin settings:
		$table = "glpi_plugin_racksdirections_config";
		$query = "DROP TABLE IF EXISTS `".$table."`;";
		$result = $DB->query($query);
		
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
				$profile_access = self::getPluginSetting('profile_right_'.$_SESSION['glpiactiveprofile']['id']);
				if($profile_access == 1) return __('Rack direction', 'RacksDirections');
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
		$out .= "		<tr><th>" . __('Change slots numbering direction in rack view', 'RacksDirections') . "</th></tr>\n";
        $out .= "			<tr>\n";
        $out .= "				<td>\n";
		$out .= "				<form action='../plugins/RacksDirections/front/saverackdirection.php' method='post'>\n";
        $out .= "					" . Html::hidden('rack_id', array('value' => $item->getID()));
		$out .= "\n";
        $out .= "					" . Html::hidden('_glpi_csrf_token', array('value' => Session::getNewCSRFToken()));
		$out .= "\n";
		$out .= "					<label for=\"rackdirection\">" . __('Direction to apply to this rack', 'RacksDirections') . ":</label>\n";
        $out .= "					<select name=\"rackdirection\" id=\"rackdirection\">\n";
		$out .= "						<option value=\"0\"";
		if(isset($_SESSION['reversed_slots_order']) && $_SESSION['reversed_slots_order'] == '0') $out .= (" selected");
		$out .= ">" . __('Default', 'RacksDirections') . "</option>\n";
		$out .= "						<option value=\"1\"";
		if(isset($_SESSION['reversed_slots_order']) && $_SESSION['reversed_slots_order'] == '1') $out .= (" selected");
		$out .= ">" . __('Reversed', 'RacksDirections') . "</option>\n";
		$out .= "					</select>\n";
		$out .= "					<input type=\"submit\" class=\"submit\" value=\"" . __('Save', 'RacksDirections') . "\" name=\"save\"/>\n";
        $out .= "				</form>\n";
        $out .= "				</td>\n";
        $out .= "			</tr>\n";
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
	function savePluginSetting($setting, $value){
		
		$DB = new DB;
	
		// Check if profile has already been set (at least once):
		$table = "glpi_plugin_racksdirections_config";
		$query = "SELECT `value` FROM `".$table."` WHERE `".$table."`.`setting`='".$setting."';";
		$result = $DB->query($query) or die($DB->error());
		
		// Do we need to insert or update profile access right?
		$profile_set = mysqli_num_rows($result);	
		if($profile_set == 0){
			// Insert profile access right information:
			$query = "INSERT INTO `".$table."` (`setting`, `value`) VALUES ('".$setting."', '".$value."')";
		}
		else{
			// Update "glpi_rackdirections" table:
			$query = "UPDATE `".$table."` SET `value`='".$value."' WHERE `".$table."`.`setting`='".$setting."';";
			
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
     * This function is called to get the plugin settings in DB
     */
	function getPluginSetting($setting){
		
		// Get plugin information from DB:
		$DB = new DB;		
		$table = "glpi_plugin_racksdirections_config";
		$query = "SHOW TABLES LIKE '".$table."';";
		if(mysqli_num_rows($DB->query($query)) > 0) {
			$query = "SELECT `value` FROM `".$table."` WHERE `setting`='".$setting."';";
			if(mysqli_num_rows($DB->query($query))){
				$result = $DB->query($query);
				$row = $result->fetch_assoc();
				return ($row['value']);
			}
			else return 0;
		}
		else return 1;
		
	}
	
	/**
     * This function is called to save user's choice about
     *  the slot numbering direction for a rack
     */
	function saveRackDirection($rack_id, $is_reversed){
		
		$DB = new DB;
	
		// Check if rack direction has already been set (at least once):
		$direction_set = 0;
		$table = "glpi_plugin_racksdirections";
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
		$table = "glpi_plugin_racksdirections";
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
			$reversed_slots_order = PluginRacksDirections::getRackDirection($rack_id);

			// Set the SESSION parameter about javascript to load according to the rack direction:
			if($reversed_slots_order) $_SESSION['glpi_js_toload']['rack'][0] = 'js/rack.reverse.js';
			else $_SESSION['glpi_js_toload']['rack'][0] = 'js/rack.js';

			$_SESSION['reversed_slots_order'] = $reversed_slots_order;  // used to set current state in selectbox
										    // in plugin tab once page is loaded
			
		}
		
		return;
		
	}
	
}
