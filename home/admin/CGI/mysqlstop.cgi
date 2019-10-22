#!/usr/bin/perl

require secure;

print "<HTML><HEAD><title>Stop MySQL</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\"><META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\"><META HTTP-EQUIV=\"Expires\" CONTENT=\"-1\"></HEAD>";
print "<body><center>\n";

$res=`\\home\\admin\\program\\pv.exe mysqld-opt.exe`;

if ($res=~/mysqld/i){
 system "\\usr\\local\\mysql\\bin\\mysqladmin.exe -u root shutdown";
 print "MySQL server is stopped.<br>
 <script>
 daddy = window.self;
 daddy.opener = window.self;
 daddy.close();
 </script>";

 } else {
if ($ENV{'QUERY_STRING'}eq"F"){
 system "\\usr\\local\\mysql\\bin\\mysqladmin.exe -u root shutdown";
 print "MySQL server was forced to stop.<br>";
 } else {
print <<ENDDD;
MySQL server was not running.<br>
But if you think that it is a mistake push on <a href="$ENV{SCRIPT_NAME}?F">this</a> button<br>
ENDDD
 }}

print <<ENDDD;
<script language="JavaScript">
<!--
function close_window() {
daddy = window.self;
daddy.opener = window.self;
daddy.close();
}
//-->
</script>
<br><center><a href="#" onClick="close_window()">Close This Window</a></center>
</BODY></HTML>
ENDDD
exit;
#(c) Anatoliy and Taras Slobodskyy