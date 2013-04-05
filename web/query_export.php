<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

// Start new session
session_start();

// Include configuration settings
include("config.php");

// Declare and initialize variables
$ActiveNetworkStatusTable = null;
$ActiveDescriptorTable = null;

$HeaderRowString = "";

$ColumnList_ACTIVE = null;
$SR = null;
$SO = null;
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
$CSField = null;
$CSMod = null;
$CSInput = null;

// Function Declarations
function GenerateHeaderRow()
{
	global 	
			$HeaderRowString, 
			$ColumnList_ACTIVE;

	$HeaderRowString .= "Router Name";

	foreach($ColumnList_ACTIVE as $value)
	{
		switch ($value)
		{
			case "Fingerprint":
   			$HeaderRowString .= ",Fingerprint";
   			break;

			case "CountryCode":
			$HeaderRowString .= ",Country Code";
			break;

			case "Bandwidth":
			$HeaderRowString .= ",Bandwidth (KB/s)";
			break;

			case "Uptime":
			$HeaderRowString .= ",Uptime (Days)";
			break;

			case "LastDescriptorPublished":
			$HeaderRowString .= ",Last Descriptor Published (GMT)";
			break;

			case "Hostname":
			$HeaderRowString .= ",Hostname";
			break;

			case "IP":
			$HeaderRowString .= ",IP Address";
			break;

			case "ORPort":
			$HeaderRowString .= ",ORPort";
			break;

			case "DirPort":
			$HeaderRowString .= ",DirPort";
			break;

			case "Platform":
			$HeaderRowString .= ",Platform";
			break;

			case "Contact":
			$HeaderRowString .= ",Contact";
			break;

			case "Authority":
			$HeaderRowString .= ",Flag - Authority";
			break;

			case "BadDir":
			$HeaderRowString .= ",Flag - Bad Directory";
			break;

			case "BadExit":
			$HeaderRowString .= ",Flag - Bad Exit";
			break;

			case "Exit":
			$HeaderRowString .= ",Flag - Exit";
			break;

			case "Fast":
			$HeaderRowString .= ",Flag - Fast";
			break;

			case "Guard":
			$HeaderRowString .= ",Flag - Guard";
			break;

			case "Hibernating":
			$HeaderRowString .= ",Flag - Hibernating";
			break;

			case "Named":
			$HeaderRowString .= ",Flag - Named";
			break;

			case "Stable":
			$HeaderRowString .= ",Flag - Stable";
			break;

			case "Running":
			$HeaderRowString .= ",Flag - Running";
			break;

			case "Valid":
			$HeaderRowString .= ",Flag - Valid";
			break;

			case "V2Dir":
			$HeaderRowString .= ",Flag - V2Dir";
			break;

			case "HSDir":
			$HeaderRowString .= ",Flag - HSDir";
			break;
		}
	}

	$HeaderRowString .= "\n";
}

function DisplayRouterRow()
{
	global $record, $ColumnList_ACTIVE;

	echo $record['Name'];

	foreach($ColumnList_ACTIVE as $value)
	{
		if($value == 'Uptime')
		{
			if ($record[$value] > -1)
			{
				echo "," . str_replace(",","-",str_replace("\"","'",$record[$value]));
			}
			else
			{
				echo ",N/A";
			}
		}
		else if($value == 'DirPort')
		{
			if ($record[$value] > 0)
			{
				echo "," . str_replace(",","-",str_replace("\"","'",$record[$value]));
			}
			else
			{
				echo ",None";
			}
		}
		else if($value == 'CountryCode')
		{
			if ($record[$value] != null)
			{
				echo "," . str_replace(",","-",str_replace("\"","'",$record[$value]));
			}
			else
			{
				echo ",N/A";
			}
		}
		else
		{
			echo "," . str_replace(",","-",str_replace("\"","'",$record[$value]));
		}
	}

	echo "\n";
}

// Connect to database, select schema
$link = mysql_connect($SQL_Server, $SQL_User, $SQL_Pass) or die('Could not connect: ' . mysql_error());
mysql_select_db($SQL_Catalog) or die('Could not open specified database');

