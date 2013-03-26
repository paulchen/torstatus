#!/usr/bin/perl
#
# plot.pl for TorStatus
# Copyright (c) Kasimir Gabert 2007
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
# Required Perl packages:
#  * CGI from CPAN to communicate easily with requests from the web server
#  * PHP::Session from CPAN to communicate easily with the PHP 
#    parts of TorStatus
#  * Date::Parse from CPAN
#  * Date::Format from CPAN
#  * GD::Graph from CPAN to create the graphs
#
# Included Perl Packages
#  * serialize.pm

use GD::Graph::mixed;
use PHP::Session;
use Date::Parse;
use Date::Format;
use CGI;
use serialize;

my $cgi = new CGI;

# Retrieve the variables and information from CGI
my $plot_type = $cgi->param('plottype');

my $data_session;
my $label_session;
my $x_label;
my $y_label;
my $title;
my $width = "1160";
my $height = "418";
my $x_labels_vertical = 0;
my $type = ["bars"];
my $y_long_ticks = 1;
my $show_values = 1;
my $x_label_skip = 1;
my $inc;
my $last;

# Make sure that the session exists

my $session_name = $cgi->cookie("PHPSESSID");
if (!$session_name || !$plot_type)
{
	print "Content-type: text/html\n\n";
	print "N/A\n";
	exit;
}

# Start the session
my $session = PHP::Session->new($session_name, { save_path => "/var/lib/php5/" });

if ($plot_type eq "cc")
{
	$data_session = "CCGraph_DATA_ARRAY_SERIALIZED";
	$label_session = "CCGraph_LABEL_ARRAY_SERIALIZED";
	$x_label = "Country Code";
	$y_label = "Number of Routers";
#	$title = "Number of Routers by Country Code";
}
elsif ($plot_type eq "cce")
{
	$data_session = "CCExitGraph_DATA_ARRAY_SERIALIZED";
	$label_session = "CCExitGraph_LABEL_ARRAY_SERIALIZED";
	$x_label = "Country Code";
	$y_label = "Number of Exit Routers";
#	$title = "Number of Exit Routers by Country Code";
}
elsif ($plot_type eq "up")
{
	$data_session = "UptimeGraph_DATA_ARRAY_SERIALIZED";
	$label_session = "UptimeGraph_LABEL_ARRAY_SERIALIZED";
	$x_label = "Uptime (weeks)";
	$y_label = "Number of Routers";
#	$title = "Number of Routers by Uptime (weeks)";
}
elsif ($plot_type eq "bw")
{
	$data_session = "BWGraph_DATA_ARRAY_SERIALIZED";
	$label_session = "BWGraph_LABEL_ARRAY_SERIALIZED";
	$x_label = "Bandwidth Range (KB/s)";
	$y_label = "Number of Routers";
#	$title = "Number of Routers by Observed Bandwidth (KB/s)";
	$width = "564";
	$height = "300";
	$x_labels_vertical = 1;
}
elsif ($plot_type eq "os")
{
	$data_session = "PlatformGraph_DATA_ARRAY_SERIALIZED";
	$label_session = "PlatformGraph_LABEL_ARRAY_SERIALIZED";
	$x_label = "Platform";
	$y_label = "Number of Routers";
#	$title = "Number of Routers by Platform";
	$width = "564";
	$height = "300";
}
elsif ($plot_type eq "sum")
{
	$data_session = "SummaryGraph_DATA_ARRAY_SERIALIZED";
	$label_session = "SummaryGraph_LABEL_ARRAY_SERIALIZED";
	$x_label = "";
	$y_label = "Number of Routers";
#	$title = "Number of Routers Matching Specified Criteria";
}
elsif ($plot_type eq "rtw" || $plot_type eq "rtr")
{
	$label_session = "";
	$x_label = "Time";
	$y_label = "Bytes/Sec";
	$x_labels_vertical = 1;
	if ($plot_type eq "rtw")
	{
		$data_session = "WriteHistory_DATA_ARRAY_SERIALIZED";
		$title = "Recent Write History (Bytes/Sec Average)";
		$last = $session->get('WriteHistory_LAST');
		$inc = $session->get('WriteHistory_INC');
	}
	else
	{
		$data_session = "ReadHistory_DATA_ARRAY_SERIALIZED";
		$title = "Recent Read History (Bytes/Sec Average)";
		$last = $session->get('ReadHistory_LAST');
		$inc = $session->get('ReadHistory_INC');
	}
	$width = '480';
	$height = '400';
	$show_values = 0;
	$type = ["area"];
}

# Retrieve and format the variables from the PHP session
my $data_s = $session->get($data_session);
my $label_s = $session->get($label_session);
my %data_h = %{unserialize($data_s)};
my @data;
my @sorted = sort { $a <=> $b } keys %data_h;
#	print "Content-type: text/html\n\n";
foreach my $v (@sorted)
{
#	print $v . " " . $data_h{$v}, "<br/>";
	push @data, $data_h{$v};
}
#exit;
my @label;
if ($plot_type ne "rtw" && $plot_type ne "rtr")
{
	my %label_h = %{unserialize($label_s)};
	@sorted = sort { $a <=> $b } keys %label_h;
	foreach my $k (@sorted)
	{
		push @label, $label_h{$k};
	}
}
else
{
	my $num = scalar(@data);
	$x_label_skip = int($num/12);
	my $lastnum = str2time($last);
	my $end = $lastnum + $inc;
	my $start = $end - $num*$inc;
	for (my $x = 0; $x < $num; $x++)
	{
		my @time = localtime($start+$x*$inc);
		push @label, strftime('%D %H:%M', @time);
	}
	for (my $i = 0; $i < scalar(@data); $i++)
	{
		$data[$i] = $data[$i]/$inc;
	}
}
my @plot = [[@label],[@data]];

my $graph = GD::Graph::mixed->new($width, $height);

$graph->set(
    x_label     => $x_label,
    y_label     => $y_label,
    title       => $title,
    fgclr	=> 'yellow',
    accentclr	=> 'black',
    shadowclr	=> 'black',
    valuesclr	=> 'black',
    dclrs	=> ['blue'],
    bar_spacing	=> '3',
    x_label_position	=> '.5',
    shadow_depth	=> '0',
    t_margin	=> '40',
    b_margin	=> '10',
    l_margin	=> '30',
    r_margin	=> '30',
#    y_number_format	=> '%d',
    show_values	=> $show_values,
    values_vertical => 0,
    transparent	=> 0,
    x_label_skip => $x_label_skip,
    x_labels_vertical	=> $x_labels_vertical,
    y_long_ticks	=> $y_long_ticks,
    types	=> $type,
) or warn $graph->error;
my $image = $graph->plot(@plot) or warn $graph->error;

print "Content-type: image/png\n\n";
print $image->png;

