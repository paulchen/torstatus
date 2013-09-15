<?php
$directory = opendir(dirname(__FILE__) . '/web/img/flags/');
$filenames = array();
while(($filename = readdir($directory)) !== false) {
	if(preg_match('/^[0-9a-z]+\.gif$/', $filename)) {
		$filenames[] = $filename;
	}
}
closedir($directory);

sort($filenames);
$total_height = 0;
$output = fopen(dirname(__FILE__) . '/web/css/flags.css', 'w');

foreach($filenames as $filename) {
	$country = substr($filename, 0, strlen($filename)-4);

	$size = getimagesize(dirname(__FILE__) . "/web/img/flags/$filename");
	$width = $size[0];
	$height = $size[1];

	fputs($output, "div.flag_$country { display: inline-block; height: {$height}px; width: {$width}px; background-image: url(/img/flags.gif); background-position: 0 -{$total_height}px; }\n");

	$total_height += $height;
}
fclose($output);
chdir(dirname(__FILE__) . '/web/img/flags/');

system('convert -append ' . implode(' ', $filenames) . ' ../flags.gif');