// Read all variables from session
if (isset($_SESSION["SR"]))
{
	$SR = $_SESSION["SR"];
}
if (isset($_SESSION["SO"]))
{
	$SO = $_SESSION["SO"];
}
if (isset($_SESSION["ColumnList_ACTIVE"]))
{
	$ColumnList_ACTIVE = $_SESSION["ColumnList_ACTIVE"];
}
if (isset($_SESSION["FAuthority"]))
{
	$FAuthority = $_SESSION["FAuthority"];
}
if (isset($_SESSION["FBadDirectory"]))
{
	$FBadDirectory = $_SESSION["FBadDirectory"];
}
if (isset($_SESSION["FBadExit"]))
{
	$FBadExit = $_SESSION["FBadExit"];
}
if (isset($_SESSION["FExit"]))
{
	$FExit = $_SESSION["FExit"];
}
if (isset($_SESSION["FFast"]))
{
	$FFast = $_SESSION["FFast"];
}
if (isset($_SESSION["FGuard"]))
{
	$FGuard = $_SESSION["FGuard"];
}
if (isset($_SESSION["FHibernating"]))
{
	$FHibernating = $_SESSION["FHibernating"];
}
if (isset($_SESSION["FNamed"]))
{
	$FNamed = $_SESSION["FNamed"];
}
if (isset($_SESSION["FStable"]))
{
	$FStable = $_SESSION["FStable"];
}
if (isset($_SESSION["FRunning"]))
{
	$FRunning = $_SESSION["FRunning"];
}
if (isset($_SESSION["FValid"]))
{
	$FValid = $_SESSION["FValid"];
}
if (isset($_SESSION["FV2Dir"]))
{
	$FV2Dir = $_SESSION["FV2Dir"];
}
if (isset($_SESSION["CSField"]))
{
	$CSField = $_SESSION["CSField"];
}
if (isset($_SESSION["CSMod"]))
{
	$CSMod = $_SESSION["CSMod"];
}
if (isset($_SESSION["CSInput"]))
{
	$CSInput = $_SESSION["CSInput"];
}

// Variable Scrubbing / Default Values
if(
	$SR != "Name"				&&
	$SR != "Fingerprint"			&&
	$SR != "CountryCode"			&&
	$SR != "Bandwidth"			&&
	$SR != "Uptime"			&&
	$SR != "LastDescriptorPublished"	&&
	$SR != "IP"				&&
	$SR != "Hostname"			&&
	$SR != "ORPort"			&&
	$SR != "DirPort"			&&
	$SR != "Platform"			&&
	$SR != "Contact"			&&
	$SR != "FAuthority"			&&
	$SR != "FBadDirectory"		&&
	$SR != "FBadExit"			&&
	$SR != "FExit"			&&
	$SR != "FFast"			&&
	$SR != "FGuard"			&&
	$SR != "Hibernating"			&&
	$SR != "FNamed"			&&
	$SR != "FStable"			&&
	$SR != "FRunning"			&&
	$SR != "FValid"			&&
	$SR != "FV2Dir")
{
	$SR = "Name";
} 

if(
	$SO != "Asc"				&&
	$SO != "Desc")
{
	$SO = "Asc";
} 

if (!(isset($_SESSION['ColumnSetVisited'])) && !(isset($_SESSION['IndexVisited'])))
{
	$ColumnList_ACTIVE = $ColumnList_ACTIVE_DEFAULT;
}

if($FAuthority != '0' && $FAuthority != '1' && $FAuthority != 'OFF')
{
	$FAuthority = 'OFF';
}

if($FBadDirectory != '0' && $FBadDirectory != '1' && $FBadDirectory != 'OFF')
{
	$FBadDirectory = 'OFF';
}

if($FBadExit != '0' && $FBadExit != '1' && $FBadExit != 'OFF')
{
	$FBadExit = 'OFF';
}

if($FExit != '0' && $FExit != '1' && $FExit != 'OFF')
{
	$FExit = 'OFF';
}

if($FFast != '0' && $FFast != '1' && $FFast != 'OFF')
{
	$FFast = 'OFF';
}

if($FGuard != '0' && $FGuard != '1' && $FGuard != 'OFF')
{
	$FGuard = 'OFF';
}

if($FHibernating != '0' && $FHibernating != '1' && $FHibernating != 'OFF')
{
	$FHibernating = 'OFF';
}

if($FNamed != '0' && $FNamed != '1' && $FNamed != 'OFF')
{
	$FNamed = 'OFF';
}

if($FStable != '0' && $FStable != '1' && $FStable != 'OFF')
{
	$FStable = 'OFF';
}

if($FRunning != '0' && $FRunning != '1' && $FRunning != 'OFF')
{
	$FRunning = 'OFF';
}

if($FValid != '0' && $FValid != '1' && $FValid != 'OFF')
{
	$FValid = 'OFF';
}

if($FV2Dir != '0' && $FV2Dir != '1' && $FV2Dir != 'OFF')
{
	$FV2Dir = 'OFF';
}

