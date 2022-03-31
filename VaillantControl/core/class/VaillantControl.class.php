<?php
error_reporting(E_ALL);  
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../../VaillantControl/3rdparty/VaillantControl-API/autoload.php';
/*
if (!class_exists('VaillantControl_Scen') && file_exists(__DIR__ . '/VaillantControl_Scen.class.php')) {
	//require_once __DIR__ . '/VaillantControl_Scen.class.php';
}  
*/
/* * ***************************class VaillantControl********************************* */
class VaillantControl extends eqLogic {
	
	//public static $_widgetPossibility = array('custom' => true);
  	public static $_surDebug = false;
  	public static $_widgetPossibility = array('custom' => 'true');//'parameters['allow_displayType',default ],'advanceWidgetParameter'
  	private static $_templateArray = array();
  
	public static $_data = array();
  	private static $_client = null;
  	private static $_eqConfig = null;
  	private static $_eqData = null;
	private static $_apiHome = array();
  	public static $_isMaster = false;
	//public static $_homeIds = array();
  	
	
  	public static function OnEvent($level="error", $message){
		log::add('VaillantControl', $level,  __FUNCTION__ . '  '. $message);
	}

///////////////////////////////
	public static function getClient($int=NULL,$sn=NULL) {
		if (VaillantControl::$_client === null){
            $credential = [
                            'username' => config::byKey('username', 'VaillantControl'),
                            'password' => config::byKey('napassword', 'VaillantControl'),
                            'enableCustomRequests' => false,
                            'mVt_prefix' => config::byKey('mVt_prefix', 'VaillantControl'),
                            'cookies' => jeedom::getTmpFolder('VaillantControl').'/cookie_VaillantControl',
                            'func_cb' => 'funccb',
                            'object_cb' => 'VaillantControl',
            ];
            $arToken = cache::bykey('VaillantControl_auth')->getValue();
            if ( isset($arToken['authToken']) ){// && strlen($login['authToken']) >= 57
                $credential['arToken'] = $arToken;
                log::add('VaillantControl', 'debug', __FUNCTION__ ." arToken: ".json_encode($arToken)." ".strlen($arToken['authToken']));
            }
          	if(!is_null($sn)){
            	$credential['_currentFacility'] = $sn;
            }
          	$client = new VaillantControlApi\Clients\mVt_ApiSenso($credential);
      			
      			/*if ($client == false) {
                    $errMsg = "error: !" .__FUNCTION__;
                    log::add('VaillantControl', 'debug', __FUNCTION__.' '.$errMsg);
					return $errMsg;
                }*/
        	VaillantControl::$_client = $client;
        }
      	
      
		
		return VaillantControl::$_client;
	}
///////////////////////////////
	public static function funccb($arToken) {
      	//log::add('VaillantControl', 'debug', __FUNCTION__ ." start ");
      	if(is_array($arToken)){
          	cache::set('VaillantControl_auth', $arToken);
      		log::add('VaillantControl', 'debug', __FUNCTION__ ." arToken: ".json_encode($arToken));
        }else{
          /*//log::add('VaillantControl', 'warning', __FUNCTION__ ." !arToken: ".$arToken);
          	$login_ar = VaillantControl_W::getlogin(true);
          	//$client = VaillantControl::getWClient(__FUNCTION__, true);
          	if(isset($login_ar['access_token']) ){
          		return $login_ar['access_token'];
          	}
         	return $login_ar;*/
        }
      	
    }
////////////////////////////////	
	public static function getdata_url($url){
    	$client = VaillantControl::getClient();
  		$return = $client->_rqstApi($url);
      	unset($return['meta']);
      	log::add('VaillantControl', 'warning', __FUNCTION__.' '.json_encode($return, JSON_PRETTY_PRINT));
      	//return $return;
      	return json_encode($return, JSON_PRETTY_PRINT);
    }
////////////////////////////////	
	public static function test($base_url=null, $params=null) {
		
      
      	sleep(5);
      	$d_arg2=" --cmd=Infos";
		$cmd2=substr(dirname(__FILE__),0,strpos (dirname(__FILE__),'/core/class')).'/desktop/php/textalk.php'.$d_arg2;
		//log::add('VaillantControl_Daemon', 'debug', $cmd);
		log::add('VaillantControl', 'debug', 'cmd2 '.$cmd2);
		$result2 = exec('php ' . $cmd2 . ' >> ' . log::getPathToLog('VaillantControl_Daemon') . ' 2>&1 &');
      
      /*
      	list($roomid, $homeid) = explode('|', $multiId);
		$client = VaillantControl::getClient('int');
      
		$apicmd= $client->api($base_url, 'GET', $params);
		sleep(4);
	
		return $apicmd; 
        */
		
	}
///////////////////////////////////////
	public static function infoStation($update=false, $src) {
      	log::add('VaillantControl', 'debug', '*');
		log::add('VaillantControl', 'debug',__FUNCTION__ . '  Starting ****************');
		/*cache::set('VaillantControl_auth', []);
          	if (file_exists(jeedom::getTmpFolder('VaillantControl').'/cookie_VaillantControl')) {
              	//$cmd = system::getCmdSudo() . 'rm ' . jeedom::getTmpFolder('VaillantControl').'/cookie_VaillantControl');
				//exec($cmd);
              	shell_exec(system::getCmdSudo() . ' rm -rf '.jeedom::getTmpFolder('VaillantControl').'/cookie_VaillantControl');
			}*/
      	$isError = true;
      	$ApiData = self::syncSenso( ["systemControl","emf", "gatewayType", "facilityStatus", "roomInstallationStatus", "liveReport"], false);
      	foreach ($ApiData as $eqId=>$eqData) {
          	list($device_id, $sn) = explode('|', $eqId);
          	if($device_id == "Home"){
              	self::makeOrUpdateEq($eqData);
            }
          	elseif(substr($device_id, 0, 9) == "Control_Z"){
              	self::makeOrUpdateEq($eqData);
            }
          	elseif($device_id == "Control_DHW"){
              	self::makeOrUpdateEq($eqData);
            }
        }
      	self::refreshInfos(null,null,$ApiData);
          	
      	return "ok 😎";
    }
  	////////////////////////////////	
	public static function makeOrUpdateEq($eqData) {
      	log::add('VaillantControl', 'debug',__FUNCTION__ . '  Starting ****************');
		$eqLogic = self::byLogicalId($eqData['id'], __CLASS__);
      	list($device_id, $sn) = explode('|', $eqData['id']);
		$create = false;
      	$operation = "";
      	if (!is_object($eqLogic)) {
          	log::add(__CLASS__, 'info', __FUNCTION__ . " Creating new device with id='{$eqData['id']}' and name='{$eqData['name']}'");
			$eqLogic = new self();
			$eqLogic->setLogicalId($eqData['id']);
			$eqLogic->setConfiguration('type', $eqData['type']);
			$eqLogic->setEqType_name(__CLASS__);
			$eqLogic->setIsVisible(1);
			$eqLogic->setIsEnable(1);
			$eqLogic->setCategory('heating', '1');
			$eqLogic->setName($eqData['name']);
          	$eqLogic->setConfiguration('type', $eqData['type']);
          	$eqLogic->setConfiguration('cmdsType', $eqData['cmdsType']);
			if ($eqData['type'] == 'Home') {
				//$eqLogic->setConfiguration('serial_number', $eqData['serial_number']);
				$eqLogic->setConfiguration('firmware_version', $eqData['firmwareVersion']);
				$eqLogic->setConfiguration('gatewayType', $eqData['gatewayType']);
				$eqLogic->setConfiguration('room_avalaible', $eqData['rbr']);
			}
			$defaultObject = intval(config::byKey('defaultParentObject','VaillantControl','',true));
			if($defaultObject) $eqLogic->setObject_id($defaultObject);
			$msg='added';
			$operation = "Creating";
		}
      	else{
          $eqLogic->setConfiguration('cmdsType', $eqData['cmdsType']);
		  $operation = "Updating";
        }
		if ($eqData['type'] == 'valve' || $eqData['type'] == 'thermostat') {
			$eqLogic->setConfiguration('battery_type', $eqData['battery_type']);
			$eqLogic->setConfiguration('room_id', $eqData['room_id']);
			$eqLogic->save(true);

			$eqLogic->batteryStatus($eqData['battery_low'] == 0 ? 100 : 10);
		}
      	self::$_eqData = $eqData;
      		
		if($operation = "Updating") {
			log::add('VaillantControl', 'debug','*   '.__FUNCTION__ .' updating ');//.$roomName.' ...'.json_encode($cmdsType)
			$eqLogic->save();
			$eqLogic->makeCmd($eqData);
		}
		else {
			log::add('VaillantControl', 'debug','*   '.__FUNCTION__ .' creating zone : '.$zoneName.' ...');
			$eqLogic->save(); 
		}
		log::add(__CLASS__, 'debug', __FUNCTION__ . " Updating info commands of '{$eqData['name']}' - Type:'{$eqData['type']}' ");
		/*foreach ($eqData as $key => $value) {
			if ($key == 'reports') {
				$eqLogic->addReports($value);
				continue;
			}
			if ($key == 'quick_mode' || substr($key, -11) == 'active_mode' || substr($key, -12) == 'active_state') {
				$value = self::translateMode($value);
			}

			$eqLogic->checkAndUpdateCmd($key, $value);
		}*/
		$eqLogic->setCache('need_update', 0);
	}
////////////////////////////////	
	public function makeCmd($eqData = array()) {
		$eqLogicId = $this->getLogicalId();
		list($device_id, $sn) = explode('|', $eqLogicId);
		$eqhome_Id='Home|'.$sn;
		$eqhome = self::byLogicalId($eqhome_Id, __CLASS__);
		$cmds =[];
      	$cmdsType = $this->getConfiguration('cmdsType',[]);
      	if(empty($cmdsType)){
			log::add(__CLASS__, 'error','	'. __FUNCTION__ .' empty cmdsType: '.$this->getName().' cmdsType: '. json_encode($cmdsType));
			return;
        }
		log::add(__CLASS__, 'debug','	>>> '. __FUNCTION__ .' cmds for: '.$this->getName()." : ".json_encode($cmdsType));
   		
    
	//  ************************************************* //
  		$commands=$this->loadEqConfig(NULL);
      	$reports = isset($eqData['reports']) ? $eqData['reports'] :[];
      	//log::add(__CLASS__, 'debug','	'. __FUNCTION__ .' reports '.json_encode($eqData));
      	foreach($reports as $reportId=>$report){
          	$cmdData = [
                    'logicalId' => $report['_id'],
                    'name' => __($report['name'], __FILE__),
                    'unite' => isset($report['unit']) ? $report['unit'] : '',
                    'subtype' => 'numeric',
                    'type' => "info",
                    'isVisible' => 1,
                    'isHistorized' => 1,
                    'template' => ['dashboard'=>'line','mobile'=>'line'],
              		'configuration' => [
                      					'measurement_category'=>$report['measurement_category'],
                                     	'associated_device_function'=>$report['associated_device_function'],
                                     	'deviceId'=>$report['deviceId'],
                                     	'deviceName'=>$report['deviceName'],
                                     	],
                    'value' =>$report['value'],
            ];
          	log::add(__CLASS__, 'debug','	'. __FUNCTION__ .' Add report cmd '.$report['name'] .' to: '.$this->getName());
          	$commands['commands'][] = $cmdData;
          	//$this->newCmdi((array) $cmdData);
          
        }
      //
      	$emfs = isset($eqData['emf']) ? $eqData['emf'] :[];
      	foreach($emfs as $emfId=>$emf){
          	$emf_devices = $this->getConfiguration('emf_devices',[]);
          	if(!in_array($emfId, $emf_devices)){
              	$emf_devices[] = $emfId;
                $this->setConfiguration('emf_devices',$emf_devices);
          	}
          	$mesuresIndex = $emf['mesuresIndex'] ; 
          	foreach($mesuresIndex as $emfLogId=>$emf_data){
              	$cmdData = [
                    'logicalId' => $emfLogId,
                    'name' => __($emfLogId, __FILE__),
                    'unite' => 'kwh',
                    'subtype' => 'numeric',
                    'type' => "info",
                    'isVisible' => 1,
                  	'isHistorized' => 1,
                    'template' => ['dashboard'=>'line','mobile'=>'line'],
              		'configuration' => [
                      					'type'=>'emf',
                      					'deviceId'=>$emfId,
                                     	'associated_device_type'=>$emf['name'],
                                     	'deviceName'=>$emf['name'],
                                     	],
                    'value' =>$emf_data,
            	];
                log::add(__CLASS__, 'debug','	'. __FUNCTION__ .' Add report cmd '.$emfLogId .' to: '.$this->getName());
                $commands['commands'][] = $cmdData;
            }
          	//$this->newCmdi((array) $cmdData);
          
        }
      
      
      
      
  		if(!empty($commands['commands'])){
          	//log::add(__CLASS__, 'debug','          '. __FUNCTION__ .' commands: '. json_encode($commands));
			$this->setConfiguration('cmdsMaked', true);
           	$import = $this->import($commands);
          	/*foreach ($commands['commands'] as $cmdData) {
              	$this->newCmdi($cmdData);
            }*/
          	log::add(__CLASS__, 'debug','	'. __FUNCTION__ .' fin import '.$import);
		}else{
          	log::add(__CLASS__, 'warning','	'. __FUNCTION__ .' empty [commands]');
          	$this->remove();
            return false;
        }
      	$cmdas = $this->getCmd('action', null);
		foreach ($cmdas as $cmda) {
          	$cmdi_LogId = $cmda->getConfiguration('cmdi_LogId', '');
			if($cmdi_LogId != ''){
              	$cmdi = $this->getCmd(null, $cmdi_LogId);
              	if(is_object($cmdi)){
                  	$cmda->setValue($cmdi->getId());
                  	$cmda->save();
                }else{
                 	log::add(__CLASS__, 'error','	'. __FUNCTION__ .' cmdi_LogId '.$cmdi_LogId. ' introuvable ');
              	 
                }
              	
            }
        }
        
      
	/* ********************* */
      	if($this->getConfiguration('relay_infos','') != ''){//is room
      		
            $cmd_consigne = $this->getCmd(null, 'consigne');
            $cmd_consigneset = $this->getCmd(null, 'consigneset');
            if ( is_object($cmd_consigne) && is_object($cmd_consigneset)){
               $cmd_consigneset->setValue($this->getCmd(null, 'consigne')->getId() );
               $cmd_consigneset->save();
            }
          	$cmd_consigneset_mobile = $this->getCmd(null, 'consigneset_mobile');
            if ( is_object($cmd_consigne) && is_object($cmd_consigneset_mobile)){
               $cmd_consigneset_mobile->setValue($this->getCmd(null, 'consigne')->getId() );
               $cmd_consigneset_mobile->save();
            }
          
          
            $cmd_room_modetech = $this->getCmd(null, 'room_modetech');
            $cmd_set_room_mode = $this->getCmd(null, 'set_room_mode');
            if ( is_object($cmd_set_room_mode) &&is_object($cmd_room_modetech) ){
               $cmd_set_room_mode->setValue($this->getCmd(null, 'room_modetech')->getId() );
               $cmd_set_room_mode->save();
            } 
        }  
	/* ********************* */
      	if($eqLogicId == $eqhome_Id){
          	$eqhome = self::byLogicalId($eqLogicId, __CLASS__);
          	/* ********************* */
      		$cmd_nowplanid = $eqhome->getCmd(null, 'nowplanid');
        	$cmd_planningset = $eqhome->getCmd(null, 'planningset');
            if ( is_object($cmd_nowplanid) && is_object($cmd_planningset)){
                $cmd_planningset->setValue($cmd_nowplanid->getId() );
                $cmd_planningset->save();
            }
          /* ********************* */
      		$cmd_dhw_setpoint_temperature = $eqhome->getCmd(null, 'dhw_setpoint_temperature');
        	$cmd_set_dhw_setpoint_temperature = $eqhome->getCmd(null, 'set_dhw_setpoint_temperature');
            if ( is_object($cmd_dhw_setpoint_temperature) && is_object($cmd_set_dhw_setpoint_temperature)){
                $cmd_set_dhw_setpoint_temperature->setValue($cmd_dhw_setpoint_temperature->getId());
              	$cmd_set_dhw_setpoint_temperature->setConfiguration('minValue',  $eqhome->getConfiguration('dhw_temperature_min', 38));
                $cmd_set_dhw_setpoint_temperature->setConfiguration('maxValue',  $eqhome->getConfiguration('dhw_temperature_max', 65));
                $cmd_set_dhw_setpoint_temperature->save();
            }
          /* ********************* */
      		$cmd_therm_heating_algorithm = $eqhome->getCmd(null, 'therm_heating_algorithm');
        	$cmd_set_therm_heating_algorithm = $eqhome->getCmd(null, 'set_therm_heating_algorithm');
            if ( is_object($cmd_therm_heating_algorithm) && is_object($cmd_set_therm_heating_algorithm)){
                $cmd_set_therm_heating_algorithm->setValue($cmd_therm_heating_algorithm->getId() );
                $cmd_set_therm_heating_algorithm->save();
            }
          /* ********************* */
      		$cmd_heating_curve_slope = $eqhome->getCmd(null, 'heating_curve_slope');
        	$cmd_set_heating_curve_slope = $eqhome->getCmd(null, 'set_heating_curve_slope');
            if ( is_object($cmd_heating_curve_slope) && is_object($cmd_set_heating_curve_slope)){
                $cmd_set_heating_curve_slope->setValue($cmd_heating_curve_slope->getId() );
                $cmd_set_heating_curve_slope->save();
            }
          /* ********************* */
      		$cmd_dhw_enabled = $eqhome->getCmd(null, 'dhw_enabled');
        	$cmd_set_dhw_enabled = $eqhome->getCmd(null, 'set_dhw_enabled');
            if ( is_object($cmd_dhw_enabled) && is_object($cmd_set_dhw_enabled)){
                $cmd_set_dhw_enabled->setValue($cmd_dhw_enabled->getId() );
                $cmd_set_dhw_enabled->save();
            }
          /* ********************* */
      		$cmd_therm_setpoint_mode = $eqhome->getCmd(null, 'therm_setpoint_mode');
        	$cmd_set_room_mode = $eqhome->getCmd(null, 'set_room_mode');
            if ( is_object($cmd_therm_setpoint_mode) && is_object($cmd_set_room_mode)){
                $cmd_set_room_mode->setValue($cmd_therm_setpoint_mode->getId() );
                $cmd_set_room_mode->save();
            }
          
          /* ********************* */
      		$cmd_home_modetech = $eqhome->getCmd(null, 'home_modetech');
        	$cmd_setmode = $eqhome->getCmd(null, 'setmode');
            if ( is_object($cmd_home_modetech) && is_object($cmd_setmode)){
                $cmd_setmode->setValue($cmd_home_modetech->getId() );
                $cmd_setmode->save();
            }
          	/* ********************* */
      		$cmd_thermPriority = $eqhome->getCmd(null, 'thermPriority');
        	$cmd_thermPriority_set = $eqhome->getCmd(null, 'thermPriority_set');
            if ( is_object($cmd_thermPriority) && is_object($cmd_thermPriority_set)){
                $cmd_thermPriority_set->setValue($cmd_thermPriority->getId());
                $cmd_thermPriority_set->save();
            }
              
		/* ********************* */
      		$listener = listener::byClassAndFunction(__CLASS__, 'triggerEvent', array(__CLASS__.'_id' => $eqhome->getLogicalId()));
            //$eqhome->getLogicalId()
			if (!is_object($listener)) {
                 	$listener = new listener();
			}
			$cmd_wbhook_last = $eqhome->getCmd(null, 'wbhook_last');
          	if (is_object($cmd_wbhook_last)) {
                $listener->setClass(__CLASS__);
                $listener->setFunction('triggerEvent');
                $listener->setOption(array(__CLASS__.'_id' => $eqhome->getLogicalId()));
                $listener->emptyEvent();

                $listener->addEvent($cmd_wbhook_last->getId());
                $listener->save();
          	}
          
         
        }
      	return "ok";
    }
////////////////////////////////	
	private function loadEqConfig($cmdsType=array()) {
        log::add(__CLASS__, 'debug','		'. __FUNCTION__ .' started ***************** ');
  		if (empty($cmdsType)) {
             $cmdsType = $this->getConfiguration('cmdsType', []);
        }
      	if(self::$_eqConfig == null){
			$configFile = __DIR__ . '/../config/'.__CLASS__.'_config.json';
            if (!file_exists($configFile)) {
                log::add(__CLASS__, 'error', __FUNCTION__ .' Fichier de configuration introuvable ! '.$configFile);
            }
			$eqConfig = json_decode(file_get_contents($configFile),true);
          	if(!$eqConfig){
                log::add(__CLASS__, 'error', __FUNCTION__ .' Fichier de configuration corempu !');
            	throw new Exception(__('Fichier de configuration inexploitable !', __FILE__));
            }
        	self::$_eqConfig = $eqConfig;
		}
        $eqConfig = self::$_eqConfig;
      	$commands['commands']=[];
        foreach ($cmdsType as $cmdType) {
			if (array_key_exists($cmdType, $eqConfig)) {
				//$commands['commands'] = $eqConfig[$cmdType];
              	$commands['commands'] = array_merge($commands['commands'], $eqConfig[$cmdType]);
				log::add(__CLASS__, 'debug', __FUNCTION__ ." cmds for cmdsType: $cmdType ".json_encode($eqConfig[$cmdType]));
			}
        }
    	//log::add(__CLASS__, 'debug', __FUNCTION__ .' data for '.$eqtype.': '. json_encode($eqConfig));
    	
    	//$commands=$eqConfig[$eqtype];
		//$commands=$eqConfig[$eqtype];
		if(empty($commands['commands'])){
          	log::add('VaillantControl', 'error', __FUNCTION__ .' No commands found in config for '.$eqtype);
          	return null;
        }
      	log::add('VaillantControl', 'debug', __FUNCTION__ .' commands: '. json_encode($commands));
    	return $commands;
        
    }
///////////////////////////////////// ********************* ///////////////////////////////////// 
    public function newCmdi(array $cmdData) {
      
        $cmd_LogicalId = $cmdData["logicalId"];
        $VaillantControlCmd = VaillantControlCmd::byEqLogicIdAndLogicalId($this->getId(),$cmdData["logicalId"]);
        if (!is_object($VaillantControlCmd)) {
                log::add('VaillantControl', 'debug', __FUNCTION__.' Création de la commande ' . $cmdData["name"]);
                $VaillantControlCmd = new VaillantControlCmd();
                $VaillantControlCmd->setLogicalId($cmdData["logicalId"]);
                $VaillantControlCmd->setName(__($cmdData["name"], __FILE__));
                $VaillantControlCmd->setEqLogic_id($this->getId());
                $VaillantControlCmd->setEqType('VaillantControl');
                $VaillantControlCmd->setType($cmdData["type"]);
                $VaillantControlCmd->setSubType($cmdData["subtype"]);
                if(isset($cmdData["generic_type"])) $VaillantControlCmd->setGeneric_type($cmdData["isVisible"]);
                if(isset($cmdData["isVisible"])) $VaillantControlCmd->setIsVisible($cmdData["isVisible"]);
                if(isset($cmdData["display"])) $VaillantControlCmd->setIsHistorized($cmdData["isHistorized"]);
                if(isset($cmdData["template"])) $VaillantControlCmd->setTemplate($cmdData["template"]);
                if(isset($cmdData["configuration"])) $VaillantControlCmd->setConfiguration($cmdData["configuration"]);
              	if(isset($cmdData["unite"])) $VaillantControlCmd->setUnite($cmdData["unite"]);
                if(isset($cmdData["display"])) $VaillantControlCmd->setDisplay($cmdData["display"]);
          		
                $VaillantControlCmd->save();
                $VaillantControlCmd->event( $cmdData["cmd_Value"]);
          		
		}
		else $this->checkAndUpdateCmd($cmd_LogicalId, $cmdData["value"]);
		
      	if ($cmdData["type"] == "action" && isset($cmdData["value_logId"])) {
          	$value_logId = $cmda->getConfiguration('value_logId', '');
			if($value_logId != ''){
              	$cmdi = $this->getCmd(null, $value_logId);
              	if(is_object($cmdi)){
                  	$cmda->setValue($cmdi->getId());
                  	$cmda->save();
                }else{
                 	log::add(__CLASS__, 'error','	'. __FUNCTION__ .' value_logId '.$value_logId. ' introuvable ');
              	 
                }
              	
            }
		}
      
    }
/////////////////////////////////////////
  	public static function getMultimaticProgrameState(array $programe) {
      	
      	$now_time = date("H:i");
		$day_today = strtolower(date("l"));
		$day_tomorow = strtolower(date("l",strtotime("+1 day")));
      	$progs = array_reverse($programe[$day_today]);
      	$return = [];
      	foreach($progs as $keyprog=>$prog){
          	$progTime = $prog['startTime'];
          	if($progTime <= $now_time){
                $keyGoodplan = $keyprog;
                $mode = isset($prog['setting']) ? $prog['setting'] : $prog['mode'];
                $return=[ "startTime"=>$prog['startTime'], "mode"=>$mode];
                log::add('VaillantControl', 'debug', __FUNCTION__.'Actif Prog:  ' . json_encode($return));
                if( isset($progs[$keyprog -1]['startTime']) ){
                    $return["endTime"] = $progs[$keyprog -1]['startTime'];
                }else $return["endTime"] = "00:04";

                break;
            }
          
        }
      	return $return;
      
      
    }
/////////////////////////////////////////
  	public static function getSensoProgrameState(array $programe, $circ, $str=null) {
      	$now_time = date("H:i");//"23:45";//
		$day_today = strtolower(date("l"));
		$day_tomorow = strtolower(date("l",strtotime("+1 day")));
      	$progs = $programe[$day_today];
      	$return = [];
      	$countProg=count($progs);
      	foreach($progs as $keyprog=>$prog){
          	//log::add('VaillantControl', 'warning', '  '.__FUNCTION__ ." prog_$keyprog/$countProg $circ : ".json_encode($prog));
          	$progStartTime = $prog['start_time'];
          	$progEndTime = $prog['end_time'];
          	if($progStartTime < $now_time && $progEndTime > $now_time){
              	$keyGoodplan = $keyprog;
              	$startTime = $progStartTime;
              	$endTime = $progEndTime;
              	$actif = true;
                //log::add('VaillantControl', 'warning', '	'.__FUNCTION__ ." now good prog_$keyprog/$countProg $day_today : ".json_encode($prog));
          		break;
            }elseif($progStartTime > $now_time){
              	$keyGoodplan = $keyprog;
              	$lastkey = $keyprog-1;
              	$startTime = isset($progs[$lastkey]) ? $progs[$lastkey]['end_time'] : "";
              	$endTime = $progStartTime;
              	$actif = false;
                /*log::add('VaillantControl', 'warning', '	'.__FUNCTION__ ." next good prog_$keyprog/$countProg  $day_today : "
                         . json_encode($prog).$startTime ."=>".$endTime ." ".$actif);*/
          		
              	continue;
            }elseif($keyprog == $countProg -1){
              	$prog = $programe[$day_tomorow][0];
              	$keyGoodplan = $keyprog;
              	$startTime = $progEndTime;
              	$endTime = date('d-m', strtotime("+1 day"))." ".$prog['start_time'];
              	$actif = false;
                //log::add('VaillantControl', 'warning', '	'.__FUNCTION__ ." tomorow good prog_$keyprog/$countProg $day_tomorow : ".json_encode($prog));
          		break;
            }
			
          
        }
		$return["start_time"] = $startTime;
		$return["end_time"] = ($endTime=="24:00") ? "00:00" : $endTime;
		$return["actif"] = $actif;
			      	
		if(isset($prog['setting'])){
			$return["setting"] = $prog['setpoint'];
		}elseif(isset($prog['setpoint'])){
			$return["setpoint"] = $actif ? $prog['setpoint'] : null;
		}elseif(isset($prog['mode'])){
			$return["mode"] = $prog['mode'];
		}
      	return $return;
      
      
    }
///////////////////////////////////////
	public static function getValueForPeriod($cmdId, $_startTime, $_endTime) {
		$values = array('cmd_id' => $cmdId);
  		if ($_startTime !== null) $values['startTime'] = $_startTime;
    	if ($_endTime !== null) $values['endTime'] = $_endTime;
  		//value as `last`
		try{
			$sql = 'SELECT  value as result
            FROM (
                SELECT *
                FROM history
                WHERE cmd_id=:cmd_id
                AND `datetime`>=:startTime
                AND `datetime`<=:endTime
                UNION ALL
                SELECT *
                FROM historyArch
                WHERE cmd_id=:cmd_id
                AND `datetime`>=:startTime
                AND `datetime`<=:endTime
            ) as dt ORDER BY datetime ASC';
            $result = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
        }catch (Exception $ex) {
          	log::add('VaillantControl', 'debug', ' Erreur '.__FUNCTION__ .' : '.$ex);
        }
      	return $result['result'];
	}
/////////////////////////////////////*********************///////////////////////////////////// 
    public static function removeAll(){
        log::add('VaillantControl', 'debug', __FUNCTION__ . ' start ');
        $eqLogics = VaillantControl::byType('VaillantControl', false);
        foreach ($eqLogics as $eqLogic) {
            $eqLogic->remove();
        }
        config::remove('homes', __CLASS__);
      	//cache::set('VaillantControl' . '_token_auth' , null);
        //cache::set('VaillantControl' . '_token_time' , null);
        return array(true,'remove ok');//'remove ok'
    }
///////////////////////////////////////
	public static function syncMultimatic() {
		log::add('VaillantControl', 'debug', '*');
		log::add('VaillantControl', 'debug',__FUNCTION__ . '  Starting ****************');
		$isError = true;
      	try {
          	$client = VaillantControl::getClient();
			$facilities = $client->getFacilities();
            if (!$facilities) {
                $errMsg = "error: Your Vaillant login seems incorrect( !facilities)";
                log::add('VaillantControl', 'debug', __FUNCTION__.' '.$errMsg);
            }
			//log::add('VaillantControl', 'debug', __FUNCTION__. ' facilities '.json_encode($facilities, JSON_PRETTY_PRINT));
            if (!$facilities) {
                $code=$client->getLastHttpCode();
                if ($code === 401) {
                    $errMsg = 'error: Either connection problem or username/password is wrong!';
                    log::add('VaillantControl', 'debug', 'error: Multimatic '.'facilities : '.$errMsg);
                }
                return $errMsg;
            }
          	$return=[];
          	
          
          	//return; 
          	$i = 1;
            $dbFac = [];//json_encode([]);
            foreach ($facilities as $facility) {
              	$data=[];
                $sn = isset($facility['serialNumber']) ? $facility['serialNumber'] : null;
                if(empty($sn) || !$sn){
                  	log::add('VaillantControl', 'warning', __FUNCTION__. " No valid serial found ");
              		continue;
                }
              	
              
              	$facilityCap = isset($facility['capabilities']) ? $facility['capabilities'] : [];
                if(in_array("SYSTEMCONTROL_SENSO", $facilityCap )) $prefix = '/tli/v1';
                else $prefix = '/v1';//SYSTEMCONTROL_MULTIMATIC
                //log::add('VaillantControl', 'warning', __FUNCTION__. " facilityCap: ".$facilityCap);
                config::save('mVt_prefix', $prefix, 'VaillantControl');
                $client->_prefix = $prefix;
              
              	log::add('VaillantControl', 'debug', __FUNCTION__. " ❤️ facilitie $i sn ".$sn);
              	
                $client->setCurrentFacility($sn);
              	$gatewayType = $client->getGatewayType();
              	$facilityStatus = $client->getFacilityStatus();
              
              
              
              	if ("Home" == "Home") {
                  	$eqHomeId = "Home|".$sn;
              		$return[$eqHomeId] = [];
                  	$return[$eqHomeId]["systemControl"]=in_array("SYSTEMCONTROL_SENSO", $facilityCap ) ? "SYSTEMCONTROL_SENSO":in_array("SYSTEMCONTROL_MULTIMATIC", $facilityCap ) ? "SYSTEMCONTROL_MULTIMATIC":"";
              		$return[$eqHomeId]["capabilities"]=$facility['capabilities'];
                  	$return[$eqHomeId]["firmwareVersion"]=$facility['firmwareVersion'];
                  	$return[$eqHomeId]["name"]=$facility['name'];
                  	$return[$eqHomeId]["gatewayType"]=$gatewayType['gatewayType'];
                  	$return[$eqHomeId]["online_status"]=($facilityStatus['online_status']['status'] == "ONLINE") ? true : false;
                  	$return[$eqHomeId]["is_up_to_date"]= ($facilityStatus['firmwareUpdateStatus']['status'] != "UPDATE_NOT_PENDING") ? false : true;
                  	$roomInstallationStatus = $client->_getRoomInstallationStatus();
                    if($roomInstallationStatus['code'] != 409){
                        $return[$eqHomeId]["rbr_avalaible"]= true;
                    }else{
                      	$return[$eqHomeId]["rbr_avalaible"]= false;
                    }
                  
                }//end if ("Home" == "Home") { 
              	$systemControl = $client->getSystemControl();
				self::$_data['systemControl'] = $systemControl;
			///////////////////////////////////////	
              	$sysZones = isset($systemControl['zones']) ? $systemControl['zones'] : [];
                foreach ($sysZones as $keyZone => $zone) {
                  	$data['zone']=$zone;
                  	$zoneId = $zone['_id'];
                  	$eqZoneId = $zoneId."|".$sn;
                  	$return[$eqZoneId] = [];
                  	$return[$eqZoneId]['zone_Id'] = $zoneId;
                  	
                  	$zoneHeating = $zone['heating'];
                  	
                  	$zoneHeatingConfig = $zoneHeating['configuration'];
                  	$zone_operation_mode = $zoneHeatingConfig['mode'];
                    $return[$eqZoneId]['zone_operation_mode'] = $zone_operation_mode;
                  	$return[$eqZoneId]['setback_temperature'] = $zoneHeatingConfig['setback_temperature'];
                  	$return[$eqZoneId]['setpoint_temperature'] = $zoneHeatingConfig['setpoint_temperature'];
                  	if($zone_operation_mode == "AUTO"){
                        $zone_program = $zoneHeating['timeprogram'];
                        $return[$eqZoneId]['zone_prog_mode'] = self::getProgrameState($zone_program)["mode"];
                    }
                  
                  	$zoneConfig = $zone['configuration'];
                  	$zoneName = $zoneConfig['name'];
                  	$return[$eqZoneId]['name'] = $zoneName;
                  	$return[$eqZoneId]['enabled'] = $zoneConfig['enabled'];;
                  	$return[$eqZoneId]['active_function'] = $zoneConfig['active_function'];
                  	$return[$eqZoneId]['temperature'] = $zoneConfig['inside_temperature'];
                  	$return[$eqZoneId]['quick_veto_active'] = $zoneConfig['quick_veto']['active'];
                  	$return[$eqZoneId]['quick_veto_consigne'] = $zoneConfig['quick_veto']['setpoint_temperature'];
                  	
                  	
                }
              
			///////////////////////////////////////////////////////////////
              	$sysConfig = isset($systemControl['configuration']) ? $systemControl['configuration'] : [];
              	$return[$eqHomeId]["eco_mode"] = $sysConfig['eco_mode'];
              	$holidaymode_is_active = $sysConfig['holidaymode']['active'];
              	$return[$eqHomeId]["holidaymode_is_active"] = $holidaymode_is_active;
              	if($holidaymode_is_active == true){
              		$return[$eqHomeId]["holidaymode_start_date"] = $sysConfig['holidaymode']['start_date'];
              		$return[$eqHomeId]["holidaymode_end_date"] = $sysConfig['holidaymode']['end_date'];
                }
              	$return[$eqHomeId]["holidaymode_consigne"] = $sysConfig['holidaymode']['temperature_setpoint'];
              	
              
			///////////////////////////////////////////////////////////////
              	$sysStatus = $systemControl['status'];
				$return[$eqHomeId]["datetime"] = gmdate('d-m-Y H:i:s', strtotime($sysStatus['datetime']) );
              	$return[$eqHomeId]["outdoor_temperature"] = $sysStatus['outside_temperature'];
              	
			///////////////////////////////////////////////////////////////
              	$sysParameters = isset($systemControl['parameters']) ? $systemControl['parameters'] : [];
                
			///////////////////////////////////////////////////////////////
              	$sysDhw = isset($systemControl['dhw']) ? $systemControl['dhw'] : [];
              	$eqDhwId = "Control_DHW|".$sn;
              	$return[$eqDhwId] = [];
              	foreach ($sysDhw as $key => $dhw) {
                  	$data['dhw']=$dhw;
                    $dhwId = $dhw['_id'];
                    $return[$eqDhwId] = [];
                  	$return[$eqDhwId]['dhw_Id'] =$dhwId;
                  	$dhwCompenents=[];
                    if(isset($dhw['hotwater'])) $dhwCompenents[]='hotwater';
                    if(isset($dhw['circulation'])) $dhwCompenents[]='circulation';
                    $return[$eqDhwId]['compenents'] = $dhwCompenents;
                  	
                  //////
                  	$dhw_temperature_setpoint = $dhw['hotwater']['configuration']['temperature_setpoint']; 
                    $return[$eqDhwId]['temperature_setpoint'] = $dhw_temperature_setpoint;
                  	$dhw_operation_mode = $dhw['hotwater']['configuration']['operation_mode'];
                  	$return[$eqDhwId]['dhw_operation_mode'] = $dhw_operation_mode;
                    if($dhw_operation_mode == "ON"){//OFF
                        $dhw_program = $dhw['hotwater']['timeprogram'];
                        $return[$eqDhwId]['dhw_prog_mode'] = self::getProgrameState($dhw_program)["mode"];
                    }
                    $hwCirculation_operation_mode = $dhw['circulation']['configuration']['operationMode'];
                    $return[$eqDhwId]['circulation_operation_mode'] = $hwCirculation_operation_mode;
                    if($hwCirculation_operation_mode == "AUTO"){
                        $hwCirculation_program = $dhw['circulation']['timeprogram'];
                        $return[$eqDhwId]['circulation_prog_mode'] = self::getProgrameState($hwCirculation_program)["mode"];
                    }
                }  
              
              
              	$liveReport = $client->getLiveReport(); 
                $devices = $liveReport['devices'];
              	self::$_data['devices'] = $devices;
              	
				foreach( $devices as $keyDevice=>$device){
          			if($keyDevice === 'meta') continue;

                    $deviceId = $device['_id'];// Control_CC1/Control_SYS_MultiMatic/Control_DHW
                    $deviceName = $device['name'];
                    log::add('VaillantControl', 'debug', __FUNCTION__ ."	device $keyDevice found $deviceName => $deviceId" );
                    $supposed_eqId = $deviceId.'|'.$sn;
                    if($deviceId == "Control_DHW"){
                      	$eqId = 'Control_DHW|'.$sn; 
                    	log::add('VaillantControl', 'debug', __FUNCTION__ ."	eqId1: $eqId" );
                    }
                    elseif(substr($deviceId, 0, 9) == "Control_Z"){
                        $eqId = $deviceId.'|'.$sn;
                      	log::add('VaillantControl', 'debug', __FUNCTION__ ."	eqId2: $eqId" );
                        
                    }
                    else{//substr($deviceId, 0, 10) != "Control_CC"
                        $eqId = "Home|".$sn;
                    	log::add('VaillantControl', 'debug', __FUNCTION__ ."	eqId3: $eqId" );
                    }

                    if ( !isset($return[$eqId]) ) {
                        log::add('VaillantControl', 'warning', __FUNCTION__ ."	No Object for $eqId" );
                    }
                  	log::add('VaillantControl', 'warning', __FUNCTION__ ."		$deviceName => $eqId" );
					$report = $device['reports'][0];
					$reportUnit = isset($report['unit']) ? $report['unit'] : null;
					$reportName = $report['name'];
					$reportMeasurement_category = $report['measurement_category'];
					$reportAssociated_device_function = $report['associated_device_function'];
					$reportId = $report['_id'];//
					$value = $report['value'];
					unset($report['value']);
					// $devicesConfig[$deviceId] = $reportId;
					$return[$eqId]['reports'][$reportId] = $value;
                  
                  // ZonesList/0/configuration/inside_temperature  SystemControl/zones/0/configuration/inside_temperature
                }//foreach( $devices as $keyDevice=>$device){
                
              	
              	
              	

                $i++;
            }// fin foreach ($facilities as $facility) {
        	self::writedataStat(json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'sync.json');
          	log::add('VaillantControl', 'debug', __FUNCTION__. ' sync: '.json_encode($return, JSON_PRETTY_PRINT));
       
        }//fi try
		catch (Exception $ex) {
          	
			$error_msg = __FUNCTION__." Erreur catch :  " .$ex->getCode()." - ". $ex->getMessage() . "\n";
			throw new Exception($error_msg);
          	log::add('VaillantControl', 'error', ' 😡 ******'.__FUNCTION__ ." ". $error_msg );
           
		}
      	if(is_string($client) && substr($client, 0, 5) == "error"){
            log::add('VaillantControl', 'info', ' 😡 ******'.__FUNCTION__ ." err string client ". $client);
      		return $client;
        }else{
          	log::add('VaillantControl', 'info', '  ******'.__FUNCTION__ ." json client ". json_encode($client, JSON_PRETTY_PRINT) );
          	//$facilities = $client->getFacilities();
          	//log::add('VaillantControl', 'info', '  ******'.__FUNCTION__ ." SN ". $facilities[0]["serialNumber"] );
          
          	return "ok 😎";
        }
      	
	}
///////////////////////////////////////
	public static function syncEmf($homeId, $write=false) {
      	//$dataToSync = array("emf", "gatewayType", "facilityStatus", "roomInstallationStatus", "liveReport")
		$client = VaillantControl::getClient();
    }
///////////////////////////////////////
	public static function syncSenso($dataToSync=array(), $write=false) {
		log::add('VaillantControl', 'debug', '*');
		log::add('VaillantControl', 'debug',__FUNCTION__ . '  Starting **************** '.json_encode($dataToSync));
		
      	$isError = true;
      	$devicesAr=[
        	"Control_SYS_senso" => "HEATING",
        	"Control_ZO1" => "RELATIVE_HUMIDITY",
        	"Control_DHW" => "DHW",
        	"Control_CC1" => "HEATING",
        	
        	
        ];
      	try {
          	$client = VaillantControl::getClient();
			$facilities = $client->getFacilities();
            if (!$facilities) {
                $errMsg = "error: Your Vaillant login seems incorrect( !facilities)";
                log::add('VaillantControl', 'debug', __FUNCTION__.' '.$errMsg);
            }
			log::add('VaillantControl', 'debug', __FUNCTION__. ' facilities '.json_encode($facilities, JSON_PRETTY_PRINT));
            if (!$facilities) {
                $code=$client->getLastHttpCode();
                if ($code === 401) {
                    $errMsg = 'error: Either connection problem or username/password is wrong!';
                    log::add('VaillantControl', 'debug', 'error: Multimatic '.'facilities : '.$errMsg);
                }
                return $errMsg;
            }
          	$return=[];
          	
          
          	//return; 
          	$i = 1;
            $dbFac = [];//json_encode([]);
          	$dbFac['facilities'] = $facilities;//$facilities
            $nowTime= time();  
            foreach ($facilities as $facility) {
              	$data=[];
                $sn = isset($facility['serialNumber']) ? $facility['serialNumber'] : null;
                if(empty($sn) || !$sn){
                  	log::add('VaillantControl', 'warning', __FUNCTION__. " No valid serial found ");
              		continue;
                }
              	
              
              	$facilityCap = isset($facility['capabilities']) ? $facility['capabilities'] : [];
                if(in_array("SYSTEMCONTROL_SENSO", $facilityCap )) $prefix = '/tli/v1';
                else $prefix = '/v1';//SYSTEMCONTROL_MULTIMATIC
                //log::add('VaillantControl', 'warning', __FUNCTION__. " facilityCap: ".$facilityCap);
                config::save('mVt_prefix', $prefix, 'VaillantControl');
                $client->_prefix = $prefix;
              
              	log::add('VaillantControl', 'debug', __FUNCTION__. " ❤️ facilitie $i sn ".$sn);
              	
                $client->setCurrentFacility($sn);
              	if(in_array("gatewayType", $dataToSync)){
                  	$dbFac['gatewayType'] = $gatewayType;
                	$gatewayType = $client->getGatewayType();
                }
              	if(in_array("facilityStatus", $dataToSync)){
                  	$facilityStatus = $client->getFacilityStatus();
              		$dbFac['facilityStatus'] = $facilityStatus;
                }
              	if("Home" == "Home") {
                  	$eqHomeId = "Home|".$sn;
              		$return[$eqHomeId] = [];
                  	$return[$eqHomeId]["id"]=$eqHomeId;
                  	$return[$eqHomeId]["name"] = "Home_".$facility['name'];
                  	$return[$eqHomeId]["type"] = "Home";
                  	$return[$eqHomeId]["cmdsType"]=["Home"];
                  	$return[$eqHomeId]["systemControl"]=in_array("SYSTEMCONTROL_SENSO", $facilityCap ) ? "SYSTEMCONTROL_SENSO":in_array("SYSTEMCONTROL_MULTIMATIC", $facilityCap ) ? "SYSTEMCONTROL_MULTIMATIC":"";
              		$return[$eqHomeId]["capabilities"]=$facility['capabilities'];
                  	$return[$eqHomeId]["firmwareVersion"]=$facility['firmwareVersion'];
                  	if(isset($gatewayType)){
                      	$return[$eqHomeId]["gatewayType"]= $gatewayType['gatewayType'];
                  	}
                  	if(isset($facilityStatus)){
                      	$return[$eqHomeId]["onlineStatus"] = $facilityStatus['onlineStatus']['status'];
                  		$return[$eqHomeId]["is_up_to_date"]= ($facilityStatus['firmwareUpdateStatus']['status'] != "UPDATE_NOT_PENDING") ? false : true;
                    }
                  
                  	if (in_array("roomInstallationStatus", $dataToSync)){
                      	$roomInstallationStatus = $client->_getRoomInstallationStatus();
                     	$dbFac['roomInstallationStatus'] = $roomInstallationStatus;
                    }
                  	if(isset($roomInstallationStatus) && roomInstallationStatus['code'] != 409){
                    	$return[$eqHomeId]["rbr_avalaible"]= true;
                    }else{
                      	$return[$eqHomeId]["rbr_avalaible"]= false;
                    }
                  
                  
                  
                  
                }//end if ("Home" == "Home") { 
              	if(in_array("systemControl", $dataToSync)){
                  	$systemControl = $client->getSystemControl();
                    $dbFac['systemControl'] = $systemControl;
                  	self::$_data['systemControl'] = $systemControl;
                ///////////////////////////////////////	
                    $sysZones = isset($systemControl['zones']) ? $systemControl['zones'] : [];
                    foreach ($sysZones as $keyZone => $zone) {
                        $data['zone']=$zone;
                        $zoneId = $zone['_id'];
                        $eqZoneId = $zoneId."|".$sn;
                        $return[$eqZoneId] = [];
                        $return[$eqZoneId]['id'] = $eqZoneId;
                        $return[$eqZoneId]['zone_Id'] = $zoneId;
                        $return[$eqZoneId]["type"]="ZONE";
                        $return[$eqZoneId]["cmdsType"]=["ZONE"];
                        $zoneHeating = $zone['heating'];
                        $arOpMode_Conv = [
                            "TIME_CONTROLLED"=>"AUTO",
                            "OFF"=>"OFF",
                            "MANUAL"=>"MANUAL",
                            "AUTO"=>"AUTO",
                            "ON"=>"ON",
                            "STANDBY"=>"STANDBY",

                        ];

                        $zoneHeatingConfig = $zoneHeating['configuration'];
                        $zone_operation_mode = $arOpMode_Conv[$zoneHeatingConfig['operation_mode']];//mode
                        $return[$eqZoneId]['zone_operation_mode'] = $zone_operation_mode;
                        $setback_spTemperature = $zoneHeatingConfig['setback_temperature_setpoint'];
                        $return[$eqZoneId]['setback_spTemperature'] = $setback_spTemperature;
                        $return[$eqZoneId]['manual_spTemperature'] = $zoneHeatingConfig['manual_mode_temperature_setpoint'];//setpoint_temperature
                        if($zone_operation_mode == "TIME_CONTROLLED" || $zone_operation_mode == "AUTO"){
                            $zone_program = $zoneHeating['timeprogram'];
                            $actif_prog = self::getSensoProgrameState($zone_program, "zone_".$zoneId, "setpoint");
                            $prog_spTemperature = isset($actif_prog["setpoint"]) ? $actif_prog["setpoint"] : $setback_spTemperature;
                            $return[$eqZoneId]['prog_spTemperature'] = $prog_spTemperature;
                            $prog_endTime = $actif_prog["end_time"];
                            $return[$eqZoneId]['prog_actif'] = $actif_prog["actif"];
                            $return[$eqZoneId]['mode_endTime'] = $prog_endTime;
                            $autoMode = ($actif_prog["actif"] == false) ? "Reduit" : "Confort";
                            $prog_mode = ($actif_prog["actif"] == false) ? "Reduit" : "Confort";
                            $return[$eqZoneId]['prog_mode'] = $prog_mode;
                            //log::add('VaillantControl', 'error', __FUNCTION__. "  $prog_spTemperature -- $setback_spTemperature");
                            $return[$eqZoneId]['zone_status'] = $zone_operation_mode ."($prog_mode) jusqu'a ".$prog_endTime;
                        }

                        $zoneConfig = $zone['configuration'];
                        $zoneName = $zoneConfig['name'];
                        $return[$eqZoneId]['name'] = $zoneName;
                        $return[$eqZoneId]['enabled'] = $zoneConfig['enabled'];;
                        $return[$eqZoneId]['active_function'] = $zoneConfig['active_function'];
                        $return[$eqZoneId]['temperature'] = $zoneConfig['inside_temperature'];
                        if(isset($zoneConfig['eco_mode'])){
                            $return[$eqZoneId]['eco_mode'] = $zoneConfig['eco_mode'];
                        }
                        if(array_key_exists('current_desired_setpoint', $zoneConfig)){
                            $return[$eqZoneId]['current_desired_consigne'] = $zoneConfig['current_desired_setpoint'];
                        }

                        $zone_QM = "Aucun";
                     //VENTILATION_BOOST QUICK_VETO // SYSTEM_OFF
                        if(array_key_exists('current_quickmode', $zoneConfig)){
                            $zone_QM = $zoneConfig['current_quickmode'];
                            $return[$eqZoneId]['current_quickmode'] = $zone_QM;
                            $keyQm = strtolower($zone_QM);
                            // QM = quickVeto
                            if(array_key_exists('expires_at', $zoneConfig[$keyQm])){
                                $QM_endTime = strtotime($zoneConfig[$keyQm]['expires_at']);
                                if($QM_endTime > strtotime('tomorrow')){
                                  $zone_QM_endTime = gmdate('d.m H:i', $QM_endTime );  
                                }else $zone_QM_endTime = gmdate('H:i', $QM_endTime );  

                            }
                            // QM = away
                            elseif(array_key_exists('end_datetime', $zoneConfig[$keyQm])){
                                $QM_endTime = strtotime($zoneConfig[$keyQm]['end_datetime']);
                                if($QM_endTime > strtotime('tomorrow')){
                                  $zone_QM_endTime = gmdate('d.m H:i', $QM_endTime );  
                                }else $zone_QM_endTime = gmdate('H:i', $QM_endTime );  

                            }else $zone_QM_endTime = "";
                            $return[$eqZoneId]['QM_endTime'] = $zone_QM_endTime;  


                            if(array_key_exists('temperature_setpoint', $zoneConfig[$keyQm])){
                                $zone_QM_spTemperature = $zoneConfig[$keyQm]['temperature_setpoint'];
                                $return[$eqZoneId]['QM_spTemperature'] = $zone_QM_spTemperature;
                            } 

                        }
                        $return[$eqZoneId]['quick_mode'] = $zone_QM;
                    // Zone Away QM
                        $zone_away_end_datetime = strtotime($zoneConfig['away']['end_datetime']);
                        $zone_away_start_datetime = strtotime($zoneConfig['away']['start_datetime']);
                        if($zone_QM == "Aucun" && $zone_away_start_datetime <= $nowTime && $zone_away_end_datetime > $nowTime ){
                            $zone_QM = "AWAY";
                            $return[$eqZoneId]['away_start_datetime'] = gmdate('d-m-Y H:i', $zone_away_start_datetime );
                            if($zone_away_end_datetime > strtotime('tomorrow')){
                                $zone_QM_endTime = gmdate('d-m H:i', $zone_away_end_datetime );  
                            }else $zone_QM_endTime = gmdate('H:i', $zone_away_end_datetime );  
                            $return[$eqZoneId]['away_end_datetime'] = $zone_QM_endTime;//gmdate('d-m-Y H:i', $zone_QM_endTime );
                            $return[$eqZoneId]['QM_spTemperature'] = $zoneConfig['away']['temperature_setpoint'];
                        }				
                        $return[$eqZoneId]['away_spTemperature'] = $zoneConfig['away']['temperature_setpoint'];

                        if($zone_QM != "Aucun"){
                            $return[$eqZoneId]['zone_mode'] = $zone_QM;
                            $return[$eqZoneId]['consigne'] = isset($return[$eqZoneId]['QM_spTemperature']) ? $return[$eqZoneId]['QM_spTemperature'] : $zoneConfig['current_desired_setpoint'];
                            $return[$eqZoneId]['mode_endTime'] = $zone_QM_endTime;
                            $zone_status = $zone_QM;
                            $zone_status .= $zone_QM_endTime ? " jusqu'a ".$zone_QM_endTime : "";
                            $return[$eqZoneId]['zone_status'] = $zone_status;
                            //$zone_QM_spTemperature $zone_QM_endTime 
                        }
                        elseif($zone_operation_mode == "MANUAL"){
                            $return[$eqZoneId]['zone_mode'] = "MANUAL";
                            $return[$eqZoneId]['consigne'] = isset($return[$eqZoneId]['manual_spTemperature']) ? $return[$eqZoneId]['manual_spTemperature'] : $return[$eqZoneId]['current_desired_consigne'];
                            $return[$eqZoneId]['zone_status'] = $zone_operation_mode;
                            $return[$eqZoneId]['mode_endTime'] = "Aucun";

                        } 
                        elseif($zone_operation_mode == "AUTO"  ){
                            $return[$eqZoneId]['zone_mode'] = "AUTO";
                            if($return[$eqZoneId]['prog_actif'] == true){
                                $return[$eqZoneId]['consigne'] = isset($return[$eqZoneId]['prog_spTemperature']) ? $return[$eqZoneId]['prog_spTemperature'] : $return[$eqZoneId]['current_desired_consigne'];
                            }else{
                                $return[$eqZoneId]['consigne'] = $return[$eqZoneId]['setback_spTemperature'];
                            }
                        }
                        else{//off
                            $return[$eqZoneId]['zone_mode'] = $zone_operation_mode;
                            $return[$eqZoneId]['consigne'] = null;
                            $return[$eqZoneId]['zone_status'] = $zone_operation_mode;
                        }

                    }

                ///////////////////////////////////////////////////////////////
                    $sysConfig = isset($systemControl['configuration']) ? $systemControl['configuration'] : [];
                    $manual_cooling_is_active = isset($sysConfig['manual_cooling']['active']) ? $sysConfig['manual_cooling']['active'] :null;
                    if($manual_cooling_is_active && $manual_cooling_is_active == true){
                        $return[$eqHomeId]["manual_cooling_start_date"] = $sysConfig['manual_cooling']['start_date'];
                        $return[$eqHomeId]["manual_cooling_end_date"] = $sysConfig['manual_cooling']['end_date'];
                    }
                    /*$return[$eqHomeId]["eco_mode"] = $sysConfig['eco_mode'];
                    $holidaymode_is_active = $sysConfig['holidaymode']['active'];
                    $return[$eqHomeId]["holidaymode_is_active"] = $holidaymode_is_active;
                    if($holidaymode_is_active == true){
                        $return[$eqHomeId]["holidaymode_start_date"] = $sysConfig['holidaymode']['start_date'];
                        $return[$eqHomeId]["holidaymode_end_date"] = $sysConfig['holidaymode']['end_date'];
                    }
                    $return[$eqHomeId]["holidaymode_consigne"] = $sysConfig['holidaymode']['temperature_setpoint'];
                    */

                ///////////////////////////////////////////////////////////////
                    $sysStatus = $systemControl['status'];
                    $return[$eqHomeId]["datetime"] = gmdate('d-m-Y H:i', strtotime($sysStatus['datetime']) );
                    $return[$eqHomeId]["outside_temperature"] = $sysStatus['outside_temperature'];

                ///////////////////////////////////////////////////////////////
                    $sysParameters = isset($systemControl['parameters']) ? $systemControl['parameters'] : [];
                
			///////////////////////////////////////////////////////////////
                    if("ecs" == "ecs"){
                    $sysDhw = isset($systemControl['dhw']) ? $systemControl['dhw'] : [];
                    $eqDhwId = "Control_DHW|".$sn;
                    $return[$eqDhwId] = [];
                    //foreach ($sysDhw as $key => $dhw) {
                        $dhw = $sysDhw;
                        $data['dhw']=$dhw;
                        $dhwId = isset($dhw['_id']) ? $dhw['_id'] :"Control_DHW";
                        $return[$eqDhwId] = [];
                        $return[$eqDhwId]['id'] = $eqDhwId;
                        $return[$eqDhwId]['dhw_Id'] =$dhwId;//hotwater_
                        $return[$eqDhwId]["type"]="DHW";
                        $return[$eqDhwId]["name"]="Dhw";
                        $dhwCompenents=[];
                        $cmdsType=[];



                        if(isset($dhw['hotwater'])){
                          $dhwCompenents[]='hotwater';
                          $cmdsType[]='DHW|HOTWATER';
                        }
                        if(isset($dhw['circulation'])){
                          $dhwCompenents[]='circulation';
                          $cmdsType[]='DHW|CIRCULATION';
                        }
                        $return[$eqDhwId]['compenents'] = $dhwCompenents;
                        $return[$eqDhwId]["cmdsType"]=$cmdsType;
                        $dhw_QM = "Aucun";  //HOTWATER_BOOST

                      //////
                        $hotwater_temperature_setpoint = $dhw['hotwater']['configuration']['hotwater_temperature_setpoint'];//temperature_setpoint
                        $return[$eqDhwId]['temperature_setpoint'] = $hotwater_temperature_setpoint;
                        $hotwater_operation_mode = $arOpMode_Conv[$dhw['hotwater']['configuration']['operation_mode']];

                        $return[$eqDhwId]['hotwater_operation_mode'] = $hotwater_operation_mode;
                        $hotwater_actif = false;
                        if($hotwater_operation_mode == "AUTO" || $hotwater_operation_mode == "ON"){
                            $hotwater_program = $dhw['hotwater']['timeprogram'];
                            $actif_prog = self::getSensoProgrameState($hotwater_program, "hotwater");
                            $return[$eqDhwId]['hotwater_prog_actif'] = $actif_prog["actif"];
                            $hotwater_prog_endTime = $actif_prog["end_time"];
                            $return[$eqDhwId]['hotwater_prog_endTime'] = $hotwater_prog_endTime;
                            $hotwater_actif = $actif_prog["actif"];
                            if($hotwater_actif == true){
                                $return[$eqDhwId]['Ecs_status'] = "Allumé jusqu'a ".$hotwater_prog_endTime;
                            }else{
                                $return[$eqDhwId]['Ecs_status'] = "Eteint jusqu'a ".$hotwater_prog_endTime;
                            }

                          //$return[$eqDhwId]['hotwater_prog_mode'] = $actif_prog["mode"];
                        }
                        $return[$eqDhwId]['hotwater_actif'] = $hotwater_actif;

                    ////////////////////////////////////////     
                        $dhwConfig = $dhw['configuration'];
                        if(isset($dhwConfig['current_quickmode'])){
                            $dhw_QM = $dhwConfig['current_quickmode'];
                            $return[$eqDhwId]['current_quickmode'] = $dhw_QM;
                        }
                        $dhw_away_end_datetime = strtotime($dhwConfig['away']['end_datetime']);
                        $dhw_away_start_datetime = strtotime($dhwConfig['away']['start_datetime']);
                        if($dhw_away_start_datetime >= $nowTime && $dhw_away_end_datetime > $nowTime ){
                            $dhw_QM = "AWAY";
                            $return[$eqDhwId]['away_start_datetime'] = gmdate('d-m-Y H:i', $dhw_away_start_datetime );
                            if($dhw_away_end_datetime > strtotime('tomorrow')){
                                $dhw_qm_endTime = gmdate('d-m H:i', $dhw_away_end_datetime );  
                            }else $dhw_qm_endTime = gmdate('H:i', $dhw_away_end_datetime );  
                            $return[$eqDhwId]['away_end_datetime'] = $dhw_qm_endTime;//gmdate('d-m-Y H:i', $dhw_qm_endTime );
                        }
                    ////////////////////////////////////////     
                        $hwCirculation_operation_mode = $arOpMode_Conv[$dhw['circulation']['configuration']['operation_mode']];//operationMode
                        $return[$eqDhwId]['circulation_operation_mode'] = $hwCirculation_operation_mode;
                        if($hwCirculation_operation_mode == "TIME_CONTROLLED" || $hwCirculation_operation_mode == "AUTO"){
                            $hwCirculation_program = $dhw['circulation']['timeprogram'];
                            $actif_prog = self::getSensoProgrameState($hwCirculation_program, "circulation");
                            $circ_actif = $actif_prog["actif"];
                            $return[$eqDhwId]['circulation_prog_actif'] = $circ_actif;
                            $circulation_prog_endTime = $actif_prog["end_time"];
                            $return[$eqDhwId]['circulation_prog_endTime'] = $circulation_prog_endTime;
                            $return[$eqDhwId]['circulation_mode'] = "AUTO";
                            if($circ_actif == true){
                                $return[$eqDhwId]['circulation_status'] = "On jusqu'a ".$actif_prog["end_time"];
                            }else{
                                $return[$eqDhwId]['circulation_status'] = "Off jusqu'a ".$actif_prog["end_time"];
                            }
                          //$return[$eqDhwId]['circulation_prog_mode'] = self::getSensoProgrameState($hwCirculation_program)["mode"];
                        }
                    //}  
                        if($dhw_QM == "AWAY" || $dhw_QM == "SYSTEM_OFF"){//
                            $return[$eqDhwId]['dhw_mode'] = $dhw_QM;
                            $return[$eqDhwId]['hotwater_actif'] = false;
                            $return[$eqDhwId]['circulation_prog_actif'] = false;
                            $return[$eqDhwId]['circulation_mode'] = $dhw_QM;

                            $Ecs_status = "Eteint Abscence";
                            $Ecs_status .= $dhw_qm_endTime ? " jusqu'a ".$dhw_qm_endTime : "";
                            $return[$eqDhwId]['Ecs_status'] = $Ecs_status;

                            $circulation_status= "Off Abscence";
                            $circulation_status .= $dhw_qm_endTime ? " jusqu'a ".$dhw_qm_endTime : "";
                            $return[$eqDhwId]['circulation_status'] = $circulation_status;

                            $return[$eqDhwId]['hotwater_prog_endTime'] = "";
                            $return[$eqDhwId]['circulation_prog_endTime'] = "";

                        }
                        elseif($dhw_QM != "Aucun"){
                            $return[$eqDhwId]['dhw_mode'] = $dhw_QM;
                            $return[$eqDhwId]['hotwater_actif'] = true;
                            $return[$eqDhwId]['circulation_prog_actif'] = true;
                            $return[$eqDhwId]['circulation_mode'] = $dhw_QM;
                            $return[$eqDhwId]['Ecs_status'] = "Allumé ".$dhw_QM;
                            $return[$eqDhwId]['circulation_status'] = "On ".$dhw_QM;
                        }
                        elseif($hotwater_operation_mode == "MANUAL"){
                            $return[$eqDhwId]['dhw_mode'] = "MANUAL";
                            $return[$eqDhwId]['hotwater_actif'] = true;
                            $return[$eqDhwId]['circulation_prog_actif'] = true;
                            $return[$eqDhwId]['circulation_mode'] = "MANUAL";
                            $return[$eqDhwId]['Ecs_status'] = "Allumé manuel";
                            $return[$eqDhwId]['circulation_status'] = "On ".$hotwater_operation_mode;
                        }
                        else{
                            $return[$eqDhwId]['dhw_mode'] = $hotwater_operation_mode;
                            //$return[$eqDhwId]['hotwater_actif'] = false;
                            $return[$eqDhwId]['circulation_mode'] = $hotwater_operation_mode;
                        }

                    }
                    if($dhw_QM == $zone_QM) {
                        $return[$eqHomeId]["quick_mode"] = $dhw_QM;
                    }else $return[$eqHomeId]["quick_mode"] = "Autres";

                    if($return[$eqZoneId]['zone_mode'] == $return[$eqDhwId]['dhw_mode'] 
                       && $return[$eqDhwId]['dhw_mode'] == $return[$eqDhwId]['circulation_mode']) {
                        $return[$eqHomeId]["system_mode"] = $return[$eqZoneId]['zone_mode'];
                    }else $return[$eqHomeId]["system_mode"] = "Autres";
				}
              
                if(in_array("liveReport", $dataToSync)){
                    $liveReport = $client->getLiveReport(); 
                    $dbFac['liveReport'] = $liveReport;
                	$devices = $liveReport['devices'];
                    self::$_data['devices'] = $devices;

                    foreach( $devices as $keyDevice=>$device){
                    if($keyDevice === 'meta') continue;
                    $deviceId = $device['_id'];// Control_CC1/Control_SYS_MultiMatic/Control_DHW
                    $deviceName = $device['name'];
                    //log::add('VaillantControl', 'debug', __FUNCTION__ ."	device $keyDevice found $deviceName => $deviceId" );
                    $supposed_eqId = $deviceId.'|'.$sn;
                    if($deviceId == "Control_DHW"){
                        $eqId = 'Control_DHW|'.$sn; 
                        //log::add('VaillantControl', 'debug', __FUNCTION__ ."	eqId1: $eqId" );
                    }
                    elseif(substr($deviceId, 0, 9) == "Control_Z"){
                        $eqId = $deviceId.'|'.$sn;
                        //log::add('VaillantControl', 'debug', __FUNCTION__ ."	eqId2: $eqId" );
                    }
                    else{//substr($deviceId, 0, 10) != "Control_CC"
                        $eqId = "Home|".$sn;
                        //log::add('VaillantControl', 'debug', __FUNCTION__ ."	eqId3: $eqId" );
                    }
                    if ( !isset($return[$eqId]) ) {
                        //log::add('VaillantControl', 'warning', __FUNCTION__ ."	No Object for $eqId" );
                    }
                    //log::add('VaillantControl', 'debug', __FUNCTION__ ."		$deviceName => $eqId" );
                    $report = $device['reports'][0];
                    $reportUnit = isset($report['unit']) ? $report['unit'] : null;
                    $reportName = $report['name'];
                    $reportMeasurement_category = $report['measurement_category'];
                    $reportAssociated_device_function = $report['associated_device_function'];
                    $reportId = $report['_id'];//
                    $value = $report['value'];
                    //unset($report['value']);
                    $report['deviceId'] = $deviceId;
                    $report['deviceName'] = $deviceName;
                    // $devicesConfig[$deviceId] = $reportId;
                    $return[$eqId]['reports'][$reportId] = $report;//$value;



                      // ZonesList/0/configuration/inside_temperature  SystemControl/zones/0/configuration/inside_temperature
                }//foreach( $devices as $keyDevice=>$device){
                }
                if(in_array('emf', $dataToSync)){
                	$emfInfos = $client->getEmfInfos();
                	$dbFac['emfInfos'] = $emfInfos;
					foreach($emfInfos as $emfData){
                		$emfDeviceId = $emfData['id'];//
                		$return[$eqHomeId]["emf"][$emfDeviceId]=[];
                		$emfType = $emfData['type'];//boiler
                		$emfName = $emfData['marketingName'];//Thema
                		$return[$eqHomeId]["emf"][$emfDeviceId]['type']=$emfType;
                		$return[$eqHomeId]["emf"][$emfDeviceId]['name']=$emfName;

                		$emfReports = $emfData['reports'];
                      	// function(DHW,CENTRAL_HEATING,COOLING,COMBINED)
						//	energyType(CONSUMED_ELECTRICAL_POWER,CONSUMED_PRIMARY_ENERGY,ENVIRONMENTAL_YIELDSOLAR_YIELD)
                		foreach($emfReports as $emfReport){
                			$emfReportId = $emfType."|".$emfReport["function"]."|".strtolower($emfReport["energyType"]);
                          	//"DHW|CONSUMED_ELECTRICAL_POWER"
                			$emfReportIndex = round($emfReport["currentMeterReading"]/1000,1);
                			$return[$eqHomeId]["emf"][$emfDeviceId]['mesuresIndex'][$emfReportId]=$emfReportIndex;

                			$emfReportDate = $emfReport["from"]."|".$emfReport["to"];//"2021-08-12|2022-03-10"
							$return[$eqHomeId]["emf"][$emfDeviceId]['mesuresDates'][$emfReportId]=$emfReportDate;
                		}//foreach($emfReports
                	}//foreach($emfInfos as $emfData)
                }

               
                



                $i++;
            }// fin foreach ($facilities as $facility) {
        	if($write == true){
              	self::writedataStat(json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'sync.json');
          		log::add('VaillantControl', 'debug', __FUNCTION__. ' sync: '.json_encode($return, JSON_PRETTY_PRINT));
          
          		self::writedataStat(json_encode($dbFac, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'dbFac.json');
          		//log::add('VaillantControl', 'debug', __FUNCTION__. ' sync: '.json_encode($dbfac, JSON_PRETTY_PRINT));
            }
        }//fi try
		catch (Exception $ex) {
          	
			$error_msg = __FUNCTION__." Erreur catch :  " .$ex->getCode()." - ". $ex->getMessage() . "\n";
			throw new Exception($error_msg);
          	log::add('VaillantControl', 'error', ' 😡 ******'.__FUNCTION__ ." ". $error_msg );
           
		}
      	return $return;
    }
///////////////////////////////////////
	public static function refreshInfos($eqLogicId = null, $cronPhase=null, $ApiData=null){ 
      	$cronpair=false;
      	if (is_int($cronPhase) &&  $cronPhase%2 == 0){
            $cronpair=true;
        }
      	
      
        $eqLogics = [];
        if($eqLogicId == null){
          	$eqLogics = self::byType(__CLASS__, true);  
        }else{
          	$eqLogics[] = self::byLogicalId($eqLogicId, __CLASS__);
        }
        if($ApiData == null){
          	$ApiData = self::syncSenso( ["systemControl"], false);
          	//$ApiData = self::syncSenso( ["systemControl","emf", "gatewayType", "facilityStatus", "roomInstallationStatus", "liveReport"], false);
        }
          
        $eqCount = 1;
		foreach($eqLogics as $eqLogic) {
			$eqLogicId=$eqLogic->getLogicalId();//
          	list($device_id, $sn) = explode('|', $eqLogicId);
			$eqhomeId='Home|'.$sn;
          	$eqhome = self::byLogicalId($eqhomeId, __CLASS__);
          	$eqInfos=[];
          	if (!isset($ApiData[$eqLogicId])) {
              	log::add(__CLASS__,"debug", "⚠️	".__FUNCTION__." Equipement( ".$eqLogic->getName()." ) invalide !");
            	continue;
            }
          	log::add(__CLASS__,"debug", "".__FUNCTION__." Updating ( ".$eqLogic->getName()." )");
          	//$eqCmdis = $eqLogic->getCmd('info', null, null, false);//$_multiple
          	$eqData = $ApiData[$eqLogicId];
          	$cmdis = $eqLogic->getCmd('info', null);
			foreach ($cmdis as $cmd) {
				$cmd_LogId = $cmd->getLogicalId();
              	if (array_key_exists($cmd_LogId, $eqData)) {
                  	$refresh = true;
                  	$cmdValue = $eqData[$cmd_LogId];
                  	log::add(__CLASS__,"debug",  "	".__FUNCTION__." cmd found( ".$cmd_LogId." ) : ".$cmdValue);
              		$eqLogic->checkAndUpdateCmd($cmd, $cmdValue);
                  
                }
                elseif (array_key_exists($cmd_LogId, $eqData['reports'])) {
                  	$refresh = true;
                  	$cmdValue = $eqData['reports'][$cmd_LogId]['value'];
                  	log::add(__CLASS__,"debug",  "	".__FUNCTION__." cmd_report found( ".$cmd_LogId." ) : ".$cmdValue);
              		$eqLogic->checkAndUpdateCmd($cmd, $cmdValue);
                }
              	elseif ($device_id == "Home" && $cmd->getConfiguration('type','') == 'emf' ) {
                  	$refresh = true;
                  	$emfDeviceId = $cmd->getConfiguration('deviceId','');
                  	if (array_key_exists($cmd_LogId, $eqData['emf'][$emfDeviceId]['mesuresIndex'])) {
                  
                        $cmdValue = $eqData['emf'][$emfDeviceId]['mesuresIndex'][$cmd_LogId];
                        log::add(__CLASS__,"debug",  "	".__FUNCTION__." cmd_report found( ".$cmd_LogId." ) : ".$cmdValue);
                        $eqLogic->checkAndUpdateCmd($cmd, $cmdValue);
                    }
                }
              	else{
					log::add(__CLASS__,"warning",  "	".__FUNCTION__." cmd Not found ( ".$cmd_LogId." ) ");
              	}
          
            }
          	if($refresh == true) $eqLogic->refreshWidget();
        }
          
          
          
          
          
          
          
    }
////////////////////////////////cronHourly() 
	public static function cronJob($eqLogicid=null, $from=__FUNCTION__) {
		$cronLeng= (is_numeric(substr($from, 4, 2))) ? substr($from, 4, 2) : null ;
      	$cronPhase = config::byKey('croncount', 'VaillantControl', 1);
      	   
      	if($cronLeng == 5){
      		$nbcron=12;
            $cronPhase = $cronPhase/2;//($cronPhase%2) ?  (($cronPhase)) : ($cronPhase) ;
        }elseif($cronLeng == 10){
      		$nbcron=6;
            $cronPhase = $cronPhase ;
        }
      	$cronpair = (is_int($cronPhase) && $cronPhase%2 == 0) ? true: false;
      
		$seuilWarn =($cronLeng==10)?3:6;//
        $seuilErr =($cronLeng==10)?10:20;
      	$maxTimeUpdate="45";//min
		log::add('VaillantControl', 'debug', __FUNCTION__ .' started ***************** '.$cronPhase ."--".$cronpair."--".$cronLeng);
		
      
      	try {
          	if($cronPhase == 1){
              	$ApiData = self::syncSenso( ["systemControl", "liveReport", "emf"], false);
            }elseif($cronpair){
              	$ApiData = self::syncSenso( ["systemControl", "liveReport"], false);
            }else{
              	$ApiData = self::syncSenso( ["systemControl"], false);
            }
          	self::refreshInfos(null, $cronPhase, $ApiData);//, $nbcron
          	
          	$croncount = config::byKey('croncount', 'VaillantControl', 1);
			if($croncount >= $nbcron){
                 $croncount=1;
			}
			else $croncount = $croncount + 1 ;
           	config::save('croncount', $croncount, 'VaillantControl');
        } 
		catch (Exception $ex) {	
			log::add('VaillantControl', 'debug',''.__FUNCTION__ . __(' Erreur sur ', __FILE__) . ' : ' . $ex->getMessage());
          	//throw new Exception(__($msg, __FILE__));
		}
      	return;
      	
		///////////////////	
		$eqLogics = VaillantControl::byType('VaillantControl', true);
      	
		foreach ($eqLogics as $eqLogic) {
			$errors=false;
          	$type=$eqLogic->getConfiguration('type');
			if ($type=='home') {
				//continue;
              //throw new Exception(__($msg, __FILE__));
			}
			
          	$arMsg = $eqLogic->getStatus('Erreurs_Msg', '');
          	(!is_array($arMsg)) ? $eqLogic->setStatus('Erreurs_Msg', array("","","","","")):null;
			
			//log::add('VaillantControl', 'debug', "**arMsg: ". json_encode($arMsg));
          	if ( isset($arMsg[0]) && $arMsg[0] != "") {
              	$error_level[]="warning";
              	$errors=true;
              	log::add('VaillantControl', 'warning', __FUNCTION__ ." Erreur 0 sur [".$eqLogic->getName()."] : ".$arMsg[0]);
			}
          	
          	if ( isset($arMsg[1]) && $arMsg[1] != "") {
              	$error_level[]="danger";
              	$errors=true;
              	log::add('VaillantControl', 'warning', __FUNCTION__ ." Erreur 1 sur [".$eqLogic->getName()."] : ".$arMsg[1]);
			}
          	if ( isset($arMsg[2]) && $arMsg[2] != "") {
              	$error_level[]="warning";
              	$errors=true;
              	log::add('VaillantControl', 'warning', __FUNCTION__ ." Erreur 2 sur [".$eqLogic->getName()."] : ".$arMsg[2]);
			}
          	if ( isset($arMsg[3]) && $arMsg[3] != "") {
              	$error_level[]="warning";
              	$errors=true;
              	log::add('VaillantControl', 'warning', __FUNCTION__ ." Erreur 3 sur [".$eqLogic->getName()."] : ".$arMsg[3]);
			}
          	if ( isset($arMsg[4]) && $arMsg[4] != "") {
              	$error_level[]="danger";
              	$errors=true;
              	log::add('VaillantControl', 'warning', __FUNCTION__ ." Erreur 4 sur [".$eqLogic->getName()."] : ".$arMsg[4]);
			}
          	if ($eqLogic->getStatus('reachable_Nok', 0) > $seuilErr) {
				$errors=true;
              	$error_level[]="danger";
              	$eqLogic->setStatus('numberTryWithoutSuccess', $eqLogic->getStatus('numberTryWithoutSuccess', 0)+1);
              	$eqLogic->setStatus('danger', 1);
              	$eqLogic->setStatus('reachable_Nok', 1);
                log::add('VaillantControl', 'error', __FUNCTION__ ." Erreur 5".$seuilErr ."sur [".$eqLogic->getName()."] : ".$arMsg[0].' '.$arMsg[1].' '.$arMsg[2]);
            }elseif ($eqLogic->getStatus('reachable_Nok', 0) > $seuilWarn) {
				$errors=true;
              	$error_level[]="danger";
              	$eqLogic->setStatus('numberTryWithoutSuccess', $eqLogic->getStatus('numberTryWithoutSuccess', 0)+1);
              	$eqLogic->setStatus('danger', 1);
              	$eqLogic->setStatus('reachable_Nok', 1);
                log::add('VaillantControl', 'warning', __FUNCTION__ ." Erreur 5".$seuilErr ."sur [".$eqLogic->getName()."] : ".$arMsg[0].' '.$arMsg[1].' '.$arMsg[2]);
            }elseif ($eqLogic->getStatus('module_reachable_Nok', 0) > $seuilErr) {
              	$error_level[]="danger";
              	$errors=true;
              	$eqLogic->setStatus('danger', 1);
              	log::add('VaillantControl', 'error', __FUNCTION__ ." Erreur 6".$seuilErr ."sur [".$eqLogic->getName()."] : ".$arMsg[2]);
			}elseif ($eqLogic->getStatus('module_reachable_Nok', 0) > $seuilWarn) {
              	$error_level[]="danger";
              	$errors=true;
              	$eqLogic->setStatus('danger', 1);
              	log::add('VaillantControl', 'warning', __FUNCTION__ ." Erreur 6 sur [".$eqLogic->getName()."] : ".$arMsg[2]);
			}
          
			$plugUptime=$eqLogic->getStatus('lastCommunication');
          	$syncTime=strtotime($eqLogic->getStatus('syncTime', 0));
			if ($eqLogic->getStatus('numberTryWithoutSuccess', 0) > $seuilErr) {
				$error_level[]="danger";
              	$eqLogic->setStatus('danger', 1);
              	$errors=true;
              	$err_msg = " Erreur WS12 sur [".$eqLogic->getName() 
					."] il n'y a pas eu de mise à jour des données depuis : ".  $eqLogic->getStatus('module_reachable_NokTime');
              	log::add('VaillantControl', 'error', __FUNCTION__ .$err_msg );
			}
          	elseif ($eqLogic->getStatus('numberTryWithoutSuccess', 0) > $seuilWarn ) {
				$error_level[]="warning";
              	$eqLogic->setStatus('warning', 1);
              	$errors = true;
              	$err_msg =" Erreur WS2 sur [".$eqLogic->getName() 
					."] il n'y a pas eu de mise à jour des données depuis : ".  $eqLogic->getStatus('lastCommunication') ;
                log::add('VaillantControl', 'warning', __FUNCTION__ .$err_msg);
			}
          
          
          
          
          	if (!$errors) {
				$eqLogic->setStatus('reachable_NokTime', '');
              	$eqLogic->setStatus('reachable_Nok', '');
              	$eqLogic->setStatus('numberTryWithoutSuccess', 0);
              	$eqLogic->setStatus('module_reachable_NokTime', '');
              	$eqLogic->setStatus('module_reachable_Nok', '');
              	$eqLogic->setStatus('warning', 0);
              	$eqLogic->setStatus('danger', 0);
				$eqLogic->setStatus('Erreurs_Msg',array("","","","",""));
              	
              	
			}
          	elseif(array_key_exists('danger', $error_level)){
				$eqLogic->setStatus('danger', 1);
            }
          	elseif(array_key_exists('warning', $error_level)){
				$eqLogic->setStatus('warning', 1);
            }
            
          /*if(1 == 2){
              $eqLogic->setStatus('module_reachable_NokTime', '');
              $eqLogic->setStatus('reachable_NokTime', '');
              $eqLogic->setStatus('module_reachable_Nok', 0);
              $eqLogic->setStatus('reachable_Nok', 0);
              $eqLogic->setStatus('numberTryWithoutSuccess', 0);
              
          }*/
		}
      	
		//log::add('VaillantControl', 'info', 'Fin '.__FUNCTION__ .'  ************************');
	}
////////////////////////////////
  	public static function acronHourly() {
      	log::add('VaillantControl','info',__FUNCTION__ .' start... ');
      	$ApiData = self::syncSenso( ["emf"], false);
      	self::refreshInfos(null,null,$ApiData);
     }
////////////////////////////*********************///////////////////////////////////// 
    public static function cron5($eqLogicid=null, $from=__FUNCTION__) {
		if (config::byKey('functionality::cron10::enable', 'VaillantControl', 0) == 1){
			config::save('functionality::cron5::enable', 0, 'VaillantControl');
			return;
		}
		
      	VaillantControl::cronJob($eqLogicid, __FUNCTION__);
		log::add('VaillantControl', 'info', ' Fin '.__FUNCTION__ .'  ************************ *');
    }
////////////////////////////*********************///////////////////////////////////// 
    public static function cron10($eqLogicid=null, $from =__FUNCTION__) {
		if (config::byKey('functionality::cron5::enable', 'VaillantControl', 0) == 1){
			config::save('functionality::cron10::enable', 0, 'VaillantControl');
			return;
		}
      	VaillantControl::cronJob($eqLogicid, __FUNCTION__);
		log::add('VaillantControl', 'info', ' Fin '.__FUNCTION__ .'  ************************ *');
    }
/////////////////////////////////////*********************///////////////////////////////////// 
	public function postSave() {
		log::add('VaillantControl', 'info', '              '.__FUNCTION__ .' started ********* '.$this->getName());
		if ($this->getConfiguration('cmdsMaked') != true) {
			return $this->makeCmd(self::$_eqData); 
        }
      	
	}
/////////////////////////////////////////
  	public function toHtml($_version = 'dashboard') {
      	//$eqtuile = $this->getConfiguration('eqtuile','');
      	/*if ($eqtuile == "core"){
          	VaillantControl::$_widgetPossibility = array('custom' => 'layout');
          	return eqLogic::toHtml($_version);
        }*/
      	$eqtuile = $this->getConfiguration($_version.'eqtuile', 'default');
     	if ($eqtuile == "core" || $eqtuile == "default"){
          	VaillantControl::$_widgetPossibility = array('custom' => 'layout');
          	return eqLogic::toHtml($_version);
        }
     
		$replace = $this->preToHtml($_version);
 		if (!is_array($replace)) {
 			return $replace;
  		}
      
      	$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
      	$_eqType = $this->getConfiguration('type');
      	$eqLog_id = $this->getLogicalId();
			list($roomid, $homeid) = explode('|', $this->getLogicalId());
          	$eqhome_Id='Home|'.$homeid;
      		$eqhome=VaillantControl::byLogicalId($eqhome_Id, 'VaillantControl');
		////////////////////// CMD Info /////////////////////
      	if(is_object($eqhome) && $eqLog_id != $eqhome_Id){
		 	$cmdis=array_merge($eqhome->getCmd('info', null), $this->getCmd('info', null));
          	$cmdas=array_merge($eqhome->getCmd('action', null), $this->getCmd('action', null));
      	}else{
      		$cmdis=$this->getCmd('info', null);
          	$cmdas=$this->getCmd('action', null);
        }
        foreach ($cmdis as $cmd) {
          	$cmd_LogId=$cmd->getLogicalId(); 
			//log::add('VaillantControl', 'debug', ' '.__FUNCTION__ .' cmd: '.$cmd_LogId );
          	$replace['#' . $cmd_LogId . '#'] = $cmd->execCmd();
			$replace['#' . $cmd_LogId . '_id#'] = $cmd->getId();
			$replace['#' . $cmd_LogId . '_collectDate#'] =date('d-m-Y H:i:s',strtotime($cmd->getCollectDate()));
			$replace['#' . $cmd_LogId . '_updatetime#'] =date('d-m-Y H:i:s',strtotime( $this->getConfiguration('updatetime')));
			
			if ($cmd->getConfiguration('isdate', false)) {
				$actualdate=date('d-m-Y');
              	$cmdvalue=$cmd->execCmd();
              	if (!$cmdvalue) {//date('d-m-Y', strtotime($cmd->execCmd()))
					$htmlvalue = '';
				}elseif ($cmdvalue == 'Nouvel Ordre' ) {//date('d-m-Y', strtotime($cmd->execCmd()))
					$htmlvalue = 'Nouvel Ordre';
				}elseif ($actualdate == date('d-m-Y', strtotime($cmdvalue) )) {//date('d-m-Y', strtotime($cmd->execCmd()))
					$htmlvalue = date('H:i', strtotime($cmdvalue));
				}else {
					$htmlvalue = date('d/m H:i', strtotime($cmdvalue));
				}
              	$replace['#' . $cmd_LogId. '#'] = $htmlvalue;
              	//$vcmd=$cmd->execCmd();
              	//log::add('VaillantControl', 'debug', ' '.__FUNCTION__ .' cmdvalue: '.$cmdvalue.' to: '.$htmlvalue );
            }
          	if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd_LogId . '_history#'] = 'history cursor';
			}
          	if ($cmd_LogId == 'open_window' && $cmd->execCmd() == 1) {
              	$replace['#window_alert_icon#'] = 'icon jeedomapp-fenetre-ouverte';
            }
          	if ($cmd_LogId == 'wbhook_last') {
              	//$cmd_wbhook_last_val = $cmd->execCmd();
              	//list($opt_eqId, $opt_event) = explode(' ** ', $cmd_wbhook_last_val);
              	$replace['#wbhook_subscribe_date#'] = $cmd->getConfiguration('subscribe_date','');
              	
              
            }
		}
		$replace['#batterydanger#'] = ($this->getStatus('batterydanger')!=0)? $this->getStatus('batterydanger'):0;
		$replace['#lastCommunication#'] = $this->getStatus('lastCommunication');
        $replace['#numberTryWithoutSuccess#'] = $this->getStatus('numberTryWithoutSuccess',0);
		$replace['#danger#'] = $this->getStatus('danger',0);
      	$replace['#reachable_Nok#'] = $this->getStatus('reachable_Nok',0);
      	$replace['#lastCommTherm#'] = date('d-m-Y H:i:s',strtotime( $this->getStatus('lastCommTherm')));
        $replace['#lastCom#'] = date('d-m-Y H:i:s',strtotime( $this->getStatus('lastCommunication') ));
      	//$replace['#nahomeid#'] = $this->getConfiguration('HomeId');
      	$replace['#eqLogic_class#'] = 'eqLogic_layout_default';
      	$replace['#roomType#'] = $this->getConfiguration('roomType');
      	$replace['#type#'] = $this->getConfiguration('type');
      	$replace['#alert_level#'] = $this->getStatus('alert_level',0);
      
