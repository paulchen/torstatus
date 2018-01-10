<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

$ip = $_GET['ip'];
if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/",$ip))
{
	header('HTTP/1.1 403 Forbidden');
	die();
}


header("Location: https://www.whois.com/whois/$ip", true, 302);
die();