if(
	$CSField != "Fingerprint"			&&
	$CSField != "Name"				&&
	$CSField != "CountryCode"			&&
	$CSField != "Bandwidth"			&&
	$CSField != "Uptime"				&&
	$CSField != "LastDescriptorPublished"	&&
	$CSField != "IP"				&&
	$CSField != "Hostname"			&&
	$CSField != "ORPort"				&&
	$CSField != "DirPort"			&&
	$CSField != "Platform"			&&
	$CSField != "Contact")
{
	$CSField = "Fingerprint";
} 

if(
	$CSMod != "Equals"		&&
	$CSMod != "Contains"		&&
	$CSMod != "LessThan"		&&
	$CSMod != "GreaterThan")
{
	$CSMod = "Equals";
}

if ($CSInput != null)
{
	if (strlen($CSInput) > 128)
	{
		$CSInput = substr($CSInput,0,128);
	}
}

// Get active table information from database
$query = "select ActiveNetworkStatusTable, ActiveDescriptorTable from Status";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$record = mysql_fetch_assoc($result);

$ActiveNetworkStatusTable = $record['ActiveNetworkStatusTable'];
$ActiveDescriptorTable = $record['ActiveDescriptorTable'];

// Prepare and execute master router query
$query = "select $ActiveNetworkStatusTable.Name, $ActiveNetworkStatusTable.Fingerprint";