      	//$replace['#eq_type#'] = $this->getConfiguration('type');
      	$arMsg = array_map('htmlentities', $this->getStatus('Erreurs_Msg', null));
      	//$arMsg = $this->getStatus('Erreurs_Msg', null);
      	//$replace['#Erreurs_Msg#'] = json_encode(array_map('htmlentities', $this->getStatus('Erreurs_Msg', null)));
      	$replace['#Erreurs_Msg#'] = json_encode($this->getStatus('Erreurs_Msg', null),JSON_UNESCAPED_UNICODE);
      
      	//$replace['#Erreurs_Msg#'] = array_map('htmlentities', $this->getStatus('Erreurs_Msg', null));
      //$replace['#Erreurs_Msg#'] = a$this->getStatus('Erreurs_Msg', null);
      	
      	
      	if ($eqhome->getConfiguration('temperature_ext', '') != ''){
        	$value = '';
          	$value=round(jeedom::evaluateExpression($eqhome->getConfiguration('temperature_ext')), 1);
          	if ($value != null) {
				$replace['#temperature_ext#'] =$value;
              	//log::add('VaillantControl','debug',__FUNCTION__.'   temperature_ext '.$value);
			}
          
          	$Expression='';
			preg_match_all("/#([0-9]*)#/", $eqhome->getConfiguration('temperature_ext'), $matches);
			foreach ($matches[1] as $cmd_id) {
              //log::add('VaillantControl','debug',__FUNCTION__.'		$cmd_id '.$cmd_id);
				if (is_numeric($cmd_id)) {
					$cmd = cmd::byId($cmd_id);
					if (is_object($cmd) && $cmd->getType() == 'info') {
						$Expression .= '#' . $cmd_id . '#';
                      	$replace['#temperature_ext#'] = $cmd->execCmd();
                      	//$replace['#temperature_ext_id#'] = $cmd_id;
                  //log::add('VaillantControl','debug',__FUNCTION__.'   temperature_ext2 '.$cmd->execCmd());
						break;
					}
				}
			}
        }
      	else{$replace['#temperature_ext#'] = '';}
      
      
      	foreach ($cmdas as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            /*if ($cmd->getLogicalId()=='planningset') {
				$cmd->setConfiguration('listValue',  $this->getCmd(null, 'listplanning')->execCmd());
				$cmd->setValue($this->getCmd(null, 'planning')->execCmd())
                $cmd->save();
				$cmd->refresh();
			}*/
            if ($cmd->getConfiguration('listValue', '') != '') {
				$listOption = '';
				$elements = explode(';', $cmd->getConfiguration('listValue'));
				$foundSelect = false;
				foreach ($elements as $element) {
					//list($item_val, $item_text) = explode('|', $element);
					$coupleArray = explode('|', $element);
                  	$item_val = $coupleArray[0];
                  	$item_text  = (isset($coupleArray[1])) ? $coupleArray[1]: $item_val;
                  
					$cmdValue = $cmd->getCmdValue();
					
                  	if (is_object($cmdValue) && $cmdValue->getType() == 'info') {
						if ($cmdValue->execCmd() == $item_val) {
                          	$valSelected=$item_text;
							$listOption .= '<option value="' . $item_val . '" selected>' . $item_text . '</option>';
							$foundSelect = true;
						} else {
							$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
						}
					} else {
						$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
					}
				}
				if (!$foundSelect) {
					$listOption = '<option value="" selected>Aucun</option>' . $listOption;
                  	$replace['#' . $cmd->getLogicalId() . '_Value#'] = 'Aucun';
				}else{
                  	$replace['#' . $cmd->getLogicalId() . '_Value#'] = $valSelected;
                }
                  
				
				//$replace['#listValue#'] = $listOption;
				 //$replace['#' . $cmd->getLogicalId() . '_id_listValue#'] = $listOption;
				 $replace['#' . $cmd->getLogicalId() . '_listValue#'] = $listOption;
              //log::add('VaillantControl','debug',__FUNCTION__.'		***********   listOption '.$listOption);
			}
			
                    
        }
		
