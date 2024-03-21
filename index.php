<?php
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
require_once('globals.php');
require_once('config.php');
require_once(S_CONF_DIR . 'successClass.php');
require_once(S_CONF_DIR . 'errorClass.php');
$response = '';
try {
	if(!array_key_exists(1, $argv)) throw new Err("No parameter passed");
	parse_str($argv[1], $args);
	$request = $args['rq'];
	$service = $args['sb'] . 'Service';
	if (!file_exists(S_SERVICES_DIR . $service . '.php')) throw new Err('Request Unknown');
	require_once(S_LIBS_DIR . 'dbclass.php');
	require_once(S_CONF_DIR . 'service.php');
	require_once(S_SERVICES_DIR . $service . '.php');
	$srv = new $service();
	if (!method_exists($srv, $request)) throw new Err('Request Method Not Found');
	$r = $srv->$request();
	throw new Success($r);
} catch (Err $e) {
	$response = "Error : ".$e->getMessage();
} catch (Success $r) {
	$response = "Success : ".$r->getVarMessage();
} catch (Exception $e) {
	$response = "Error : ".$e->getMessage();
} finally {
	$r = $response;
	ob_clean();
	header('Content-Type: text/plain');
	echo "\r\n".$r."\r\n";
	ob_end_flush();
}
