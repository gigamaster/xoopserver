#!/usr/bin/perl

if ($ENV{'QUERY_STRING'}eq"F"){
print "Location: http://localhost/a/\n\n";

 deletex ("/www");
 deletex ("/cgi-bin");
 coppy("/home/admin/CGI/.htaccess","/www/.htaccess");
 coppy("/home/admin/CGI/.htaccess","/cgi-bin/.htaccess");
 } else {
print "Content-Type: text/html\n\n";
print "<HTML><HEAD><title>Delete files...</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\"><META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\"><META HTTP-EQUIV=\"Expires\" CONTENT=\"-1\"></HEAD>";
print <<ENDDD;
This will delete context of \\cgi-bin\\ and \\www\\<br>
Are you sure: <a href="$ENV{SCRIPT_NAME}?F">Yes. Continue</a><br><br>
<a href="/">Back</a></center>
</center>
</BODY></HTML>
ENDDD
}
exit;

# usege deletex (path);
sub deletex {
 my @names;
 my $name;
 my ($path)=$_[0] ;
 opendir DIR,"$path";
 $name=readdir DIR;
 $name=readdir DIR;

 @names=readdir DIR;
 closedir DIR;
 foreach $name (@names){
  if (-d "$path/$name"){deletex ("$path/$name"); rmdir ("$path/$name");};
  @namess=split(/\./, $name);
  if (!(-d "$path/$name")&&(!($skip=~ /@namess[$namess+1]/)||(@namess[$namess+1]eq''))){
  unlink "$path/$name";
   }
  }
 }

sub coppy {
   open (FILE,"$_[0]");
   binmode FILE;
   @lines=<FILE>;
   close (FILE);
   open (FILE,">$_[1]");
   binmode FILE;
   print FILE @lines;
   close (FILE);
}

#(c) Anatoliy and Taras Slobodskyy