      	if($_eqType == 'NRV' || $_eqType == 'NATherm1'){
          	$replace['#showHomeMode#'] = $this->getConfiguration('showHomeMode', true);
          $replace['#showRoomModtech#'] = $this->getConfiguration('showRoomModtech', true);
          $replace['#showPlannings#'] = $this->getConfiguration('showPlannings', true);
          
          	$templateArray[$version] = getTemplate('core', $version, $eqtuile.'_eqTherm', 'VaillantControl');
        }else{
          	$templateArray[$version] = getTemplate('core', $version, $eqtuile.'_eqHome', 'VaillantControl');
        }
      
      
      
      
      
/***************/
      
      if ($roomid=='Home'){
      		$body = null;
        	$colorDark = 'color: rgb(69, 70, 72)';
        	
          	$th_Nom = '<th style=" text-align: center;">Piéce</th>';
            //$th_Tendance = '<th style=" text-align: center;">Tendance</th>';
        	//$th_Actif = '<th style=" text-align: center;">Actif</th>';//$th_Actif
          	$th_Temp = '<th style=" text-align: center;">T °</th>';//$th_Temp
          	$th_Cons = '<th style=" text-align: center;">C °</th>';//$th_Cons
          	$th_Mode = '<th style=" text-align: center;">Mode</th>';//$th_Mode
          	//$th_Type
                        
            $thead = $th_Nom.  $th_Temp. $th_Cons. $th_Mode;//. $th_Type$th_Tendance.
            
          
          	$eqLogics = VaillantControl::byType('VaillantControl', true);
            foreach ($eqLogics as $eqLogic) {
                $_eqType = $eqLogic->getConfiguration('type');
               	if ($_eqType == "Home" || $eqLogic->getIsVisible() != 1) {
					continue;
                }
                
                $eq_id = $eqLogic->getID();
				$cmdis=["actif","varTempIn","temperature","consigne","room_modetech"];
              	$arCmd=[];
              	// $arCmd["actif"] $arCmd["varTempIn"] $arCmd["temperature"] $arCmd["consigne"]//$arCmd["mode"]//"
              	
              	
              	foreach ($cmdis as $cmdi) {
                  	$cmd = $eqLogic->getCmd(null, $cmdi);
                    if ( is_object($cmd) ){
                        $cmd_val = $cmd->execCmd();
                      	$arCmd[$cmdi] = $cmd_val;
                    }
                }
				$eqName = $eqLogic->getName();
              	$eqstatus = $arCmd['actif'];
              	$varTempIn = $arCmd['varTempIn'];
              	//$temperature = floatval($arCmd['temperature']*10/10);
              	$temperature = number_format($arCmd['temperature'], 1, '.', "");
              	$consigne =  number_format($arCmd['consigne'], 1, '.', "");
                 // $consigne = round($arCmd['consigne']*10,1)/10;//$arCmd['consigne']
             // round(($temperature - $oldTemperature), 1);
              	$room_modetech = $arCmd['room_modetech'];
              
              
              	
              
              
				$eq_typeIcon = '';
				$eq_type=$eqLogic->getConfiguration('type');
				if ($eq_type=='NRV') {
					$eq_typeIcon = 'fa-globe';
				} elseif ($eq_type=='NATherm1'){
					$eq_typeIcon = 'fa-tablet';
				}
				
                
              
              	 	$statusIcon = 'fa-check';
					$statusStyle = '';
					$activeStyleOff = $colorDark;
					$activeStyleOn = '';
              
              
              
              	$td_eqName = '<td><span class="fas '.$eq_typeIcon . ' " style="margin-right: 5px;' . $statusStyle . ';"></span><span style="' . $statusStyle . ';">' . $eqName . '</span></td>';
                  
                //  
                $divStatus = '<div class="history fas ' . $statusIcon . ' " data-type="info" data-subtype="binary" data-cmd_id="' . $eqstatus . '" style="margin: 5px;' . $statusStyle . ';"' .' title="status: '.$eqstatus. '"></div>';  
              	$td_eqstatus = '<td style="text-align: center;">' . $divStatus . '</td>';
              	$varTempIco = '';
              	if ($varTempIn > 0) {
					$varTempIco = 'fas fa-arrow-up';
				} elseif ($varTempIn < 0){
					$varTempIco = 'fas fa-arrow-down';
				}
              	$sp_varTempIn='<span class="varTempIn '.$varTempIco.'" title="'.$varTempIn.'" style="' . $statusStyle . ';"></span>';
              	//$td_varTempIn = '<td class="varTempIn"><span title="'.$varTempIn.'" style="' . $statusStyle . ';">' . $varTempIn . '</span></td>';
              	
              
              	$td_temperature = '<td class="temperature">'. $sp_varTempIn.' <span style="' . $statusStyle . ';">'.$temperature . '</span></td>';
              	$td_consigne = '<td class="consigne"><span style="' . $statusStyle . ';">' . $consigne . '</span></td>';
              	$td_room_modetech = '<td class="room_modetech"><span style="' . $statusStyle . ';">' . $room_modetech . '</span></td>';
              
              	$body .= '<tr class="' . $eqstatus . '" id="' . $eq_id . '">'
                  			.$td_eqName
                  			//.$td_eqstatus
                            //.$td_varTempIn
                            .$td_temperature
                            .$td_consigne
                            .$td_room_modetech
						.'</tr>';
				
				
				
			}
			//fin foreach ($eqLogics as $eqLogic) 
            
          
          	$replace['#thead#'] = $thead;
            $replace['#body#'] = $body;
            
        	
        	
    	}
/***************/       
      
      
      //$templateArray[$version] = getTemplate('core', $version, 'eq_'.$_eqType, 'VaillantControl');
		//$templateArray[$version] = getTemplate('core', $version, 'eq_NATherm1', 'VaillantControl');
		return $this->postToHtml($_version, template_replace($replace, $templateArray[$version])); 
   
	}
