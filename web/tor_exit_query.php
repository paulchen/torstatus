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

$Self = $_SERVER['PHP_SELF'];

$QueryIP = null;
$DestinationIP = null;
$DestinationPort = null;
$QueryIPDBCount = null;
$PositiveMatch_IP = 0;
$PositiveMatch_ExitPolicy = null;
$TorNodeName = null;
$TorNodeFP = null;
$TorNodeExitPolicy = null;

$Count = 0;

// Function Declarations
function IsIPInSubnet($IP,$Subnet)
{
	// Credit for the parts of the code in this function:
	// This code used in this function was found on the PHP.net website's 'IP2Long' function page.
	// It was posted by 'Ian B' on '24-Dec-2006 04:22'.

	/* always return true if subnet is wildcard */
	if ($Subnet == '*')
	{
		return 1;
	}

	/* always return true if ip is an exact match as is */
	if ($Subnet == $IP)
	{
		return 1;
	}

	/* always return false if only an ip was provided, and it's not an exact match */
	if (strpos($Subnet, '/') === FALSE)
	{
		return 0;
	}

       /* get the base and the bits from the subnet */
       list($base, $bits) = explode('/', $Subnet);

       /* now split it up into it's classes */
       list($a, $b, $c, $d) = explode('.', $base);

       /* now do some bit shifting/switching to convert to ints */
       $i = ($a << 24) + ($b << 16) + ($c << 8) + $d;
       $mask = $bits == 0 ? 0 : (~0 << (32 - $bits));

       /* here's our lowest int */
       $low = $i & $mask;

       /* here's our highest int */
       $high = $i | (~$mask & 0xFFFFFFFF);

       /* now split the ip we're checking against up into classes */
       list($a, $b, $c, $d) = explode('.', $IP);

       /* now convert the ip we're checking against to an int */
       $check = ($a << 24) + ($b << 16) + ($c << 8) + $d;

       /* if the ip is within the range, including highest/lowest values, then it's within the subnet range */
       if ($check >= $low && $check <= $high)
	{
		return 1;
	}
       else
	{
		return 0;
	}
}

// Read in submitted variables
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (isset($_POST["QueryIP"]))
	{
		$QueryIP = $_POST["QueryIP"];
	}
	if (isset($_POST["DestinationIP"]))
	{
		$DestinationIP = $_POST["DestinationIP"];
	}
	if (isset($_POST["DestinationPort"]))
	{
		$DestinationPort = $_POST["DestinationPort"];
	}
}

// Connect to database, select schema
$link = mysql_connect($SQL_Server, $SQL_User, $SQL_Pass) or die('Could not connect: ' . mysql_error());
mysql_select_db($SQL_Catalog) or die('Could not open specified database');

// Variable scrubbing
if (strlen($QueryIP) > 15)
{
	$QueryIP = null;
}
else
{
	$QueryIP = mysql_real_escape_string($QueryIP);
}
if ($QueryIP != null)
{
	$QueryIP_Long = ip2long($QueryIP);
	if ($QueryIP_Long == -1 || $QueryIP_Long === FALSE)
	{
		$QueryIP = null;
	}
	else
	{
		$QueryIP = long2ip($QueryIP_Long);
	}
}

if (strlen($DestinationIP) > 15)
{
	$DestinationIP = null;
}
else
{
	$DestinationIP = mysql_real_escape_string($DestinationIP);
}
if ($DestinationIP != null)
{
	$DestinationIP_Long = ip2long($DestinationIP);
	if ($DestinationIP_Long == -1 || $DestinationIP_Long === FALSE)
	{
		$DestinationIP = null;
	}
	else
	{
		$DestinationIP = long2ip($DestinationIP_Long);
	}
}

if (strlen($DestinationPort) > 5)
{
	$DestinationPort = null;
}
else
{
	$DestinationPort = mysql_real_escape_string($DestinationPort);
}
if ($DestinationPort != null)
{
	if 	(
		!is_numeric($DestinationPort) 	|| 
		intval($DestinationPort) < 0	|| 
		intval($DestinationPort) > 65535
		) 
	{
		$DestinationPort = null;
	}
}

// Get active table information from database
$query = "select ActiveNetworkStatusTable, ActiveDescriptorTable from Status";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$record = mysql_fetch_assoc($result);

$ActiveNetworkStatusTable = $record['ActiveNetworkStatusTable'];
$ActiveDescriptorTable = $record['ActiveDescriptorTable'];

