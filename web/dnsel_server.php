<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

// Start new session
@session_start() or die();

// Include configuration settings
include("config.php");

// Handle situation where no DNSEL Domain name is set in config file
if ($DNSEL_Domain == null)
{
	echo "\n";
	echo "<!-- Begin Page Render -->\n";
	echo "\n";
	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>\n";
	echo "\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<meta http-equiv='Content-Type' content='text/html;charset=utf-8'>\n";
	echo "<title>Tor Network Status -- DNSEL Server</title>\n";
	echo "<link rel='StyleSheet' TYPE='Text/CSS' HREF='css/main.css'>\n";
	echo "</head>\n";
	echo "<body class='BOD'>\n";
	echo "<br><br>\n";
	echo "<table width='70%' cellspacing='2' cellpadding='2' border='0' align='center'>\n";
	echo "<tr>\n";
	echo "<td class='PT'><br><a href='index.php'>Tor Network Status</a> -- DNSEL Server<br><br></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td class='TRSC'><br><br><br><b>ERROR -- No DNSEL Server Available</b><br><br></td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</body>\n";
	echo "</html>\n";

	exit;
}

?>

<!-- Begin Page Render -->

<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>

<html>
<head>
<meta http-equiv='Content-Type' content='text/html;charset=utf-8'>
<title>Tor Network Status -- DNSEL Server</title>
<link rel='StyleSheet' TYPE='Text/CSS' HREF='css/main.css'>
</head>

<body class='BOD'>

<br><br>

<table width='70%' cellspacing='2' cellpadding='2' border='0' align='center'>

<tr>
<td class='PT'><br><a href='index.php'>Tor Network Status</a> -- DNSEL Server<br><br></td>
</tr>

<tr>
<td class='TRSC'><br><br><br><br><b>DNSEL Server Domain Name:<br><font color='#3344ee'><?php echo "$DNSEL_Domain"; ?></font><br><br><br></b></td>
</tr>

<tr>
<td class='TRSB'><br><b>Usage Instructions:<br><br>The DNSEL server responds to one specially formatted 'A' record query. This query provides an answer for the question "Is this IP an active Tor server, and, if so, would its exit policy allow an exit to this destination IP and port?"<br><br>The format of this query is as follows:<br><br>{IP1}.{port}.{IP2}.<?php echo "$DNSEL_Domain" ?><br><br>So, as an example, if you want to check if IP '1.2.3.4' is an active Tor server capable of exiting to '55.66.77.88', on port 60000, you would send the following 'A' record query:<br><br>4.3.2.1.60000.88.77.66.55.<?php echo "$DNSEL_Domain"; ?><br><br>Note that the octets of both IP addresses are reversed, kindof like we were doing a PTR query, but this is, in fact, an 'A' query.<br><br>If '1.2.3.4' is an active Tor server, AND if that Tor server can exit to '55.66.77.88', port 60000, the DNSEL server will respond with a '127.0.0.2' 'A' record. If '1.2.3.4' is NOT an active Tor server, or if '1.2.3.4' IS an active Tor server, but is using an exit policy that would prevent exit to '55.66.77.88', port 60000, the DNSEL server will respond with a 'Non-Existent Domain (NXDOMAIN)' error.</br></td>
</tr>

<tr>
<td class='TRSB'><br><b>The DNSEL server will always respond with a SERVFAIL error if a client tries to lookup anything outside of the '<?php echo "$DNSEL_Domain" ?>' domain name. So, if a client sends an 'A' request for 'www.google.com', the DNSEL server will return a SERVFAIL error.<br><br>The DNSEL server will always respond with a NXDOMAIN (Non-Existent Domain) error if a client sends anything other than an 'A' record query, such as an 'MX' or 'SOA' query, and that query is within the '<?php echo "$DNSEL_Domain" ?>' domain name.<br><br>The DNSEL server will set the 'Authoritative' flag to true for responses that are within the '<?php echo "$DNSEL_Domain" ?>' domain name, and will set it to false for responses that are outside of it.</b></td>
</tr>

</table>

<br><br><br><br>

<table width='70%' cellspacing='2' cellpadding='2' border='0' align='center'>
<tr>
<td class='TRC'><?php echo $footerText; ?></td>
</tr>
</table>
</body>
</html>
