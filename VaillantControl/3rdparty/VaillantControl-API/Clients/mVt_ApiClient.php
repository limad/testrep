<?php
namespace VaillantControlApi\Clients;
error_reporting(E_ALL);  
//use VaillantControlApi\Common\mVt_Data as mVt_Data;//NARestErrorCode::INVALID_authToken:
use log as log;
use jeedom as jeedom;
use Exception as Exception;
//namespace app\components;
//require_once realpath(dirname(__FILE__) . '/../../../../core/php/core.inc.php');

	
class mVt_ApiClient {

    protected $conf = array();
  	public $healthCheckPassed = false;
    public $lastGeneratedCommand;
  	private $_func_cb;
    private $_object_cb;
  	private $_ch;
    private $_lastCurlError;
    private $_lastCurlInfo;
    private $_lastCurlErrno;
    private $_proxyPort;
    private $_proxyHost;
    //private $_apiAddress = 'https://smart.vaillant.com/mobile/api';//195.179.164.64
    private $_smartphoneId = 'multimaticweb';//'mvaillant';//sensoweb
    private $_username;
    private $_password;
    private $_authToken;//*************************
  	private $_jsessionid;
    private $_loginRequired = false;
    private $_lastApiMeta;
    
  	public $_currentFacility="21211900202826970938020910N8";
    public $_prefix;// = 'tli/';
    //public $_rqstAll = array();
  
	private $_healthCheckUri = 'https://smart.vaillant.com/mobile';//$_healthCheckUri
  	private $_loginCheck = 'https://smart.vaillant.com/mobile/api/check';
    
  	private $_authenticate ="https://smart.vaillant.com/mobile/api/v4/account/authentication/v1/authenticate";
    private $_new_token = "https://smart.vaillant.com/mobile/api/v4/account/authentication/v1/token/new";
    private $_logout = "https://smart.vaillant.com/mobile/api/v4/account/authentication/v1/logout";
  	
