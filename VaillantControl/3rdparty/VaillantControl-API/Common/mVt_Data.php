<?php
namespace VaillantControlApi\Common;
//error_reporting(E_ALL);  

class mVt_Data {
  
  	const _PREFIX = 'tli/';
  
    const _BASE = "https://smart.vaillant.com/mobile/api/v4";

    const _BASE_AUTHENTICATE = _BASE ."/account/authentication/v1";
    const _AUTHENTICATE = _BASE_AUTHENTICATE ."/authenticate";
    const _NEW_TOKEN = _BASE_AUTHENTICATE ."/token/new";
    const _LOGOUT = _BASE_AUTHENTICATE ."/logout";

// """Facility details// """;
    const _FACILITIES_LIST = _BASE ."/facilities";
    const _FACILITIES = _FACILITIES_LIST ."/{serial}";
    const _GATEWAY_TYPE = _FACILITIES ."/public/v1/gatewayType";
    const _FACILITIES_DETAILS = _FACILITIES ."/system/v1/details";
    const _FACILITIES_STATUS = _FACILITIES ."/system/v1/status";
    const _FACILITIES_SETTINGS = _FACILITIES ."/storage";
    const _FACILITIES_DEFAULT_SETTINGS = _FACILITIES ."/storage/default";
    const _FACILITIES_INSTALLER_INFO = _FACILITIES ."/system/v1/installerinfo";

// """Rbr (Room by room)// """;
    const _RBR_BASE = _FACILITIES ."/rbr/v1";
    const _RBR_INSTALLATION_STATUS = _RBR_BASE ."/installationStatus";
    const _RBR_UNDERFLOOR_HEATING_STATUS = _RBR_BASE ."/underfloorHeatingStatus";

// """Rooms// """;
    const _ROOMS_LIST = _RBR_BASE ."/rooms";
    const _ROOM = _ROOMS_LIST ."/{id}";
    const _ROOM_CONFIGURATION = _ROOM ."/configuration";
    const _ROOM_QUICK_VETO = _ROOM_CONFIGURATION ."/quickVeto";
    const _ROOM_TIMEPROGRAM = _ROOM ."/timeprogram";
    const _ROOM_OPERATING_MODE = _ROOM_CONFIGURATION ."/operationMode";
    const _ROOM_CHILD_LOCK = _ROOM_CONFIGURATION ."/childLock";
    const _ROOM_NAME = _ROOM_CONFIGURATION ."/name";
    const _ROOM_DEVICE_NAME = _ROOM_CONFIGURATION ."/devices/{sgtin}/name";
    const _ROOM_TEMPERATURE_SETPOINT = _ROOM_CONFIGURATION ."/temperatureSetpoint";

// """Repeaters// """;
    const _REPEATERS_LIST = _RBR_BASE ."/repeaters";
    const _REPEATER_DELETE = _REPEATERS_LIST ."/{sgtin}";
    const _REPEATER_SET_NAME = _REPEATERS_LIST ."/{sgtin}/name";

// """HVAC (heating, ventilation and Air-conditioning)// """;
    const _HVAC = _FACILITIES ."/hvacstate/v1/overview";
    const _HVAC_REQUEST_UPDATE = _FACILITIES ."/hvacstate/v1/hvacMessages/update";

// """EMF (Embedded Metering Function)// """;
    const _LIVE_REPORT = _FACILITIES ."/livereport/v1";
    const _LIVE_REPORT_DEVICE = _LIVE_REPORT ."/devices/{device_id}/reports/{report_id}";
    const _PHOTOVOLTAICS_REPORT = _FACILITIES ."/spine/v1/currentPVMeteringInfo";
    const _EMF_DEVICES = _FACILITIES ."/emf/v1/devices";
    const _EMF_REPORT_DEVICE = _EMF_DEVICES ."/{device_id}";

// """System control// """;
    const _SYSTEM = _FACILITIES ."/systemcontrol/"._PREFIX."v1";
    const _SYSTEM_CONFIGURATION = _SYSTEM ."/configuration";
    const _SYSTEM_STATUS = _SYSTEM ."/status";
    const _SYSTEM_DATETIME = _SYSTEM_STATUS ."/datetime";
    const _SYSTEM_PARAMETERS = _SYSTEM ."/parameters";
    const _SYSTEM_QUICK_MODE = _SYSTEM_CONFIGURATION ."/quickmode";
    const _SYSTEM_HOLIDAY_MODE = _SYSTEM_CONFIGURATION ."/holidaymode";

// """DHW (Domestic Hot Water)// """;
    const _DHWS = _SYSTEM ."/dhw";
    const _DHW = _DHWS ."/{id}";

// """Circulation// """;
    const _CIRCULATION = _DHW ."/circulation";
    const _CIRCULATION_CONFIGURATION = _CIRCULATION ."/configuration";
    const _CIRCULATION_TIMEPROGRAM = _CIRCULATION_CONFIGURATION ."/timeprogram";

// """Hot water// """;
    const _HOT_WATER = _DHW ."/hotwater";
    const _HOT_WATER_CONFIGURATION = _HOT_WATER ."/configuration";
    const _HOT_WATER_TIMEPROGRAM = _HOT_WATER_CONFIGURATION ."/timeprogram";
    const _HOT_WATER_OPERATING_MODE = _HOT_WATER_CONFIGURATION ."/operation_mode";
    const _HOT_WATER_TEMPERATURE_SETPOINT = _HOT_WATER_CONFIGURATION ."/temperature_setpoint";

// """Ventilation// """;
    const _SYSTEM_VENTILATION = _SYSTEM ."/ventilation";
    const _VENTILATION = _SYSTEM ."/ventilation/{id}";
    const _VENTILATION_CONFIGURATION = _VENTILATION ."/fan/configuration";
    const _VENTILATION_TIMEPROGRAM = _VENTILATION_CONFIGURATION ."/timeprogram";
    const _VENTILATION_DAY_LEVEL = _VENTILATION_CONFIGURATION ."/day_level";
    const _VENTILATION_NIGHT_LEVEL = _VENTILATION_CONFIGURATION ."/night_level";
    const _VENTILATION_OPERATING_MODE = _VENTILATION_CONFIGURATION ."/operation_mode";

// """Zones// """;
    const _ZONES_LIST = _SYSTEM ."/zones";
    const _ZONE = _ZONES_LIST ."/{id}";
    const _ZONE_CONFIGURATION = _ZONE ."/configuration";
    const _ZONE_NAME = _ZONE_CONFIGURATION ."/name";
    const _ZONE_QUICK_VETO = _ZONE_CONFIGURATION ."/quick_veto";

// """Zone heating// """;
    const _ZONE_HEATING_CONFIGURATION = _ZONE ."/heating/configuration";
    const _ZONE_HEATING_TIMEPROGRAM = _ZONE ."/heating/timeprogram";
    const _ZONE_HEATING_MODE = _ZONE_HEATING_CONFIGURATION ."/mode";
    const _ZONE_HEATING_SETPOINT_TEMPERATURE = _ZONE_HEATING_CONFIGURATION ."/setpoint_temperature";
    const _ZONE_HEATING_SETBACK_TEMPERATURE = _ZONE_HEATING_CONFIGURATION ."/setback_temperature";

// """Zone cooling// """;
    const _ZONE_COOLING_CONFIGURATION = _ZONE ."/cooling/configuration";
    const _ZONE_COOLING_TIMEPROGRAM = _ZONE ."/cooling/timeprogram";
    const _ZONE_COOLING_MODE = _ZONE_COOLING_CONFIGURATION ."/mode";
    const _ZONE_COOLING_SETPOINT_TEMPERATURE = _ZONE_COOLING_CONFIGURATION ."/setpoint_temperature";
    const _ZONE_COOLING_MANUAL_SETPOINT_TEMPERATURE = _ZONE_COOLING_CONFIGURATION ."/manual_mode_cooling_temperature_setpoint";

}
?>