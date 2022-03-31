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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
  	ajax::init();
  	//$type = init('type');
	////////// 
	if (init('action') == 'removeAll') {
        $return = VaillantControl::removeAll();
        
        if ($return[0]) {
            ajax::success($return[1]);
        } else {
            ajax::error($return[1]);
        }
    }
    
  	if (init('action') == 'validmodif') {
		VaillantControl::postUpdate();
		//VaillantControl::cronHourly();
		ajax::success();
	}
	
  ////////// 
	if (init('action') == 'syncStations'){
		$return = VaillantControl::infoStation(false, 'ajax');
		if (substr($return, 0, 2) == 'ok') {//!= 'bad request'
          	log::add('VaillantControl', 'debug', 'ajax::success : '.$return);
			ajax::success($return);
          	
        } else {
			log::add('VaillantControl', 'debug', ' ajax err : '.json_encode($return) );//substr($return, 11)
            ajax::error($return);
        }
	}
  
  
  
  
  
  
	if (init('action') == 'cronHourly') {
		VaillantControl::cronHourly();
		ajax::success();
	}
	if (init('action') == 'getFromThermostat') {
		ajax::success(VaillantControl::getFromThermostat());
	}

	if (init('action') == 'getFromWelcome') {
		ajax::success(VaillantControl::getFromWelcome());
	}
	if (init('action') == 'getFromWeather') {
		ajax::success(VaillantControl::getFromWeather());
	}
	if (init('action') == 'getDataUrl') {
      	if(init('dataurl') != ""){
          	$url = init('dataurl');
          	$return = VaillantControl::getdata_url($url);
          	//log::add('VaillantControl', 'debug', 'Ajax data_url: '.$url." : ".json_encode($return, JSON_PRETTY_PRINT));
        	
		}else{
          	$return = 'Ajax empty data_url';
          	log::add('VaillantControl', 'debug', 'Ajax empty data_url: '.init('dataurl'));
        }
      	ajax::success($return);
		
                                          	
    }
                                          
                                          
                                          
    if (init('action') == 'getDataG') {
        $return = VaillantControl::getDataGraph(init('device_id'), init('module_id'),init('scale'),init('type'),init('date_begin'), init('date_end'), init('limit'), init('subtitle'), init('real_time'));
		ajax::success($return);
    }
	
	if (init('action') == 'getData') {
		$type = init('type');
		$path = dirname(__FILE__) . '/../../data/' . $type . '.json';
		if (!file_exists($path)) {
			log::add('VaillantControl', 'debug', 'pas trovue le fichier');
			return array();
		}	else {
			log::add('VaillantControl', 'debug', 'fichier ok');
		}
		com_shell::execute(system::getCmdSudo() . 'chmod 777 ' . $path) ;
		$lines = explode("\n", trim(file_get_contents($path)));
		$result = array();
		foreach ($lines as $line) {
			$result[] = json_decode($line, true);
		}
		ajax::success($result);
	}
		
	throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
?>