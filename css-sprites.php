<?php
$sprites = array('flags', 'os', 'status');

$output = fopen(dirname(__FILE__) . '/web/css/sprites.css', 'w');
$total_height = 0;
$full_filenames = array();
foreach($sprites as $sprite) {
	$directory = opendir(dirname(__FILE__) . "/web/img/$sprite/");
	$filenames = array();
	while(($filename = readdir($directory)) !== false) {
		if(preg_match('/^[0-9a-z]+\.gif$/i', $filename)) {
			$filenames[] = $filename;
		}
		if(preg_match('/^[0-9a-z]+\.png$/i', $filename)) {
			$filenames[] = $filename;
		}
	}
	closedir($directory);

	sort($filenames);

	foreach($filenames as $filename) {
		$country = substr($filename, 0, strlen($filename)-4);

		$size = getimagesize(dirname(__FILE__) . "/web/img/$sprite/$filename");
		$width = $size[0];
		$height = $size[1];

		fputs($output, "div.{$sprite}_$country { display: inline-block; height: {$height}px; width: {$width}px; background-image: url(/img/sprites.png); background-position: 0 -{$total_height}px; }\n");

		$total_height += $height;
		$full_filenames[] = "$sprite/$filename";
	}
}
fclose($output);

chdir(dirname(__FILE__) . '/web/img/');
system('convert -append ' . implode(' ', $full_filenames) . ' sprites.png');

