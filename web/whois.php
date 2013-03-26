<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

// Start new session
session_start();

// Include configuration settings
include("config.php");

$ip = $_GET['ip'];
if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/",$ip))
{
	echo "Is there a problem here?";
	exit;
}

$pageTitle = "WHOIS Query";
include("header.php");

?>
<table width='100%' cellspacing='2' cellpadding='2'>
<tr>
<td>
<table class="displayTable" width='100%' cellspacing='0' cellpadding='0' align='center'>

<tr>
<td class="HRN">WHOIS Query for <?php echo $ip; ?></td>
</tr>

<tr>
<td style="white-space: normal;" class='TRS'>
<pre>
<?php passthru("whois $ip"); ?>
</pre>
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
