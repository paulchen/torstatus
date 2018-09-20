<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

require_once('common.php');

// Declare and initialize variables
$ColumnList_ACTIVE = null;
$ColumnList_INACTIVE = null;

$Self = $_SERVER['PHP_SELF'];

$CR_ACTIVE = null;
$CR_INACTIVE = null;
$CR_Add = null;
$CR_Remove = null;
$CR_Up = null;
$CR_Down = null;

// Function declarations
function array_move_element($array, $value, $direction) 
{
	$temp = array();
  
	if(end($array) == $value && $direction == 'down') 
	{
		return $array;
   	}
   	if(reset($array) == $value && $direction == 'up') 
	{
       	return $array;
   	}

   	while ($array_value = current($array)) 
	{
		$this_key = key($array);

       	if ($array_value == $value) 
		{
           		if($direction == 'down') 
			{
               		$next_value = next($array);
               		$temp[key($array)] = $next_value;
               		$temp[$this_key] = $array_value;
           		} 
			else 
			{
              		$prev_value = prev($array);
               		$prev_key = key($array);
               		unset($temp[$prev_key]);
               		$temp[$this_key] = $array_value;
               		$temp[$prev_key] = $prev_value;
               		next($array);
               		next($array);
 			}
			continue;
		} 
		else 
		{
           		$temp[$this_key] = $array_value;
       	}

	       next($array);
   	}

	return $temp;
}

// Set ColumnList_ACTIVE and ColumnList_INACTIVE variables to default values if they have not been set before, otherwise get from SESSION
if (!(isset($_SESSION['ColumnSetVisited'])) && !(isset($_SESSION['IndexVisited'])))
{
	$ColumnList_ACTIVE = $ColumnList_ACTIVE_DEFAULT;
	$ColumnList_INACTIVE = $ColumnList_INACTIVE_DEFAULT;
}
else
{
	if (isset($_SESSION["ColumnList_ACTIVE"]))
	{
		$ColumnList_ACTIVE = $_SESSION["ColumnList_ACTIVE"];
	}
	if (isset($_SESSION["ColumnList_INACTIVE"]))
	{
		$ColumnList_INACTIVE = $_SESSION["ColumnList_INACTIVE"];
	}
}

// Get CR_ACTIVE, CR_INACTIVE CR_Add, CR_Remove, CR_Up, & CR_Down variables from POST
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (isset($_POST["CR_ACTIVE"]))
	{
		$CR_ACTIVE = $_POST["CR_ACTIVE"];
	}
	if (isset($_POST["CR_INACTIVE"]))
	{
		$CR_INACTIVE = $_POST["CR_INACTIVE"];
	}
	if (isset($_POST["Add"]))
	{
		$CR_Add = $_POST["Add"];
	}
	if (isset($_POST["Remove"]))
	{
		$CR_Remove = $_POST["Remove"];
	}
	if (isset($_POST["Up"]))
	{
		$CR_Up = $_POST["Up"];
	}
	if (isset($_POST["Down"]))
	{
		$CR_Down = $_POST["Down"];
	}

	// Variable Scrubbing
	if (
		$CR_ACTIVE != null				&&
		$CR_ACTIVE != 'Fingerprint'			&&
		$CR_ACTIVE != 'CountryCode'			&&
		$CR_ACTIVE != 'Bandwidth'			&&
		$CR_ACTIVE != 'Uptime'			&&
		$CR_ACTIVE != 'LastDescriptorPublished'	&&
		$CR_ACTIVE != 'Hostname'			&&
		$CR_ACTIVE != 'IP'				&&
		$CR_ACTIVE != 'ORPort'			&&
		$CR_ACTIVE != 'DirPort'			&&
		$CR_ACTIVE != 'Platform'			&&
		$CR_ACTIVE != 'Contact'			&&
		$CR_ACTIVE != 'Authority'			&&
		$CR_ACTIVE != 'BadDir'			&&
		$CR_ACTIVE != 'BadExit'			&&
		$CR_ACTIVE != 'Exit'				&&
		$CR_ACTIVE != 'Fast'				&&
		$CR_ACTIVE != 'Guard'			&&
		$CR_ACTIVE != 'Hibernating'			&&
		$CR_ACTIVE != 'Named'			&&
		$CR_ACTIVE != 'Stable'			&&
		$CR_ACTIVE != 'Running'			&&
		$CR_ACTIVE != 'Valid'			&&
		$CR_ACTIVE != 'V2Dir'			&&
		$CR_ACTIVE != 'HSDir')
	{
		$CR_ACTIVE = null;
	}

	if (
		$CR_INACTIVE != null				&&
		$CR_INACTIVE != 'Fingerprint'		&&
		$CR_INACTIVE != 'CountryCode'		&&
		$CR_INACTIVE != 'Bandwidth'			&&
		$CR_INACTIVE != 'Uptime'			&&
		$CR_INACTIVE != 'LastDescriptorPublished'	&&
		$CR_INACTIVE != 'Hostname'			&&
		$CR_INACTIVE != 'IP'				&&
		$CR_INACTIVE != 'ORPort'			&&
		$CR_INACTIVE != 'DirPort'			&&
		$CR_INACTIVE != 'Platform'			&&
		$CR_INACTIVE != 'Contact'			&&
		$CR_INACTIVE != 'Authority'			&&
		$CR_INACTIVE != 'BadDir'			&&
		$CR_INACTIVE != 'BadExit'			&&
		$CR_INACTIVE != 'Exit'			&&
		$CR_INACTIVE != 'Fast'			&&
		$CR_INACTIVE != 'Guard'			&&
		$CR_INACTIVE != 'Hibernating'		&&
		$CR_INACTIVE != 'Named'			&&
		$CR_INACTIVE != 'Stable'			&&
		$CR_INACTIVE != 'Running'			&&
		$CR_INACTIVE != 'Valid'			&&
		$CR_INACTIVE != 'V2Dir'			&&
		$CR_INACTIVE != 'HSDir')
	{
		$CR_INACTIVE = null;
	}
}

