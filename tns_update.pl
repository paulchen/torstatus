#!/usr/bin/perl
#
# tns_update.pl
# Copyright (c) 2007-2008 Kasimir Gabert
# A Perl script designed to update the database of TorStatus for the
# most current information from a local Tor server.
#
#    This program is part of TorStatus
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as published 
#    by the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
# Required Perl packages:
#  * DBI from CPAN to access the MySQL database
#  * IO::Socket::INET from CPAN to communicate with Tor
#  * MIME::Base64 from CPAN (alternately libmime-base64-perl in Debian)
#  * LWP::Simple from CPAN
#  * Date::Parse from CPAN
#  * Geo::IP   ** Geo::IP::PurePerl should be used for those without
#                 access to the C version of GeoIP.
#
# Included Perl packages
#  * serialize.pm
#

# Include the required packages
use DBI;
use IO::Socket::INET;
use PHP::Serialization qw(serialize unserialize);
use Geo::IP;
use MIME::Base64;
use LWP::Simple;
use Date::Parse;
use File::Touch;
use Time::HiRes qw(gettimeofday);
use Parallel::ForkManager;
use POSIX qw(strftime);
use Cache::Memcached;
use IO::Handle;

print gettimeofday() . "\n";

# Set the constant to break out of getting the hostnames
use constant TIMEOUT => 1;
$SIG{ALRM} = sub {die "timeout"};

# Caching constansts for increased speed
my %CACHE;
my %geoIPCache;

# First the configuration file must be read
# All of the variables will be inputed into a hash for ease of use
my %config;
open (my $config_handle, "<", "web/config.php");
while (<$config_handle>)
{
	# A regular expression is going to try to pull out the configuration
	# items
	
	chomp (my $line = $_);
	if ($line =~ /^\$(.*?) = (.*?);/)
	{
		my $item = $1;
		my $data = $2;

		# Remove any quotations around the data
		$data =~ s/'$//;
		$data =~ s/^'//;
		$data =~ s/"$//;
		$data =~ s/^"//;

		# Save the configuration item
		$config{$item} = $data;
	}
}
close ($config_handle);