/////////////////////////////////////////
  	public static function writedataStat($data, $file=null, $putOption=null) {
		log::add('VaillantControl', 'debug', __FUNCTION__ .' '.$file.' started *****************');
      	$filename = (!$file ? 'data.json' : $file);
      	$path = __DIR__  . '/../../data';
      	if (!is_dir($path)) {
        	@mkdir($path, 0775, true);
        	log::add('VaillantControl', 'debug', 'Dossier data crée...');
		} else {
          	//log::add('VaillantControl', 'debug', 'Le dossier data existe');
      		com_shell::execute(system::getCmdSudo() .'chmod 777 -R ' . $path. ' > /dev/null 2>&1;');
          	//log::add('VaillantControl', 'debug', 'Droit sudo ok');
     	}
		//file_put_contents($dir . '/' . $_language . '.json', json_encode($_translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		//file_put_contents($path.'/'.$filename.'.json', $data . "\n", FILE_APPEND);
      	file_put_contents($path.'/'.$filename, $data . "\n", $putOption);
	}
/////////////////////////////////////////
  	public static function is_timstamp($data=null, $action=null) {
      	if(!$data){
          	log::add('VaillantControl', 'debug',''. __FUNCTION__ .' null time'.$data);
          	return NULL;
        }
      	if(!is_numeric($data)){
          	$data = floatval(strtotime($data));
        }
      	if($data > 0 && strlen($data) != 10){//in minutes
          	$data_stamp = time() + ($data * 60);
        }elseif($data < time() || $data <= 0){//is stamp
          	$data_stamp = null;//"in past";
        }elseif($data > strtotime("+1 year")){//is stamp
          	$data_stamp = null;//"too long";
        }elseif($data > strtotime("+1 day") && $action == "manual"){//is stamp
          	$data_stamp = null;//"too long";
        }else{
          	$data_stamp= $data;
        }
      	$formDate=($data_stamp) ? " (".date('d-m-Y H:i',$data_stamp).") ":'';
      	log::add('VaillantControl', 'debug',''. __FUNCTION__ .' '.$data." => ".$data_stamp.$formDate);
        return $data_stamp;
	}