// Process arrays
if ($CR_Add != null)
{
	if($CR_INACTIVE != null)
	{
		$key = array_search($CR_INACTIVE,$ColumnList_INACTIVE);
		unset($ColumnList_INACTIVE[$key]);
		array_push($ColumnList_ACTIVE, $CR_INACTIVE);
	}
}
else if ($CR_Remove != null)
{
	if($CR_ACTIVE != null)
	{
		$key = array_search($CR_ACTIVE,$ColumnList_ACTIVE);
		unset($ColumnList_ACTIVE[$key]);
		array_push($ColumnList_INACTIVE, $CR_ACTIVE);
	}
}
else if ($CR_Up != null)
{
	if($CR_ACTIVE != null)
	{
		$Direction = "up";
		$ColumnList_ACTIVE = array_move_element($ColumnList_ACTIVE, $CR_ACTIVE, $Direction);
	}
}
else if ($CR_Down != null)
{
	if($CR_ACTIVE != null)
	{
		$Direction = "down";
		$ColumnList_ACTIVE = array_move_element($ColumnList_ACTIVE, $CR_ACTIVE, $Direction);
	}
}

// Register variables in SESSION
if (!isset($_SESSION['ColumnList_ACTIVE'])) 
{
	$_SESSION['ColumnList_ACTIVE'] = $ColumnList_ACTIVE;
} 
else
{
	unset($_SESSION['ColumnList_ACTIVE']);
	$_SESSION['ColumnList_ACTIVE'] = $ColumnList_ACTIVE;
}

if (!isset($_SESSION['ColumnList_INACTIVE'])) 
{
	$_SESSION['ColumnList_INACTIVE'] = $ColumnList_INACTIVE;
} 
else
{
	unset($_SESSION['ColumnList_INACTIVE']);
	$_SESSION['ColumnList_INACTIVE'] = $ColumnList_INACTIVE;
}

$pageTitle = "Column Display Preferences";
include("header.php");

?>

<table width='100%' cellspacing='2' cellpadding='2'>
<tr>
<td>

<table class='displayTable' width='100%' cellspacing='0' cellpadding='0' align='center'>
<tr>
<td class='HRN'>Column Display Preferences Detail</td>
</tr>
<tr>
<td class='TRSCN'>

<?php

	echo "<form action='$Self' method='post'>\n";
	echo "<table border='0' align='center' cellpadding='10' cellspacing='8'>\n";
	echo "<tr>\n";
	echo "<td align='center'>\n";
	echo "<table border='0' align='center'>\n";
	echo "<tr>\n";
	echo "<td class='TRSCN' align='center'>\n";
	echo "<input class='BTNCOLSEL' name='Remove' type='submit' value='Remove Column &gt;&gt;' />\n";
	echo "<br/>\n";
	echo "<select name='CR_ACTIVE' size='20' class='BOXCOLSEL'>\n";
	foreach($ColumnList_ACTIVE as $value)
	{
		if ((($CR_Up != null || $CR_Down != null) && ($CR_ACTIVE == $value)) || (($CR_Add != null) && ($CR_INACTIVE == $value)))
		{
			echo "<option value='$value' selected>$value</option>\n";
		}
		else
		{
			echo "<option value='$value'>$value</option>\n";
		}
	}
	echo "</select>\n";
	echo "<br/><b>Currently Selected Columns</b>\n";
	echo "</td>\n";
	echo "<td align='left'>\n";
	echo "<input class='BTNUPDOWN' name='Up' type='submit' value='Up' /><br/><input class='BTNUPDOWN' name='Down' type='submit' value='Down' />\n";	
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</td>\n";
	echo "<td class='TRSCN' align='center'>\n";
	echo "<input class='BTNCOLSEL' name='Add' type='submit' value='&lt;&lt; Add Column' />\n";
	echo "<br/>\n";
	echo "<select name='CR_INACTIVE' size='20' class='BOXCOLSEL'>\n";
	foreach($ColumnList_INACTIVE as $value)
	{
		if (($CR_Remove != null) && ($CR_ACTIVE == $value))
		{
			echo "<option value='$value' selected>$value</option>\n";
		}
		else
		{
			echo "<option value='$value'>$value</option>\n";
		}
	}
	echo "</select>\n";
	echo "<br/><b>Available Columns</b>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";
?>

<a class='tab' href='index.php'><b>Done / Return to Main Page</b></a>
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

// Register session variable to mark that this page has been loaded
if (!isset($_SESSION['ColumnSetVisited'])) 
{
	$_SESSION['ColumnSetVisited'] = 1;
} 

?>
