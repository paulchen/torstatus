<?php

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

// Start new session
session_start();

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
if (isset($_SESSION["CCGraph_DATA_ARRAY_SERIALIZED"]))
{
	$DATA_ARRAY = unserialize($_SESSION['CCGraph_DATA_ARRAY_SERIALIZED']);
}
if (isset($_SESSION["CCGraph_LABEL_ARRAY_SERIALIZED"]))
{
	$LABEL_ARRAY = unserialize($_SESSION['CCGraph_LABEL_ARRAY_SERIALIZED']);
}
if (isset($_SESSION["CCGraph_Title"]))
{
	$Title = $_SESSION['CCGraph_Title'];
}
if (isset($_SESSION["CCGraph_Legend"]))
{
	$Legend = $_SESSION['CCGraph_Legend'];
}

$graph = new Graph(1144,398,'auto');
$graph->SetMargin(40,10,30,30);
$graph->SetScale("textlin");
$graph->xaxis->SetTickLabels($LABEL_ARRAY);
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