/////////////////////////////////////////
  	public function setZoneCons($data, $device_id) {
      	$eqLogicId = $this->getLogicalId();
      	list($device_id, $sn) = explode('|', $eqLogicId);
      	log::add('VaillantControl', 'debug', ''.__FUNCTION__ ."  start  $eqLogicId  data: ".json_encode($data));
      	//_setZoneQV //_setZoneMode // _setZoneAway //_setZoneQM
      	//_changeHeatManualCons //_changeHeatAwayCons //_changeHeatEcoCons //_changeHeatQvCons
      	$temperature = +$data;
        $ModeCmd = $this->getCmd(null, 'zone_mode');  	
        if(is_object($ModeCmd)){
			$mode = $ModeCmd->execCmd();
        }
      	$client = VaillantControl::getClient('int', $sn);
            
      	switch($mode){
                case "MANUAL":
            		$command = $client->_changeHeatManualCons($temperature, $device_id);
                	break;
				case "OFF":
                case "AUTO":
            		$duration = +$this->getConfiguration('qv_duaration', 180)/60;
                	$command = $client->_setZoneQV($temperature, $device_id, $duration);
                	break;
                case "QUICK_VETO":	
            		$command = $client->_changeHeatQvCons($temperature, $device_id);
                	break;
                case "AWAY":
            		/*$ModeEndTimeCmd = $this->getCmd(null, 'mode_endTime');  	
                    if(is_object($ModeEndTimeCmd)){
                        $ModeEndTime_cmdVal = $ModeEndTimeCmd->execCmd();
                      	$ModeEndTime = strtotime($ModeEndTime_cmdVal);
                    }*/
            		
            		$command = $client->_changeHeatAwayCons($temperature, $device_id);
                	break;
          		default:
            		$command = $client->_changeHeatQvCons($temperature, $device_id);
                	break;
                
            }
          	return $command;
      	
    }