# Loop through until killed
while (1 == 1)
{

# Find the initial time
my $start_time = time();

my $memcached = new Cache::Memcached {
	'servers' => [ '127.0.0.1:11211' ],
	'debug' => 0,
	'compress_threshold' => 10_000
};
$memcached->enable_compress(0);

# Initiate a connection to the MySQL server
my $dbh = DBI->connect('DBI:mysql:database='.$config{'SQL_Catalog'}.';host='.$config{'SQL_Server'},$config{'SQL_User'},$config{'SQL_Pass'}, {
	PrintError => 0,
	RaiseError => 1
}) or die "Unable to connect to MySQL server";

my $query;
my $dbresponse;
my $record;

# Initiate a connection to the Tor server
my $torSocket = IO::Socket::INET->new(
	PeerAddr 	=> $config{'LocalTorServerIP'},
	PeerPort 	=> $config{'LocalTorServerControlPort'},
	Proto		=> "tcp",
	Type		=> SOCK_STREAM)
	or die "Could not connect to Tor server: $!\n";
my $torLogfile;
open $torLogfile, '>>', '/var/log/tns_update.log';
$torLogfile->autoflush;
print { $torLogfile } strftime("UPDATE STARTED: %Y-%m-%d %H:%M:%S", localtime) . "\n";

# Prepare all of the database information, which Descriptor table, make sure
# database is installed, etc
$query = "SElECT count(*) AS Count FROM Status;";
$dbresponse = $dbh->prepare($query);
$dbresponse->execute();
$record = $dbresponse->fetchrow();

# If the count is less then one, then an initial row needs to be created
if ($record < 1)
{
	die "There was an error with the installation of TorStatus. " .
	"Please make sure that the SQL database has been created.";
}

# Determine which tables should be updated in the next cycle
$query = "SELECT ActiveNetworkStatusTable, ActiveDescriptorTable FROM Status WHERE ID = 1;";
$dbresponse = $dbh->prepare($query);
$dbresponse->execute();
my @record = $dbresponse->fetchrow_array;
my $descriptorTable = 1;
if ($record[0] =~ /1/)
{
	$descriptorTable = 2;
}

#Determine whether or not we need to authenticate with a password to the server
my $torPass = "";
if ($config{'LocalTorServerPassword'} ne "null")
{
	$torPass = " \"" . $config{'LocalTorServerPassword'} . "\"";
}
print $torSocket "AUTHENTICATE${torPass}\r\n";

# Wait for a response
my $response = <$torSocket>;
if ($response !~ /250/)
{
	die "Unable to authenticate with the Tor server.";
}


############ Updating router descriptions ####################################

# Delete all of the records from the descriptor table that is going to be
# modified as well as the DNSEL table
$dbh->do("TRUNCATE TABLE Bandwidth${descriptorTable};");
$dbh->do("TRUNCATE TABLE Descriptor${descriptorTable};");
$dbh->do("TRUNCATE TABLE DNSEL_INACT;");

# Prepare the updating query
$query1 = "INSERT INTO Descriptor${descriptorTable} (Name, IP, ORPort, DirPort, Platform, LastDescriptorPublished, Fingerprint, Uptime, BandwidthMAX, BandwidthBURST, BandwidthOBSERVED, OnionKey, SigningKey, Hibernating, Contact, WriteHistoryLAST, WriteHistoryINC, WriteHistorySERDATA, ReadHistoryLAST, ReadHistoryINC, ReadHistorySERDATA, FamilySERDATA, ExitPolicySERDATA, DescriptorSignature) VALUES ";
my @query1_parts = ();
my @query1_params = ();
# $dbresponse = $dbh->prepare($query);

$query2 = 'INSERT INTO DNSEL_INACT (IP,ExitPolicy) VALUES ';
my @query2_parts = ();
my @query2_params = ();

$query4 = "INSERT INTO Bandwidth${descriptorTable} (fingerprint, `read`, `write`) VALUES ";
my @query4_parts = ();
my @query4_params = ();

# Prepare the DNSEL update
#$query = "INSERT INTO DNSEL_INACT (IP,ExitPolicy) VALUES ( ? , ? );";
#my $dbresponse2 = $dbh->prepare($query);

# Now all of the recent descriptors data needs to be retrieved
my @descAll;
print $torSocket "GETINFO desc/all-recent\r\n";
my $response = <$torSocket>;
unless ($response =~ /250+/) { die "There was an error retrieving descriptors."; }

# Now iterate through each line of response
my %currentRouter;

my $router_count = 0;

# print "3\n";
while (<$torSocket>)
{
	chop(my $line = $_);
	chop($line);

	print { $torLogfile } "$line\n";

	my $router = 0;

	# print "x: $line\n";
	# Trim the line so as to remove odd data
	
	if ($line =~ /250 OK/) { last; } # Break when done

#	print "b\n";
	# Format for the router line:
	# "router" nickname address ORPort SOCKSPort DirPort NL
	if ($line =~ /^router (.*?) (.*?) (.*?) (.*?) (.*?)$/)
	{
		my $nickname = $1;
		my $address = $2;
		my $or = $3;
		my $dir = $5;

		$router_count++;
		
		if($router == 1) {
			print "Now " . $currentRouter{'nickname'} . " and " . $nickname . " get mixed up.\n";
		}

		# Gather the data
		$currentRouter{'nickname'} = $nickname;
		$currentRouter{'address'} = $address;
		$currentRouter{'ORPort'} = $or;
		$currentRouter{'DirPort'} = $dir;
		# Set hibernate because it will be published on demand
		$currentRouter{'Hibernating'} = 0;

		$router = 1;
	}

	# Format for the bandwidth line
	#  "bandwidth" bandwidth-avg bandwidth-burst bandwidth-observed NL
	if ($line =~ /^bandwidth (.*?) (.*?) (.*?)$/)
	{
		$currentRouter{'BandwidthMAX'} = $1;
		$currentRouter{'BandwidthBURST'} = $2;
		$currentRouter{'BandwidthOBSERVED'} = $3;
	}

	# Format for the platform line
	# "platform" string NL
	if ($line =~ /platform (.*?)$/)
	{
		$currentRouter{'Platform'} = $1;
	}

	# Format for the last descriptor published line
	# "published" YYYY-MM-DD HH:MM:SS NL
	if ($line =~ /^published (.*?)$/)
	{
		$currentRouter{'LastDescriptorPublished'} = $1;
	}

	# Format for the fingerprint line
	# "fingerprint" fingerprint NL
	if ($line =~ /fingerprint (.*?)$/)
	{
		# Remove all of the spaces from the fingerprint
		my $fingerprint = $1;
		$fingerprint =~ s/ //g;
		$currentRouter{'Fingerprint'} = $fingerprint;
	}

	# Format for the hibernating line
	# "hibernating" bool NL
	if ($line =~ /hibernating (.*?)$/)
	{
		$currentRouter{'Hibernating'} = $1;
	}
	
	# Format for the uptime line
	# "uptime" number NL
	if ($line =~ /uptime (.*?)$/)
	{
		$currentRouter{'Uptime'} = $1;
	}
	
	# Format for the onion-key line
	# "onion-key" NL a public key in PEM format
	if ($line =~ /onion-key/ && $line !~ /ntor-onion-key/ && $line !~ /crosscert/)
	{
		my $onion_key;
		# Continue to receive lines until the end of the key
		my $current_line;
		my $iteration = 0;
		while ($current_line !~ /-----END RSA PUBLIC KEY-----/)
		{
			$current_line = <$torSocket>;
			print { $torLogfile } "$current_line\n";
			# print "z1: $current_line\n";
			if($iteration == 0 && $current_line !~ /-----BEGIN RSA PUBLIC KEY-----/)
			{
				$line = $current_line;
				break;
			}
			else
			{
				$onion_key .= $current_line;
				$iteration++;
			}
		}
		chomp($onion_key);
		if($onion_key =~ /router/) {
			# print "\n\nkey: $onion_key";
			die;
		}
		$currentRouter{'OnionKey'} = $onion_key;
	}

	# Format for the signing-key line
	# "signing-key" NL a public key in PEM format
	if ($line =~ /signing-key/)
	{
		my $signing_key;
		# Continue to receive lines until the end of the key
		my $current_line;
		my $iteration = 0;
		while ($current_line !~ /-----END RSA PUBLIC KEY-----/)
		{
			$current_line = <$torSocket>;
			print { $torLogfile } "$current_line\n";
			# print "z2: $current_line\n";
			if($iteration == 0 && $current_line !~ /-----BEGIN RSA PUBLIC KEY-----/)
			{
				$line = $current_line;
				break;
			}
			else
			{
				$signing_key .= $current_line;
				$iteration++;
			}
		}
		chomp($signing_key);
		if($signing_key =~ /router/) {
			# print "\n\nkey: $signing_key";
			die;
		}
		$currentRouter{'SigningKey'} = $signing_key;
	}

	# Format for the contact info line
	# "contact" info NL
	if ($line =~ /contact (.*?)$/)
	{
		$currentRouter{'Contact'} = $1;
	}

	# Format for the extra-info-digest line
	# "extra-info-digest" digest NL
	if ($line =~ /extra-info-digest (.*?)$/)
	{
		$currentRouter{'Digest'} = $1;
	}

	# Format for family line
	# "family" names NL
	if ($line =~ /family (.*?)$/)
	{
		my @family = split(/ /,$1);
		$currentRouter{'FamilySERDATA'} = serialize(\@family);
	}

	# Format for either reject or accept line
	# "accept" exitpattern NL
	# "reject" exitpattern NL
	if ($line =~ /^reject/ || $line =~ /^accept/)
	{
		$line =~ s/[^\w\d :\.\*\/\-]//g; 
		$currentRouter{'exitpolicy'} = $currentRouter{'exitpolicy'} . $line . "!";
	}

	# Format for the read-history line
	# "read-history" YYYY-MM-DD HH:MM:SS (NSEC s) NUM,NUM,NUM,NUM,NUM... NL
	if ($line =~ /read-history (.*?) (.*?) \((.*?) s\) (.*?)$/)
	{
		# Format for storing the data:
		# "time:NUM"
		my $time = str2time("$1 $2");
		my $increment = $3;
		# Find and split the numbers
		my @nums = reverse(split(/,/,$4));
		# Loop through the numbers, and attach each to a timestamp
		my $offset = 0;
		my @readhistory;
		foreach my $num (@nums)
		{
			my $numtime = $time - $offset;
			push @readhistory, "$numtime:$num";
			$offset -= $increment;
		}
		$currentRouter{'read'} = join(';',@readhistory);

		# TEMPORARY FOR BACKWARDS COMPATIBILITY
		$currentRouter{'ReadHistoryLAST'} = "$1 $2";
		$currentRouter{'ReadHistoryINC'} = $3;
		# Serialize the last part of the data
		@readhistory = split(/,/,$4);
		$currentRouter{'ReadHistorySERDATA'} = serialize(\@readhistory);

	}

	# Format for the write-history line
	# "write-history" YYYY-MM-DD HH:MM:SS (NSEC s) NUM,NUM,NUM,NUM,NUM... NL
	if ($line =~ /write-history (.*?) (.*?) \((.*?) s\) (.*?)$/)
	{
		# Format for storing the data:
		# "time:NUM"
		my $time = str2time("$1 $2");
		my $increment = $3;
		# Find and split the numbers
		my @nums = reverse(split(/,/,$4));
		# Loop through the numbers, and attach each to a timestamp
		my $offset = 0;
		my @writehistory;
		foreach my $num (@nums)
		{
			my $numtime = $time - $offset;
			push @writehistory, "$numtime:$num";
			$offset -= $increment;
		}
		$currentRouter{'write'} = join(';',@writehistory);
		
		# TEMPORARY FOR BACKWARDS COMPATIBILITY
		$currentRouter{'WriteHistoryLAST'} = "$1 $2";
		$currentRouter{'WriteHistoryINC'} = $3;
		# Serialize the last part of the data
		@writehistory = split(/,/,$4);
		$currentRouter{'WriteHistorySERDATA'} = serialize(\@writehistory);
	}

	# Format for the router-signature line
	# "router-signature" NL Signature NL
	if ($line =~ /router-signature/)
	{
		# This always comes at the very end
		my $signature;
		# Continue to receive lines until the end of the key
		my $current_line;
		while ($current_line !~ /-----END SIGNATURE-----/)
		{
			$current_line = <$torSocket>;
			print { $torLogfile } "$current_line\n";
			# print "z3: $current_line\n";
			$signature .= $current_line;
		}
		chomp($signature);
		$currentRouter{'DescriptorSignature'} = $signature;

		# Serialize the exit policy
		chop $currentRouter{'exitpolicy'};
		my @exitpolicy = split(/!/,$currentRouter{'exitpolicy'});
		$currentRouter{'ExitPolicySERDATA'} = serialize(\@exitpolicy);
		# Create a string for the exit policy as well (for DNSEL)
		my $exitpolicystring = join ('::',@exitpolicy);

		# See if there is no family.  It should be blank, not NULL
		# if there is none
		unless ($currentRouter{'FamilySERDATA'})
		{
			$currentRouter{'FamilySERDATA'} = "";
		}

		if ($currentRouter{'Digest'})
		{
		# If there is a digest, extra information needs to be retrieved
		# for this router
		# A second Tor control stream will be opened
		#
		# TODO reuse connection
		my $digestSocket = IO::Socket::INET->new(
			PeerAddr 	=> $config{'LocalTorServerIP'},
			PeerPort 	=> $config{'LocalTorServerControlPort'},
			Proto		=> "tcp",
			Type		=> SOCK_STREAM)
			or die "Could not connect to Tor server: $!\n";
		# Authenticate with it
		print $digestSocket "AUTHENTICATE${torPass}\r\n";
		# Wait for a response
		my $response = <$digestSocket>;
		if ($response !~ /250/)
		{
			die "Unable to authenticate with the Tor server.";
		}
		# And request the data
		my $digest = ($currentRouter{'Digest'} =~ s/\s+.*$//r);

		my $digestRequest = "GETINFO extra-info/digest/$digest\r\n";
		print { $torLogfile } "DIGEST: $digestRequest";
		print $digestSocket $digestRequest;

		while (<$digestSocket>)
		{
			chop (my $dline = $_);
			print { $torLogfile } "DIGEST: $dline\n";
			chop($dline);
			if ($dline =~ /^250 OK/) { last; } # Break when done
			if ($dline =~ /^552 /) { last; } # Break on error
			
			# Format for the read-history line
			# "read-history" YYYY-MM-DD HH:MM:SS (NSEC s) NUM,NUM,NUM,NUM,NUM... NL
			# TODO dirrec-read-history!
			if ($dline =~ /read-history (.*?) (.*?) \((.*?) s\) (.*?)$/)
			{
				# Format for storing the data:
				# "time:NUM"
				my $time = str2time("$1 $2");
				my $increment = $3;
				# Find and split the numbers
				my @nums = reverse(split(/,/,$4));
				# Loop through the numbers, and attach each to a timestamp
				my $offset = 0;
				my @readhistory;
				foreach my $num (@nums)
				{
					my $numtime = $time - $offset;
					push @readhistory, "$numtime:$num";
					$offset -= $increment;
				}
				# TODO wtf?
				$currentRouter{'read'} = join(';',@readhistory);
			
				# TEMPORARY FOR BACKWARDS COMPATIBILITY
				$currentRouter{'ReadHistoryLAST'} = "$1 $2";
				$currentRouter{'ReadHistoryINC'} = $3;
				# Serialize the last part of the data
				@readhistory = split(/,/,$4);
				$currentRouter{'ReadHistorySERDATA'} = serialize(\@readhistory);
			}
		
			# Format for the write-history line
			# "write-history" YYYY-MM-DD HH:MM:SS (NSEC s) NUM,NUM,NUM,NUM,NUM... NL
			# TODO dirrec-write-history!
			if ($dline =~ /write-history (.*?) (.*?) \((.*?) s\) (.*?)$/)
			{
				# Format for storing the data:
				# "time:NUM"
				my $time = str2time("$1 $2");
				my $increment = $3;
				# Find and split the numbers
				my @nums = reverse(split(/,/,$4));
				# Loop through the numbers, and attach each to a timestamp
				my $offset = 0;
				my @writehistory;
				foreach my $num (@nums)
				{
					my $numtime = $time - $offset;
					push @writehistory, "$numtime:$num";
					$offset -= $increment;
				}
				# TODO wtf?
				$currentRouter{'write'} = join(';',@writehistory);
				
				# TEMPORARY FOR BACKWARDS COMPATIBILITY
				$currentRouter{'WriteHistoryLAST'} = "$1 $2";
				$currentRouter{'WriteHistoryINC'} = $3;
				# Serialize the last part of the data
				@writehistory = split(/,/,$4);
				$currentRouter{'WriteHistorySERDATA'} = serialize(\@writehistory);
			}
		}
		# Close the new Tor connection
		close ($digestSocket);
		}
	
		if($currentRouter{'SigningKey'} == '')
		{
#			$currentRouter{'SigningKey'} = $currentRouter{'OnionKey'};
		}
		if($currentRouter{'OnionKey'} == '')
		{
#			$currentRouter{'OnionKey'} = $currentRouter{'SigningKey'};
		}

		# Save the data to the MySQL database
		my @fields = ('nickname', 'address', 'ORPort', 'DirPort', 'Platform', 'LastDescriptorPublished', 'Fingerprint', 'Uptime', 'BandwidthMAX', 'BandwidthBURST', 'BandwidthOBSERVED', 'OnionKey', 'SigningKey', 'Hibernating', 'Contact', 'WriteHistoryLAST', 'WriteHistoryINC', 'WriteHistorySERDATA', 'ReadHistoryLAST', 'ReadHistoryINC', 'ReadHistorySERDATA', 'FamilySERDATA', 'ExitPolicySERDATA', 'DescriptorSignature');
		foreach(@fields) {
			push(@query1_params, $currentRouter{$_});
		}
		push(@query1_parts, '( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )');

#		if(scalar(@query_parts) > 10) {
#			last;
#		}	
#		$dbresponse->execute( $currentRouter{'nickname'},
#		 $currentRouter{'address'},
#		 $currentRouter{'ORPort'},
#		 $currentRouter{'DirPort'},
#		 $currentRouter{'Platform'},
#		 $currentRouter{'LastDescriptorPublished'},
#		 $currentRouter{'Fingerprint'},
#		 $currentRouter{'Uptime'},
#		 $currentRouter{'BandwidthMAX'},
#		 $currentRouter{'BandwidthBURST'},
#		 $currentRouter{'BandwidthOBSERVED'},
#		 $currentRouter{'OnionKey'},
#		 $currentRouter{'SigningKey'},
#		 $currentRouter{'Hibernating'},
#		 $currentRouter{'Contact'},
#		 $currentRouter{'WriteHistoryLAST'},
#		 $currentRouter{'WriteHistoryINC'},
#		 $currentRouter{'WriteHistorySERDATA'},
#		 $currentRouter{'ReadHistoryLAST'},
#		 $currentRouter{'ReadHistoryINC'},
#		 $currentRouter{'ReadHistorySERDATA'},
#		 $currentRouter{'FamilySERDATA'},
#		 $currentRouter{'ExitPolicySERDATA'},
#		 $currentRouter{'DescriptorSignature'}
#		);

		push(@query4_params, $currentRouter{'Fingerprint'});
		push(@query4_params, $currentRouter{'read'});
		push(@query4_params, $currentRouter{'write'});
		push(@query4_parts, "(?, ?, ?)");
		# Update the read and write bandwidth history
#		updateBandwidth( $currentRouter{'Fingerprint'},
#			$currentRouter{'write'},
#			$currentRouter{'read'},
#			$dbh);

		# Save to the DNSEL table as well
#		$dbresponse2->execute($currentRouter{'address'},$exitpolicystring);
		push(@query2_params, $currentRouter{'address'});
		push(@query2_params, $exitpolicystring);
		push(@query2_parts, '(?, ?)');

		if(scalar(@query1_parts) > 1000) {
			my $query1x = $query1 . join(', ', @query1_parts);
#				print "$query1x\n";
			$dbresponse = $dbh->prepare($query1x);
			$dbresponse->execute(@query1_params);
			@query1_params = ();
			@query1_parts = ();

			my $query2x = $query2 . join(', ', @query2_parts);
#				print "$query2x\n";
			$dbresponse = $dbh->prepare($query2x);
			$dbresponse->execute(@query2_params);
			@query2_params = ();
			@query2_parts = ();

			my $query4x = $query4 . join(', ', @query4_parts);
#				print "$query4x\n";
			$dbresponse = $dbh->prepare($query4x);
			$dbresponse->execute(@query4_params);
			@query4_params = ();
			@query4_parts = ();
		}
		# Clear the old data
		%currentRouter = ();
	}
#	print "a...\n";
}

#print "y...\n";

print "Number of routers: $router_count\n";

if(scalar(@query1_parts) > 0) {
	$query1 .= join(', ', @query1_parts);
	# print "$query1\n";
	$dbresponse = $dbh->prepare($query1);
	$dbresponse->execute(@query1_params) or print "failed query: $query1\n";
	if($dbh->errstr) {
		print "failed query: $query1";
	}
}

if(scalar(@query2_parts) > 0) {
	$query2 .= join(', ', @query2_parts);
	# print "$query2\n";
	$dbresponse = $dbh->prepare($query2);
	$dbresponse->execute(@query2_params);
}

if(scalar(@query4_parts) > 0) {
	$query4 .= join(', ', @query4_parts);
	# print "$query4\n";
	$dbresponse = $dbh->prepare($query4);
	$dbresponse->execute(@query4_params);
}

# print("3\n");

############ Updating network status #########################################

# Geo::IP needs to be loaded
my $gi = Geo::IP->open($config{'GEOIP_Database_Path'} . "GeoIP.dat",GEOIP_STANDARD);

# Delete all of the records from the network status table that is going to be
# modified
$dbh->do("TRUNCATE TABLE NetworkStatus${descriptorTable};");

# Request the network status information
print $torSocket "GETINFO ns/all \r\n";
my $response = <$torSocket>;
unless ($response =~ /250+/) { die "There was an error retrieving the network status."; }

my $query3 = "INSERT INTO NetworkStatus${descriptorTable} (Name,Fingerprint,DescriptorHash,LastDescriptorPublished,IP,Hostname,ORPort,DirPort,FAuthority,FBadDirectory,FBadExit,FExit,FFast,FGuard,FNamed,FStable,FRunning,FValid,FV2Dir,FHSDir,CountryCode) VALUES ";
my @query3_parts = ();
my @query3_params = ();

# Prepare the query so that data entry is faster
#$query = "INSERT INTO NetworkStatus${descriptorTable} (Name,Fingerprint,DescriptorHash,LastDescriptorPublished,IP,Hostname,ORPort,DirPort,FAuthority,FBadDirectory,FBadExit,FExit,FFast,FGuard,FNamed,FStable,FRunning,FValid,FV2Dir,FHSDir,CountryCode) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?);";
#$dhresponse = $dbh->prepare($query);

#print "4\n";
#
my $routers_processed = 0;

while (<$torSocket>)
{
	chop(my $line = $_);
	chop($line);
	# Trim the line so as to remove odd data

	print { $torLogfile } "$line\n";

#	print "y: $line\n";	
	if ($line =~ /250 OK/) { last; } # Break when done

	print gettimeofday() . "\n";

	# Format for the "r" line
	# "r" SP nickname SP identity SP digest SP publication SP IP SP ORPort
	# SP DirPort NL
	
	if ($line =~ /^r (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?)$/ || $line =~ /^\./)
	{
		# If there is previous data, it should be saved now
		#
		#
		$routers_processed++;
		print "Processing router " . $currentRouter{'Nickname'} . " ($routers_processed/$router_count)...\n";

		print "A: " . gettimeofday() . "\n";
		if ($currentRouter{'Nickname'})
		{
			push(@query3_parts, '( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?)');
			push(@query3_params, $currentRouter{'Nickname'});
			push(@query3_params, $currentRouter{'Identity'});
			push(@query3_params, $currentRouter{'Digest'});
			push(@query3_params, $currentRouter{'Publication'});
			push(@query3_params, $currentRouter{'IP'});
			push(@query3_params, $currentRouter{'Hostname'});
			push(@query3_params, $currentRouter{'ORPort'});
			push(@query3_params, $currentRouter{'DirPort'});
			push(@query3_params, ($currentRouter{'Authority'}?1:0));
			push(@query3_params, ($currentRouter{'BadDirectory'}?1:0));
			push(@query3_params, ($currentRouter{'BadExit'}?1:0));
			push(@query3_params, ($currentRouter{'Exit'}?1:0));
			push(@query3_params, ($currentRouter{'Fast'}?1:0));
			push(@query3_params, ($currentRouter{'Guard'}?1:0));
			push(@query3_params, ($currentRouter{'Named'}?1:0));
			push(@query3_params, ($currentRouter{'Stable'}?1:0));
			push(@query3_params, ($currentRouter{'Running'}?1:0));
			push(@query3_params, ($currentRouter{'Valid'}?1:0));
			push(@query3_params, ($currentRouter{'V2Dir'}?1:0));
			push(@query3_params, ($currentRouter{'HSDir'}?1:0));
			push(@query3_params, $currentRouter{'Country'});

			if(scalar(@query3_parts) > 1000) {
				my $query3x = $query3 . join(', ', @query3_parts);
#				print "$query3x\n";
				$dbresponse = $dbh->prepare($query3x);
				$dbresponse->execute(@query3_params);
				@query3_params = ();
				@query3_parts = ();
			}
#			$dhresponse->execute(
#			 $currentRouter{'Nickname'},
#			 $currentRouter{'Identity'},
#			 $currentRouter{'Digest'},
#			 $currentRouter{'Publication'},
#			 $currentRouter{'IP'},
#			 $currentRouter{'Hostname'},
#			 $currentRouter{'ORPort'},
#			 $currentRouter{'DirPort'},
#			 ($currentRouter{'Authority'}?1:0),
#			 ($currentRouter{'BadDirectory'}?1:0),
#			 ($currentRouter{'BadExit'}?1:0),
#			 ($currentRouter{'Exit'}?1:0),
#			 ($currentRouter{'Fast'}?1:0),
#			 ($currentRouter{'Guard'}?1:0),
#			 ($currentRouter{'Named'}?1:0),
#			 ($currentRouter{'Stable'}?1:0),
#			 ($currentRouter{'Running'}?1:0),
#			 ($currentRouter{'Valid'}?1:0),
#			 ($currentRouter{'V2Dir'}?1:0),
#			 ($currentRouter{'HSDir'}?1:0),
#			 $currentRouter{'Country'}
#			);
		
			# Clear the old data
			%currentRouter = ();
		}

		# This makes sure that it is not the last router
		if ($1)
		{
		
		$currentRouter{'Nickname'} = $1;
		$currentRouter{'Identity'} = unpack('H*',decode_base64($2));
		$currentRouter{'Digest'} = $3;
		$currentRouter{'Publication'} = "$4 $5";
		$currentRouter{'IP'} = $6;
		$currentRouter{'ORPort'} = $7;
		$currentRouter{'DirPort'} = $8;

		print "B1: " . gettimeofday() . "\n";
		# We need to find the country of the IP
		if ($geoIPCache{$6})
		{
			$currentRouter{'Country'} = $geoIPCache{$6};
		}
		else
		{
			$currentRouter{'Country'} = $gi->country_code_by_addr($6);
			$geoIPCache{$6} = $currentRouter{'Country'};
		}

		print "B2: " . gettimeofday() . "\n";
		# And the host by addr
#		$currentRouter{'Hostname'} = lookup($6);
		# If the hostname was not found, it should be an IP
#		unless ($currentRouter{'Hostname'})
#		{
#			$currentRouter{'Hostname'} = $6;
#		}
		}
		print "C: " . gettimeofday() . "\n";
	}

	# Format for the "s" line
	# "s" SP Flags NL
	if ($line =~ /^s (.*?)$/)
	{
		my @flags = split(/ /,$1);
		foreach my $flag (@flags)
		{
			$currentRouter{$flag} = 1;
		}
	}
	print gettimeofday() . "\n";
}

if(scalar(@query3_parts) > 0) {
	my $query3x = $query3 . join(', ', @query3_parts);
	#print "$query3x\n";
	$dbresponse = $dbh->prepare($query3x) or print "failed query: $query3x\n";
	$dbresponse->execute(@query3_params) or print "failed query: $query3x\n";
}
$dbh->disconnect();

my $dbh = DBI->connect('DBI:mysql:database='.$config{'SQL_Catalog'}.';host='.$config{'SQL_Server'},$config{'SQL_User'},$config{'SQL_Pass'}, {
	PrintError => 0,
	RaiseError => 1
}) or die "Unable to connect to MySQL server";

####my $pm = Parallel::ForkManager->new(1);

$query = "SELECT Fingerprint, IP FROM NetworkStatus${descriptorTable}";
$dbresponse = $dbh->prepare($query);
$dbresponse->execute();

my $lookup_counter = 0;
DATA_LOOP:
while(@record = $dbresponse->fetchrow_array) {
	$fingerprint = $record[0];
	$ip = $record[1];

	$lookup_counter++;
	print gettimeofday() . ": Looking up $ip ($lookup_counter/$router_count)\n";

####	my $pid = $pm->start and next DATA_LOOP;

####	my $dbhx = DBI->connect('DBI:mysql:database='.$config{'SQL_Catalog'}.';host='.$config{'SQL_Server'},$config{'SQL_User'},$config{'SQL_Pass'}, {
####		PrintError => 0,
####		RaiseError => 1
####	}) or die "Unable to connect to MySQL server";

	my $dbhx = $dbh;

	my $cache_key = "torstatus_host_$ip";
	my $hostname = $memcached->get($cache_key);
	my $cached = 0;
	if($hostname) {
		print gettimeofday() . " Cached entry in memcache found!\n";
	}
	unless ($hostname) {
		$host_query1 = 'SELECT hostname FROM hostnames WHERE ip = ?';
		my $host_dbresponse1 = $dbhx->prepare($host_query1);
		$host_dbresponse1->execute(($ip));
		my @record_dbresponse1 = $host_dbresponse1->fetchrow_array;
		if(@record_dbresponse1) {
			print gettimeofday() . " Cached entry in database found!\n";
			$hostname = $record_dbresponse1[0];
		}
		$host_dbresponse1->finish();
	}
	if($hostname) {
		$cached = 1;
	}
	unless ($hostname) {
		print gettimeofday() . " No cached entry found, executing lookup\n";
		$hostname = lookup($ip);
	}
	# If the hostname was not found, it should be an IP
	unless ($hostname) {
		$hostname = $ip;
	}

	print gettimeofday() . " Hostname: $hostname, fingerprint: $fingerprint, ip: $ip\n";
	$query2 = "UPDATE NetworkStatus${descriptorTable} SET Hostname = ? WHERE Fingerprint = ?";

	my $dbresponse = $dbhx->prepare($query2);
	$dbresponse->execute(($hostname, $fingerprint));
	$dbresponse->finish();

	print gettimeofday() . "\n";
	if(!$cached) {
		$host_query2 = 'INSERT INTO hostnames (ip, hostname) VALUES (?, ?)';
		my $host_dbresponse2 = $dbhx->prepare($host_query2);
		$host_dbresponse2->execute(($ip, $hostname));
		$host_dbresponse2->finish();
	}

	$memcached->set($cache_key, $hostname);

####	$dbhx->disconnect();

	print gettimeofday() . ": Looked up $ip\n";

####	$pm->finish;
}

#### $pm->wait_all_children;

# exit;

my $dbh = DBI->connect('DBI:mysql:database='.$config{'SQL_Catalog'}.';host='.$config{'SQL_Server'},$config{'SQL_User'},$config{'SQL_Pass'}, {
	PrintError => 0,
	RaiseError => 1
}) or die "Unable to connect to MySQL server";

$dbh->do("UPDATE Descriptor${descriptorTable} SET LastDescriptorPublished = NOW() WHERE LastDescriptorPublished > NOW()");
$dbh->do("UPDATE NetworkStatus${descriptorTable} SET LastDescriptorPublished = NOW() WHERE LastDescriptorPublished > NOW()");

# Update the opinion source
# We need to find out who we are
print $torSocket "GETCONF nickname \r\n";
chop (my $line = <$torSocket>);
chop($line);
my $nickname = "UNKNOWNNICK";
if ($line =~ /250 Nickname=(.*?)$/)
{
	$nickname = $1;
}
$dbh->do("TRUNCATE TABLE NetworkStatusSource");
# Prevent multiple usernames from being an issue
# Determine whether a fingerprint is present
my $sourceQuery = "Name = '$nickname'";
if ($config{'SourceFingerprint'})
{
	$sourceQuery = "Fingerprint = '" . $config{'SourceFingerprint'} . "'";
}	
$dbh->do("INSERT INTO NetworkStatusSource SELECT * FROM Descriptor${descriptorTable} WHERE $sourceQuery LIMIT 1;");
# Set the ID back to one
$dbh->do("UPDATE NetworkStatusSource SET ID=1;");

my $end_time = time();

# Set the status to use the new data
$dbh->do("UPDATE Status SET LastUpdate = now(), LastUpdateElapsed = ($end_time-$start_time), ActiveNetworkStatusTable = 'NetworkStatus${descriptorTable}', ActiveDescriptorTable = 'Descriptor${descriptorTable}' WHERE ID = 1;");

# Rename the DNSEL table so it is used
$dbh->do("RENAME TABLE DNSEL TO tmp_table, DNSEL_INACT TO DNSEL, tmp_table TO DNSEL_INACT;");

print { $torLogfile } strftime("UPDATE ENDED: %Y-%m-%d %H:%M:%S", localtime) . "\n";

# Close both the database connection and the Tor server connection
$dbh->disconnect();
close($torSocket);
close($torLogfile);

# print "5\n";

# Sleep for the desired time from the configuration file
#sleep($config{'Cache_Expire_Time'});
# print "6\n";
my @file_list = ('/var/www/TorNetworkStatus/last_update');
touch(@file_list);
exit 0
}

############ Subroutines #####################################################

# This is used to look up hostnames
sub lookup {
    my $ip = shift;
    return $ip unless $ip=~/\d+\.\d+\.\d+\.\d+/;
    unless (exists $CACHE{$ip}) {
        my @h = eval <<'END';
        alarm(TIMEOUT);
        my @i = gethostbyaddr(pack('C4',split('\.',$ip)),2);
        alarm(0);
        @i;
END
    $CACHE{$ip} = $h[0] || undef;
    }
    return $CACHE{$ip} || $ip;
}

# This updates the bandwidth table for a given router
#sub updateBandwidth {
#	my ($fingerprint, $write, $read, $dbh) = @_;
#	my $dbresponse3 = $dbh->prepare("SELECT `read`, `write` FROM `Bandwidth` WHERE `fingerprint` LIKE '$fingerprint'\;");
#	$dbresponse3->execute();
#	my @results = $dbresponse3->fetchrow();
#}
