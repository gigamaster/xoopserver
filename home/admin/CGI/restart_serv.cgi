#!/usr/bin/perl

print "Content-Type: text/html\n\n";
print "<HTML><HEAD><title>Restart</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\"></HEAD>";
print "<body><center>\n";

if ($ENV{'QUERY_STRING'}eq"M"){
$res=`net top mysql`;
$res=`net start mysql`;

print "The MySQL server restarted.<br>";
 }

if ($ENV{'QUERY_STRING'}ne""){
print <<ENDDD;
<script>
daddy = window.self;
daddy.opener = window.self;
daddy.close();
</script>
</BODY></HTML>
ENDDD
}

if  ($ENV{'QUERY_STRING'}eq"A"){

print "The Apache server is restarting.<br>";

$res=`net stop Apache2`;
exec "net start Apache2";
exit;
 }
print <<ENDDD;
This script will restart the servers.<br>
It may take some time.<br>
I am sure: <a href="$ENV{SCRIPT_NAME}?A">Restart Apache2</a><br><br>
I am sure: <a href="$ENV{SCRIPT_NAME}?M">Restart MySQL</a>
<br><br>
<a href="javascript:window.close();">Close This Window</a></center>
</center>
</BODY></HTML>
ENDDD
exit;
#(c) Anatoliy and Taras Slobodskyy