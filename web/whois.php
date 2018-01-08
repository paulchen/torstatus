<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

header('Location: notfound.php');
die();

require_once('common.php');

$ip = $_GET['ip'];
if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/",$ip))
{
	echo "Is there a problem here?";
	exit;
}

$pageTitle = "WHOIS Query";
include("header.php");

$query = "select ActiveNetworkStatusTable, ActiveDescriptorTable from Status";
$record = db_query_single_row($query);

$ActiveNetworkStatusTable = $record['ActiveNetworkStatusTable'];

// Populate variables from database
$query = "select count(*) ips from $ActiveNetworkStatusTable where IP = '$ip'";
$record = db_query_single_row($query);
$ips = $record['ips'];

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
<?php if($ips == 0): ?>
<pre>
Sorry, this service can only to be used for querying data about Tor relays.
</pre>
<?php else: ?>
<pre>
<?php
$m = new Memcached();
$m->addServer('localhost', 11211);
$key = "whois_$ip";
$data = $m->get($key);
if(!$data) {
#	exec("whois -h 193.0.6.135 $ip", $lines);
	exec("whois $ip", $lines);
	$data = implode("\n", $lines);
}
echo $data;
$m->set($key, $data, 3600);
?>
</pre>
<?php endif; ?>
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