  	public $_base = "https://smart.vaillant.com/mobile/api/v4";
    public $_system = "https://smart.vaillant.com/mobile/api/v4/facilities/{_serial}/systemcontrol";
  

// ************************************************************************************************************************** //  	
  	public function __construct($config = array()){
      	if(isset($config["username"])) {
            $this->_username = $config["username"];
        }
        if(isset($config["password"])) {
            $this->_password = $config["password"];
        }
       	if(isset($config["smartPhoneId"])) {
            $this->_smartphoneId = $config["smartPhoneId"];
        }
		if(isset($config["cookies"])) {
          	$cookies = $config["cookies"];
        }else{
			$cookies = realpath(dirname(__FILE__) . '/cookie_VaillantControl.txt');
        }
      	$this->_cookies = $cookies;
      
      	if(isset($config["mVt_prefix"])) {
            $this->_prefix = $config["mVt_prefix"];
          	$this->_system = $this->_system.$config["mVt_prefix"];
        }else{
          	$this->_prefix = '/v1';
          	$this->_system = $this->_system.'/v1';
        }
      	if(isset($config["arToken"])) {//["authToken"]
            $this->_authToken = $config["arToken"]["authToken"];
          	$this->_jsessionid = $config["arToken"]["JSESSIONID"];
        }
      	if(isset($config["func_cb"]) && isset($config["object_cb"]) ) {
          	$this->_func_cb = $config["func_cb"] ;
          	$this->_object_cb = $config["object_cb"] ;
        }
      	if(isset($config["_currentFacility"])) {
          	$this->_currentFacility = $config["_currentFacility"] ;
        }
    }

// ************************************************************************************************************************** //  	
  	public function healthcheck(){
      	$url = $this->_healthCheckUri . '/';
      	$healthcheck =  $this->_makeRequest($url);
      	if ($healthcheck && $this->_lastCurlInfo['http_code'] == 200) {
            $this->healthCheckPassed = true;
          	return true;
        }
      	return false;
    }

// ************************************************************************************************************************** //  	
  	public function _getUri($data){
      	$dataUri = isset($this->$data) ? $this->$data : null;
      	if($data != "" && !$dataUri){
          	$error_msg = "Error ".__FUNCTION__."() No uri found for: ".$data;
          	log::add('VaillantControl', 'warning','		'.__FUNCTION__ .' '. $error_msg);
          	throw new Exception($error_msg);
        }
      	//log::add('VaillantControl', 'debug','		'.__FUNCTION__ . ' dataUri: '. $dataUri);
    	$url = str_replace(array("{_system}", "{_base}"), array($this->_system,$this->_base), $dataUri);
      	$url = $this->_currentFacility ? str_replace("{_serial}", $this->_currentFacility, $url) : $url;
            
      	return $url;
    }
  	
// ************************************************************************************************************************** //  	
  	public function _rqstApi($command, $method = 'GET', $postFields = array(), $retry = false){
        if (is_array($method) && empty($postFields)){
            $postFields = $method;
            $method = 'GET';
        }
      	$httpheader = array();//'Content-Type: application/json'
      	if (substr($command, 0, 4) == "http"){
          	$url = str_replace("{_system}", $this->_system, $command);//. '/' . 
        }
      	else if (substr($command, 0, 1) == "_"){
            $url = $this->_getUri($command);
          	$url = str_replace("{_serial}", $this->_currentFacility, $url);
        
        }else{
          	$url = $this->_base;
          	$url .= (substr($command, 0, 1) == "/") ? "" : "/";
          	$url .= $command;
        }
      	$this->lastGeneratedCommand = $command;
        log::add('VaillantControl', 'debug', __FUNCTION__ .' url: '.$url);
       
      	if(isset($postFields['authToken'])){
			$httpheader[] = 'Authorization: Bearer ' . $postFields['authToken'];
			unset($postFields['authToken']);
        }
      	if(isset($postFields['JSESSIONID'])){
			$httpheader[] = 'Cookie: JSESSIONID='. $postFields['JSESSIONID'];// . ' #HttpOnly_smart.vaillant.com';
          	unset($postFields['JSESSIONID']);
        }
		$postFields = ($method == "GET" && !empty($postFields)) ? json_encode($postFields, true) : $postFields;
		try{
			$cookie = true;
            $rqst = $this->_makeRequest($url, $method , $postFields, $cookie, $httpheader );
          	  	
          	if(isset($rqst['code']) && $rqst['code'] == 401 && !$retry){
				$this->_loginRequired = true; 
              	if ($this->_login()) {
                	//Make original request
                	$return = $this->_rqstApi($url, $method, $postFields, true);
                	$this->_loginRequired = false;
                    if ($this->_lastCurlInfo['http_code'] != 401) {
                        return $return;
                    } else {
                        return false;
                    }
                }
            }
            
      	
			if(isset($rqst["body"]) && $rqst["body"] != ""){ //&& $rqst["status"] == "ok" 
                log::add('VaillantControl', 'debug', __FUNCTION__ .' rqst_body : '.json_encode($rqst));
				$return = $rqst["body"];
              	if(isset($rqst["meta"]) && !empty($rqst["meta"])) $return["META"]=$rqst["meta"];
              	return $return;
            }elseif(isset($rqst["code"]) && $rqst["code"] == 200){ //&& $rqst["status"] == "ok" 
                $return = isset($rqst["status"]) ? $rqst["status"] : "ok";
              	log::add('VaillantControl', 'debug', __FUNCTION__ .' rqst_200 : '.json_encode($rqst)." ".$return." ".$rqst['status']);
				return $return;
            }else{
                log::add('VaillantControl', 'debug', __FUNCTION__ .' rqst : '.json_encode($rqst));
				return $rqst;
            }
        }
      	catch (Exception $ex) { 
          	$msg=$ex->getMessage();
			if($retry == false && stripos($msg, 'access token') ){//$ex->getCode() == 2 || 1 || 3 
				log::add('VaillantControl', 'warning', __FUNCTION__ .' token error catched : '.$ex->getMessage()." ".$ex->getCode());
          		$login = $this->getlogin(true);
				return $this->_rqstApi($url, $method, $postFields, true);
          	}
          	$error_msg = "Error ".__FUNCTION__."() " .$ex->getCode()." : ". $ex->getMessage();
          	throw new Exception($error_msg);
        }
    }

// ************************************************************************************************************************** //  	
  	private function _makeRequest($_path, $method = 'GET', $params=null, $cookie = false, $headers = array() ){
		$method = strtoupper($method);
      	$opts =  [
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER         => TRUE,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_USERAGENT      => "(X11; Linux) Safari",
          	CURLOPT_SSL_VERIFYPEER => TRUE,
          	CURLINFO_HEADER_OUT	   => TRUE,
          	CURLOPT_FOLLOWLOCATION => TRUE,
        ];
      	$generalHeaders = [
					"Accept" => "application/json",
					"Content-Type" => "application/json",
					//"Connection" => "Keep-Alive",
        ];
      	//override genral header
      	$headers = array_merge($generalHeaders, $headers);
        
          //batch request or conform it:
		if($cookie){
          	$cookiefile = $this->_cookies;
          	$opts[CURLOPT_COOKIEJAR] =  $cookiefile;
          	$opts[CURLOPT_COOKIEFILE] = $cookiefile ;
        }
		if($method == 'GET'){
          	$query = "";
            $opts[CURLOPT_HTTPGET] = TRUE;
          	if (!is_array($params)){
				if( json_decode($params)){
                  	$query = http_build_query(json_decode($params));
                   	$_path .= "?".$query;
              	}elseif( $params != "") {
                  	$query = $params;
                  	$_path .= "?".$query;
              	}
              	//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method Not array query: ".$query);
          	}
			else{//is array
              	$params = self::formatArray($params);
				$query = http_build_query($params);
              	$_path .= "?".$query;
              	//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method array query: ".$query);
              	
          	}
          	$payload=$query;
          	//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method query: ".$query);
        }
      	elseif($method == 'PUT' || $method == 'POST'){//if($method == 'POST')
          	if(is_array($params)){//is array
				$payload = json_encode($params);
				//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method array: ".$payload);
			}
          	elseif(json_decode($params)){
                $payload = $params;
              	//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method json: ".$payload);
            }
          	else{
				$headers['Content-Type'] = "application/x-www-form-urlencoded";
                $payload = $params;
				//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method string: ".$payload);
			}
			$opts[CURLOPT_CUSTOMREQUEST] = $method;
          	//$opts[CURLOPT_POST] = true;
          	$opts[CURLOPT_POSTFIELDS] = $payload;
          	$lenght = strlen($payload);
          	$lenght ? $headers['Content-Length'] = $lenght : "" ;
      	
          
        }
      	else{//if($method == 'POST')
          	if(is_array($params)){//is array
				$payload = json_encode($params);
				//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method array: ".$payload);
			}
          	elseif(json_decode($params)){
                $payload = $params;
              	//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method json: ".$payload);
            }
          	else{
				$headers['Content-Type'] = "application/x-www-form-urlencoded";
                $payload = $params;
				//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method string: ".$payload);
			}
			$opts[CURLOPT_CUSTOMREQUEST] = $method;
          	//$opts[CURLOPT_POST] = true;
          	$opts[CURLOPT_POSTFIELDS] = $payload;
          	$lenght = strlen($payload);
          	$lenght ? $headers['Content-Length'] = $lenght : "" ;
      	
          
        }
      	
      	//log::add('VaillantControl', 'debug', __FUNCTION__ ." $method ".parse_url($_path, PHP_URL_PATH)." ".$payload );
      
      
		foreach($headers as $key=>$value){
            if(!is_numeric($key)){
            	$opts[CURLOPT_HTTPHEADER][] = $key.': '.$value;
            }else{
            	$opts[CURLOPT_HTTPHEADER][] = $value;
            }
		}
        //log::add('VaillantControl', 'debug', __FUNCTION__ ." HTTPHEADER: ".var_export($opts[CURLOPT_HTTPHEADER], true));
      	$opts[CURLOPT_URL] = $_path;
      	$ch = curl_init();
        curl_setopt_array($ch, $opts);
      	//log::add('VaillantControl', 'debug', __FUNCTION__ ." opts: ".var_export($opts, true));
      	
		$result = curl_exec($ch);
      	
        $errno = curl_errno($ch);
        // CURLE_SSL_CACERT || CURLE_SSL_CACERT_BADFILE
        if ($errno == 60 || $errno == 77){
            log::add('VaillantControl', 'error', __FUNCTION__ ." WARNING ! SSL_VERIFICATION has been disabled...\n");
          	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $result = curl_exec($ch);
        }
      	
      	//log::add('VaillantControl', 'debug', __FUNCTION__ .' result: ' . $result);
        $this->_lastCurlInfo = curl_getinfo($ch);
		$http_code = $this->_lastCurlInfo['http_code'];
      	
      
      	if ($result === FALSE){
          	$er_code = curl_errno($ch)."--".curl_error($ch);
          	$er_msg = ' curl Error ('.curl_errno($ch).") : ".curl_error($ch).' at '.parse_url($_path, PHP_URL_PATH);
          	//curl_close($ch);
          	log::add('VaillantControl', 'warning', __FUNCTION__ ." ".$er_msg);
          	$return = [
						'code' => $er_code,
						'msg' => $er_msg,
						'status' => "Nok",
						//'body' => $apiEr_msg,
						'url' => parse_url($_path, PHP_URL_PATH),
            ];
            return $return;
          	////throw new Exception($er_msg);
        }
      	//log::add('VaillantControl', 'debug', __FUNCTION__ .' CurlInfo: '.json_encode($this->_lastCurlInfo));
      	$header_size = $this->_lastCurlInfo['header_size'];//curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      	$request_header = $this->_lastCurlInfo['request_header'];//curl_getinfo($ch,CURLINFO_HEADER_OUT);//need CURLOPT_HEADER
      	//log::add('VaillantControl', 'debug', __FUNCTION__ .' request_header: ' . $request_header);
        
      
      
        //curl_close($ch);
		if( isset($opts[CURLOPT_HEADER]) && $opts[CURLOPT_HEADER]==true ){
          	$rawHeaders = explode("\r\n", substr($result, 0, $header_size));
          	$body = substr($result, $header_size);
          	preg_match('/^HTTP\/1.1 ([0-9]{3,3}) (.*)$/', $rawHeaders[0], $matches);
			$headerCode = $matches[1];
			$headerStatus = strtolower($matches[2]);
		}else{
          	$rawHeaders = explode("\r\n", $request_header);
          	$body = $result;
        }
      	if(substr($http_code, 0, 1) === '2'){
          	$rsp_status = "ok";
            $decode = json_decode($body, TRUE);
            if(!$decode){
                $rsp_body = $body;
              	//log::add('VaillantControl', 'debug', __FUNCTION__ .' !decode: '.$rsp_body);
            } 
          	elseif(isset($decode['body'])){
              	//log::add('VaillantControl', 'error', __FUNCTION__ .' 200 decode && body: '.json_encode($decode, true));
            	if(isset($decode['body']['errors'])){
                  	foreach ($decode['body']['errors'] as $error) {
                      	if(isset($error['code'])){
                          	$errmsg = ( isset($error['command']) ) ? $error['command'] .': '.self::$err200[$error['code']] : self::$err200[$error['code']];
                          	$errors[$error['code']] = $errmsg;
                        }
                    }
                  	$decode['body']['errors'] = $errors;
                } 
				$rsp_body = $decode['body'];
				$rsp_status = isset($decode['status']) ? strtolower($decode['status']) : null;
            }
          	else{
				$rsp_body = $decode;
              	$rsp_status = isset($decode['status']) ? strtolower($decode['status']) : null;
            }
          	if (isset($decode['meta']) && !empty($decode['meta']) ) {
                $this->_lastApiMeta = $decode['meta'];
              	$meta = $decode['meta']; 
              	$return['meta'] = $meta;
            } else {
                $meta = null;
              	$this->_lastApiMeta = false;
            }
            $return['code']= $http_code;
          	$return['body'] = $rsp_body; 
          	$return['status'] = $rsp_status ? $rsp_status : $headerStatus;
          	
          	//$return['headers'] = $rawHeaders;
          	
        }
        else{//http_code != 200
          	//log::add('VaillantControl', 'warning', __FUNCTION__ .'  ! http_code: '.$http_code.' at '.parse_url($_path, PHP_URL_PATH));
				
            $decode = json_decode($body, TRUE);
			if(!$decode){
              	$code= $http_code ? $http_code : $headerCode ;
              	$code= $code ? $code : $headerStatus ; 
              	
				if( $http_code != 200 ){
					$err_msg = " Request error_1 $http_code(".$this->_httpMsg[$http_code].") " .$headerStatus;
                  	$return = [
                        'code' => $http_code,
                        'msg' => $err_msg,
                        'url' => parse_url($_path, PHP_URL_PATH),
                        'status' => 'nok',
                        'body' => isset($rsp_body) ? $rsp_body : '',
                        //'headers' => isset($rawHeaders) ? $rawHeaders : '',
                        //'result' => $result
                    ];
                  	//log::add('VaillantControl', 'warning', __FUNCTION__ ." ".$err_msg);
                  	return $return;
          			//throw new Exception($err_msg);
				}elseif( is_string($body) ){
                  	$err_msg = " Request error_2 $http_code(".$this->_httpMsg[$http_code].") ";
                  	log::add('VaillantControl', 'warning', __FUNCTION__ ." ".$err_msg);
                  	$return = [
                        'code' => $http_code,
                        'status' => 'nok',
                        'msg' => $err_msg,
                        'url' => parse_url($_path, PHP_URL_PATH),
                        'body' => $body
                    ];
                  	return $return;
          			//throw new Exception($err_msg);
                }
              	log::add('VaillantControl', 'warning', __FUNCTION__ .' !decode: '.$code.$err_msg);
              	
            }
          	else{
               	$status = array_keys($decode)[0];
               	//log::add('VaillantControl', 'debug', __FUNCTION__ .' status '.$status);
				$msg = array_values($decode)[0];
              	if($status == "error" || isset($decode["error"])){
                //log::add('VaillantControl', 'debug', __FUNCTION__ .' msg: '.$msg['message']);
               		//$apiEr_msg = ( isset($msg['message']) )?$msg['message'] : isset($decode['error_description']) ? $decode['error_description']: "";
                	if(isset($msg['message'])){
                      $apiEr_msg = $msg['message'];
                    }elseif(isset($decode['error_description'])){
                      $apiEr_msg = $decode['error_description'];
                    }elseif(is_string($decode['error'])){
                      $apiEr_msg = $decode['error'];
                    }else{
                       $apiEr_msg =' unknown';
                    }
                  	$apiEr_code = (isset($msg['code']))  ? $msg['code']: $http_code;
                  	$ermsg = " API_Erreur at ".parse_url($_path, PHP_URL_PATH). " : ".$apiEr_msg ;//(code $apiEr_code) 
                  	log::add('VaillantControl', 'warning', __FUNCTION__ .' '.$ermsg);
                  	$err['error']['code'] = $apiEr_code;
                    $err['error']['message'] = $apiEr_msg;
                    //throw new NAApiErrorType($apiEr_code, $ermsg, $result);
                  	throw new Exception($ermsg);
                  
                }
              	//log::add('VaillantControl', 'debug', __FUNCTION__ .' '.$ermsg);
            }
          	$return = [
						'code' => (isset($apiEr_code)) ? $apiEr_code : $http_code,
						'msg' => $this->_httpMsg[$http_code].' '.((isset($apiEr_msg)) ? $apiEr_msg : ''),
						'status' => strtolower($status),
						'body' => (isset($apiEr_msg)) ? $apiEr_msg : '',
						'url' => parse_url($_path, PHP_URL_PATH),
            ];
        }
      	return $return;
      
      
	}

// ************************************************************************************************************************** //  	
  	private function _login(){

        //log::add('VaillantControl', 'debug','		'.__FUNCTION__ . ' Starting ****************');
		$token_data = $this->_getAuthToken();

        if ($this->_lastCurlInfo['http_code'] != 200 || !isset($token_data['authToken'])) {
          	log::add('VaillantControl', 'debug', '		'.__FUNCTION__ . ' Failled to get token : '.$this->_lastCurlInfo['http_code'] .': '.json_encode($token_data));
            return false;
        }
        //log::add('VaillantControl', 'debug','		'.__FUNCTION__ . ' token : '.$token_data['authToken']);
        $this->_authToken = $token_data['authToken'];
		
        $this->_authenticate();
		if ($this->_lastCurlInfo['http_code'] != 200) {
            return false;
        }
		$ar_token['token_date'] = time();
        $ar_token['authToken'] = $token_data['authToken'];
      	
      	$cookie_content = file_get_contents($this->_cookies);
		preg_match("/JSESSIONID\s*(.*)/", $cookie_content, $match_session);
		$jsessionid = trim(array_pop($match_session));
		if(isset($jsessionid)){
          	$ar_token['JSESSIONID'] = $jsessionid;
      		//log::add('VaillantControl', 'debug','		'.__FUNCTION__ . ' cookie_contents: '. json_encode($ar_token)."--".$cookie_content);
      	}
      	$this->_updateSession($ar_token);
        return true;

    }


// ************************************************************************************************************************** //  	
  	private function _getAuthToken(){
        log::add('VaillantControl', 'debug',__FUNCTION__ . '  Starting ****************');
      	$url = $this->_new_token;
      	$return = $this->_makeRequest($url, 'POST' , [
            'smartphoneId' => $this->_smartphoneId,
            'username'     => $this->_username,
            'password'     => $this->_password
        ], true);
      	return $return['body'];
		
    }

// ************************************************************************************************************************** //  	
  	private function _authenticate(){
        log::add('VaillantControl', 'debug',__FUNCTION__ . '  Starting ****************');
		$url = $this->_authenticate;
      	$return = $this->_makeRequest($url, 'POST' , [
            'smartphoneId' => $this->_smartphoneId,
            'username'     => $this->_username,
            'authToken'    => $this->_authToken
        ], true);
      	log::add('VaillantControl', 'debug',__FUNCTION__ . '   '.json_encode($return));
      	return $return['body'];
    }

// ************************************************************************************************************************** //  	
  	public function getLastHttpCode(){
        return $this->_lastCurlInfo ? $this->_lastCurlInfo['http_code'] : $this->_lastCurlError['http_code'];
    }

// ************************************************************************************************************************** //  	
  	public function __destruct(){
        $this->_ch ? curl_close($this->_ch) : die(false);
    }

// ************************************************************************************************************************** //  	
  	public function getApiMeta(){
        return $this->_lastApiMeta;
    }

// ************************************************************************************************************************** //  	
  	public function makeCustomRequest($command, $method){
        $commandUri = $this->_getUri($command);
      	$url = str_replace("{_serial}", $this->_currentFacility, $commandUri);
        return $this->_makeRequest($url, $method, [], true);
    }

// ************************************************************************************************************************** //  	
  	public function getLastCurlError(){
        return $this->_lastCurlError;
    }

// ************************************************************************************************************************** //  	
  	public function getLastCurlInfo() {
        return $this->_lastCurlInfo;
    }

// ************************************************************************************************************************** //  	
  	protected function sendEvent($type=null, $data){
      	$callabl= is_callable('OnEvent',true);
      	if($callabl){
          call_user_func('OnEvent', $type, $data);
        }
    }
  
// ************************************************************************************************************************** //  	
  	private static function getheadersVal($headers, $keyhead, $val=''){
		$keyleng=strlen($keyhead);
      	$valleng=strlen($val);
      	$result = [];
       	foreach ($headers as $entry){
			if (substr($entry, 0, $keyleng) == $keyhead 
                && substr($entry, $keyleng+2, $valleng) == $val){
				$found=substr($entry, $keyleng+2, $valleng);
              	$return=trim(substr($entry, $keyleng+2));
              	if($val=='' && $return) return array($keyhead => $return);
              	$exp=explode(';', $return);
        		if(is_array($exp) && !empty($exp)){
                  	foreach ($exp as $expvalue){
                      //list($key, $fval) = explode('=', trim($expvalue), 2);
                      list($key, $fval) = array_pad(explode('=', trim($expvalue), 2), -2, null);
                      //ll
                      $result[trim($key, "\x1B\x00..\x1F")]=trim($fval, "\x00..\x1F");
                    }
                }
            break;
            }
        }
    	return $result;
    }

// ************************************************************************************************************************** //  	
  	private static function formatArray($array){
      	$result=[];
        foreach ($array as $key => $value){
			if(is_array($value)){
              	if(is_numeric($key)) $key = json_encode($key);
              	$result[$key] = self::formatArray($value);
            }
          	elseif (is_bool($value) || $value == null){
				$result[$key] = json_encode($value);//urldecode($NameValueParts['value']);
			}else{
              	$result[$key] = $value;
            }
		}
      	return $result;
    }
  
// ************************************************************************************************************************** //  	
  	private function _updateSession($value){
        $cb = $this->_func_cb;
      	$object = $this->_object_cb;
      	$return = false;
      	if($object && $cb){
            if(method_exists($object, $cb)){
              	$return = call_user_func_array([ $object, $cb], [$value]);
            }
        }
        else if($cb && is_callable($cb)){
            $return = call_user_func_array($cb, [$value]);
        }
      	return $return;
    }

// ************************************************************************************************************************** //  	
  	public function setVariable($name, $value){
        $this->conf[$name] = $value;
        return $this;
    }

// ************************************************************************************************************************** //  	
  	public function getVariable($name, $default = NULL){
        return isset($this->conf[$name]) ? $this->conf[$name] : $default;
    }

// ************************************************************************************************************************** //  	
  	public $_httpMsg = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        499 => 'Something went wrong',//499 - Ooops. Something went wrong.
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
	];
  	
}
?>