<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

require_once('common.php');

$RouterCount = 0;

$CountryCode_DATA_ARRAY = null;
$CountryCode_LABEL_ARRAY = null;
$CountryCode_Title = 'Number of Routers by Country Code';
$CountryCode_Legend = null;

$CountryCodeExit_DATA_ARRAY = null;
$CountryCodeExit_LABEL_ARRAY = null;
$CountryCodeExit_Title = 'Number of Exit Routers by Country Code';
$CountryCodeExit_Legend = null;

$Uptime_DATA_ARRAY = null;
$Uptime_LABEL_ARRAY = null;
$Uptime_Title = 'Number of Routers by Time Running (Weeks)';
$Uptime_Legend = null;

$Bandwidth_DATA_ARRAY = null;
$Bandwidth_LABEL_ARRAY = array('0-10','11-20','21-50','51-100','101-500','501-1000','1001-2000','2001-3000','3001-5000','5001+');
$Bandwidth_Bucket_0_10 = 0;
$Bandwidth_Bucket_11_20 = 0;
$Bandwidth_Bucket_21_50 = 0;
$Bandwidth_Bucket_51_100 = 0;
$Bandwidth_Bucket_101_500 = 0;
$Bandwidth_Bucket_501_1000 = 0;
$Bandwidth_Bucket_1001_2000 = 0;
$Bandwidth_Bucket_2001_3000 = 0;
$Bandwidth_Bucket_3001_5000 = 0;
$Bandwidth_Bucket_5001plus = 0;
$Bandwidth_Title = 'Number of Routers by Observed Bandwidth (KB/s)';
$Bandwidth_Legend = null;

$Platform_DATA_ARRAY = null;
$Platform_LABEL_ARRAY = array('Unknown','FreeBSD','Linux','Macintosh','NetBSD','OpenBSD','SunOS','Windows');
$Platform_Title = 'Number of Routers by Platform';
$Platform_Legend = null;

$Summary_DATA_ARRAY = null;
$Summary_LABEL_ARRAY = array('Total','Authority','BadDirectory','BadExit','Exit','Fast','Guard','Hibernating','Named','Stable','Running','Valid','V2Dir','Dir. Mirror');
$Summary_Title = 'Aggregate Summary -- Number of Routers Matching Specified Criteria';
$Summary_Legend = null;

$count = 0;

// Get total number of routers from database
$query = "select count(*) as Count from $ActiveNetworkStatusTable";
$record = db_query_single_row($query);

$RouterCount = $record['Count'];

// Perform CountryCode aggregate query
$query = "select CountryCode, count(CountryCode) as Count from $ActiveNetworkStatusTable group by CountryCode";
$result = $mysqli->query($query);
if(!$result) {
	die_503('Query failed: ' . $mysqli->error);
}

while ($record = $result->fetch_assoc()) {
	$CountryCode_LABEL_ARRAY[$count] = $record['CountryCode'];
	$CountryCode_DATA_ARRAY[$count] = $record['Count'];

	if ($CountryCode_LABEL_ARRAY[$count] == null)
	{
		$CountryCode_LABEL_ARRAY[$count] = 'N/A';
	}
	
	$count++;
}
$result->free();

// The label array

