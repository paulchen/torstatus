<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

require_once('common.php');

$Name = null;
$IP = null;
$Hostname = null;
$ORPort = null;
$DirPort = null;
$Fingerprint = null;
$Platform = null;
$LastDescriptorPublished = null;
$Uptime = null;
$Bandwidth_MAX = null;
$Bandwidth_BURST = null;
$Bandwidth_OBSERVED = null;
$OnionKey = null;
$SigningKey = null;
$Contact = null;
$ExitPolicy_DATA_ARRAY = null;
$Family_DATA_ARRAY = null;
$FAuthority = null;
$FBadDirectory = null;
$FBadExit = null;
$FExit = null;
$FFast = null;
$FGuard = null;
$FHibernating = null;
$FNamed = null;
$FStable = null;
$FRunning = null;
$FValid = null;
$FV2Dir = null;
$CountryCode = null;

// Read in submitted variables
if (isset($_GET["FP"]))
{
	$Fingerprint = $_GET["FP"];
}

// Perform variable scrubbing
$Fingerprint = strip_tags($Fingerprint);
if (strlen($Fingerprint) != 40)
{
	$Fingerprint = null;
}

// Populate variables from database
$query = "select $ActiveNetworkStatusTable.Name, $ActiveDescriptorTable.LastDescriptorPublished, $ActiveNetworkStatusTable.IP, $ActiveNetworkStatusTable.Hostname, $ActiveNetworkStatusTable.ORPort, $ActiveNetworkStatusTable.DirPort, $ActiveDescriptorTable.Platform, $ActiveDescriptorTable.Contact, CAST(UNIX_TIMESTAMP() AS SIGNED) - CAST(UNIX_TIMESTAMP($ActiveDescriptorTable.LastDescriptorPublished) AS SIGNED) + $OffsetFromGMT + CAST($ActiveDescriptorTable.Uptime AS SIGNED) as Uptime, $ActiveDescriptorTable.BandwidthMAX, $ActiveDescriptorTable.BandwidthBURST, $ActiveDescriptorTable.BandwidthOBSERVED, $ActiveDescriptorTable.OnionKey, $ActiveDescriptorTable.SigningKey, $ActiveDescriptorTable.WriteHistoryLAST, $ActiveDescriptorTable.WriteHistoryINC, $ActiveDescriptorTable.WriteHistorySERDATA, $ActiveDescriptorTable.ReadHistoryLAST, $ActiveDescriptorTable.ReadHistoryINC, $ActiveDescriptorTable.ReadHistorySERDATA, $ActiveDescriptorTable.ExitPolicySERDATA, $ActiveDescriptorTable.FamilySERDATA, $ActiveNetworkStatusTable.CountryCode, $ActiveDescriptorTable.Hibernating, $ActiveNetworkStatusTable.FAuthority, $ActiveNetworkStatusTable.FBadDirectory, $ActiveNetworkStatusTable.FBadExit, $ActiveNetworkStatusTable.FExit, $ActiveNetworkStatusTable.FFast, $ActiveNetworkStatusTable.FGuard, $ActiveNetworkStatusTable.FNamed, $ActiveNetworkStatusTable.FStable, $ActiveNetworkStatusTable.FRunning, $ActiveNetworkStatusTable.FValid, $ActiveNetworkStatusTable.FV2Dir from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where $ActiveNetworkStatusTable.Fingerprint = '$Fingerprint'";
$record = db_query_single_row($query);