/////////////////////////////////////////
  	public function execCmda($function, $data) {
      	if(is_null($function) || $function == "") throw new Exception(__FUNCTION__ ." Aucune fonction associée à cette action");
      	log::add('VaillantControl', 'debug', ''.__FUNCTION__ .'  start function: '.$function. ' -- data: '.json_encode($data));
      	$eqLogicId = $this->getLogicalId();
      	list($device_id, $sn) = explode('|', $eqLogicId);
      	//$data[] = $device_id ;
      	if( method_exists('VaillantControl', $function) ){
			log::add('VaillantControl', 'debug', ''.__FUNCTION__ .'  method_exists 2 : '.$function);
			$return = call_user_func_array([$this, $function], [$data, $device_id]);
        }
      	else {
          	$object = VaillantControl::getClient('int', $sn);
            if(method_exists($object, $function)){
                $return = call_user_func_array([ $object, $function], [$data, $device_id] );
              	$log = is_array($return) ? json_encode($return) : $return;
              	log::add('VaillantControl', 'debug', ''.__FUNCTION__ .'  Api method_exists : '.$function.": ".$log);
                
            }
      		else{
				//$return = "Nok";
              	$error_msg = __FUNCTION__." No method_exists($function) : " .$return;
				log::add('VaillantControl', 'warning', ' 😡 ****** '. $error_msg );
				throw new Exception($error_msg);
           }
      	}
      	if($return == "ok"){
          	 sleep(3);
             self::refreshInfos($eqLogicId);
        }else{
			$error_msg = __FUNCTION__." " .$return;
			log::add('VaillantControl', 'warning', ' 😡 ****** '. $error_msg );
			throw new Exception($error_msg);
        }
      	return $return;
	}
  
  
  
}
/* * ***************************class VaillantControlCmd********************************* */




