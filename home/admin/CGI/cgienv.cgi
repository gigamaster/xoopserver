#!/usr/bin/perl

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


###############

sub urldecode{ 
local($val)=@_; 
$val=~s/\+/ /g; 
$val=~s/%([0-9A-H]{2})/pack('C',hex($1))/ge; 
return $val;
}
print "Content-Type: text/html\n\n";

print "$themeheader\n";

print "<div class=\"centerCblockTitle\">XOOPSERVER CGI</div>\n"; 
print "<table class=\"outer\">\n";
print "<tr><th colspan=\"2\">Environment Variables</th></tr>\n";

foreach $env_var (keys %ENV) 
{ 
print "<tr><td class=\"head\">$env_var</td><td class=\"even\">$ENV{$env_var}</td></tr>\n";
}

print"</table>\n";
 
print "$themefooter\n";