if (in_array("CountryCode", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.CountryCode";
}

if (in_array("Bandwidth", $ColumnList_ACTIVE))
{
	$query .= ", floor($ActiveDescriptorTable.BandwidthOBSERVED / 1024) as Bandwidth";
}

if (in_array("Uptime", $ColumnList_ACTIVE))
{
	$query .= ", floor(CAST(((UNIX_TIMESTAMP() - (UNIX_TIMESTAMP($ActiveDescriptorTable.LastDescriptorPublished) + $OffsetFromGMT)) + $ActiveDescriptorTable.Uptime) AS SIGNED) / 86400) as Uptime";
}

if (in_array("LastDescriptorPublished", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveDescriptorTable.LastDescriptorPublished";
}

if (in_array("Hostname", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.Hostname";
}

if (in_array("IP", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.IP";
}

if (in_array("ORPort", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.ORPort";
}

if (in_array("DirPort", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.DirPort";
}

if (in_array("Platform", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveDescriptorTable.Platform";
}

if (in_array("Contact", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveDescriptorTable.Contact";
}

if (in_array("Authority", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FAuthority as Authority";
}

if (in_array("BadDir", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FBadDirectory as BadDir";
}

if (in_array("BadExit", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FBadExit as BadExit";
}

if (in_array("Exit", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FExit as 'Exit'";
}

if (in_array("Fast", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FFast as Fast";
}

if (in_array("Guard", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FGuard as Guard";
}

if (in_array("Hibernating", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveDescriptorTable.Hibernating as 'Hibernating'";
}

if (in_array("Named", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FNamed as Named";
}

if (in_array("Stable", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FStable as Stable";
}

if (in_array("Running", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FRunning as Running";
}

if (in_array("Valid", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FValid as Valid";
}

if (in_array("V2Dir", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FV2Dir as V2Dir";
}

if (in_array("HSDir", $ColumnList_ACTIVE))
{
	$query .= ", $ActiveNetworkStatusTable.FHSDir as HSDir";
}

$query .= ", INET_ATON($ActiveNetworkStatusTable.IP) as NIP from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint";

if ($FAuthority != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FAuthority = $FAuthority";
		}
	else
		{
			$query = $query . " and FAuthority = $FAuthority";
		}
}

if ($FBadDirectory != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FBadDirectory = $FBadDirectory";
		}
	else
		{
			$query = $query . " and FBadDirectory = $FBadDirectory";
		}
}

if ($FBadExit != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FBadExit = $FBadExit";
		}
	else
		{
			$query = $query . " and FBadExit = $FBadExit";
		}
}

if ($FExit != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FExit = $FExit";
		}
	else
		{
			$query = $query . " and FExit = $FExit";
		}
}

if ($FFast != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FFast = $FFast";
		}
	else
		{
			$query = $query . " and FFast = $FFast";
		}
}

if ($FGuard != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FGuard = $FGuard";
		}
	else
		{
			$query = $query . " and FGuard = $FGuard";
		}
}

if ($FHibernating != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where Hibernating = $FHibernating";
		}
	else
		{
			$query = $query . " and Hibernating = $FHibernating";
		}
}

if ($FNamed != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FNamed = $FNamed";
		}
	else
		{
			$query = $query . " and FNamed = $FNamed";
		}
}

if ($FStable != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FStable = $FStable";
		}
	else
		{
			$query = $query . " and FStable = $FStable";
		}
}

if ($FRunning != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FRunning = $FRunning";
		}
	else
		{
			$query = $query . " and FRunning = $FRunning";
		}
}

if ($FValid != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FValid = $FValid";
		}
	else
		{
			$query = $query . " and FValid = $FValid";
		}
}

if ($FV2Dir != 'OFF')
{
	if (strpos($query, "where") === false)
		{
			$query = $query . " where FV2Dir = $FV2Dir";
		}
	else
		{
			$query = $query . " and FV2Dir = $FV2Dir";
		}
}

if ($CSInput != null)
{
	$CSInput_SAFE = null;
	$QueryPrepend = null;

	if (strpos($query, "where") === false)
	{
		$QueryPrepend = " where "; 
	}
	else
	{
		$QueryPrepend = " and ";
	}

	$query .= $QueryPrepend;

	$CSInput_SAFE = mysql_real_escape_string($CSInput);

	if ($CSField == 'Fingerprint')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.Fingerprint = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.Fingerprint like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.Fingerprint < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.Fingerprint > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'Name')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.Name = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.Name like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.Name < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.Name > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'CountryCode')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.CountryCode = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.CountryCode like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.CountryCode < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.CountryCode > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'Bandwidth')
	{
		if(!(is_numeric($CSInput_SAFE))) 
		{
			$CSInput = 0;
			$CSInput_SAFE = 0;
		}

		if($CSMod == 'Equals')
		{
			$query .= "floor($ActiveDescriptorTable.BandwidthOBSERVED / 1024) = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "floor($ActiveDescriptorTable.BandwidthOBSERVED / 1024) like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "floor($ActiveDescriptorTable.BandwidthOBSERVED / 1024) < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "floor($ActiveDescriptorTable.BandwidthOBSERVED / 1024) > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'Uptime')
	{
		if(!(is_numeric($CSInput_SAFE))) 
		{
			$CSInput = 0;
			$CSInput_SAFE = 0;
		}

		if($CSMod == 'Equals')
		{
			$query .= "floor($ActiveDescriptorTable.Uptime / 86400) = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "floor($ActiveDescriptorTable.Uptime / 86400) like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "floor($ActiveDescriptorTable.Uptime / 86400) < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "floor($ActiveDescriptorTable.Uptime / 86400) > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'LastDescriptorPublished')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.LastDescriptorPublished = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.LastDescriptorPublished like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.LastDescriptorPublished < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.LastDescriptorPublished > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'IP')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.IP = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.IP like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.IP < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.IP > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'Hostname')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.Hostname = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.Hostname like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.Hostname < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.Hostname > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'ORPort')
	{
		if(!(is_numeric($CSInput_SAFE))) 
		{
			$CSInput = 0;
			$CSInput_SAFE = 0;
		}

		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.ORPort = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.ORPort like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.ORPort < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.ORPort > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'DirPort')
	{
		if(!(is_numeric($CSInput_SAFE))) 
		{
			$CSInput = 0;
			$CSInput_SAFE = 0;
		}

		if($CSMod == 'Equals')
		{
			$query .= "$ActiveNetworkStatusTable.DirPort = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveNetworkStatusTable.DirPort like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveNetworkStatusTable.DirPort < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveNetworkStatusTable.DirPort > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'Platform')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveDescriptorTable.Platform = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveDescriptorTable.Platform like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveDescriptorTable.Platform < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveDescriptorTable.Platform > '$CSInput_SAFE'";
		}
	}
	else if ($CSField == 'Contact')
	{
		if($CSMod == 'Equals')
		{
			$query .= "$ActiveDescriptorTable.Contact = '$CSInput_SAFE'";
		}
		else if($CSMod == 'Contains')
		{
			$query .= "$ActiveDescriptorTable.Contact like '%$CSInput_SAFE%'";
		}
		else if($CSMod == 'LessThan')
		{
			$query .= "$ActiveDescriptorTable.Contact < '$CSInput_SAFE'";
		}
		else if($CSMod == 'GreaterThan')
		{
			$query .= "$ActiveDescriptorTable.Contact > '$CSInput_SAFE'";
		}
	}
}

if ($SR == 'Name')
{
	$query = $query . " order by " . $SR . " " . $SO;
}
else if ($SR == 'IP')
{
	$query = $query . " order by NIP " . $SO . ", Name Asc";
}
else
{
	$query = $query . " order by " . $SR . " " . $SO . ", Name Asc";
}

// die($query);
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// Generate header row
GenerateHeaderRow();

// Output CSV file to browser
header('Content-Transfer-Encoding: Binary');
header('Content-type: application/force-download');
header('Content-disposition: inline; filename=Tor_query_EXPORT.csv');

echo $HeaderRowString;

while ($record = mysql_fetch_assoc($result)) 
{
	DisplayRouterRow();
}

// Close connection
mysql_close($link);

?>