class VaillantControlCmd extends cmd {
   
/*	
	public function dontRemoveCmd() {
      return true;
    }
     */
///////////////////////////////
 
/////////////////////////////////
	public function execute($_options = array()) {
		
		if ($this->getType() == '') {
			return '';
		}
		$eqLogic = $this->getEqlogic();
      	//list($roomid, $homeid) = explode('|', );
      	list($device_id, $sn) = explode('|', $eqLogic->getLogicalId());
          	
		$action = $this->getLogicalId();
		
		log::add('VaillantControl', 'debug', ''.__FUNCTION__ .' Action : '.$action.' sur: '.$eqLogic->getName());
      	//$data = [];
        if ($action == 'refresh') {
          	if ($device_id == 'Home')
				//$command = VaillantControl::refreshInfos();
          		$command = VaillantControl::cronHourly();//cronJob($eqLogicid=null, $from=__FUNCTION__)
          	else 
              	$command = VaillantControl::refreshInfos($eqLogic->getLogicalId());
          	return;
		} else {
          	$function = $this->getConfiguration('function', null);
      		if(is_null($function)){
             	$function = $action;  
            }
          	log::add('VaillantControl', 'debug', ''.__FUNCTION__ .' Action: '.$action.' => function: '.$function." ".$this->getSubType());
      		//else throw new Exception(__FUNCTION__ ." Aucune fonction associée à cette action");
        }
		
      	if($this->getSubType() == "slider"){
          	if (isset($_options['slider']) && $_options['slider'] != ''){
				//$data['slider'] = $_options['slider'];
            	$data = +$_options['slider'];
            }
        } elseif($this->getSubType() == "select"){
          	if (isset($_options['select']) && $_options['select'] != ''){
				$data = $_options['select'];
            }
        } elseif($this->getSubType() == "message"){
			if (isset($_options['message']) && $_options['message'] != ''){
				$data = $_options['message'];
            }
          	if (isset($_options['title']) && $_options['title'] != ''){
				$data .= ', '.$_options['title'];
            }
        } elseif($this->getSubType() == "other"){
          	$data = $action;
        }  
        
      
		//if(is_null($data) ) throw new Exception(__FUNCTION__ ." Aucune fonction associée à cette action");
		$command = $eqLogic->execCmda($function, $data);
      	log::add('VaillantControl', 'debug', ''.__FUNCTION__ .' command : '.$command);
      	return $command;
      	
      	
	////////////////////////les actions //////////////////////////////////////////////////
	
		if ($this->getLogicalId() == 'temperature_ext') {
			return round(jeedom::evaluateExpression($eqhome->getConfiguration('temperature_ext')), 1);
		} 
		if ($action == 'writedata') {
			 $command = VaillantControl::cronExt();				
        } 
        elseif ($action == 'refresh') {
          	if ($device_id == 'Home')
				$command = VaillantControl::refreshInfos();
          	else 
              	$command = VaillantControl::refreshInfos($eqLogic->getLogicalId());
		} 
      	/////
        elseif ($action == 'refreshall') {
			$command = VaillantControl::cron10();
		} 
      	/////
      	elseif ($action == 'schedule') {//Home
			$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(),$action);
		}
      	/////
      	elseif ($action == 'setaway' || $action == 'sethg') {
			$time='';
          	$endtime=null;
			if (isset($_options['message'])){
				$endtime=$_options['message'];
            }
          	$modeToset = substr($action,3);
			$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(), $modeToset, $endtime);
        }elseif ($action == 'homeAuto' || $action == 'cancelhg' || $action == 'cancelaway' || $action == 'setschedule') {//Home
			$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(),'schedule');
		}
      	///// 
      	elseif ($action == 'setmax') {
			$time='';
          	$endtime=null;
			if (isset($_options['message']) && $_options['message'] != ''){
				$endtime=$_options['message'];
              	log::add('VaillantControl', 'debug', ''.__FUNCTION__ .' time : '.$time.' endtime : '.$endtime);
			}
          	$modeToset = substr($action,3);
			$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(), $modeToset, null, $endtime);
        }
      	/////
      	elseif ($action == 'setoff') {
          	$modeToset = substr($action,3);
			$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),$modeToset);
		} 
		/////$action==="roomAuto" || $action==="home"
      	elseif ($action=='roomAuto' || $action=='manoff'|| $action=='canceloff' || $action=='cancelmanual'){
			$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(), $action);
		}
		/////
      	elseif ($action == 'consigneset_mobile') {
			$roomConsset = $_options['slider'];
			$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),'manual', $roomConsset);
          	//$command = $eqLogic->changeThermPoint($eqLogic->getLogicalId(),'manual', $roomConsset);
		} 
		
		/////
      	elseif ($action == 'consigneset') {
          	$temperatureset = null;
          	$setmode='manual';
          
          	if (isset($_options['title']) && $_options['title'] != ''){
              	$temperatureset = $_options['title'];
              	//$setmode='manual';
            }elseif (isset($_options['slider']) && $_options['slider'] != ''){
              	$temperatureset = $_options['slider'];
              	//$setmode='manual';
            }
          	$endtime = null;
          	if (isset($_options['message']) && $_options['message'] != ''){
				$endtime = floatval($_options['message']) * 60 + time();
              	$endtime = $_options['message'];
            }
          	if(!$temperatureset && !$endtime){
              	log::add('VaillantControl', 'error', 'Parametres insuffisants pour consigneset '. $temperatureset.' '.$endtime);
            }
          	//log::add('VaillantControl', 'debug', ''.__FUNCTION__ .' setmode: '.$setmode.' temperatureset : '.$temperatureset.' endtime : '.$endtime);
          	$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(), $setmode,$temperatureset, $endtime);
          	//$command = $eqLogic->changeThermPoint($eqLogic->getLogicalId(),$setmode, $temperatureset, $endtime);
        } 
		/////
      	elseif ($action == 'setmode') {
          	$mode = ( isset($_options['title']) ) ?  $_options['title'] : null;
          	$duree_set = ( isset($_options['message']) || $_options['message'] !== '') ?  $_options['message'] : null;
			if(!$mode && !$duree_set){
              	log::add('VaillantControl', 'error', 'Parametres insuffisants pour setmode '.$mode);
            }
          	//$timestamp = ($duree_set) ? strtotime($duree_set):null;
          	$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(),$mode,$duree_set);
        } /////
      	elseif ($action == 'planningset') {
			$scheduleid = $_options['select'];
          	$command = $eqLogic->changescheduleTherm($eqLogic->getLogicalId(), $scheduleid);
		}
		/////
      	elseif ($action == 'thermPriority_set') {
			$priority = $_options['select'];
          	$command = $eqLogic->changeHeatingPriority($eqLogic->getLogicalId(), $priority);
		}      
		/////
      	elseif ($action == 'setoffset'){
          	$offset = $_options['slider'];
          	$command = $eqLogic->changeMeasureOffset($eqLogic->getLogicalId(), $offset); 
            
        }  
      	elseif ($action == 'setTrueTemperature') {
			$corrected_temperature = $_options['message'];
          	if($corrected_temperature === null || $corrected_temperature == ''){
            	return;
            }
          	$command = $eqLogic->setTrueTemperature($eqLogic->getLogicalId(),$corrected_temperature);
		}
		/////
      	elseif ($action == 'homeboost') {
			$consOrder = $_options['slider'];
          	$endtime=null;
			$command = $eqLogic->changeThermPoint($eqLogic->getLogicalId(),'homeboost', $consOrder, $endtime);
          
		}
		////////Added
		log::add('VaillantControl', 'debug', ''.__FUNCTION__ .' Fin cmd-Action : '.$action.' ');
	}

    /*     * **********************Getteur Setteur*************************** */
}


?>