<?php
namespace VaillantControlApi\Clients;
error_reporting(E_ALL);  
//use VaillantControlApi\Common\mVt_Data as mVt_Data;//NARestErrorCode::INVALID_authToken:
use log as log;
//use jeedom as jeedom;
use Exception as Exception;
//namespace app\components;
//require_once realpath(dirname(__FILE__) . '/../../../../core/php/core.inc.php');

	
class mVt_ApiMulti extends mVt_ApiClient {

    protected $conf = array();
  	//public $healthCheckPassed = false;
    //public $lastGeneratedCommand;
  	//private $_authToken;//*************************
  	//private $_jsessionid;
    //private $_currentFacility;
    //private $_loginRequired = false;
    //private $_lastApiMeta;
    //private $_enableCustomRequests = false;
 	//public $_prefix;// = 'tli/';
    //public $_rqstAll = array();
	
   //private $_apiVersion = 'v4';
  //public $_base = "https://smart.vaillant.com/mobile/api/v4";
 // private $_base_authenticate = "{_base}/account/authentication/v1";

// """ General"""
  //private $_authenticate = "{_base}/account/authentication/v1/authenticate";
  //private $_new_token = "{_base}/account/authentication/v1/token/new";
  //private $_logout = "{_base}/account/authentication/v1/logout";
  
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
  public $_room_device_name = "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/devices/{sgtin}/name";
  public $_room_temperature_setpoint = "{_base}/facilities/{_serial}/rbr/v1/rooms/{_roomId}/configuration/temperatureSetpoint";
//"""Repeaters"""
  public $_repeaters_list = "{_base}/facilities/{_serial}/rbr/v1/repeaters";
  public $_repeater_delete = "{_base}/facilities/{_serial}/rbr/v1/repeaters/{sgtin}";
  public $_repeater_set_name = "{_base}/facilities/{_serial}/rbr/v1/repeaters/{sgtin}/name";
// """HVAC (heating, ventilation and Air-conditioning)"""
  public $_hvac = "{_base}/facilities/{_serial}/hvacstate/v1/overview";
  public $_hvac_request_update = "{_base}/facilities/{_serial}/hvacstate/v1/hvacMessages/update";//cmd
// report
  public $_live_report = "{_base}/facilities/{_serial}/livereport/v1";
  public $_live_report_device = "{_base}/facilities/{_serial}/livereport/v1/devices/{device_id}/reports/{report_id}";
  public $_photovoltaics_report = "{_base}/facilities/{_serial}/spine/v1/currentPVMeteringInfo";
  public $_eebus_node = "{_base}/facilities/{_serial}/spine/v1/ship/self";

// """EMF (Embedded Metering Function)"""
  public $_emf_devices = "{_base}/facilities/{_serial}/emf/v1/devices";
  public $_emf_report_device = "{_base}/facilities/{_serial}/emf/v1/devices/{device_id}";

