#!/usr/bin/perl


####################

sub Constant_HTML {
local(*FILE); # filehandle
local($file); # file path
local($HTML); # HTML data

$file = $_[0] || die "There was no file specified!\n";

open(FILE, "<$file") || die "Couldn't open $file!\n";
$HTML = do { local $/; <FILE> }; #read whole file in through slurp #mode (by setting $/ to undef)
close(FILE);

return $HTML;
}

$themeheader = &Constant_HTML('/home/admin/www/plugins/themeheader.html');

$themefooter = &Constant_HTML('/home/admin/www/plugins/themefooter.html');


################

# error.log
$logfile = "/tmp/error.log";
#
####################################


print "$themeheader\n";

open (LOG, "$logfile")|| die "Can't open data file!\n";
@log = <LOG>;
close (LOG);

@log=reverse(@log);
splice @log, 50;

print "<div class=\"confirm\">There is a list of recent messages of the Apache Web Server.</div>\n";

print "<table class=\"outer\">\n";
print "<tr><th>Environment Variables</th></tr>\n";

foreach $logs (@log) {

#if ($logs=~/error/) {
  print "<tr <td class=\"even\">$logs</td></tr>\n";
#}
}

print"</table>\n";
 
print "$themefooter\n";

exit;

