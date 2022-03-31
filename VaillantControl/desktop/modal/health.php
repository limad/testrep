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
/*
if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
* */

//$eqLogics = nagraphs::byType('nagraphs');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('VaillantControl');
sendVarToJS('eqType', $plugin->getId());
//$eqLogics = eqLogic::byType($plugin->getId());
$sortEqLogs=[];
foreach(eqLogic::byType($plugin->getId()) as $eqLogic) {
	$type = $eqLogic->getConfiguration('type', '');
	if($type == "Home"){
		$Homes[] = $eqLogic;
	}elseif($type == "NATherm1"){
		array_unshift($sortEqLogs, $eqLogic);
	}else{
		$sortEqLogs[] = $eqLogic;
		//array_push($sortEqLogs, $eqLogic);
	}
}
$eqLogics = array_merge($Homes, $sortEqLogs);
?>
<legend>
	<center class="title_table">Santé par pièce  - Netatmo Energie</center>
</legend>
<table class="table table-condensed tablesorter" id="table_healthVaillantControl">
	<thead>
		<tr>
			<th>{{Pièce}}</th>
			<th>{{ID}}</th>
			<th>{{Device}}</th>
			<th>{{Wifi}}</th>
			<th>{{Rf}}</th>
			<th>{{Batterie}}</th>
			<th>{{Statut}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
	 <?php
	
foreach ($eqLogics as $eqLogic) {
  	$isActif=$eqLogic->getIsEnable();
  	$eqLogId=$eqLogic->getLogicalId();
  	list($roomid, $homeid) = explode('|', $eqLogId);
  	$isHome=($roomid == 'Home') ? ' label-danger' : '';
  	$eqConfig=$eqLogic->getConfiguration();
  	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
  //echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
  	echo '<tr>';
  	echo '<td><span class="label '.$isHome.'"><a class="eqLogicDisplayCard cursor '.$opacity.'" href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getName(true) . '</a></span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getId() . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogId . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getStatus('wifi_strength') . ' </span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getStatus('room_rf') . '</span></td>';
	
	$battery_status = '<span class="label label-success" style="font-size : 1em;">{{OK}}</span>';
	$battery = $eqLogic->getStatus('battery');//$eqLogic->getConfiguration('battery_percent');
	if ($battery == '') {
		$battery_status = '<span class="label label-primary" style="font-size : 1em;" title="{{Secteur}}"><i class="fas fa-plug"></i></span>';
  } elseif ($battery < 20) {
		$battery_status = '<span class="label label-danger" style="font-size : 1em;">' . $battery . '%</span>';
	} elseif ($battery < 60) {
		$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $battery . '%</span>';
	} elseif ($battery > 60) {
		$battery_status = '<span class="label label-success" style="font-size : 1em;">' . $battery . '%</span>';
	} else {
		$battery_status = '<span class="label label-primary" style="font-size : 1em;">' . $battery . '%</span>';
	}
	echo '<td>' . $battery_status . '</td>';
	
	$status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
	if ($eqLogic->getStatus('state') == 'nok') {
		$status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NOK}}</span>';
	}
	echo '<td>' . $status . '</td>';
	$lastComm=$eqLogic->getStatus('lastCommunication');
	if ((time()-strtotime($lastComm)) > 60*60 ){
		echo '<td><span class="label label-danger" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	
	}elseif ((time()-strtotime($lastComm)) > 30*60 ){
		echo '<td><span class="label label-warning" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	
	}else{
		echo '<td><span class="label label-success" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	}
	//echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}

?>
  
  
   </tbody>
</table>
  
<br>
  
  
  
<legend>
	<center class="title_cmdtable">Santé par module  - Netatmo Energie</center>
</legend>
                          
                          
 <!-- -->
                          
<table class="table table-condensed tablesorter" id="table_healthVaillantControl2">
	<thead>
		<tr>
			<th>{{Module}}</th>
			<th>{{Pièce}}</th>
			<th>{{Type}}</th>
  			<th>{{DeviceId}}</th>
			<th>{{Rf}}</th>
			<th>{{Batterie %}}</th>
  			<th>{{Batterie}}</th>
  			<th>{{Joignable}}</th>
			<th>{{Firmware}}</th>
			
			<th>{{Dernière communication}}</th>
			
		</tr>
	</thead>
	<tbody>
	 <?php
	
foreach ($eqLogics as $eqLogic) {
  	$isActif=$eqLogic->getIsEnable();
  	if(!$isActif) continue;
  	$eqLogId=$eqLogic->getLogicalId();
  	list($roomid, $homeid) = explode('|', $eqLogId);
  	$isHome=($roomid == 'Home') ? ' label-danger' : '';
  	$eqConfig=$eqLogic->getConfiguration();
  	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
  
  
	$eqmodules_cmd = $eqLogic->getCmd(null, 'eqmodules');
	if (!is_object($eqmodules_cmd)) {
		continue;
	}
  	$cmdVal = $eqmodules_cmd->execCmd();
  	$eqmodules = json_decode($cmdVal, true);
  		//echo '<tr><td colspan="9"><span class="">'.$eqmodules[0]['name'].'</span></td></tr>';
  
  
  
    foreach ($eqmodules as $eqmodule) {                
        echo '<tr class="eqModuleInfos">';
		echo '<td><span class="label '.$isHome.' '. $opacity.'">' .$eqmodule['name'] .'</span></td>';
///   DeviceId   ////////////////////////////////////
      		
      	echo '<td><span class="" style="font-size : 1em;">' . $eqLogic->getName() . '</span></td>';
///   Type   ////////////////////////////////////
      	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqmodule['type'] . '</span></td>';
		
///   DeviceId   ////////////////////////////////////
      	echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqmodule['id'] . ' </span></td>';
///   Rf Power   //////////////////////////////////// 
     	$rf_status=(isset($eqmodule['rfpower'])) ? $eqmodule['rfpower']: '';
      	echo '<td><span class="label label-info" style="font-size : 1em;">' . $rf_status . '</span></td>';
///   Batterie   //////////////////////////////////// 
      	$battery_status = '<span class="label label-success" style="font-size : 1em;">{{OK}}</span>';
      	$battery = $eqmodule['battery_percent'];//$eqLogic->getConfiguration('battery_percent');
      	if ($battery == '') {
      		$battery_status = '<span class="label label-primary" style="font-size : 1em;" title="{{Secteur}}"><i class="fas fa-plug"></i></span>';
      	} elseif ($battery < 20) {
      		$battery_status = '<span class="label label-danger" style="font-size : 1em;">' . $battery . '%</span>';
      	} elseif ($battery < 60) {
      		$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $battery . '%</span>';
      	} elseif ($battery > 60) {
      		$battery_status = '<span class="label label-success" style="font-size : 1em;">' . $battery . '%</span>';
      	} else {
      		$battery_status = '<span class="label label-primary" style="font-size : 1em;">' . $battery . '%</span>';
      	}
      	echo '<td>' . $battery_status . '</td>';

 //////////////////////////////////// 
            //$eqLogic->getStatus('module_reachable_Nok', 0)
      
      	$battery_state = '<span class="label label-info" style="font-size : 1em; cursor : default;">{{NC}}</span>';
      	if ( isset($eqmodule['reachable'])) {
      		$battery_state = '<span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqmodule['battery_state'] .' - '  .$eqmodule['battery_level'] .'</span>';
      	}
      	echo '<td>' . $battery_state . '</td>';
      
      
      
      
      
      ///   Joignable   //////////////////////////////////// 
            //$eqLogic->getStatus('module_reachable_Nok', 0)
      
      	$status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
      	if ($eqLogic->getStatus('module_reachable_Nok', 0) > 1//  
      		|| ( isset($eqmodule['reachable']) && $eqmodule['reachable'] !== true )) {
      		$status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NOK}}</span>';
      	}
      	echo '<td>' . $status . '</td>';
///   Firmware   ////////////////////////////////////
            echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqmodule['firmware_revision'] . '</span></td>';     
///   lastCommunication   ////////////////////////////////////      
            $lastComm=$eqLogic->getStatus('lastCommunication');
            if ((time()-strtotime($lastComm)) > 60*60 ){
                echo '<td><span class="label label-danger" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';

            }elseif ((time()-strtotime($lastComm)) > 30*60 ){
                echo '<td><span class="label label-warning" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';

            }else{
                echo '<td><span class="label label-success" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
            }
///////////////////////////////////////


          echo '</tr>';
    }
    
}

?>  
  
  
  </tbody>
</table>