  // """System control""" ******************
   //public $_system = "{_base}/facilities/{_serial}/systemcontrol";
  //public $_system = "https://smart.vaillant.com/mobile/api/v4/facilities/{_serial}/systemcontrol";
  public $_system_configuration = "{_system}/configuration";//manual_cooling["start_date", "end_date"]
  public $_system_status = "{_system}/status";//outside_temperature
  public $_system_datetime = "{_system}/status/datetime";//404////"Method Not Allowed"
  public $_system_parameters = "{_system}/parameters";//set
  public $_system_quick_mode = "{_system}/configuration/quickmode";//set
  public $_system_holiday_mode = "{_system}/configuration/holidaymode";//set
// """DHW (Domestic Hot Water)"""
  public $_dhws = "{_system}/dhw";
  public $_dhw = "{_system}/dhw/{_dhwId}";
// circulation
  public $_circulation = "{_system}/dhw/{_dhwId}/circulation";
  public $_circulation_configuration = "{_system}/dhw/{_dhwId}/circulation/configuration";
  public $_circulation_timeprogram = "{_system}/dhw/{_dhwId}/circulation/configuration/timeprogram";
  //"/facilities/21180900202529260938010427N8/systemcontrol/v1/dhw/Control_SYS_MultiMatic"
    //"{_system}/dhw/Control_SYS_MultiMatic
  //"/facilities/21180900202529260938010427N8/systemcontrol/v1/dhw/Control_DHW"
  
  
  
// hot_water
  public $_hot_water = "{_system}/dhw/{_dhwId}/hotwater";
  public $_hot_water_configuration = "{_system}/dhw/{_dhwId}/hotwater/configuration";
  public $_hot_water_timeprogram = "{_system}/dhw/{_dhwId}/hotwater/configuration/timeprogram";
  public $_hot_water_operating_mode = "{_system}/dhw/{_dhwId}/hotwater/configuration/operation_mode";
  public $_hot_water_temperature_setpoint = "{_system}/dhw/{_dhwId}/hotwater/configuration/temperature_setpoint";
  
 
  
  
  
// ventilation
  public $_system_ventilation = "{_system}/ventilation";
  public $_ventilation = "{_system}/ventilation/{_ventilationId}";
  public $_ventilation_configuration = "{_system}/ventilation/{_ventilationId}/fan/configuration";
  public $_ventilation_timeprogram = "{_system}/ventilation/{_ventilationId}/fan/configuration/timeprogram";
  public $_ventilation_day_level = "{_system}/ventilation/{_ventilationId}/fan/configuration/day_level";
  public $_ventilation_night_level = "{_system}/ventilation/{_ventilationId}/fan/configuration/night_level";
  public $_ventilation_operating_mode = "{_system}/ventilation/{_ventilationId}/fan/configuration/operation_mode";
// zones
  public $_zones_list = "{_system}/zones";
  public $_zone = "{_system}/zones/{_zoneId}";
  public $_zone_configuration = "{_system}/zones/{_zoneId}/configuration";
  public $_zone_name = "{_system}/zones/{_zoneId}/configuration/name";
  public $_zone_quick_veto = "{_system}/zones/{_zoneId}/configuration/quick_veto";
// zone_heating
  public $_zone_heating_configuration = "{_system}/zones/{_zoneId}/heating/configuration";
  public $_zone_heating_timeprogram = "{_system}/zones/{_zoneId}/heating/timeprogram";
  public $_zone_heating_mode = "{_system}/zones/{_zoneId}/heating/configuration/mode";
  public $_zone_heating_setpoint_temperature = "{_system}/zones/{_zoneId}/heating/configuration/setpoint_temperature";
  public $_zone_heating_setback_temperature = "{_system}/zones/{_zoneId}/heating/configuration/setback_temperature";
  
  
  
// zone_cooling
  public $_zone_cooling_configuration = "{_system}/zones/{_zoneId}/cooling/configuration";
  public $_zone_cooling_timeprogram = "{_system}/zones/{_zoneId}/cooling/timeprogram";
  public $_zone_cooling_mode = "{_system}/zones/{_zoneId}/cooling/configuration/mode";
  public $_zone_cooling_setpoint_temperature = "{_system}/zones/{_zoneId}/cooling/configuration/setpoint_temperature";
  public $_zone_cooling_manual_setpoint_temperature = "{_system}/zones/{_zoneId}/cooling/configuration/manual_mode_cooling_temperature_setpoint";
 

// ************************************************************************************************************************** //  	
  	

	public function _setDhwTemp($dhwId = null, $temperature = null, $mode = null){
      	//$url = $this->_getUri("_hot_water_temperature_setpoint");
      	//$url = "https://smart.vaillant.com/mobile/api/v4/facilities/21211900202826970938020910N8/systemcontrol/tli/v1/dhw/";
      	$url = $this->_getUri("_hot_water_configuration_SENSO");
      	
          
      	$postFields = [];
      	if(!is_null($temperature)){
          	//$url = $this->_getUri("_hot_water_temperature_setpoint");
          	//$url .= "Control_DHW/hotwater/configuration/hotwater_temperature_setpoint";
        	$suffix = "hotwater_temperature_setpointe";
          	$url .= "/".$suffix;
          	$postFields[$suffix] = $temperature;
        }
      	elseif(!is_null($mode)){
          	$url = $this->_getUri("_hot_water_operating_mode");
          	$postFields["operation_mode"] = $mode;//"ON";AUTO;OFF
        }
      	$currentFacility = $this->_currentFacility ? $this->_currentFacility:"21211900202826970938020910N8";
        $url = str_replace("{_serial}", $currentFacility, $url);
      	$dhwId = $dhwId ? $dhwId : "Control_DHW";
        $url = str_replace("{_dhwId}", $dhwId, $url);
      	$return = $this->_rqstApi($url, 'PUT', $postFields);
      	return $return;
    }
   
// ************************************************************************************************************************** //  	
  	public function _setTemp($zoneId, $temperature, $active){
     	$url = $this->_getUri("_zone_quick_veto");
      	//$url = $this->_getUri("_hot_water_temperature_setpoint");
      	$url = str_replace("{_zoneId}", $zoneId, $url);
      	$currentFacility = $this->_currentFacility ? $this->_currentFacility:"21211900202826970938020910N8";
        $url = str_replace("{_serial}", $currentFacility, $url);
      	$dhwId = "Control_DHW";
        $url = str_replace("{_dhwId}", $dhwId, $url);//
      	
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
          				"setpoint_temperature" => 17,
						//"active" =>  false,
          			];
      	$httpheader = array();
      	$cookie = true;
        //$return = $this->_makeRequest($url, 'PUT' , $postFields, $cookie, $httpheader );
        $return = $this->_rqstApi($url, 'PUT', $postFields);
          //$return = $this->_makeCurlRequest($url, 'PUT', $postFields , $httpheader);	
      	return $return;
    }


