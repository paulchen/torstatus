<?php

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information

// See README file for description of values listed here

// This config file utilizes folds.  For VIM, you may activate this using
// :set foldmethod=marker

// ++++++++++ Tor Connection ++++++++++ {{{

$LocalTorServerIP = "tor";
$LocalTorServerControlPort = "9051";
$LocalTorServerPassword = "torstatus";

// }}}

// ++++++++++ Squid and SSL ++++++++++ {{{

$RealServerIP = "78.46.53.2";

// }}}

// ++++++++++ Database ++++++++++ {{{

$SQL_Server = "mariadb";
$SQL_User = "torstatus";
$SQL_Pass = "torstatus";
$SQL_Catalog = "torstatus";

// }}}

// ++++++++++ Paths ++++++++++ {{{
$JPGraph_Path = "jpgraph/";
if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.onion') === false)
{
	define("WHOISPath","https://www.whois.com/whois/");
}

// }}}

// ++++++++++ Interface ++++++++++ {{{

$footerText = "<b>The software used for this page was initially taken from project.torstatus.kgprog.com. <br />The software running on this software has been modified to ensure proper operation and is maintained at <a href='https://github.com/paulchen/torstatus'>GitHub</a>.</b><br /><br />Site operator: <a href='mailto:paulchen@rueckgr.at'><strong>Paul Staroch</strong></a> &ndash; <a href='//rueckgr.at/'><strong>rueckgr.at</strong></a>";
$ColumnHeaderInterval = 20;
$ColumnList_ACTIVE_DEFAULT = array
(
	'CountryCode',
	'Bandwidth',
	'Uptime',
	'IP',
	'Hostname',
	'ORPort',
	'DirPort',
	'Authority',
	'Exit',
	'Fast',
	'Guard',
	'Named',
	'Stable',
	'Running',
	'Valid',
	'V2Dir',
	'HSDir',
	'Platform',
	'Hibernating'
);

$ColumnList_INACTIVE_DEFAULT = array
(
	'Fingerprint',
	'LastDescriptorPublished',
	'Contact',
	'BadDir',
	'BadExit'
);

// }}}

// ++++++++++ Other ++++++++++ {{{
$LocalTimeZone = "GMT";
$OffsetFromGMT = 0;

//$Hidden_Service_URL = null;
$Hidden_Service_URL = "http://t3qi4hdmvqo752lhyglhyb5ysoutggsdocmkxhuojfn62ntpcyydwmqd.onion/";

// See if WHOIS wants the footer
if (isset($argv) && isset($argv[1]) && $argv[1] == 'printthefooter')
{
	echo $footerText;
}

// }}}

?>