if ($QueryIP != null)
{
	// Determine if query IP exists in database as a Tor server
	$query = "select count(*) as Count from $ActiveNetworkStatusTable where IP = '$QueryIP'";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$record = mysql_fetch_assoc($result);
	
	$QueryIPDBCount = $record['Count'];
	
	if ($QueryIPDBCount > 0)
	{
		$PositiveMatch_IP = 1;	
	}
	
	// Get name, fingerprint, and exit policy of Tor node(s) if match was found and Destination IP/Port was specified, look for match in ExitPolicy
	if ($PositiveMatch_IP == 1 && $DestinationIP != null && $DestinationPort != null)
	{
		$query = "select $ActiveNetworkStatusTable.Name, $ActiveNetworkStatusTable.Fingerprint, $ActiveDescriptorTable.ExitPolicySERDATA from $ActiveNetworkStatusTable inner join $ActiveDescriptorTable on $ActiveNetworkStatusTable.Fingerprint = $ActiveDescriptorTable.Fingerprint where $ActiveNetworkStatusTable.IP = '$QueryIP'";
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	
		while ($record = mysql_fetch_assoc($result))
		{ 
			$Count++;			

			$TorNodeName[$Count] = $record['Name'];
			$TorNodeFP[$Count] = $record['Fingerprint'];
			$TorNodeExitPolicy = unserialize($record['ExitPolicySERDATA']);
	
			foreach($TorNodeExitPolicy as $ExitPolicyLine)
			{
				// Initialize variables
				$Condition = null;
				$NetworkLine = null;
				$Subnet = null;
				$PortLine = null;
				$Port = null;
	
				// Seperate parts of ExitPolicy line
				list($Condition,$NetworkLine) = explode(' ', rtrim($ExitPolicyLine));
				list($Subnet,$PortLine) = explode(':', $NetworkLine);
				$Port = explode(',', $PortLine);
	
				// Find out if Destination IP user provided is a match for the subnet specified on this ExitPolicy line
				if (IsIPInSubnet($DestinationIP,$Subnet) == 1)
				{
					// Determine if port is also a match
					foreach($Port as $CurrentPortExpression)
					{
						// Handle condition where port is a '*' character (Port always matches)
						if ($CurrentPortExpression == '*')
						{
							if ($Condition == 'accept')
							{
								$PositiveMatch_ExitPolicy[$Count] = 1;
								break 2;
							}
							else if ($Condition == 'reject')
							{
								$PositiveMatch_ExitPolicy[$Count] = 0;
								break 2;
							}
						}
	
						// $CurrentPortExpression is a range of ports
						if(strpos($CurrentPortExpression, '-') !== FALSE)
						{
							list($LowerPort,$UpperPort) = explode('-', $CurrentPortExpression);
		
							if (($DestinationPort >= $LowerPort && $DestinationPort <= $UpperPort) && ($Condition == 'accept'))
							{
								$PositiveMatch_ExitPolicy[$Count] = 1;
								break 2;
							}
							else if (($DestinationPort >= $LowerPort && $DestinationPort <= $UpperPort) && ($Condition == 'reject'))
							{
								$PositiveMatch_ExitPolicy[$Count] = 0;
								break 2;
							}
							else
							{
								continue;
							}
						}
		
						// $CurrentPortExpression is a single port number
						else
						{
							if (($DestinationPort == $CurrentPortExpression) && ($Condition == 'accept'))
							{
								$PositiveMatch_ExitPolicy[$Count] = 1;
								break 2;
							}
							else if (($DestinationPort == $CurrentPortExpression) && ($Condition == 'reject'))
							{
								$PositiveMatch_ExitPolicy[$Count] = 0;
								break 2;
							}
							else
							{
								continue;
							}
						}
					}
				}
				else
				{
					continue;
				}
			}
		}
	}
	// Get only name and fingerprint if match was found but Destination IP/Port were not specified
	else if ($PositiveMatch_IP == 1)
	{
		$query = "select $ActiveNetworkStatusTable.Name, $ActiveNetworkStatusTable.Fingerprint from $ActiveNetworkStatusTable where $ActiveNetworkStatusTable.IP = '$QueryIP'";
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());

		while ($record = mysql_fetch_assoc($result))
		{
			$Count++;
	
			$TorNodeName[$Count] = $record['Name'];
			$TorNodeFP[$Count] = $record['Fingerprint'];
		}
	}
}

