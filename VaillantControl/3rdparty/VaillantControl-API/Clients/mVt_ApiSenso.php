<?php
namespace VaillantControlApi\Clients;
error_reporting(E_ALL);  
use log as log;
//use jeedom as jeedom;
use Exception as Exception;
//namespace app\components;
//require_once realpath(dirname(__FILE__) . '/../../../../core/php/core.inc.php');

	
class mVt_ApiSenso extends mVt_ApiClient {

  protected $conf = array();
// """Facility details"""
  public $_facilities_list = "{_base}/facilities";
  //public $_facilities = "{_base}/facilities/{_serial}";
  public $_gateway_type = "{_base}/facilities/{_serial}/public/v1/gatewayType";
  public $_facilities_details = "{_base}/facilities/{_serial}/system/v1/details";
  public $_facilities_status = "{_base}/facilities/{_serial}/system/v1/status";
  public $_facilities_storage = "{_base}/facilities/{_serial}/storage";//_facilities_storage $_facilities_settings
  public $_facilities_default_storage = "{_base}/facilities/{_serial}/storage/default";
  
  public $_installer_info = "{_base}/facilities/{_serial}/system/v1/installerinfo";
// """Rbr (Room by room)"""
  public $_rbr_base = "{_base}/facilities/{_serial}/rbr/v1";
  public $_rbr_installation_status = "{_base}/facilities/{_serial}/rbr/v1/installationStatus";
  public $_rbr_underfloor_heating_status = "{_base}/facilities/{_serial}/rbr/v1/underfloorHeatingStatus";
// """Rooms"""
  public $_rooms_list = 		"{_base}/facilities/{_serial}/rbr/v1/rooms";
  public $_room = 				"{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}";
  public $_room_configuration= "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration";
  public $_room_quick_veto = 	"{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/quickVeto";//data: { temperatureSetpoint: temperature, duration }
  public $_room_timeprogram =  "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/timeprogram";
  public $_room_operating_mode = "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/operationMode";
  public $_room_child_lock = "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/childLock";
  public $_room_name = "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/name";
  public $_room_device_name = "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/devices/{_sgtin}/name";
  public $_room_temperature_setpoint = "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/temperatureSetpoint";
//"""Repeaters"""
  public $_repeaters_list = "{_base}/facilities/{_serial}/rbr/v1/repeaters";
  public $_repeater_delete = "{_base}/facilities/{_serial}/rbr/v1/repeaters/{_sgtin}";
  public $_repeater_set_name = "{_base}/facilities/{_serial}/rbr/v1/repeaters/{_sgtin}/name";
// """HVAC (heating, ventilation and Air-conditioning)"""
  public $_hvac = "{_base}/facilities/{_serial}/hvacstate/v1/overview";
  public $_hvac_request_update = "{_base}/facilities/{_serial}/hvacstate/v1/hvacMessages/update";//cmd
// report
  public $_live_report = "{_base}/facilities/{_serial}/livereport/v1";
  public $_live_report_device = "{_base}/facilities/{_serial}/livereport/v1/devices/{_deviceId}/reports/{report_id}";
  public $_photovoltaics_report = "{_base}/facilities/{_serial}/spine/v1/currentPVMeteringInfo";
  public $_eebus_node = "{_base}/facilities/{_serial}/spine/v1/ship/self";

// """EMF (Embedded Metering Function)"""
  public $_emf_devices = "{_base}/facilities/{_serial}/emf/v1/devices";
  public $_emf_report_device = "{_base}/facilities/{_serial}/emf/v1/devices/{_deviceId}";

// """System control""" ******************
 public $_system_configuration = "{_system}/configuration";//manual_cooling["start_date", "end_date"]
  public $_system_status = "{_system}/status";//outside_temperature
  public $_system_datetime = "{_system}/status/datetime";//404////"Method Not Allowed"
  public $_system_parameters = "{_system}/parameters";//set
  public $_system_quick_mode = "{_system}/configuration/quickmode";//set quickVeto; quickmode; quick_veto; quick_mode
  public $_system_holiday_mode = "{_system}/configuration/holidaymode";//set
// """DHW (Domestic Hot Water)"""
  public $_dhws = "{_system}/dhw";
  public $_dhw = "{_system}/dhw/{_deviceId}";
  public $_dhw_configuration_SENSO = "{_system}/dhw/configuration";
 
  public $_dhw_configuration = "{_system}/dhw/{_deviceId}/configuration";
// circulation
  public $_circulation = "{_system}/dhw/{_deviceId}/circulation";
  public $_circulation_configuration = "{_system}/dhw/{_deviceId}/circulation/configuration";
  public $_circulation_timeprogram = "{_system}/dhw/{_deviceId}/circulation/configuration/timeprogram";

// hotwater
  public $_hotwater = "{_system}/dhw/{_deviceId}/hotwater";
  public $_hotwater_configuration = "{_system}/dhw/{_deviceId}/hotwater/configuration";
  public $_hotwater_timeprogram = "{_system}/dhw/{_deviceId}/hotwater/configuration/timeprogram";
  public $_hotwater_operating_mode = "{_system}/dhw/{_deviceId}/hotwater/configuration/operation_mode";
  public $_hotwater_temperature_setpoint = "{_system}/dhw/{_deviceId}/hotwater/configuration/temperature_setpoint";
  
  public $_hotwater_SENSO = "{_system}/dhw/hotwater";
  public $_hotwater_configuration_SENSO = "{_system}/dhw/hotwater/configuration";
  public $_hotwater_timeprogram_SENSO = "{_system}/dhw/hotwater/configuration/timeprogram";
  public $_hotwater_operating_mode_SENSO = "{_system}/dhw/hotwater/configuration/operation_mode";
  public $_hotwater_temperature_setpoint_SENSO = "{_system}/dhw/hotwater/configuration/hotwater_temperature_setpoint";
  	
