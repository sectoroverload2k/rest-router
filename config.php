<?php
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-cache');
header('Pragma: no-cache');

define('DB_HOST','mysqlserver');
define('DB_USERNAME','mysqlusername');
define('DB_PASSWORD','mysqlpassword');
define('DB_NAME','mysqldb');

define('BASE_DIR',dirname(__FILE__));


$session_config = array(
		'host' => DB_HOST,
		'username' => DB_USERNAME,
		'password' => DB_PASSWORD,
		'db' => DB_NAME,
		'table' => 'api_sessions',
		'encrypt' => false,
		'fingerprint' => false,
		'lifetime' => 1440,
		);


require(BASE_DIR.'/includes/functions.php');

require(BASE_DIR.'/includes/class.mysql_wrapper.php');
require(BASE_DIR.'/includes/class.mysql_session_handler.php');

require(BASE_DIR.'/includes/class.database.php');
include(BASE_DIR.'/includes/class.router.php');
include(BASE_DIR.'/includes/class.rest.php');

function pre($v){echo '<pre>';print_r($v);echo '</pre>';}


define('SESSION_TOKEN_NAME','RestRouterApiToken');


$headers = getallheaders();
if(isset($headers[SESSION_TOKEN_NAME])) {
        define('TOKEN', $headers[SESSION_TOKEN_NAME]);
        session_id( $headers[SESSION_TOKEN_NAME] );
        mysql_session_start($session_config);
        session_name(SESSION_TOKEN_NAME);
}