$pageTitle = "Tor Exit Query";
include("header.php");

?>

<table width='100%' cellspacing='2' cellpadding='2'>
<tr>
<td>

<table class="displayTable" width='100%' cellspacing='0' cellpadding='0' align='center'>

<tr>
<td class="HRN">Tor Exit Query</td>
</tr>

<tr>
<td style="white-space: normal;" class='TRS'><div><br/><br/><b>You can use this page to determine if an IP
address is an active Tor server, and optionally see if that Tor server's Exit
Policy would permit it to exit to a certain destination IP address and port.</b><br/></div></td>
</tr>

<?php
	echo "<tr>\n";
	echo "<td class='TRS' style='text-align: center;'><br/><br/><b>";

	// No Query IP entered, or bogus information entered
	if ($QueryIP == null)
	{
		echo "<font color='#ff0000'>-You must enter a Query IP, at minimum-</font><br/><br/>";
	}
	
	// Query IP entered, but either the DestinationIP or DestinationPort is empty or bogus
	else if ($QueryIP != null && ($DestinationIP == null || $DestinationPort == null))
	{
		if ($PositiveMatch_IP == 1)
		{
			echo "<font color='#00dd00'>-The IP Address you entered matches one or more active Tor servers-</font><br/><br/>";
			for($i=1 ; $i < ($Count + 1) ; $i++)
			{
				echo "Server name: <a class='tab' href='router_detail.php?FP=$TorNodeFP[$i]'>$TorNodeName[$i]</a><br/>";
			}
			echo "<br/>";
		}
		else if ($PositiveMatch_IP == 0)
		{
			echo "<font color='#ff0000'>-The IP Address you entered is NOT an active Tor server-</font><br/><br/>";
		}
	}
	
	// Query IP, DestinationIP, and DestinationPort entered
	else if ($QueryIP != null && $DestinationIP != null && $DestinationPort != null)
	{
		if ($PositiveMatch_IP == 1)
		{
			echo "<font color='#00dd00'>-The IP Address you entered matches one or more active Tor servers-</font><br/><br/>";
			for($i=1 ; $i < ($Count + 1) ; $i++)
			{
				echo "Server name: <a class='tab' href='router_detail.php?FP=$TorNodeFP[$i]'>$TorNodeName[$i]</a><br/>";
				if ($PositiveMatch_ExitPolicy[$i] == 1)
				{
					echo "<font color='#00dd00'>-This Tor server would allow exiting to your destination-</font><br/><br/>";
				}
				else if ($PositiveMatch_ExitPolicy[$i] == 0)
				{
					echo "<font color='#ff0000'>-This Tor server would NOT allow exiting to your destination-</font><br/><br/>";
				}
			}
		}
		else if ($PositiveMatch_IP == 0)
		{
			echo "<font color='#ff0000'>-The IP Address you entered is NOT an active Tor server-</font><br/><br/>";
		}
	}

	echo "</b></td>\n";
	echo "</tr>\n";
?>

<tr>
<td class='TRSCN'><br/>

<table width='20%' cellpadding='8' cellspacing='2' border='1' align='center'>
<tr>
<td class='TRSCN'>
<br/>

<?php
	echo "<form action='$Self' method='post'>\n";
	echo "<b>IP Address to Query:<br/><span class='TRSM'>(Required)</span></b><br/>\n";
	echo "<input type='text' name='QueryIP' class='BOX' maxlength='15' size='20' value='" . htmlspecialchars($QueryIP, ENT_QUOTES) . "' /><br/><br/><br/>\n"; 
	echo "<b>Destination IP Address:<br/><span class='TRSM'>(Optional)</span></b><br/>\n";
	echo "<input type='text' name='DestinationIP' class='BOX' maxlength='15' size='20' value='" . htmlspecialchars($DestinationIP, ENT_QUOTES) . "' /><br/><br/>\n";
	echo "<b>Destination Port:<br/><span class='TRSM'>(Optional)</span></b><br/>\n";
	echo "<input type='text' name='DestinationPort' class='BOX' maxlength='5' size='6' value='" . htmlspecialchars($DestinationPort, ENT_QUOTES) . "' /><br/><br/><br/>\n";
	echo "<input type='submit' value='Submit Query' /><br/><br/>\n";
	echo "</form>\n";
?>

</td>
</tr>
</table>

<br/><br/>
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
mysql_close($link);

?>