// ************************************************************************************************************************** //  	
  



// ************************************************************************************************************************** //  	
  	public function getFacilities(){
		log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
      	$data = $this->_rqstApi('_facilities_list');
      	$facilitiesList = isset($data["facilitiesList"]) ? $data["facilitiesList"] : false;
      	return $facilitiesList;
    }

// ************************************************************************************************************************** //  	
  	public function setCurrentFacility($serialNumber){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$this->_currentFacility = $serialNumber;
    }

    public function getGatewayType() {
      	$commandUri = $this->_getUri('_gateway_type');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		return $this->_rqstApi($url);
    }
  
   	/**
     * @return mixed The Box-Status (VR900)
     *
     * onlineStatus: ONLINE|OFFLINE
     * firmwareUpdateStatus: UPDATE_PENDING|UPDATE_NOT_PENDING
     * facilityInstallationStatus: ?
     */
    public function getFacilityStatus() {
      
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_facilities_status');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		return $this->_rqstApi($url);
    }

    /**
     * Return the name and current time for this facility:
     * facilityName: Name of this facility
     * facilityTime: time
     * facilityTimeZone: The time zone..
     *
     * @return mixed
     */
    public function getFacilityDetails(){
		log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_facilities_details');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      
        return $this->_rqstApi($url);
    }

    /**
     * Return things like: is hot water (DHW = Domestic Hot Water) enabled or circulation pump available.
     * Seems to be always NOT_SET
     * @return bool|mixed|string
     */
    public function _getFacilityStorage(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_facilities_storage');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getFacilityDefaultStorage(){//_getFacilityDefaultSettings
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_facilities_default_storage');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }
    /**
     * Return installer email, phone and address/name
     * @return bool|mixed|string
     */
    public function _getRoom(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_rbr_base');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getRoomInstallationStatus(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_rbr_installation_status');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getRoomUnderfloorHeatingStatus(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_rbr_underfloor_heating_status');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function _getRoomList(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_rooms_list');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getInstallerInfo(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_installer_info');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

    /**
     * Return error information like maintenance or error
     * errorMessages: Array: title, type, statusCode etc..
     * @return bool|mixed|string
     */
    public function getHvacState(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_hvac');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

    /**
     * Return a long list of the system: zones, dhw, parameters, status etc.
     * @return bool|mixed|string
     */
    public function getSystemControl(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_system');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getSystemControlConfiguration() {
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_system_configuration');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }
    /**
     * Returns outsidetemp and the time of this value:
     * datetime: The date and time
     * outside_temperature: in Celsius
     * @return bool|mixed|string
     */
    public function getSystemControlStatus() {
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_system_status');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

    /**
     * Return zones with details.
     * @hint: setpoint: Solltemp, setback: Absenktemp.
     * @return bool|mixed|string
     */

// ************************************************************************************************************************** //  	
  	public function getZonesList(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_zones_list');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
     }

// ************************************************************************************************************************** //  	
  	public function getZones($zoneId){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_zone');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
       	$url = str_replace("{_zoneId}", $zoneId, $commandUri);
      	return $this->_rqstApi($url);
    }
    /**
     * Gives current infos like current Flow temperatur, current hot water temp, current water pressure:
     * devices: Array: reports: ARRAY:
     *      unit: The unit (bar, Celsius, etc)
     *      value: The value
     *      _id: the device (like: DomesticHotWaterTankTemperature)
     * @return bool|mixed|string
     */

// ************************************************************************************************************************** //  	
  	public function getLiveReport() {
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_live_report');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getLiveReportDevice($device_id, $report_id) {
      	log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_live_report_device');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
       	$url = str_replace("{_device_id}", $device_id, $commandUri);
      	$url = str_replace("{_report_id}", $report_id, $commandUri);
      	return $this->_rqstApi($url);
    }

// ************************************************************************************************************************** //  	
  	public function getEEBusShipNode(){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_eebus_node');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
       	return $this->_rqstApi($url);
    }
// ************************************************************************************************************************** //  	
  	public function getEmfInfos(){
      	//"{_base}/facilities/{_serial}/emf/v1/devices";
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_emf_devices');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
       	return $this->_rqstApi($url);
    }
	//_emf_report_device = "{_base}/facilities/{_serial}/emf/v1/devices/{device_id}";
  	public function getDeviceEmf($device_id){
        log::add('VaillantControl', 'info',__FUNCTION__ . '  Starting ****************');
		$commandUri = $this->_getUri('_zone');
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
       	$url = str_replace("{device_id}", $device_id, $commandUri);
      	return $this->_rqstApi($url);
    }
    
}

?>