  public $_hotwater_boost_SENSO = "{_system}/dhw/configuration/hotwater_boost";
 //configuration/hotwater_boost
  //"temperature_setpoint";//hotwater_temperature_setpoint,setpoint_temperature
  
  
// ventilation
  public $_system_ventilation = "{_system}/ventilation";
  public $_ventilation = "{_system}/ventilation/{_deviceId}";
  public $_ventilation_configuration = "{_system}/ventilation/{_deviceId}/fan/configuration";
  public $_ventilation_timeprogram = "{_system}/ventilation/{_deviceId}/fan/configuration/timeprogram";
  public $_ventilation_day_level = "{_system}/ventilation/{_deviceId}/fan/configuration/day_level";
  public $_ventilation_night_level = "{_system}/ventilation/{_deviceId}/fan/configuration/night_level";
  public $_ventilation_operating_mode = "{_system}/ventilation/{_deviceId}/fan/configuration/operation_mode";
// zones
  public $_zones_list = "{_system}/zones";
  public $_zone = "{_system}/zones/{_deviceId}";
  public $_zone_configuration = "{_system}/zones/{_deviceId}/configuration";
  public $_zone_name = "{_system}/zones/{_deviceId}/configuration/name";
  public $_zone_quick_veto = "{_system}/zones/{_deviceId}/configuration/quick_veto";
  public $_zone_quickmode = "{_system}/zones/{_deviceId}/configuration/quickmode";										
// zone_heating
  public $_zone_heating_configuration = "{_system}/zones/{_deviceId}/heating/configuration";//  
  public $_zone_heating_timeprogram = "{_system}/zones/{_deviceId}/heating/timeprogram";
  public $_zone_heating_mode = "{_system}/zones/{_deviceId}/heating/configuration/mode";
  public $_zone_heating_cons = "{_system}/zones/{_deviceId}/heating/configuration/setpoint_temperature";
  public $_zone_heating_setback_temperature = "{_system}/zones/{_deviceId}/heating/configuration/setback_temperature";
  
  
  
// zone_cooling
  public $_zone_cooling_configuration = "{_system}/zones/{_deviceId}/cooling/configuration";
  public $_zone_cooling_timeprogram = "{_system}/zones/{_deviceId}/cooling/timeprogram";
  public $_zone_cooling_mode = "{_system}/zones/{_deviceId}/cooling/configuration/mode";
  public $_zone_cooling_cons = "{_system}/zones/{_deviceId}/cooling/configuration/setpoint_temperature";
  public $_zone_cooling_manual_cons = "{_system}/zones/{_deviceId}/cooling/configuration/manual_mode_cooling_temperature_setpoint";
  public $_testvar = '{_system}/zones/$device_id';
  
  
// ************************************************************************************************************************** //  	
  	

// ************************************************************************************************************************** //  	
	public function testvar($device_id){
        //log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri("_testvar");
      	eval( "\$commandUri = \"$commandUri\";" );
      	//$var2 = eval( "\$".data.';' );
      	//eval( "\$var2 = \"$commandUri\";" );
      //eval( $commandUri );eval( $this->$data)
      	return $commandUri." ---- ".$var." -- ".$var2;//.eval("".$data);
    }
	
// ************************************************************************************************************************** //  	
	public function _setDhwMode0($mode, $deviceId = null){
      	$url = $this->_getUri("_hotwater_configuration_SENSO");
      	$postFields = [];
      	$avalaible_Modes=["TIME_CONTROLLED","MANUAL","OFF"];
      	//$mode = strtoupper($mode);
      	if(!is_null($mode) && in_array(strtoupper($mode), $avalaible_Modes)){
          	$suffix = "operation_mode";
          	$url .= "/".$suffix;
          	$postFields[$suffix] = strtoupper($mode);//"TIME_CONTROLLED";"MANUAL";"OFF"
          
          	$deviceId = $deviceId ? $deviceId : "Control_DHW";
            //$url = str_replace("{_deviceId}", $deviceId, $url);
            $return = $this->_rqstApi($url, 'PUT', $postFields);
            $return["postFields"] = $postFields;
          	$return["rqstUrl"] = parse_url($url, PHP_URL_PATH);//$url;//
          	$return["function"] = __FUNCTION__;
          	return $return;
        }
      	else {
          	$err_msg = " ".__FUNCTION__." Invalid mode : {$mode}";
          	throw new Exception($err_msg);
        }
      	
      	
    }
	
// ************************************************************************************************************************** //  	
	public function _setDhwTemp($temperature, $deviceId = null){
      	//log::add('VaillantControl', 'debug', ''.__FUNCTION__ ."  start temperature: $temperature  deviceId: $deviceId");
      	$temperature = +$temperature;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 35 || $temperature > 70){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
        
      	$postFields = [];
      	$url = $this->_getUri("_hotwater_configuration_SENSO");
      	$suffix = "hotwater_temperature_setpoint";
      	$url .= "/".$suffix;
      	$postFields[$suffix] = +$temperature;//59;//
      	$deviceId = $deviceId ? $deviceId : "Control_DHW";
      	$url = str_replace("{_deviceId}", $deviceId, $url);
      	$return = $this->_rqstApi($url, 'PUT', $postFields);
      	return $return;
              	
    }
   	
// ************************************************************************************************************************** //  	
	public function _setDhwMode($mode, $deviceId = null){
      	$avalaibleModes=["TIME_CONTROLLED","AUTO","MANUAL","OFF","HOTWATER_BOOST","AWAY","HOTWATER_BOOST_DISABLE","HWB_AWAY_DISABLE"];
      	$mode = strtoupper($mode);
      	if($mode=="AUTO") $mode = "TIME_CONTROLLED";
      	if(!is_null($mode) && in_array(strtoupper($mode), $avalaibleModes)){
          	switch($mode){
                case "TIME_CONTROLLED":
                case "MANUAL":
                case "OFF":
                case "AUTO":
                	$method = "PUT";
                	$url = $this->_getUri("_hotwater_configuration_SENSO");
                	$url .= "/operation_mode";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	$postFields = ["operation_mode" => $mode];
                	break;
                case "HOTWATER_BOOST":
                	$method = "PUT";
                	$postFields =[];
                	 $url = $this->_getUri("_dhw_configuration_SENSO");
                	$url .= "/hotwater_boost";
                	break;
                case "AWAY":
                	$method = "PUT";
                	$postFields =[];
                	$url = $this->_getUri("_dhw_configuration_SENSO");
                	$url .= "/away";
                	$postFields = [
              				"start_datetime" => date("Y-m-d\TH:i:s.Z"),//"2022-03-01T13:56:20.3600",//date("Y-m-d\TH:i:sZ")
              				"end_datetime" => date("Y-m-d\TH:i:s.Z", strtotime("+ 300 minutes")),//"2022-03-02T13:56:20.3600"
                	];
                	break;
                case "HOTWATER_BOOST_DISABLE":
                	$method = "DELETE";
                	$postFields =[];
                	$url = $this->_getUri("_dhw_configuration_SENSO");
                	$url .= "/hotwater_boost";//hotwater_boost_disable
                	break;
                case "HWB_AWAY_DISABLE":
                	// ! HWB_AWAY_DISABLE is fictive mode =>
                	$method = "DELETE";//away_disable
                	$postFields =[];
                	$url = $this->_getUri("_dhw_configuration_SENSO");
                	$url .= "/away";
                	break;
            }
          	$return = $this->_rqstApi($url, $method, $postFields);
            //$return["postFields"] = $postFields;
          	//$return["rqstUrl"] = parse_url($url, PHP_URL_PATH);//$url;//
          	//$return["function"] = __FUNCTION__;
          	return $return;
          
        }
      	else{
          	log::add('VaillantControl', 'warning', ''.__FUNCTION__ .' invalid mode : '. $mode );
          	return ' 😡 invalid mode '. $mode;
		}
      
    }

// ************************************************************************************************************************** //  	
	public function _setDhwQm($mode, $deviceId = null){
     	$postFields = [];
      	$avalaible_Modes=["HOTWATER_BOOST","MANUAL","OFF"];
      	$arSuffix=["","quick_veto","quickmode","current_quickmode"];//,"quickmode","quick_veto"
      	$dataR =[];
      	foreach($arSuffix as $key=>$suffix){
          	$url = "https://smart.vaillant.com/mobile/api/v4/";
      		$url = $this->_getUri("_zone_configuration");
          	$url .= "/".$suffix;
          	$arFields = ["", "quickmode", "quickMode", "mode", "current_quickmode", "quick_mode", "quick_veto", "quickVeto", "HOTWATER_BOOST"];
          	$arFields = [""];
          	foreach($arFields as $keyf=>$field){
              	//$arQv = ["QUICK_VETO","QUICK_VETO","QUICK_VETO"];
          		$postFields = [ 
                                "duration" => date("Y-m-d\TH:i:00.Z", strtotime("+ 360 minutes")),//240,
                                "temperature_setpoint" => 18.5,//"2022-03-02T05:56:00.3600",//date("Y-m-d\TH:i:sZ")
                                //$field => "AWAY",
                              	
                ];//QM_HOTWATER_BOOST QM_HOTWATER_BOOST 
              	//
              	$postFields = [
              				"start_datetime" => "2022-03-01T13:56:20.3600",//date("Y-m-d\TH:i:sZ")
              				"end_datetime" => "2022-03-02T13:56:20.3600"
                ];
              	$return = $this->_rqstApi($url, 'PUT', $postFields);
              	if ($return == "ok"){
                    $dataR[$suffix] = $field;
                    return [$suffix => $field];
                }
              	else{
                  $dataR[$key.$keyf."_/".$suffix] = "	".json_encode($postFields, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    							." ".$return["code"]." (". $return["msg"].")";
                  //$dataR["/".$suffix."---".json_encode($postFields)] = $return["code"]."--". $return["msg"];
                  
                  
                }
                sleep(1);
        	}
        }//$mode = strtoupper($mode);
      	
      	return $dataR;
      
      
      $url = "https://smart.vaillant.com/mobile/api/v4/";
      	$url .="facilities/21211900202826970938020910N8/systemcontrol/tli/v1/zones/Control_DHW/configuration";
      	
      
      if(!is_null($mode) && in_array(strtoupper($mode), $avalaible_Modes)){
          	$suffix = "quick_veto"; //quickmode  quickVeto; quick_veto; quick_mode; quickmode "current_quickmode": "HOTWATER_BOOST"
          	$url .= "/".$suffix;
          	$postFields = [
              			//"current_quickmode" => "HOTWATER_BOOST",// strtoupper($mode),//"QM_HOTWATER_BOOST";"MANUAL";"OFF",
              			
          				"mode" => "HOTWATER_BOOST",// strtoupper($mode),//"QM_HOTWATER_BOOST";"MANUAL";"OFF",
              			//"quickmode" => "HOTWATER_BOOST",//"QM_HOTWATER_BOOST";"MANUAL";"OFF",
              			//"quick_veto" => "HOTWATER_BOOST",
              			//"quick_veto" => ["quickmode" => "QM_HOTWATER_BOOST"],
						//"manual_mode_temperature_setpoint" => 19,
              			//"active" =>  true,
              			//"temperature_setpoint" => 17,// $temperature,
              			//$suffix => "HOTWATER_BOOST",//strtoupper($mode),
          	];
          
          
          	$deviceId = $deviceId ? $deviceId : "Control_DHW";//"Control_ZO1";
            $url = str_replace("{_deviceId}", $deviceId, $url);
          	
                      
            $return = $this->_rqstApi($url, 'PUT', $postFields);
            $return["rqstUrl"] = $url;//parse_url($url, PHP_URL_PATH);//
          	$return["function"] = __FUNCTION__;
          	$return["postFields"] = $postFields;
          	return $return;
        }
      	else {
          	$err_msg = " ".__FUNCTION__." Invalid mode : {$mode}";
          	throw new Exception($err_msg);
        }
      	
      	
    }
// ************************************************************************************************************************** //  	
  	public function _testZqv($mode, $deviceId = null){
      //"QUICK_VETO", "AWAY", "HOTWATER_BOOST", "VENTILATION_BOOST", "COOLING_FOR_X_DAYS", "ONE_DAY_AWAY", "ONE_DAY_AT_HOME", "PARTY", "SYSTEM_OFF", "app_miLinkRelease"
      	$postFields = [];
      	//$avalaible_Modes=["HOTWATER_BOOST","MANUAL","OFF"];
      	$dataR =[];
      	$base = "https://smart.vaillant.com/mobile/api/v4/facilities/21211900202826970938020910N8/systemcontrol/tli/v1";
      	$arurl=[
				//"zones/Control_ZO1/configuration/quick_veto", 
				//"configuration",
				//"configuration/quick_veto",
				"zones/Control_ZO1/configuration",
          		"zones/Control_ZO1/configuration/quick_veto",  
				"zones/Control_ZO1/configuration/quickmode",//OK,  
				//"configuration/quickmode",//No
				//"zones/Control_ZO1/heating/configuration",
				
        ];
      //https://smart.vaillant.com/mobile/api/v4/facilities/21211900202826970938020910N8/systemcontrol/tli/v1/configuration/system_off
            //$url .= "facilities/21211900202826970938020910N8/systemcontrol/tli/v1/zones/configuration";//quick_vet
            //$url = $this->_getUri("_zone_heating_configuration");//_zone_heating_configuration; _zone_configuration
		foreach($arurl as $keyu=>$exturl){
			$url = $base."/".$exturl;
         $arFields = ["expires_at", "quick_veto_duration","duration"];
          $arFields = [
          			date("Y-m-d\TH:i:00.Z", strtotime("+ 180 minutes")),
                    date("Y-m-d\TH:i.Z", strtotime("+ 180 minutes")),     
          			date("Y-m-d H:i:00", strtotime("+ 180 minutes")),
                    180,
                    180*60,
                    180,3600,
                    time(),
                    time()*1000,
          ];
          $arFields = [""];
           $arFields = ["quick_veto", "quickmode", ""];
			//,"remainingDuration","expires_at", "quick_veto_duration","duration", ""; "mode","current_quickmode",
          //$arFields = ["duration" ,"remainingDuration"];
			//$arSuffix=["","quick_veto","quickmode", "current_quickmode" ,];//,"quickmode","quick_veto"
			foreach($arFields as $keyf=>$field){
              	
              	$postFields = [
                  				$field => "QUICK_VETO",
                  				//"duration" => 300,
                                //$field => 360,
                  				//"enabled" =>true
                  				//"duration" => 360,
                  				"expires_at" =>  date("Y-m-d\TH:i:00.Z", strtotime("+ 360 minutes")),
                ];
              	$postFields = [
              					"temperature_setpoint" => 22,
              					//"quick_veto" => "QUICK_VETO",
              					//$field => date("Y-m-d\TH:i:00.Z", strtotime("+ 180 minutes")),
                             	//"expires_at" => $field,
                             	//"expires_at" => $field,
                             	//"expires_at" =>  date("Y-m-d\TH:i:00.Z", strtotime("+ 180 minutes")),
                ];
              	
              /*$postFields = [
              					//$field => "QUICK_VETO",//"",//QM_QUICK_VETO
                                  	"duration" =>180
                                  	//"expires_at" =>  date("Y-m-d\TH:i:00.Z", strtotime("+ 180 minutes"))
                                
                ];*/
              $postFields = [
                		//"temperature_setpoint" => 20,
                              		"duration" => 1,
                  			/*
              				$field => [
                              		/"expires_at" =>  date("Y-m-d\TH:i:00.Z", strtotime("+ 360 minutes")),
                                    ]*/
                             
                ];
              	$return = $this->_rqstApi($url, 'PUT', $postFields);
              	if ($return == "ok"){
                    $dataR["Ok" .$exturl."=>".$field] = $return["code"];
                    return [ $keyu.$exturl."=>".$field=> $return["code"]." ".json_encode($postFields, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)];
                }
              	else{
                  $dataR[$keyu." ".$exturl."=>".$field] = "	".json_encode($postFields, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    							." ".$return["code"]." (". $return["msg"].")";
                  //$dataR["/".$suffix."---".json_encode($postFields)] = $return["code"]."--". $return["msg"];
                  
                  
                }
                sleep(1);
        	}
            
        }//$mode = strtoupper($mode);
      	
      	return $dataR;
      
      
      
      	
      	
    }
// ************************************************************************************************************************** //  	
  	public function _setHeatMode($mode, $deviceId = null){
      	$url = $this->_getUri("_zone_heating_configuration");
      	$postFields = [];
      	$avalaible_Modes=["TIME_CONTROLLED","MANUAL","OFF","QUICK_VETO"];
      	//$mode = strtoupper($mode);
      	if(!is_null($mode) && in_array(strtoupper($mode), $avalaible_Modes)){
          	$url = "https://smart.vaillant.com/mobile/api/v4/";
            //$url .= "facilities/21211900202826970938020910N8/systemcontrol/tli/v1/zones/Control_ZO1/configuration";
          	$url .= "facilities/21211900202826970938020910N8/systemcontrol/tli/v1/zones/Control_ZO1/heating/configuration";
          	$suffix = "operation_mode";//"operation_mode";
          	$url .= "/".$suffix;
          	$postFields = [
          				"operation_mode" => strtoupper($mode),//"TIME_CONTROLLED";"MANUAL";"OFF",
						//"manual_mode_temperature_setpoint" => 19,
              			///"active" =>  false,
              			//"current_quickmode" => "QUICK_VETO",
              			//"temperature_setpoint" => 17,// $temperature,
              			//"expires_at" =>  false,
          	];
          
           
          	$deviceId = $deviceId ? $deviceId : "Control_ZO1";
           	$url = str_replace("{_deviceId}", $deviceId, $url);
            $return = $this->_rqstApi($url, 'PUT', $postFields);
            $return["postFields"] = $postFields;
          	$return["rqstUrl"] = parse_url($url, PHP_URL_PATH);//$url;//
          	$return["function"] = __FUNCTION__;
          	return $return;
        }
      	else {
          	$err_msg = " ".__FUNCTION__." Invalid mode : {$mode}";
          	throw new Exception($err_msg);
        }
      	
      	
    }
// ************************************************************************************************************************** //  	
  	public function _setZoneMode($mode, $deviceId = null){
      	$avalaibleModes = ["TIME_CONTROLLED", "AUTO", "MANUAL", "OFF", "QUICK_VETO","QUICK_VETO_DISABLE", "VENTILATION_BOOST", "VENTILATION_BOOST_DISABLE", "AWAY", "AWAY_DISABLE", "SYSTEM_OFF", "SYSTEM_OFF_DISABLE"];
      	$mode = strtoupper($mode);
      	if($mode=="AUTO") $mode = "TIME_CONTROLLED";
      	if(!is_null($mode) && in_array(strtoupper($mode), $avalaibleModes)){
          	switch($mode){
                case "TIME_CONTROLLED":
                case "MANUAL":
                case "OFF":
                case "AUTO":
                	$method = "PUT";
                	$postFields = ["operation_mode" => $mode];
                	$url = $this->_getUri("_zone_heating_configuration");
                	$url .= "/operation_mode";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
				case "QUICK_VETO":
					
                	break;
                case "VENTILATION_BOOST":
					$method = "PUT";
					$postFields =[];
					$url = $this->_getUri("_zone_configuration");
					$url .= "/ventilation_boost";//
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "SYSTEM_OFF":
					$method = "PUT";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/system_off";
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "SYSTEM_OFF_DISABLE":
					$method = "DELETE";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/system_off";
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "AWAY":
                	$method = "PUT";
                	$postFields = [
							"start_datetime" => date("Y-m-d\TH:i:00.Z"),//"2022-03-02T00:03:01.000Z"
							"end_datetime" => date("Y-m-d\TH:i:00.Z", strtotime("+ 300 minutes")),//"2022-03-03T00:03:01.000Z"
							"temperature_setpoint" => 19 //mandatory
                	];
                	$url = $this->_getUri("_zone_configuration");//_zone_heating_configuration
                	$url .= "/away";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "QUICK_VETO_DISABLE":
                	$method = "DELETE";
                	$postFields =[];
                	$url = $this->_getUri("_zone_configuration");
                	$url .= "/quick_veto";//hotwater_boost_disable
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "AWAY_DISABLE":
                	// ! HWB_AWAY_DISABLE is fictive mode =>
                	$method = "DELETE";//away_disable
                	$postFields =[];
                	$url = $this->_getUri("_zone_configuration");
                	$url .= "/away";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                
            }
          	$return = $this->_rqstApi($url, $method, $postFields);
            //$return["postFields"] = $postFields;
          	//$return["rqstUrl"] = parse_url($url, PHP_URL_PATH);//$url;//
          	//$return["function"] = __FUNCTION__;
          	return $return;
          
        }
      	else{
          	log::add('VaillantControl', 'warning', ''.__FUNCTION__ .' invalid mode : '. $mode );
          	return ' Plugin Infos : 😡 invalid mode '. $mode;
		}
      
    }
// ************************************************************************************************************************** //  	
  	public function _setZoneQV($temperature, $deviceId = null, $duration = null){
      	$temperature = +$temperature;
		$duration = +$duration;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 5 || $temperature > 30){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
        if($duration == null){
			$duration = 3;
            //$err_msg = " ".__FUNCTION__." Invalid duration : {$temperature}";
			//throw new Exception($err_msg);
		}elseif(!is_numeric($duration) || $duration > 12 || $duration < 0.5){
			$err_msg = " ".__FUNCTION__." Invalid duration... given : {$duration} hours";
			throw new Exception($err_msg);
		}   
      	  
      	$url = $this->_getUri("_zone_configuration");//_zone_quick_veto
		$url .= "/quick_veto";//"/quick_veto";//current_quickmode
		$url = str_replace("{_deviceId}", $deviceId, $url);
		
		$postFields = [
					"temperature_setpoint" => $temperature,
					"duration" => $duration 	
		];
		$return = $this->_rqstApi($url, 'PUT', $postFields);
        return $return;
  }
// ************************************************************************************************************************** //  	
  	public function _setZoneAway($temperature, $end_datetime = null, $deviceId = null){
		$temperature = +$temperature;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 5 || $temperature > 30){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
        $start_datetime = date("Y-m-d\TH:i:00.Z");
        if(is_null($end_datetime) ){
			$end_datetime =  date("Y-m-d\TH:i:00.Z", strtotime("+ 300 minutes"));
		}   
		$postFields = [
				"start_datetime" => $start_datetime,//"2022-03-02T00:03:01.000Z"
				"end_datetime" => $end_datetime,//"2022-03-03T00:03:01.000Z"
				"temperature_setpoint" => +$temperature //mandatory
		];
		$url = $this->_getUri("_zone_configuration");//_zone_heating_configuration
		$url .= "/away";
		$url = str_replace("{_deviceId}", $deviceId, $url);
		$return = $this->_rqstApi($url, 'PUT', $postFields);
        return $return;
  }  
// ************************************************************************************************************************** //  	
  	public function _setZoneQM($quickmode, $deviceId = null){
      	$avalaibleModes = ["TIME_CONTROLLED", "AUTO", "MANUAL", "OFF", "QUICK_VETO","QUICK_VETO_DISABLE", "VENTILATION_BOOST", "VENTILATION_BOOST_DISABLE", "AWAY", "AWAY_DISABLE", "SYSTEM_OFF", "SYSTEM_OFF_DISABLE"];
      	$mode = strtoupper($mode);
      	if($mode=="AUTO") $mode = "TIME_CONTROLLED";
      	if(!is_null($mode) && in_array(strtoupper($mode), $avalaibleModes)){
          	switch($mode){
               case "VENTILATION_BOOST":
					$method = "PUT";
					$postFields =[];
					$url = $this->_getUri("_zone_configuration");
					$url .= "/ventilation_boost";//
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "VENTILATION_BOOST_DISABLE":
                	$method = "DELETE";
                	$postFields =[];
                	$url = $this->_getUri("_zone_configuration");
                	$url .= "/quick_veto";//hotwater_boost_disable
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "SYSTEM_OFF":
					$method = "PUT";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/system_off";
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "SYSTEM_OFF_DISABLE":
					$method = "DELETE";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/system_off";
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "AWAY":
                	$method = "PUT";
                	$postFields = [
							"start_datetime" => date("Y-m-d\TH:i:00.Z"),//"2022-03-02T00:03:01.000Z"
							"end_datetime" => date("Y-m-d\TH:i:00.Z", strtotime("+ 300 minutes")),//"2022-03-03T00:03:01.000Z"
							"temperature_setpoint" => 19 //mandatory
                	];
                	$url = $this->_getUri("_zone_configuration");//_zone_heating_configuration
                	$url .= "/away";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "AWAY_DISABLE":
                	$method = "DELETE";//away_disable
                	$postFields =[];
                	$url = $this->_getUri("_zone_configuration");
                	$url .= "/away";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                
            }
          	$return = $this->_rqstApi($url, $method, $postFields);
            //$return["postFields"] = $postFields;
          	//$return["rqstUrl"] = parse_url($url, PHP_URL_PATH);//$url;//
          	//$return["function"] = __FUNCTION__;
          	return $return;
          
        }
      	else{
          	log::add('VaillantControl', 'warning', ''.__FUNCTION__ .' invalid mode : '. $mode );
          	return ' Plugin Infos : 😡 invalid mode '. $mode;
		}
      	
    }
// ************************************************************************************************************************** //  	
  	public function _setSystemMode($mode, $deviceId = null){
      	$avalaibleModes = ["QUICK_VETO_DISABLE", "VENTILATION_BOOST", "VENTILATION_BOOST_DISABLE", "AWAY", "AWAY_DISABLE", "SYSTEM_OFF", "SYSTEM_OFF_DISABLE","MANUAL_COOLING","MANUAL_COOLING_DISABLE"];
      	$mode = strtoupper($mode);
      	if($mode=="AUTO") $mode = "TIME_CONTROLLED";
      	if(!is_null($mode) && in_array(strtoupper($mode), $avalaibleModes)){
          	switch($mode){
                case "VENTILATION_BOOST":
					$method = "PUT";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/ventilation_boost";//
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                 case "VENTILATION_BOOST_DISABLE":
					$method = "DELETE";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/ventilation_boost";//
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "SYSTEM_OFF":
					$method = "PUT";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/system_off";
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "SYSTEM_OFF_DISABLE":
					$method = "DELETE";
					$postFields =[];
					$url = $this->_getUri("_system_configuration");
					$url .= "/system_off";
					$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "AWAY":
                	$method = "PUT";
                	$postFields = [
							"start_datetime" => date("Y-m-d\TH:i:00.Z"),//"2022-03-02T00:03:01.000Z"
							"end_datetime" => date("Y-m-d\TH:i:00.Z", strtotime("+ 300 minutes")),//"2022-03-03T00:03:01.000Z"
							"temperature_setpoint" => 19 //mandatory
                	];
                	$url = $this->_getUri("_system_configuration");//_zone_heating_configuration
                	$url .= "/away";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "AWAY_DISABLE":
                	// ! HWB_AWAY_DISABLE is fictive mode =>
                	$method = "DELETE";//away_disable
                	$postFields =[];
                	$url = $this->_getUri("_system_configuration");
                	$url .= "/away";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                case "MANUAL_COOLING":
                	$method = "PUT";
                	$postFields = [
							"start_datetime" => date("Y-m-d\TH:i:00.Z"),//"2022-03-02T00:03:01.000Z"
							"end_datetime" => date("Y-m-d\TH:i:00.Z", strtotime("+ 300 minutes")),//"2022-03-03T00:03:01.000Z"
							"temperature_setpoint" => 19 //mandatory
                	];
                	$url = $this->_getUri("_system_configuration");//_zone_heating_configuration
                	$url .= "/manual_cooling";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                case "MANUAL_COOLING_DISABLE":
                	// ! HWB_AWAY_DISABLE is fictive mode =>
                	$method = "DELETE";//away_disable
                	$postFields =[];
                	$url = $this->_getUri("_system_configuration");
                	$url .= "/manual_cooling";
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                break;case "QUICK_VETO_DISABLE":
                	$method = "DELETE";
                	$postFields =[];
                	$url = $this->_getUri("_system_configuration");
                	$url .= "/quick_veto";//hotwater_boost_disable
                	$url = str_replace("{_deviceId}", $deviceId, $url);
                	break;
                
                
            }
          	$return = $this->_rqstApi($url, $method, $postFields);
            return $return;
          
        }
      	else{
          	log::add('VaillantControl', 'warning', ''.__FUNCTION__ .' invalid mode : '. $mode );
          	return ' Plugin Infos : 😡 invalid mode '. $mode;
		}
      
    }
// ************************************************************************************************************************** //  	
  	public function _setSystemAway($temperature, $end_datetime = null, $deviceId = null){
		$temperature = +$temperature;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 5 || $temperature > 30){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
        $start_datetime = date("Y-m-d\TH:i:00.Z");
        if(is_null($end_datetime) ){
			$end_datetime =  date("Y-m-d\TH:i:00.Z", strtotime("+ 300 minutes"));
		}   
		$postFields = [
				"start_datetime" => $start_datetime,//"2022-03-02T00:03:01.000Z"
				"end_datetime" => $end_datetime,//"2022-03-03T00:03:01.000Z"
				"temperature_setpoint" => +$temperature //mandatory
		];
		$url = $this->_getUri("_system_configuration");//_zone_heating_configuration
		$url .= "/away";
		$url = str_replace("{_deviceId}", $deviceId, $url);
		$return = $this->_rqstApi($url, 'PUT', $postFields);
        return $return;
  }  
// ************************************************************************************************************************** //  	
  	public function _setZoneManual($temperature, $deviceId = null){
      	$this->_changeHeatManualCons($temperature, $deviceId);
      	$this->_setZoneMode("MANUAL", $deviceId);
    }
// ************************************************************************************************************************** //  	
  	public function _setZoneTemp2($temperature, $active, $deviceId = null){
      	//$url = $this->_getUri("_hotwater_temperature_setpoint");
      	//$url = "https://smart.vaillant.com/mobile/api/v4/facilities/21211900202826970938020910N8/systemcontrol/tli/v1/dhw/";
      	$url = $this->_getUri("_zone_configuration");
      	
          
      	$postFields = [];
      	if(!is_null($temperature) ){
          	//$url = $this->_getUri("_hotwater_temperature_setpoint");
          	//$url .= "Control_DHW/hotwater/configuration/hotwater_temperature_setpoint";
        	$suffix = "quick_veto";
          	$url .= "/".$suffix;
          	//$postFields[$suffix] = $temperature;
          	//"current_quickmode": "QUICK_VETO",
          	$postFields = [
          				//"active" =>  false,
              			//"current_quickmode" => "QUICK_VETO",
              			"temperature_setpoint" => 17,// $temperature,
						//"expires_at" =>  false,
          	];
          
          	$deviceId = $deviceId ? $deviceId : "Control_ZO1";
           	$url = str_replace("{_deviceId}", $deviceId, $url);
            $return = $this->_rqstApi($url, 'PUT', $postFields);
            $return["postFields"] = $postFields;
          	$return["rqstUrl"] = parse_url($url, PHP_URL_PATH);//$url;//
          	$return["function"] = __FUNCTION__;
          	return $return;
        }else{
          	$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
          	throw new Exception($err_msg);
        }
      	
    }
// ************************************************************************************************************************** //  	
  	public function _changeHeatManualCons($temperature, $deviceId = null){
      	$temperature = +$temperature;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 5 || $temperature > 30){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
          
      	$postFields = [];
      	$url = $this->_getUri("_zone_heating_configuration");
      	$suffix = "manual_mode_temperature_setpoint";
      	$url .= "/".$suffix;
      	$postFields = [
          			$suffix => +$temperature,
      	];
          
      	$deviceId = $deviceId ? $deviceId : "Control_ZO1";
      	$url = str_replace("{_deviceId}", $deviceId, $url);
      	$return = $this->_rqstApi($url, 'PUT', $postFields);
      	return $return;
    }
// ************************************************************************************************************************** //  	
  	public function _changeHeatAwayCons($temperature, $deviceId = null){
      	$temperature = +$temperature;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 5 || $temperature > 30){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
          
      	$postFields = [];
		$url = $this->_getUri("_zone_configuration");
		$suffix = "away";
		$url .= "/".$suffix;
      	$zoneConfig = $this->getZoneConfig($deviceId);
		$end_datetime = $zoneConfig["away"]["end_datetime"];
      	$postFields = [
          			"temperature_setpoint" => +$temperature,
              		"start_datetime" => date("Y-m-d\T00:00:00.Z"),//"2022-03-02T00:03:01.000Z"
					"end_datetime" => $end_datetime
          //date("Y-m-d\TH:i:00.Z", strtotime("- 60 minutes")),//"2022-03-03T00:03:01.000Z"
		];
		$deviceId = $deviceId ? $deviceId : "Control_ZO1";
		$url = str_replace("{_deviceId}", $deviceId, $url);
		$return = $this->_rqstApi($url, 'PUT', $postFields);
		return $return;
	}
  	public function getZoneConfig($deviceId){
        //log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$url = $this->_getUri('_zone_configuration');
      	$deviceId = $deviceId ? $deviceId : "Control_ZO1";
		$url = str_replace("{_deviceId}", $deviceId, $url);
		return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	public function _changeHeatEcoCons($temperature, $deviceId = null){
      	$temperature = +$temperature;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 5 || $temperature > 30){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
		$url = $this->_getUri("_zone_heating_configuration");
		$suffix = "setback_temperature_setpoint";
		$url .= "/".$suffix;
		$postFields = [
          			$suffix => +$temperature,
		];
		$deviceId = $deviceId ? $deviceId : "Control_ZO1";
		$url = str_replace("{_deviceId}", $deviceId, $url);
		$return = $this->_rqstApi($url, 'PUT', $postFields);
		return $return;
	}
// ************************************************************************************************************************** //  	
  	public function _changeHeatQvCons($temperature, $deviceId = null){
      	$temperature = +$temperature;
		if(is_null($temperature) || !is_numeric($temperature) || $temperature < 5 || $temperature > 30){
			$err_msg = " ".__FUNCTION__." Invalid Temperature : {$temperature}";
			throw new Exception($err_msg);
		}  
		$url = $this->_getUri("_zone_configuration");//_zone_quick_veto
		$url .= "/quick_veto";//"/quick_veto";//current_quickmode
		$url = str_replace("{_deviceId}", $deviceId, $url);
		
		$postFields = [
          			"temperature_setpoint" => +$temperature,
		];
		$return = $this->_rqstApi($url, 'PUT', $postFields);
		return $return;
	}
// ************************************************************************************************************************** //  	
  	public function _setTemp($temperature, $active, $deviceId = null){
      ///facilities/21211900202826970938020910N8/systemcontrol/tli/v1/zones/Control_ZO1/configuration
     	$url = $this->_getUri("_zone_quick_veto");
      	//$url = $this->_getUri("_hotwater_temperature_setpoint");
      	$deviceId = $deviceId ? $deviceId : "Control_ZO1";//"Control_DHW";
      	$url = str_replace("{_deviceId}", $deviceId, $url);
      	
      	//$suffix ="temperature_setpoint";
      	//$suffix ="hotwater_temperature_setpoint";
      	//$url = "https://smart.vaillant.com/mobile/api/v4/facilities/21211900202826970938020910N8/systemcontrol/v1/dhw/Control_DHW/hotwater/configuration/";
      	//$url = "https://smart.vaillant.com/mobile/api/v4/facilities/21211900202826970938020910N8/systemcontrol/v1/dhw/Control_DHW/hotwater/configuration/".$suffix;
      	//$url =  		"https://smart.vaillant.com/mobile/api/v4/facilities/21180900202529260938010427N8/systemcontrol/v1/dhw/Control_DHW/hotwater/configuration/temperature_setpoint".$suffix;
        //$this->_login();
      	//$jsessionid = $this->_jsessionid;
      	//$authToken = $this->_authToken;
      	$postFields = [ 
          			"temperature_setpoint" => 51,
          			
          			"id" => "Control_DHW",
          			"serial" => 51,
          			"hotwater_temperature_setpoint" => 51,
          			"dhw_id" => "Control_DHW",
        			"temperature" => 51,
        			//"JSESSIONID" => $this->_jsessionid,
        			"authToken" => $this->_authToken,//"current_desired_setpoint" => $temperature,
          			//"quick_veto" => $temperature,
          
        			//"authToken" => $this->_authToken,
        ];
      	$postFields = [
          				"temperature_setpoint" => 17,
						//"active" =>  true,
          			];
      	$httpheader = array();
      	$cookie = true;
        //$return = $this->_makeRequest($url, 'PUT' , $postFields, $cookie, $httpheader );
        $return = $this->_rqstApi($url, 'PUT', $postFields);
          //$return = $this->_makeCurlRequest($url, 'PUT', $postFields , $httpheader);	
      	return $return;
    }
// ************************************************************************************************************************** //  	
  	public function getFacilities(){
		$data = $this->_rqstApi('_facilities_list');
      	$facilitiesList = isset($data["facilitiesList"]) ? $data["facilitiesList"] : false;
      	return $facilitiesList;
    }
// ************************************************************************************************************************** //  	
  	public function setCurrentFacility($serialNumber){
        $this->_currentFacility = $serialNumber;
    }
// ************************************************************************************************************************** //  	
  	public function getGatewayType() {
      	$url = $this->_getUri('_gateway_type');
      	//log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	public function getFacilityStatus() {
      	$url = $this->_getUri('_facilities_status');
      	//log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	 public function getFacilityDetails(){
		//log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$url = $this->_getUri('_facilities_details');
      	return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	public function _getFacilityStorage(){
        //log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$url = $this->_getUri('_facilities_storage');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getFacilityDefaultStorage(){//_getFacilityDefaultSettings
        $url = $this->_getUri('_facilities_default_storage');
      	return $this->_rqstApi($url);
    }
    public function _getRoom(){
        $url = $this->_getUri('_rbr_base');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getRoomInstallationStatus(){
        $url = $this->_getUri('_rbr_installation_status');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getRoomUnderfloorHeatingStatus(){
        $url = $this->_getUri('_rbr_underfloor_heating_status');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getRoomList(){
        $url = $this->_getUri('_rooms_list');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getInstallerInfo(){
        $url = $this->_getUri('_installer_info');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getHvacState(){
        $url = $this->_getUri('_hvac');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getSystemControl(){
        $url = $this->_getUri('_system');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getSystemControlConfiguration() {
        $url = $this->_getUri('_system_configuration');
      	return $this->_rqstApi($url);
    }
    public function getSystemControlStatus() {
        $url = $this->_getUri('_system_status');
      	return $this->_rqstApi($url);
    }
    
// ************************************************************************************************************************** //  	
  	public function getZonesList(){
        $url = $this->_getUri('_zones_list');
      	return $this->_rqstApi($url);
     }

// ************************************************************************************************************************** //  	
  	public function getZones($deviceId){
        $url = $this->_getUri('_zone');
      	$url = str_replace("{_deviceId}", $deviceId, $commandUri);
      	return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	public function getLiveReport() {
        $url = $this->_getUri('_live_report');
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getLiveReportDevice($device_id, $report_id) {
      	$url = $this->_getUri('_live_report_device');
      	$url = str_replace("{_device_id}", $device_id, $commandUri);
      	$url = str_replace("{_report_id}", $report_id, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getEEBusShipNode(){
        $url = $this->_getUri('_eebus_node');
      	return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	public function getEmfInfos(){
      	$url = $this->_getUri('_emf_devices');
      	return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	public function getDeviceEmf($device_id){
       	$url = $this->_getUri("_emf_report_device");
      	//eval( "\$commandUri = \"$url\";" );
      	
      	$url = str_replace("{_deviceId}", $device_id, $url);
      	$queryParams = [
                  "timeRange" => "DAY",//String ["WEEK", "DAY", "MONTH", "YEAR"]
                  "function" => "CENTRAL_HEATING",//String
                  "energyType" => "CONSUMED_PRIMARY_ENERGY",//String=> CONSUMED_ELECTRICAL_POWER/
                  "start" => "2022-02-16",//String
                  "offset" => "0",//int i 0-6 (1=>mean +1day ago & +1day later)
		];
      	return $this->_rqstApi($url, 'GET', $queryParams);
    }
    
}

?>