// Register CountryCode variables in session
if (!isset($_SESSION['CCGraph_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['CCGraph_DATA_ARRAY_SERIALIZED'] = serialize($CountryCode_DATA_ARRAY);
} 
else
{
	unset($_SESSION['CCGraph_DATA_ARRAY_SERIALIZED']);
	$_SESSION['CCGraph_DATA_ARRAY_SERIALIZED'] = serialize($CountryCode_DATA_ARRAY);
}

if (!isset($_SESSION['CCGraph_LABEL_ARRAY_SERIALIZED'])) 
{
	$_SESSION['CCGraph_LABEL_ARRAY_SERIALIZED'] = serialize($CountryCode_LABEL_ARRAY);
} 
else
{
	unset($_SESSION['CCGraph_LABEL_ARRAY_SERIALIZED']);
	$_SESSION['CCGraph_LABEL_ARRAY_SERIALIZED'] = serialize($CountryCode_LABEL_ARRAY);
}

if (!isset($_SESSION['CCGraph_Title'])) 
{
	$_SESSION['CCGraph_Title'] = $CountryCode_Title;
} 
else
{
	unset($_SESSION['CCGraph_Title']);
	$_SESSION['CCGraph_Title'] = $CountryCode_Title;
}

if (!isset($_SESSION['CCGraph_Legend'])) 
{
	$_SESSION['CCGraph_Legend'] = $CountryCode_Legend;
} 
else
{
	unset($_SESSION['CCGraph_Legend']);
	$_SESSION['CCGraph_Legend'] = $CountryCode_Legend;
}

// Reset counter for next use
$count = 0;

// Perform CountryCodeExit aggregate query
$query = "select CountryCode, count(CountryCode) as Count from $ActiveNetworkStatusTable where FExit = '1' group by CountryCode";
$result = $mysqli->query($query);
if(!$result) {
	die_503('Query failed: ' . $mysqli->error);
}

while ($record = $result->fetch_assoc()) {
	$CountryCodeExit_LABEL_ARRAY[$count] = $record['CountryCode'];
	$CountryCodeExit_DATA_ARRAY[$count] = $record['Count'];

	if ($CountryCodeExit_LABEL_ARRAY[$count] == null)
	{
		$CountryCodeExit_LABEL_ARRAY[$count] = 'N/A';
	}
	
	$count++;
}
$result->free();

// Register CountryCodeExit variables in session
if (!isset($_SESSION['CCExitGraph_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['CCExitGraph_DATA_ARRAY_SERIALIZED'] = serialize($CountryCodeExit_DATA_ARRAY);
} 
else
{
	unset($_SESSION['CCExitGraph_DATA_ARRAY_SERIALIZED']);
	$_SESSION['CCExitGraph_DATA_ARRAY_SERIALIZED'] = serialize($CountryCodeExit_DATA_ARRAY);
}

if (!isset($_SESSION['CCExitGraph_LABEL_ARRAY_SERIALIZED'])) 
{
	$_SESSION['CCExitGraph_LABEL_ARRAY_SERIALIZED'] = serialize($CountryCodeExit_LABEL_ARRAY);
} 
else
{
	unset($_SESSION['CCExitGraph_LABEL_ARRAY_SERIALIZED']);
	$_SESSION['CCExitGraph_LABEL_ARRAY_SERIALIZED'] = serialize($CountryCodeExit_LABEL_ARRAY);
}

if (!isset($_SESSION['CCExitGraph_Title'])) 
{
	$_SESSION['CCExitGraph_Title'] = $CountryCodeExit_Title;
} 
else
{
	unset($_SESSION['CCExitGraph_Title']);
	$_SESSION['CCExitGraph_Title'] = $CountryCodeExit_Title;
}

if (!isset($_SESSION['CCExitGraph_Legend'])) 
{
	$_SESSION['CCExitGraph_Legend'] = $CountryCodeExit_Legend;
} 
else
{
	unset($_SESSION['CCExitGraph_Legend']);
	$_SESSION['CCExitGraph_Legend'] = $CountryCodeExit_Legend;
}

// Reset counter for next use
$count = 0;

// Perform Uptime aggregate query
$query = "select floor((CAST(((UNIX_TIMESTAMP() - (UNIX_TIMESTAMP($ActiveDescriptorTable.LastDescriptorPublished) + $OffsetFromGMT)) + $ActiveDescriptorTable.Uptime) AS SIGNED) / 86400) / 7) as WeeksRunning, count(floor((CAST(((UNIX_TIMESTAMP() - (UNIX_TIMESTAMP($ActiveDescriptorTable.LastDescriptorPublished) + $OffsetFromGMT)) + $ActiveDescriptorTable.Uptime) AS SIGNED) / 86400) / 7)) as Count from $ActiveDescriptorTable inner join $ActiveNetworkStatusTable on $ActiveDescriptorTable.Fingerprint = $ActiveNetworkStatusTable.Fingerprint group by WeeksRunning";
$result = $mysqli->query($query);
if(!$result) {
	die_503('Query failed: ' . $mysqli->error);
}

while ($record = $result->fetch_assoc()) {
	if ($record['WeeksRunning'] > -1)
	{
		$Uptime_LABEL_ARRAY[$count] = $record['WeeksRunning'];
		$Uptime_DATA_ARRAY[$count] = $record['Count'];
		
		$count++;
	}
}
$result->free();

// Register Uptime variables in session
if (!isset($_SESSION['UptimeGraph_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['UptimeGraph_DATA_ARRAY_SERIALIZED'] = serialize($Uptime_DATA_ARRAY);
} 
else
{
	unset($_SESSION['UptimeGraph_DATA_ARRAY_SERIALIZED']);
	$_SESSION['UptimeGraph_DATA_ARRAY_SERIALIZED'] = serialize($Uptime_DATA_ARRAY);
}

if (!isset($_SESSION['UptimeGraph_LABEL_ARRAY_SERIALIZED'])) 
{
	$_SESSION['UptimeGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Uptime_LABEL_ARRAY);
} 
else
{
	unset($_SESSION['UptimeGraph_LABEL_ARRAY_SERIALIZED']);
	$_SESSION['UptimeGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Uptime_LABEL_ARRAY);
}

if (!isset($_SESSION['UptimeGraph_Title'])) 
{
	$_SESSION['UptimeGraph_Title'] = $Uptime_Title;
} 
else
{
	unset($_SESSION['UptimeGraph_Title']);
	$_SESSION['UptimeGraph_Title'] = $Uptime_Title;
}

if (!isset($_SESSION['UptimeGraph_Legend'])) 
{
	$_SESSION['UptimeGraph_Legend'] = $Uptime_Legend;
} 
else
{
	unset($_SESSION['UptimeGraph_Legend']);
	$_SESSION['UptimeGraph_Legend'] = $Uptime_Legend;
}

// Reset counter for next use
$count = 0;

// Perform Bandwidth aggregate query
$query = "select floor($ActiveDescriptorTable.BandwidthOBSERVED / 1024) as Bandwidth, count(floor($ActiveDescriptorTable.BandwidthOBSERVED / 1024)) as Number from $ActiveDescriptorTable inner join $ActiveNetworkStatusTable on $ActiveDescriptorTable.Fingerprint = $ActiveNetworkStatusTable.Fingerprint group by Bandwidth";
$result = $mysqli->query($query);
if(!$result) {
	die_503('Query failed: ' . $mysqli->error);
}

while ($record = $result->fetch_assoc()) {
	if(($record['Bandwidth'] > -1) && ($record['Bandwidth'] < 11))
	{
		$Bandwidth_Bucket_0_10 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 10) && ($record['Bandwidth'] < 21))
	{
		$Bandwidth_Bucket_11_20 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 20) && ($record['Bandwidth'] < 51))
	{
		$Bandwidth_Bucket_21_50 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 50) && ($record['Bandwidth'] < 101))
	{
		$Bandwidth_Bucket_51_100 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 100) && ($record['Bandwidth'] < 501))
	{
		$Bandwidth_Bucket_101_500 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 500) && ($record['Bandwidth'] < 1001))
	{
		$Bandwidth_Bucket_501_1000 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 1000) && ($record['Bandwidth'] < 2001))
	{
		$Bandwidth_Bucket_1001_2000 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 2000) && ($record['Bandwidth'] < 3001))
	{
		$Bandwidth_Bucket_2001_3000 += $record['Number'];
	}
	else if(($record['Bandwidth'] > 3000) && ($record['Bandwidth'] < 5001))
	{
		$Bandwidth_Bucket_3001_5000 += $record['Number'];
	}
	else if($record['Bandwidth'] > 5000)
	{
		$Bandwidth_Bucket_5001plus += $record['Number'];
	}
}
$result->free();

$Bandwidth_DATA_ARRAY = array($Bandwidth_Bucket_0_10,$Bandwidth_Bucket_11_20,$Bandwidth_Bucket_21_50,$Bandwidth_Bucket_51_100,$Bandwidth_Bucket_101_500,$Bandwidth_Bucket_501_1000,$Bandwidth_Bucket_1001_2000,$Bandwidth_Bucket_2001_3000,$Bandwidth_Bucket_3001_5000,$Bandwidth_Bucket_5001plus);

// Register Bandwidth variables in session
if (!isset($_SESSION['BWGraph_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['BWGraph_DATA_ARRAY_SERIALIZED'] = serialize($Bandwidth_DATA_ARRAY);
} 
else
{
	unset($_SESSION['BWGraph_DATA_ARRAY_SERIALIZED']);
	$_SESSION['BWGraph_DATA_ARRAY_SERIALIZED'] = serialize($Bandwidth_DATA_ARRAY);
}

if (!isset($_SESSION['BWGraph_LABEL_ARRAY_SERIALIZED'])) 
{
	$_SESSION['BWGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Bandwidth_LABEL_ARRAY);
} 
else
{
	unset($_SESSION['BWGraph_LABEL_ARRAY_SERIALIZED']);
	$_SESSION['BWGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Bandwidth_LABEL_ARRAY);
}

if (!isset($_SESSION['BWGraph_Title'])) 
{
	$_SESSION['BWGraph_Title'] = $Bandwidth_Title;
} 
else
{
	unset($_SESSION['BWGraph_Title']);
	$_SESSION['BWGraph_Title'] = $Bandwidth_Title;
}

if (!isset($_SESSION['BWGraph_Legend'])) 
{
	$_SESSION['BWGraph_Legend'] = $Bandwidth_Legend;
} 
else
{
	unset($_SESSION['BWGraph_Legend']);
	$_SESSION['BWGraph_Legend'] = $Bandwidth_Legend;
}

// Perform Platform aggregate query
$query = "select count(*) as Count from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Platform like '%freebsd%'";
$record = db_query_single_row($query);
$Platform_DATA_ARRAY[1] = $record['Count'];
$count += $Platform_DATA_ARRAY[1];

$query = "select count(*) as Count from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Platform like '%linux%'";
$record = db_query_single_row($query);
$Platform_DATA_ARRAY[2] = $record['Count'];
$count += $Platform_DATA_ARRAY[2];

$query = "select count(*) as Count from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Platform like '%macintosh%' or Platform like '%darwin%'";
$record = db_query_single_row($query);
$Platform_DATA_ARRAY[3] = $record['Count'];
$count += $Platform_DATA_ARRAY[3];

$query = "select count(*) as Count from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Platform like '%netbsd%'";
$record = db_query_single_row($query);
$Platform_DATA_ARRAY[4] = $record['Count'];
$count += $Platform_DATA_ARRAY[4];

$query = "select count(*) as Count from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Platform like '%openbsd%'";
$record = db_query_single_row($query);
$Platform_DATA_ARRAY[5] = $record['Count'];
$count += $Platform_DATA_ARRAY[5];

$query = "select count(*) as Count from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Platform like '%sunos%'";
$record = db_query_single_row($query);
$Platform_DATA_ARRAY[6] = $record['Count'];
$count += $Platform_DATA_ARRAY[6];

$query = "select count(*) as Count from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Platform like '%windows%'";
$record = db_query_single_row($query);
$Platform_DATA_ARRAY[7] = $record['Count'];
$count += $Platform_DATA_ARRAY[7];

$Platform_DATA_ARRAY[0] = ($RouterCount - $count);

// Register Platform variables in session
if (!isset($_SESSION['PlatformGraph_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['PlatformGraph_DATA_ARRAY_SERIALIZED'] = serialize($Platform_DATA_ARRAY);
} 
else
{
	unset($_SESSION['PlatformGraph_DATA_ARRAY_SERIALIZED']);
	$_SESSION['PlatformGraph_DATA_ARRAY_SERIALIZED'] = serialize($Platform_DATA_ARRAY);
}

if (!isset($_SESSION['PlatformGraph_LABEL_ARRAY_SERIALIZED'])) 
{
	$_SESSION['PlatformGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Platform_LABEL_ARRAY);
} 
else
{
	unset($_SESSION['PlatformGraph_LABEL_ARRAY_SERIALIZED']);
	$_SESSION['PlatformGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Platform_LABEL_ARRAY);
}

if (!isset($_SESSION['PlatformGraph_Title'])) 
{
	$_SESSION['PlatformGraph_Title'] = $Platform_Title;
} 
else
{
	unset($_SESSION['PlatformGraph_Title']);
	$_SESSION['PlatformGraph_Title'] = $Platform_Title;
}

if (!isset($_SESSION['PlatformGraph_Legend'])) 
{
	$_SESSION['PlatformGraph_Legend'] = $Platform_Legend;
} 
else
{
	unset($_SESSION['PlatformGraph_Legend']);
	$_SESSION['PlatformGraph_Legend'] = $Platform_Legend;
}

// Reset counter for next use
$count = 0;

// Perform Summary aggregate query
$query = "select
	(select count(*) from $ActiveNetworkStatusTable) as 'Total',
	(select count(*) from $ActiveNetworkStatusTable where FAuthority = '1') as 'Authority',
	(select count(*) from $ActiveNetworkStatusTable where FBadDirectory = '1') as 'BadDirectory',
	(select count(*) from $ActiveNetworkStatusTable where FBadExit = '1') as 'BadExit',
	(select count(*) from $ActiveNetworkStatusTable where FExit = '1') as 'Exit',
	(select count(*) from $ActiveNetworkStatusTable where FFast = '1') as 'Fast',
	(select count(*) from $ActiveNetworkStatusTable where FGuard = '1') as 'Guard',
	(select count(*) from $ActiveDescriptorTable inner join $ActiveNetworkStatusTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where Hibernating = '1') as 'Hibernating',
	(select count(*) from $ActiveNetworkStatusTable where FNamed = '1') as 'Named',
	(select count(*) from $ActiveNetworkStatusTable where FStable = '1') as 'Stable',
	(select count(*) from $ActiveNetworkStatusTable where FRunning = '1') as 'Running',
	(select count(*) from $ActiveNetworkStatusTable where FValid = '1') as 'Valid',
	(select count(*) from $ActiveNetworkStatusTable where FV2Dir = '1') as 'V2Dir',
	(select count(*) from $ActiveNetworkStatusTable where DirPort > 0) as 'DirMirror'";

$record = db_query_single_row($query);

$Summary_DATA_ARRAY[0] = $record['Total'];
$Summary_DATA_ARRAY[1] = $record['Authority'];
$Summary_DATA_ARRAY[2] = $record['BadDirectory'];
$Summary_DATA_ARRAY[3] = $record['BadExit'];
$Summary_DATA_ARRAY[4] = $record['Exit'];
$Summary_DATA_ARRAY[5] = $record['Fast'];
$Summary_DATA_ARRAY[6] = $record['Guard'];
$Summary_DATA_ARRAY[7] = $record['Hibernating'];
$Summary_DATA_ARRAY[8] = $record['Named'];
$Summary_DATA_ARRAY[9] = $record['Stable'];
$Summary_DATA_ARRAY[10] = $record['Running'];
$Summary_DATA_ARRAY[11] = $record['Valid'];
$Summary_DATA_ARRAY[12] = $record['V2Dir'];
$Summary_DATA_ARRAY[13] = $record['DirMirror'];

// Register Summary variables in session
if (!isset($_SESSION['SummaryGraph_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['SummaryGraph_DATA_ARRAY_SERIALIZED'] = serialize($Summary_DATA_ARRAY);
} 
else
{
	unset($_SESSION['SummaryGraph_DATA_ARRAY_SERIALIZED']);
	$_SESSION['SummaryGraph_DATA_ARRAY_SERIALIZED'] = serialize($Summary_DATA_ARRAY);
}

if (!isset($_SESSION['SummaryGraph_LABEL_ARRAY_SERIALIZED'])) 
{
	$_SESSION['SummaryGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Summary_LABEL_ARRAY);
} 
else
{
	unset($_SESSION['SummaryGraph_LABEL_ARRAY_SERIALIZED']);
	$_SESSION['SummaryGraph_LABEL_ARRAY_SERIALIZED'] = serialize($Summary_LABEL_ARRAY);
}

if (!isset($_SESSION['SummaryGraph_Title'])) 
{
	$_SESSION['SummaryGraph_Title'] = $Summary_Title;
} 
else
{
	unset($_SESSION['SummaryGraph_Title']);
	$_SESSION['SummaryGraph_Title'] = $Summary_Title;
}

if (!isset($_SESSION['SummaryGraph_Legend'])) 
{
	$_SESSION['SummaryGraph_Legend'] = $Summary_Legend;
} 
else
{
	unset($_SESSION['SummaryGraph_Legend']);
	$_SESSION['SummaryGraph_Legend'] = $Summary_Legend;
}

$pageTitle = "Network Detail";
include("header.php");

?>


<table width='100%' cellspacing='2' cellpadding='2'>
<tr>
<td>

<table class="displayTable" width='100%' cellspacing='0' cellpadding='0' align='center'>
<tr>
<td class='HRN' colspan='2'>Number of Routers by Country Code</td>
</tr>
<tr>
<td class='TRSBcenter' colspan='2' align='center'>
<img src="/cc_graph.php" alt="Number of Routers by Country Code" />
</td>
</tr>
<tr>
<td class='HRN' colspan='2'>Number of Exit Routers by Country Code</td>
</tr>
<tr>
<td class='TRSBcenter' colspan='2' align='center'>
<img src="/cc_exit_graph.php" alt="Number of Exit Routers by Country Code" />
</td>
</tr>
<tr>
<td class='HRN' colspan='2'>Number of Routers by Uptime</td>
</tr>
<tr>
<td class='TRSBcenter' colspan='2' align='center'>
<img src="/uptime_graph.php" alt="Number of Routeres by Uptime" />
</td>
</tr>
<tr>
<td class='HRN'>Number of Routers by Observed Bandwidth</td>
<td class='HRN' style='border-left-color: #000072; border-left-style: solid; border-left-width: 1px;'>Number of Routers by Platform</td>
</tr>
<tr>
<td class='TRSBcenter' colspan='1' align='center'>
<img src="/bandwidth_graph.php" alt="Number of Routers by Bandwidth" />
</td>
<td class='TRSBcenter' colspan='1' align='center' style='padding: 10px; border-left-color: #59990e; border-left-style: solid; border-left-width: 1px;'>
<img src="/platform_graph.php" alt="Number of Routers by Platform" />
</td>
</tr>
<tr>
<td class='HRN' colspan='2'>Number of Routers Matching Specified Criteria</td>
</tr>

<tr>
<td class='TRSBcenter' colspan='2' align='center'>
<img src="/summary_graph.php" alt="Summary Graph" />


</td>
</tr>
</table>

</td></tr></table>

<br/>

<table width='70%' cellspacing='2' cellpadding='2' border='0' align='center'>
<tr>
<td class='TRC'><?php echo $footerText; ?></td>
</tr>
</table>
</body>
</html>

<?php

// Close connection
$mysqli->close();

?>
