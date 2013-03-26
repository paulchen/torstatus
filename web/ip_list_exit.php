<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

// Start new session
session_start();

// Include configuration settings
include("config.php");

// Declare and initialize variables
$ActiveNetworkStatusTable = null;

// Connect to database, select schema
$link = mysql_connect($SQL_Server, $SQL_User, $SQL_Pass) or die('Could not connect: ' . mysql_error());
mysql_select_db($SQL_Catalog) or die('Could not open specified database');

// Get active network status table from database
$query = "select ActiveNetworkStatusTable from Status";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$record = mysql_fetch_assoc($result);

$ActiveNetworkStatusTable = $record['ActiveNetworkStatusTable'];

// Get data from database
$query = "select IP, INET_ATON(IP) as NIP from $ActiveNetworkStatusTable where FExit = '1' order by NIP Asc";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// Output CSV file to browser
header('Content-Transfer-Encoding: Binary');
header('Content-type: application/force-download');
header('Content-disposition: inline; filename=Tor_ip_list_EXIT.csv');
while ($record = mysql_fetch_assoc($result)) 
{
	echo $record['IP'] . "\n";
}

// Close connection
mysql_close($link);

?>
