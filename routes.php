<?php
switch($data->getMethod()){
	case 'get':
		break;

	case 'post':

		break;

	case 'delete':

		break;
	case 'put':

		break;
}

$urlparts = explode('/',$_SERVER['REQUEST_URI'],3);
if(count($urlparts) < 3){
	die(RestUtils::sendResponse(400, json_encode(array('status' => 400, 'message' => 'Bad Request: less than 3 parts')),'application/json'));
}

define('API_VERSION', $urlparts[1]);

$router->init();

