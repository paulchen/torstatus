<?php 

// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

// Start new session
@session_start() or die();

// Include configuration settings
include("config.php");

// Include JPGraph items
require_once($JPGraph_Path . "jpgraph.php");
require_once($JPGraph_Path . "jpgraph_line.php");
require_once($JPGraph_Path . "jpgraph_date.php");

// Declare and initialize variables
$MODE = null;

$WriteHistory_DATA_ARRAY = null;
$WriteHistory_INC = null;
$WriteHistory_LAST = null;

$ReadHistory_DATA_ARRAY = null;
$ReadHistory_INC = null;
$ReadHistory_LAST = null;

// Read in submitted variables
if (isset($_GET["MODE"]))
{
	$MODE = $_GET["MODE"];
}

// Perform variable scrubbing
if($MODE != 'WriteHistory' && $MODE != 'ReadHistory')
{
	$MODE = null;
}

// Get variables from session
if (isset($_SESSION["WriteHistory_DATA_ARRAY_SERIALIZED"]))
{
	$WriteHistory_DATA_ARRAY = unserialize($_SESSION['WriteHistory_DATA_ARRAY_SERIALIZED']);
}
if (isset($_SESSION["WriteHistory_INC"]))
{
	$WriteHistory_INC = $_SESSION['WriteHistory_INC'];
}
if (isset($_SESSION["WriteHistory_LAST"]))
{
	$WriteHistory_LAST = $_SESSION['WriteHistory_LAST'];
}

if (isset($_SESSION["ReadHistory_DATA_ARRAY_SERIALIZED"]))
{
	$ReadHistory_DATA_ARRAY = unserialize($_SESSION['ReadHistory_DATA_ARRAY_SERIALIZED']);
}
if (isset($_SESSION["ReadHistory_INC"]))
{
	$ReadHistory_INC = $_SESSION['ReadHistory_INC'];
}
if (isset($_SESSION["ReadHistory_LAST"]))
{
	$ReadHistory_LAST = $_SESSION['ReadHistory_LAST'];
}

// Deal with no data situations
if (count($WriteHistory_DATA_ARRAY) < 2)
{
	$WriteHistory_DATA_ARRAY[0] = 0;
	$WriteHistory_DATA_ARRAY[1] = 0;
}

if (count($ReadHistory_DATA_ARRAY) < 2)
{
	$ReadHistory_DATA_ARRAY[0] = 0;
	$ReadHistory_DATA_ARRAY[1] = 0;
}

if (intval($WriteHistory_INC) < 10)
{
	$WriteHistory_INC = 10;
}

if (intval($ReadHistory_INC) < 10)
{
	$ReadHistory_INC = 10;
}

if ($MODE == "WriteHistory")
{
	// Return WriteData History Graph
	DEFINE('NDATAPOINTS', count($WriteHistory_DATA_ARRAY));
	DEFINE('SAMPLERATE', intval($WriteHistory_INC)); 
	$end = (strtotime($WriteHistory_LAST) + SAMPLERATE);
	$start = $end-NDATAPOINTS*SAMPLERATE;
	$data = array();
	$xdata = array();
	for($i=0; $i < NDATAPOINTS; ++$i) 
	{
	    $data[$i] = intval($WriteHistory_DATA_ARRAY[$i] / intval($WriteHistory_INC));
	    $xdata[$i] = $start + $i * SAMPLERATE;
	}
	$graph_write = new Graph(480,300);
	$graph_write->SetMargin(80,30,30,140);
	$graph_write->SetScale('datlin');
	$graph_write->title->Set("Recent Write History (Bytes/Sec Average) (GMT)");
	$graph_write->title->SetFont(FF_FONT2,FS_BOLD);
	$graph_write->xaxis->SetLabelAngle(90);
	$graph_write->xaxis->scale->SetTimeAlign(MINADJ_15);
	$line_write = new LinePlot($data,$xdata);
	$line_write->SetFillColor('lightblue@0.5');
	$graph_write->Add($line_write);
	$graph_write->Stroke();
}

if ($MODE == "ReadHistory")
{
	// Prepare ReadData History Graph
	DEFINE('NDATAPOINTS', count($ReadHistory_DATA_ARRAY));
	DEFINE('SAMPLERATE', intval($ReadHistory_INC)); 
	$end = (strtotime($ReadHistory_LAST) + SAMPLERATE);
	$start = $end-NDATAPOINTS*SAMPLERATE;
	$data = array();
	$xdata = array();
	for($i=0; $i < NDATAPOINTS; ++$i) 
	{
	    $data[$i] = intval($ReadHistory_DATA_ARRAY[$i] / intval($ReadHistory_INC));
	    $xdata[$i] = $start + $i * SAMPLERATE;
	}
	$graph_read = new Graph(480,300);
	$graph_read->SetMargin(80,30,30,140);
	$graph_read->SetScale('datlin');
	$graph_read->title->Set("Recent Read History (Bytes/Sec Average) (GMT)");
	$graph_read->title->SetFont(FF_FONT2,FS_BOLD);
	$graph_read->xaxis->SetLabelAngle(90);
	$graph_read->xaxis->scale->SetTimeAlign(MINADJ_15);
	$line_read = new LinePlot($data,$xdata);
	$line_read->SetFillColor('lightblue@0.5');
	$graph_read->Add($line_read);
	$graph_read->Stroke();
}

?>
