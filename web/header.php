<?php

// Header file

$Self = $_SERVER['PHP_SELF'];

// Determine whether or not SSL is being used
if ($DetermineUsingSSL == 1)
{
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
	{
		$UsingSSL = 1;
		// Set the squid value to the SSL version of the Squid value
		$UsingSquid = $SSLUsingSquid;
	}
	else
	{
		$UsingSSL = 0;
	}
}

fetch_mirrors();

?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<?php if(isset($noindex) && $noindex): ?>
	<meta name="robots" content="noindex" />
<?php endif; ?>
	<title>TorStatus - <?php echo $pageTitle; ?></title>
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<!--[if lt IE 7.]>
	<script defer type="text/javascript" src="/js/pngfix.js"></script>
	<![endif]-->
</head>

<body>
<div class="topbar" id="topbar"><br/>
<table width="100%"><tr><td style="vertical-align: bottom;">
<a href="/?CSInput=" class="logoimage"><img src="img/logo.png" alt="TorStatus" class="topbarlogo"/></a>
<span class="logotext"><?php echo $TorNetworkStatus_Version; ?><?php if ($UsingSSL == 1) { ?> - Encrypted connection<?php } elseif ($AllowSSL) { ?> - <a href="<?php echo $SSLLink; echo substr($Self,-(strlen($Self)-1)); echo "?"; echo $_SERVER['QUERY_STRING'];  ?>" class="plain">Use an encrypted connection <b>(recommended)</b></a><?php } ?></span>
</td><td style="vertical-align: bottom; text-align: right;">
<form action="/index.php" method="post" name="search">
<input type="hidden" name="CSMod" value="Contains" />
<input type="hidden" name="CSField" value="Name" />
<input type="text" class="searchbox" value="<?php echo (isset($CSInput) && $CSInput)?htmlspecialchars($CSInput, ENT_QUOTES):"search for a router";?>" onfocus="javascript:if(this.value=='search for a router') { this.style.color = 'black';this.value=''; }" id="searchbox" name="CSInput"/><a href="javascript:document.search.submit();" class="searchbox"><img class="searchbox" alt="Search" src="/img/blank.gif" /></a><noscript><input type="submit" value="Search"/></noscript>
</form>
</td></tr></table>
<?php if (!isset($CSInput) || !$CSInput) { ?>
<script type="text/javascript">
	document.getElementById('searchbox').style.color = 'gray';
</script>
<?php } ?>
</div>
<div class="separator"></div>
<div class="mirrorbar">
<table width="100%"><tr><td>
</td><td>
<div style="width: 100%; text-align: right;" id="expandcollapse">
<script type="text/javascript">
<!--
document.write('<a href="javascript:;" onclick="javascript:expand_infobar();"><img src="/img/infobarexpand.png" class="infobarbutton"/></a> <a href="javascript:;" onclick="javascript:expand_infobar();" class="plain">Show Advanced Options</a>');
// -->
</script>
<noscript>
Good job, you do not have JavaScript enabled!
</noscript>
</div>
</td></tr></table>
</div>
<div class="infobar" id="infobar">
<?php if($DNSEL_Domain != null){echo '<a class="plain" href="dnsel_server.php">DNSEL Server</a> |';} ?>
<a class="plain" href="tor_exit_query.php">Tor Exit Node Query</a> |
<a class='plain' href='index.php#AppServer'>TorStatus Server Details</a> |
<a class='plain' href='index.php#TorServer'>Opinion Source</a> |
<a class='plain' href='index.php#CustomQuery'>Advanced Query Options</a> |
<a class='plain' href='column_set.php'>Advanced Display Options</a> |
<a class='plain' href='index.php#Stats'>Network Statistic Summary</a> |
<a class='plain' href='network_detail.php'>Network Statistic Graphs</a><br/>
<a class='plain' href='query_export.php/Tor_query_EXPORT.csv'>CSV List of Current Result Set</a> |
<a class='plain' href='ip_list_all.php/Tor_ip_list_ALL.csv'>CSV List of All Current Tor Server IP Addresses</a> |
<a class='plain' href='ip_list_exit.php/Tor_ip_list_EXIT.csv'>CSV List of All Current Tor Server Exit Node IP Addresses</a>
</div>
<script type="text/javascript">
	<!--
	var closetextstart = '<div class="infobar" style="display: none;" id="expandcollapse">';
	var closetexthide = '<a href="javascript:;" onclick="javascript:collapse_infobar();"><img src="/img/infobarcollapse.png" class="infobarbutton"/></a> <a href="javascript:;" onclick="javascript:collapse_infobar();" class="plain">Hide Advanced Options</a>';
	var closetextshow = '<a href="javascript:;" onclick="javascript:expand_infobar();"><img src="/img/infobarexpand.png" class="infobarbutton"/></a> <a href="javascript:;" onclick="javascript:expand_infobar();" class="plain">Show Advanced Options</a>';
	var closetextend = '</div>';
	document.write(closetextstart + closetextshow + closetextend);
	function expand_infobar()
	{
		document.getElementById('infobar').style.display="block";
		document.getElementById('expandcollapse').innerHTML = closetexthide;
	}
	function collapse_infobar()
	{
		document.getElementById('infobar').style.display="none";
		document.getElementById('expandcollapse').innerHTML = closetextshow;
	}
	collapse_infobar();
	// -->
</script>
<?php
//<script type="text/javascript">
//expand_infobar();
//</script>
?>

<div class="content">

<br/><br/>


