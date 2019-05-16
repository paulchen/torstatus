<?php

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

// Start new session
@session_start() or die();

// Include configuration settings
include("config.php");

// Include JPGraph items
require_once($JPGraph_Path . "jpgraph.php");
require_once($JPGraph_Path . "jpgraph_bar.php");

// Declare and initialize variables
$DATA_ARRAY = null;
$LABEL_ARRAY = null;
$Title = null;
$Legend = null;

// Get variables from session
if (isset($_SESSION["PlatformGraph_DATA_ARRAY_SERIALIZED"]))
{
	$DATA_ARRAY = unserialize($_SESSION['PlatformGraph_DATA_ARRAY_SERIALIZED']);
}
else
{
	http_response_code(400);
	die();
}
if (isset($_SESSION["PlatformGraph_LABEL_ARRAY_SERIALIZED"]))
{
	$LABEL_ARRAY = unserialize($_SESSION['PlatformGraph_LABEL_ARRAY_SERIALIZED']);
}
else
{
	http_response_code(400);
	die();
}
if (isset($_SESSION["PlatformGraph_Title"]))
{
	$Title = $_SESSION['PlatformGraph_Title'];
}
else
{
	http_response_code(400);
	die();
}
if (isset($_SESSION["PlatformGraph_Legend"]))
{
	$Legend = $_SESSION['PlatformGraph_Legend'];
}

$graph = new Graph(564,300,'auto');
$graph->SetMargin(40,10,30,30);
$graph->SetScale("textlin");
$graph->xaxis->SetTickLabels($LABEL_ARRAY);
#$graph->xaxis->SetLabelAngle(90);
$graph->title->Set($Title);
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$bar = new BarPlot($DATA_ARRAY);
$bar->SetLegend($Legend);
$bar->SetShadow();
$bar->value->Show();
$bar->value->SetFormat('%d');
$graph->Add($bar);
$graph->Stroke();

?>