#echo "<!-- \n";
#print_r($record);
#echo "\n -->";
$Name = $record['Name'];
$LastDescriptorPublished = $record['LastDescriptorPublished'];
$IP = $record['IP'];
$Hostname = $record['Hostname'];
$ORPort = $record['ORPort'];
$DirPort = $record['DirPort'];
$Platform = $record['Platform'];
$Contact = $record['Contact'];
$Uptime = $record['Uptime'];
$Bandwidth_MAX = $record['BandwidthMAX'];
$Bandwidth_BURST = $record['BandwidthBURST'];
$Bandwidth_OBSERVED = $record['BandwidthOBSERVED'];
$OnionKey = $record['OnionKey'];
$SigningKey = $record['SigningKey'];
$ExitPolicy_DATA_ARRAY = unserialize($record['ExitPolicySERDATA']);
$Family_DATA_ARRAY = unserialize($record['FamilySERDATA']);
$CountryCode = $record['CountryCode'];
$FAuthority = $record['FAuthority'];
$FBadDirectory = $record['FBadDirectory'];
$FBadExit = $record['FBadExit'];
$FExit = $record['FExit'];
$FFast = $record['FFast'];
$FGuard = $record['FGuard'];
$FHibernating = $record['Hibernating'];
$FNamed = $record['FNamed'];
$FStable = $record['FStable'];
$FRunning = $record['FRunning'];
$FValid = $record['FValid'];
$FV2Dir = $record['FV2Dir'];

