<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>TorStatus - Tor Network Status</title>
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<link rel="stylesheet" type="text/css" href="css/flags.css" />
	<!--[if lt IE 7.]>
	<script defer type="text/javascript" src="/js/pngfix.js"></script>
	<![endif]-->
</head>

<body>
<div>
<?php
$directory = opendir('img/flags');
$filenames = array();
while(($filename = readdir($directory)) !== false) {
	if(preg_match('/^[0-9a-z]+\.gif$/', $filename)) {
		$filenames[] = substr($filename, 0, strlen($filename)-4);
	}
}
closedir($directory);
sort($filenames);
foreach($filenames as $filename): ?>
	<div class="flag_<?php echo $filename ?>"></div>&nbsp;<?php echo $filename; ?><br />
<?php endforeach; ?>
</div>
</body>
</html>

