<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

require_once('common.php');

if(!($output = $memcached->get("torstatus_ip_list_all_csv"))) {
	// Get data from database
	$query = "select IP, INET_ATON(IP) as NIP from $ActiveNetworkStatusTable order by NIP Asc";
	$result = $mysqli->query($query);
	if(!$result) {
		die_503('Query failed: ' . $mysqli->error);
	}

	$output = '';
	while ($record = $result->fetch_assoc()) 
	{
		$output .= "${record['IP']}\n";
	}

	// Close connection
	$result->free();

	$memcached->set("torstatus_ip_list_all_csv", $output, 1800);
}
$mysqli->close();

// Output CSV file to browser
header('Content-Transfer-Encoding: Binary');
header('Content-type: application/force-download');
header('Content-disposition: inline; filename=Tor_ip_list_EXIT.csv');

print($output);