// Register necessary variables in session
// TODO get rid of this
if (!isset($_SESSION['WriteHistory_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['WriteHistory_DATA_ARRAY_SERIALIZED'] = $record['WriteHistorySERDATA'];
} 
else
{
	unset($_SESSION['WriteHistory_DATA_ARRAY_SERIALIZED']);
	$_SESSION['WriteHistory_DATA_ARRAY_SERIALIZED'] = $record['WriteHistorySERDATA'];
}

if (!isset($_SESSION['WriteHistory_INC'])) 
{
	$_SESSION['WriteHistory_INC'] = $record['WriteHistoryINC'];
} 
else
{
	unset($_SESSION['WriteHistory_INC']);
	$_SESSION['WriteHistory_INC'] = $record['WriteHistoryINC'];
}
if (!isset($_SESSION['WriteHistory_LAST'])) 
{
	$_SESSION['WriteHistory_LAST'] = $record['WriteHistoryLAST'];
} 
else
{
	unset($_SESSION['WriteHistory_LAST']);
	$_SESSION['WriteHistory_LAST'] = $record['WriteHistoryLAST'];
}

// Do the same for the read history
if (!isset($_SESSION['ReadHistory_DATA_ARRAY_SERIALIZED'])) 
{
	$_SESSION['ReadHistory_DATA_ARRAY_SERIALIZED'] = $record['ReadHistorySERDATA'];
} 
else
{
	unset($_SESSION['ReadHistory_DATA_ARRAY_SERIALIZED']);
	$_SESSION['ReadHistory_DATA_ARRAY_SERIALIZED'] = $record['ReadHistorySERDATA'];
}
if (!isset($_SESSION['ReadHistory_INC'])) 
{
	$_SESSION['ReadHistory_INC'] = $record['ReadHistoryINC'];
} 
else
{
	unset($_SESSION['ReadHistory_INC']);
	$_SESSION['ReadHistory_INC'] = $record['ReadHistoryINC'];
}
if (!isset($_SESSION['ReadHistory_LAST'])) 
{
	$_SESSION['ReadHistory_LAST'] = $record['ReadHistoryLAST'];
} 
else
{
	unset($_SESSION['ReadHistory_LAST']);
	$_SESSION['ReadHistory_LAST'] = $record['ReadHistoryLAST'];
}

// Handle no descriptor available situation
if ($Name == null)
{
	$pageTitle = "Router Detail";
	include("header.php");
	echo "<table width='70%' cellspacing='2' cellpadding='2' border='0' align='center'>\n";
	echo "<tr>\n";
	echo "<td class='TRSC'><br/><b>ERROR -- No Descriptor Available</b><br/><br/></td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</body>\n";
	echo "</html>\n";

	// Close connection
	$mysqli->close();

	exit;
}

$pageTitle = "Router Detail";
include("header.php");

?>

<table width='100%' cellspacing='2' cellpadding='2'>
<tr>
<td>

<table class="displayTable" cellspacing="0" cellpadding="0" width='100%' align='center'>
<tr>
<td class='HRN' colspan='2'>General Information</td>
<td class='HRN' style='border-left-color: #000072; border-left-style: solid; border-left-width: 1px;'>Exit Policy Information</td>
</tr>
<tr>
<td class="TRS" colspan='2' style="vertical-align: top;" rowspan="1">
<table>
<?php

	// Display router name
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Router Name:</b></td>\n";
	echo "<td class='TRSB'>$Name</td>\n";
	echo "</tr>\n";

	// Display router Fingerprint
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Fingerprint:</b></td>\n";
	echo "<td class='TRSB'>" . chunk_split(strtoupper($Fingerprint), 4, " ") . "</td>\n";
	echo "</tr>\n";

	// Display router Contact
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Contact:</b></td>\n";
	echo "<td class='TRSB'>"; if($Contact == null){echo "None Given";} else{$Contact = htmlspecialchars($Contact, ENT_QUOTES); echo "$Contact";} echo "</td>\n";
	echo "</tr>\n";

	// Display router IP
	echo "<tr>\n";
	echo "<td class='TRAR'><b>IP Address:</b></td>\n";
	echo "<td class='TRSB'>$IP</td>\n";
	echo "</tr>\n";

	// Display router Hostname
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Hostname:</b></td>\n";
	echo "<td class='TRSB'>"; if($Hostname == $IP){echo "Unavailable";} else{echo "$Hostname";} echo "</td>\n";
	echo "</tr>\n";

	// Display ORPort
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Onion Router Port:</b></td>\n";
	echo "<td class='TRSB'>$ORPort</td>\n";
	echo "</tr>\n";

	// Display DirPort
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Directory Server Port:</b></td>\n";
	echo "<td class='TRSB'>"; if($DirPort == 0){echo "None";} else{echo "$DirPort";} echo "</td>\n";
	echo "</tr>\n";

	// Display CountryCode
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Country Code:</b></td>\n";
	echo "<td class='TRSB'>"; if($CountryCode == null){echo "Unknown";} else{echo "$CountryCode";} echo "</td>\n";
	echo "</tr>\n";

	// Display Platform
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Platform / Version:</b></td>\n";
	echo "<td class='TRSB'>$Platform</td>\n";
	echo "</tr>\n";

	// Display LastDescriptorPublished
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Last Descriptor Published (GMT):</b></td>\n";
	echo "<td class='TRSB'>$LastDescriptorPublished</td>\n";
	echo "</tr>\n";

	if ($Uptime > -1)
	{
		// Display Current Uptime
		$days = floor($Uptime/86400);
		$Uptime = $Uptime - ($days*86400);
		$hours = floor($Uptime/3600);
		$Uptime = $Uptime - ($hours*3600);
		$minutes = floor($Uptime/60);
		$Uptime = $Uptime - ($minutes*60);
		$seconds = $Uptime;

		echo "<tr>\n";
		echo "<td class='TRAR'><b>Current Uptime:</b></td>\n";
		echo "<td class='TRSB'>$days Day(s), $hours Hour(s), $minutes Minute(s), $seconds Second(s)</td>\n";
		echo "</tr>\n";
	}
	else
	{
		echo "<tr>\n";
		echo "<td class='TRAR'><b>Current Uptime:</b></td>\n";
		echo "<td class='TRSB'>Not Available</td>\n";
		echo "</tr>\n";
	}

	// Display Bandwidth stats
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Bandwidth (Max/Burst/Observed - In Bps):</b></td>\n";
	echo "<td class='TRSB'>$Bandwidth_MAX&nbsp;/&nbsp;$Bandwidth_BURST&nbsp;/&nbsp;$Bandwidth_OBSERVED</td>\n";
	echo "</tr>\n";

	// Display Family info
	echo "<tr>\n";
	echo "<td class='TRAR'><b>Family:</b></td>\n";
	echo "<td class='TRSB'>";
	if ($Family_DATA_ARRAY == null) {echo "No Info Given";}
	else
	{
		for ($i=0 ; $i < count($Family_DATA_ARRAY) ; $i++)
		{
			echo "$Family_DATA_ARRAY[$i]<br/>";
		}
	}
	echo "</td>\n";
	echo "</tr>\n";

?>
</table>

</td>

<td class='TRS' style='padding: 10px; border-left-color: #59990e; border-left-style: solid; border-left-width: 1px; vertical-align: top;'><b>
<?php
	

	for ($i=0 ; $i<count($ExitPolicy_DATA_ARRAY) ; $i++)
	{
		echo "$ExitPolicy_DATA_ARRAY[$i]<br/>\n";
	}

	echo "<br/>\n";
?>
</b>
</td>


</tr>
<tr>
<?php if(0): ?>
<td class='HRN' colspan='2'>Bandwidth</td>
<?php else: ?>
<td class='HRN' colspan='2'></td>
<?php endif; ?>
<td class='HRN' style='border-left-color: #000072; border-left-style: solid; border-left-width: 1px;'>Router Flags</td>
</tr>
<tr>
<?php if(0): ?>
<td class='TRS' style="text-align: center;">
<?php if ($usePerlGraphs == 1) { ?>
<img src="/cgi-bin/perlgraph/plot.pl?plottype=rtw" alt="Write History" /><br/>
<?php } else { ?>
<img src='bandwidth_history_graph.php?MODE=WriteHistory' />
<?php } ?>
</td>
<td class='TRSB' style="text-align: center;">
<?php if ($usePerlGraphs == 1) { ?>
<img src="/cgi-bin/perlgraph/plot.pl?plottype=rtr" alt="Read History" /><br/>
<?php } else { ?>
<img src='bandwidth_history_graph.php?MODE=ReadHistory' />
<?php } ?>
</td>
<?php else: ?>
<td class='TRS' style="text-align: center;"></td>
<td class='TRSB' style="text-align: center;"></td>
<?php endif; ?>
<td class='TRS' style='padding: 10px; border-left-color: #59990e; border-left-style: solid; border-left-width: 1px; vertical-align: top;'>
<table cellspacing='0' cellpadding='0'>
<?php

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Authority:</b></td>\n";
	echo "<td class='F$FAuthority'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Bad Directory:</b></td>\n";
	echo "<td class='F$FBadDirectory'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Bad Exit:</b></td>\n";
	echo "<td class='F$FBadExit'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Exit:</b></td>\n";
	echo "<td class='F$FExit'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Fast:</b></td>\n";
	echo "<td class='F$FFast'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Guard:</b></td>\n";
	echo "<td class='F$FGuard'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Hibernating:</b></td>\n";
	echo "<td class='F$FHibernating'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Named:</b></td>\n";
	echo "<td class='F$FNamed'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Stable:</b></td>\n";
	echo "<td class='F$FStable'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Running:</b></td>\n";
	echo "<td class='F$FRunning'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>Valid:</b></td>\n";
	echo "<td class='F$FValid'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr class='nr'>\n";
	echo "<td class='TRAR'><b>V2Dir:</b></td>\n";
	echo "<td class='F$FV2Dir'>";

	echo "</td>\n";
	echo "</tr>\n";

?>
</table>
</td>
</tr>

<tr>
<td class='HRN' colspan='3'>Router Keys</td>
</tr>
<tr>
<td class='TRS' colspan='3'>
<?php
	
	echo "<br/>\n";
	echo "<b>Onion Key:</b><pre>" . $OnionKey . "</pre>\n";
	echo "<b>Signing Key:</b><pre>" . $SigningKey . "</pre><br/>\n";
?>
</td>
</tr>
</table>

</td>
</tr>
</table>


<br/>

<table width='70%' cellspacing='2' cellpadding='2' border='0' align='center'>
<tr>
<td class='TRC'><?php echo $footerText; ?></td>
</tr>
</table>
</div>
</body>
</html>

<?php

// Close connection
$mysqli->close();

?>
