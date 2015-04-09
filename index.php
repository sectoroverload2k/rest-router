<?php
require(dirname(__FILE__).'/config.php');

$db = new database();

$router = Router::getInstance();

$data = RestUtils::processRequest();

require('routes.php');

$VERSION = strtolower($router->getVersion());
$CONTROLLER = strtolower($router->getController());
$ACTION = strtolower($router->getAction());
$PARAMS = $router->getParams();

error_log('URI: '.$_SERVER['REQUEST_URI']);
error_log('METHOD: '.$data->getMethod());
error_log('VERSION: '.$VERSION);
error_log('CONTROLLER: '.$CONTROLLER);
error_log('ACTION: '.$ACTION);
error_log('PARAMS: '.json_encode($PARAMS));

$controller_file = BASE_DIR.'/controllers/'.$VERSION.'/'.$CONTROLLER.'.php';
if(!file_exists(BASE_DIR.'/controllers/'.$VERSION.'/'.$CONTROLLER.'.php')){
	die(RestUtils::sendResponse(400, json_encode(array('status' => 400, 'message' => RestUtils::getStatusCodeMessage(400).': controller missing')), 'application/json'));
}
require(BASE_DIR.'/controllers/'.$VERSION.'/'.$CONTROLLER.'.php');

$C = new $CONTROLLER;
if(is_array($PARAMS)){
	$PARAMS = array_merge($PARAMS, $data->getRequestVars());
}

$REQUEST_METHOD = $data->getMethod();
$response = null;
if(method_exists($C, $REQUEST_METHOD.'_'.$ACTION)){
	$do = $REQUEST_METHOD.'_'.$ACTION;
	$response = (count($PARAMS) > 0) ? $C->$do($PARAMS) : $C->$do();
} elseif(method_exists($C, $ACTION)) {
	$do = $ACTION;
	$response = (count($PARAMS) > 0) ? $C->$ACTION($PARAMS) : $C->$ACTION();
} else {
	die(RestUtils::sendResponse(404, json_encode(array('status' => 404, 'message' => RestUtils::getStatusCodeMessage(404))), 'application/json'));
}

if($response === false){
	die(RestUtils::sendResponse(400, json_encode(array('status' => 400, 'message' => RestUtils::getStatusCodeMessage(400).': response is false')), 'application/json'));
}

switch($REQUEST_METHOD){
	case 'get':
#		RestUtils::appendJsonStatus($response, 200);
		RestUtils::sendResponse(200, json_encode($response), 'application/json');
		break;
	case 'post':
#		RestUtils::appendJsonStatus($response, 201);
		RestUtils::sendResponse(201, json_encode($response), 'application/json');
		break;

	case 'delete':
		if(is_array($response)){
#			RestUtils::appendJsonStatus($response, 200);
			RestUtils::sendResponse(200, json_encode($response), 'application/json'); 
		} else {
#			RestUtils::appendJsonStatus($response, 204);
			RestUtils::sendResponse(204);
		}
		break;
	case 'put':
		if(is_array($response)) {
#			RestUtils::appendJsonStatus($response, 200);
			RestUtils::sendResponse(200, json_encode($response), 'application/json'); 
		} else {
#			RestUtils::appendJsonStatus($response, 204);
			RestUtils::sendResponse(204);
		}
		break;
}
