<?php
require_once('_include.php');

use chetch\Config as Config;
use chetch\Utils as Utils;
use chetch\api\APIMakeRequest as APIMakeRequest;

try{
	$lf = "\n";
	$entry = LogEntry::createInstanceFromID(94);
	$payload = $entry->getRowData();

	$payload['requires_revision'] = 1;

	/*$payload = array();
	$payload["employee_id"] = "88011";
	$payload["state"] = "MOVING";
	$payload["event"] = "SET_ANCHOR";
	$payload["latitude"] = 0.3;
	$payload["longitude"] = -0.3;*/
	$payload["notes"] = "This is just a test entry";
 
	//$req = APIMakeRequest::createDeleteRequest("http://127.0.0.1:8005/api", "entry/1");
	//$req = APIMakeRequest::createGetRequest("http://127.0.0.1:8005/api", "entries");
	$req = APIMakeRequest::createPutRequest("http://127.0.0.1:8005/api", "entry", $payload);
	$data = $req->request();
	
	print_r($data);
	
	echo "DONE sdf";
} catch (Exception $e){
	echo "EXCEPTION: ".$e->getMessage();
}
?>