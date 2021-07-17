<?php

function die_503($text) {
	error_log("HTTP 503 returned to client; reason: $text");
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	die();
}

function die_400() {
	header('HTTP/1.1 400 Bad Request');
	header('Status: 400 Bad Request');
	die();
}

function db_query_single_row($query, $cache_expiration = -1) {
	global $mysqli, $memcached;

	$record = false;
	if($cache_expiration > -1) {
		$cache_key = "torstatus_query_" . sha1($query);
		$record = unserialize($memcached->get($cache_key));
	}
	if(!$record) {
		$result = $mysqli->query($query);
		if(!$result) {
			die_503('Query failed: ' . $mysqli->error);
		}
		$record = $result->fetch_assoc();
		$result->free();

		if($cache_expiration > -1) {
			$memcached->set($cache_key, serialize($record), $cache_expiration);
		}
	}

	return $record;
}

function fetch_mirrors() {
	global $mirrorList;

	// Retrieve the mirror list from the database
	$query = "SELECT mirrors FROM `Mirrors` WHERE id=1";
	$mirrorListRow = db_query_single_row($query, 86400);
	$mirrorList = $mirrorListRow['mirrors'];
}

// Start new session
@session_start() or die_400();

// Include configuration settings
require_once("config.php");

$memcached = new Memcached();
$memcached->addServer('127.0.0.1', 11211);

// Get script start time
$TimeStart = microtime(true);

// Connect to database, select schema
$mysqli = new mysqli($SQL_Server, $SQL_User, $SQL_Pass, $SQL_Catalog);
if($mysqli->connect_error) {
	die_503('Could not connect: ' . $link->connect_error);
}

// Get last update and active table information from database
$query = "select LastUpdate, LastUpdateElapsed, ActiveNetworkStatusTable, ActiveDescriptorTable, ActiveORAddressesTable from Status";
$record = db_query_single_row($query, 60);

$LastUpdate = $record['LastUpdate'];
$LastUpdateElapsed = $record['LastUpdateElapsed'];
$ActiveNetworkStatusTable = $record['ActiveNetworkStatusTable'];
$ActiveDescriptorTable = $record['ActiveDescriptorTable'];
$ActiveORAddressesTable = $record['ActiveORAddressesTable'];

$timestamp = time();
$year = date('Y', $timestamp);
$month = date('n', $timestamp);
$day = date('j', $timestamp);
$hour = date('G', $timestamp);
$minute = date('i', $timestamp);
$second = date('s', $timestamp);
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $mysqli->escape_string($_SERVER['HTTP_USER_AGENT']) : '';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $mysqli->escape_string($_SERVER['REQUEST_URI']) : '';
$session_id = $mysqli->escape_string(session_id());
$ip = isset($_SERVER['REMOTE_ADDR']) ? $mysqli->escape_string($_SERVER['REMOTE_ADDR']) : '';

#$query = "INSERT INTO access_log (`timestamp`, year, month, day, hour, minute, second, user_agent, request_uri, session_id, ip) VALUES (FROM_UNIXTIME($timestamp), '$year', '$month', '$day', '$hour', '$minute', '$second', '$user_agent', '$request_uri', '$session_id', '$ip')";
#$mysqli->query($query);


