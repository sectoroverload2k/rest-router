<?php
# for web based with gui
use RestRouter\WebRouter as Router;
# for rest based without gui
#use RestRouter\Router;

use RestRouter\RestUtils;
use RestRouter\Exceptions\NotImplementedException;

$router = Router::getInstance();
$router->setDefaultController('index');

$data = RestUtils::processRequest();

require('routes.php');

$CONTROLLER = 'c_'.strtolower($router->getController());
$ACTION = strtolower($router->getAction());
$PARAMS = $router->getParams() ?? [];

$controller_file = 'controllers/'.$CONTROLLER.'.php';
if(!file_exists('controllers/'.$CONTROLLER.'.php')) {
    die(new NotImplementedException());
}
require('controllers/'.$CONTROLLER.'.php');

$C = new $CONTROLLER();
if(is_array($PARAMS)) {
    $PARAMS = array_merge($PARAMS, $data->getRequestVars()) ?? [];
}
$REQUEST_METHOD = $data->getMethod();
$response = null;
// check get_action
if(method_exists($C, $REQUEST_METHOD.'_'.$ACTION)) {
    $do = $REQUEST_METHOD.'_'.$ACTION;
    $response = (count($PARAMS) > 0) ? $C->$do($PARAMS) : $C->$do();

    // check action (without get)
} elseif(method_exists($C, $ACTION)) {
    $response = (count($PARAMS) > 0) ? $C->$ACTION($PARAMS) : $C->$ACTION();
}
echo $response;
