package secure;

print "Content-Type: text/html\n\n";

# If you wand disable security check - remark next line.

# if ($ENV{HTTP_REFERER}!~/^http[s]?:\/\/localhost\/a/){print "<a href=\"http://localhost/a/\">Security alert!</a><br>Possible attack HTTP_REFERER is not localhost.<br><br> (to disable go: w:/home/admin/CGI/Secure.pm)</center></body></html>";exit;};

return 1;