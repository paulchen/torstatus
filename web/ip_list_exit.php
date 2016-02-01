<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

require_once('common.php');

// Get data from database
$query = "select IP, INET_ATON(IP) as NIP from $ActiveNetworkStatusTable where FExit = '1' order by NIP Asc";
$result = $mysqli->query($query);
if(!$result) {
	die_503('Query failed: ' . $mysqli->error);
}

// Output CSV file to browser
header('Content-Transfer-Encoding: Binary');
header('Content-type: application/force-download');
header('Content-disposition: inline; filename=Tor_ip_list_EXIT.csv');
while ($record = $result->fetch_assoc()) 
{
	echo $record['IP'] . "\n";
}

// Close connection
$result->free();
$mysqli->close();

