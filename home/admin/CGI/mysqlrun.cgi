#!/usr/bin/perl

require secure;

print "<HTML><HEAD><title>Start MySQL</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\"><META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\"><META HTTP-EQUIV=\"Expires\" CONTENT=\"-1\"></HEAD>";
print "<body><center>\n";

$res=`\\home\\admin\\program\\pv.exe mysqld-opt.exe`;

if ($res=~/mysqld/i)
 {
if ($ENV{'QUERY_STRING'}eq"F"){
 print "MySQL server was forced to start.<br>";
 exec "\\usr\\local\\mysql\\bin\\mysqld-opt.exe --defaults-file=/usr/local/mysql/bin/my-small.cnf";
 } else {
print <<ENDDD;
MySQL was already started.<br>
But if you think that it is mistake push on <a href="$ENV{SCRIPT_NAME}?F">this</a> button<br>
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
 }} else {
 print "Now MySQL server is started.<br>";
print <<ENDDD;
<a href='javascript:window.close();'>Close This Window</a></center>
</center>
<script>
daddy = window.self;
daddy.opener = window.self;
daddy.close();
</script>
</BODY></HTML>
ENDDD
 exec "\\usr\\local\\mysql\\bin\\mysqld-opt.exe --defaults-file=/usr/local/mysql/bin/my-small.cnf";
 }

exit;
#(c) Anatoliy and Taras Slobodskyy