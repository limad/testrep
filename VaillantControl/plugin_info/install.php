<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function VaillantControl_install() {
	$plugin = plugin::byId('VaillantControl');
	$eqLogics = eqLogic::byType($plugin->getId(), true);
	config::save('functionality::cron10::enable', 1, 'VaillantControl');
  
	$pathD = __DIR__  . '/../core/template/dashboard/';
	$pathM = __DIR__  . '/../core/template/mobile/';
	foreach (array('eqHome.html','eqTherm.html') as $file) {
		if(!file_exists($pathD.'custom_'.$file) && file_exists($pathD.'default_'.$file)){
			log::add('VaillantControl', 'debug', __FUNCTION__ .' file !exist: '.'custom_'.$file.' creating...');
			$sm= shell_exec('cp -f '.$pathD.'default_'.$file.' '.$pathD.'custom_'.$file. ' > /dev/null 2>&1;');
		}
		if(!file_exists($pathM.'custom_'.$file) && file_exists($pathM.'default_'.$file)){
			log::add('VaillantControl', 'debug', __FUNCTION__ .' file !exist: '.'custom_'.$file.' creating...');
			$sm= shell_exec('cp -f '.$pathM.'default_'.$file.' '.$pathM.'custom_'.$file. ' > /dev/null 2>&1;');
		}
    }
}

function VaillantControl_update() {
	$plugin = plugin::byId('VaillantControl');
	$eqLogics = eqLogic::byType($plugin->getId());
	//echo "<pre>".'VaillantControl_update start '."</pre>";
	//config::save('functionality::cron10::enable', 1, 'VaillantControl');
  
  
  //VaillantControl::connectMigo();
	
  	$pathD = __DIR__  . '/../core/template/dashboard/';
	$pathM = __DIR__  . '/../core/template/mobile/';
	foreach (array('eqHome.html','eqTherm.html') as $file) {
		if(!file_exists($pathD.'custom_'.$file) && file_exists($pathD.'default_'.$file)){
			log::add('VaillantControl', 'debug', __FUNCTION__ .' file !exist: '.'custom_'.$file.' creating...');
			$sm= shell_exec('cp -f '.$pathD.'default_'.$file.' '.$pathD.'custom_'.$file. ' > /dev/null 2>&1;');
		}
		if(!file_exists($pathM.'custom_'.$file) && file_exists($pathM.'default_'.$file)){
			log::add('VaillantControl', 'debug', __FUNCTION__ .' file !exist: '.'custom_'.$file.' creating...');
			$sm= shell_exec('cp -f '.$pathM.'default_'.$file.' '.$pathM.'custom_'.$file. ' > /dev/null 2>&1;');
		}
    }
  	foreach($eqLogics as $eqLogic) {
      	$cmdsToremove = ["requestOn","setpointmode_starttime"];
      	foreach ($cmdsToremove as $cmdToremove) {
          	$cmd = $eqLogic->getCmd(null, $cmdToremove);
            if (!is_object($cmd)) {
                continue;
            }
          	$cmd->remove();
        }
      	$cmdsToupdate = ["consigneset"];
      	foreach ($cmdsToupdate as $cmdToupdate) {
          	$cmd = $eqLogic->getCmd(null, $cmdToupdate);
            if (!is_object($cmd)) {
                continue;
            }
          	$cmd->setDisplay('title_placeholder',"Consigne (°C)");
          	$cmd->setDisplay('message_placeholder',"Durée (minutes)");
          	$cmd->setGeneric_type( 'THERMOSTAT_SET_SETPOINT');
        }
    }
  	VaillantControl::infoStation(false, 'update_plugin');
  	//VaillantControl::removeAll();
}



function VaillantControl_remove() {
    $plugin = plugin::byId('VaillantControl');
	$eqLogics = eqLogic::byType($plugin->getId(), true);

	foreach ($eqLogics as $eqLogic) {
        if (is_object($eqLogic)) {
          $eqLogic->remove();
        }
    }
  	config::remove('homes','VaillantControl');
    //cache::remove('VaillantControl' . '_token_auth' , null);
    
}

?>