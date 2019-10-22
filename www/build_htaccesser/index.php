<?

include_once "/home/admin/www/plugins/themeheader.html";

echo '<h2>Dot HTAccesser - .htaccess file generator</h2>
<h3>Sometimes it\'s easier online</h3>';

if (isset($_REQUEST['submit']))
{
//export the form submits to global variables
while (isset($_REQUEST)&&(list($k,$v)=each($_REQUEST)))
{ if ($v) { $$k=$v; } } 

$output="";
$options="";
$cgi_hand="";
if (isset($opt_execCGI))
{
$options.=" +execCGI"; 
if (isset($handle_cgi)) { $cgi_hand.=" cgi"; }
if (isset($handle_pl)) { $cgi_hand.=" pl"; }
if (isset($handle_exe)) { $cgi_hand.=" exe"; }
if (isset($handle_sh)) { $cgi_hand.=" sh"; }
if (isset($cgi_hand)) {
$output.="\nAddHandler cgi-script $cgi_hand"; 
}
};

if (isset($opt_include))
{ $options.=" +Includes"; }
else
{
if (isset($opt_includeNOEXEC))
{ $options.=" +IncludeNOEXEC"; }
}

if (isset($opt_FollowSymLinks))
{ $options.=" +FollowSymLinks"; }

if (isset($opt_FollowSymLinksIfOwnerMatch))
{ $options.=" +FollowSymLinksIfOwnerMatch"; }

if (isset($opt_indexes))
{ $options.=" +Indexes"; }

if (isset($opt_multiview))
{ $options.=" +MultiViews";}

if (isset($auth_name)||isset($auth_user)||isset($auth_group))
{ $output.="\nAuthType Basic"; }

if (isset($auth_name))
{ $output.="\nAuthName \"$auth_name\""; }

if (isset($auth_user))
{  $output.="\nAuthUserFile $auth_userpath"; }

if (isset($auth_group))
{  $output.="\nAuthGroupFile $auth_userpath"; }

if (isset($auth_denyall))
{ $output.="\nOrder allow,deny"; }
else
{ $output.="\nOrder deny,allow"; }

if (isset($satisfy_any))
{ $output.="\nSatisfy Any"; }

if (isset($auth_valid_user))
{ $output.="\nRequire valid-user"; }

if (isset($auth_allow_users))
{ $output.="\nRequire user $auth_allow_users"; }

if (isset($auth_allow_groups))
{ $output.="\nRequire group $auth_allow_groups"; }

if (isset($auth_allow_ip))
{ $output.="\nAllow from $auth_allow_ip"; }

if (isset($auth_deny_ip))
{ $output.="\nDeny from $auth_deny_ip"; }

if (isset($mime_types))
{
if (is_array($mime_types))
{
while (list($k,$v)=each($mime_types))
{
$output.="\nAddType $v";
}
} else
{
$output.="\nAddType $mime_types";
}
}

if (isset($opt_includeNOEXEC)||isset($opt_include))
{ 
if (isset($opt_include_ext))
{ $output.="\nAddType text/html $opt_include_ext\nAddHandler server-parsed $opt_include_ext";};
};

if (isset($protect)) {
$output.="\n<Files .htaccess .htpasswd .htuser .htgroups $protect_files>";
$output.="\norder allow,deny\ndeny from all\n</Files>"; 
}


if (isset($redirect)) {
$output.="\nRedirect permanent /$redirect_file $redirect_url";
}

if (isset($force_ssl))
{
$output.="\n<IfModule !mod_ssl.c>";
$output.="\nRedirect permanent / https://$force_ssl_domain/";
$output.="\n</IfModule>";

}

if (isset($no_index)) {
$output.="\nIndexIgnore */*";
}

if (isset($cache))
{
$output.="\nExpiresActive on\nExpiresDefault ";
if (isset($cache_server))
{ $output.="M"; }
else
{ $output.="A"; }
$output.=$cachelength;
}

if (isset($check_media_referrer)) { $modrewrite="true"; }
if (isset($failed_redirect))	{ $modrewrite="true"; }
if (isset($user_dir)) { $modrewrite="true"; }
if (isset($timed_pages)) { $modrewrite="true"; }
if (isset($block_harvesters)) { $modrewrite="true"; }
if (isset($rewrite_browser_page)) { $modrewrite="true"; }
if (isset($remap_script)&&isset($remap_folder)) { $modrewrite="true"; }

if (isset($modrewrite)&&($modrewrite!="false"))
{
$output.="\nRewriteEngine  on";

if (isset($check_media_referrer)) {
$output.="\n".'RewriteCond %{HTTP_REFERER} !^$';
$output.="\n".'RewriteCond %{HTTP_REFERER} !^http://(www\.)?'.$referrer_domain.'/.*$ [NC]';
$output.="\n".'RewriteRule \.(gif|jpg|png|mp3|mpg|avi|mov)$ - [F]  ';
}

if (isset($failed_redirect))
{
$output.="\n".'RewriteCond   %{REQUEST_URI} !-U';
$output.="\n".'RewriteRule   ^(.+)          http://'.$failed_redirect_server.'/$1';
}

if (isset($user_dir)) {
$user_domain=str_replace('.','\.',$user_domain);
$output.="\n".'RewriteCond   %{HTTP_HOST}                 ^www\.[^.]+\.'.$user_domain.'$';
$output.="\n".'RewriteRule   ^(.+)                        %{HTTP_HOST}$1          [C]';
$output.="\n".'RewriteRule   ^www\.([^.]+)\.'.$user_domain.'(.*) /'.$user_dir_path.'$1$2';
}

if (isset($timed_pages))
{
$timed_page=str_replace('.','\.',$timed_page);
$output.="\n".'RewriteCond   %{TIME_HOUR}%{TIME_MIN} >'.$timed_page_start;
$output.="\n".'RewriteCond   %{TIME_HOUR}%{TIME_MIN} <'.$timed_page_end;
$output.="\n".'RewriteRule   ^'.$timed_page.'$	'.$timed_page_day;
$output.="\n".'RewriteRule   ^'.$timed_page.'$	'.$timed_page_night;
}
if (isset($block_harvesters)) {
$output.="\nRewriteCond %{HTTP_USER_AGENT} Wget [OR] ";
$output.="\nRewriteCond %{HTTP_USER_AGENT} CherryPickerSE [OR] ";
$output.="\nRewriteCond %{HTTP_USER_AGENT} CherryPickerElite [OR] ";
$output.="\nRewriteCond %{HTTP_USER_AGENT} EmailCollector [OR] ";
$output.="\nRewriteCond %{HTTP_USER_AGENT} EmailSiphon [OR] ";
$output.="\nRewriteCond %{HTTP_USER_AGENT} EmailWolf [OR] ";
$output.="\nRewriteCond %{HTTP_USER_AGENT} ExtractorPro ";
$output.="\nRewriteRule ^.*$ $block_doc [L]";
}

if (isset($rewrite_browser_page))
{ //rewrite browser pages
$rw_page='^'.str_replace('.','\.',$rewrite_browser_page).'$';
if (isset($geoip_country))
{
$output.="\nRewriteCond %{ENV:GEOIP_COUNTRY_CODE} $geoip_country [NC]";
$output.="\nRewriteRule $rw_page $geoip_page [L]\n";
}
if (isset($rewrite_browser_page_ns))
{
$output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Mozilla/[345].*Gecko*';
$output.="\nRewriteRule $rw_page $rewrite_browser_page_ns [L]\n";
}
if (isset($rewrite_browser_page_ie))
{
$output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Mozilla/[345].*MSIE*';
$output.="\nRewriteRule $rw_page $rewrite_browser_page_ie [L]\n";
}
if (isset($rewrite_browser_page_lynx))
{
$output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Mozilla/[12].* [OR]';
$output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Lynx/*';
$output.="\nRewriteRule $rw_page $rewrite_browser_page_lynx [L]\n";
}

if (isset($rewrite_browser_page_default))
{
$output.="\nRewriteRule $rw_page $rewrite_browser_page_default [L]\n";
}

}
if (isset($remap_script)&&isset($remap_folder))
{
$output.="\nRewriteRule $remap_folder(.*) /$remap_script$1 [PT]";
}
}
if (isset($error_400)) { $output.="\nErrorDocument 400 $error_400"; }
if (isset($error_401)) { $output.="\nErrorDocument 401 $error_401"; }
if (isset($error_403)) { $output.="\nErrorDocument 403 $error_403"; }
if (isset($error_404)) { $output.="\nErrorDocument 404 $error_404"; }
if (isset($error_500)) { $output.="\nErrorDocument 500 $error_500"; }
if (isset($default_page)) { $output.="\nDirectoryIndex $default_page"; }

if ($options) { $output="Options $options\n".$output; }
?>


<h3>Your .htaccess file contents</h3>
<p>Copy the lines below and paste them into your .htaccess file</p>

<textarea cols=80 rows=20><?=$output;?></textarea>
<?php };?>

<form method="post" action="<?=$_SERVER["PHP_SELF"]?>">

<table class="outer">
<tr>
<th colspan="2">Default Page</th>
</tr>
<tr>
<td class="head">What page to load if the user doesn't specify any (usually index.html or index.php)</td>
<td class="odd">
Directory Index : <input name="default_page" type="text" size="40">
<br />
Can specify multiple in a list (ie index.php index.html index.htm default.htm</td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Options</th>
</tr>
<tr>
<td width="50%" class="head" >Execution of CGI scripts using mod_cgi is permitted.</td>
<td class="odd">
<input type=checkbox name="opt_execCGI" value="false"> execute CGI programs</td>
</tr>
<tr>
<td class="head" >File Extensions</td>
<td class="even"> 
<input type=checkbox name="handle_cgi" value="false"> .cgi
<br /><input type=checkbox name="handle_pl" value="false"> .pl
<br /><input type=checkbox name="handle_exe" value="false"> .exe
<br /><input type=checkbox name="handle_sh" value="false"> .sh  </td>
</tr>
<tr>   
<td class="head">Server-side includes provided by mod_include are permitted. </td>
<td class="odd">
<input type=checkbox name="opt_include" value="false"> include files (SSI)
<br /><input type=checkbox name="opt_includeNOEXEC" value="false"> or without #exec 
<br /></td>
</tr>
<tr>
  <td class="head">File extension</td>
  <td class="even"><input type=text name="opt_include_ext" value="shtml"></td>
</tr>
<tr>
<td class="head">The server will follow symbolic links in this directory. </td>
<td class="odd"><input type=checkbox name="opt_FollowSymLinks" value="false"> Follow Symbolic Links</td>
</tr>
<tr>
<td class="head">The server will only follow symbolic links for which the target file or directory is owned by the same user id as the link. </td>
<td class="even"><input type=checkbox name="opt_SymLinksIfOwnerMatch" value="false"> Follow Symbolic Links if owner matches</td>
</tr>
<tr>
<td class="head">If a URL which maps to a directory is requested, and there is no DirectoryIndex (e.g., index.html) in that directory, then mod_autoindex will return a formatted listing of the directory.        </td>
<td class="odd"> <input type=checkbox name="opt_indexes" value="false"> Indexes</td>
</tr>
<tr>
<td class="head">Content negotiated "MultiViews" are allowed using mod_negotiation. </td>
<td class="even"> <input type=checkbox name="opt_multiview" value="false"> Content Negotiation (MultiViews)</td>
</tr>
<tr>
<td class="head">Force HTTP requests to redirect HTTPS</td>
<td class="odd"> <input type=checkbox name="force_ssl" value="false"> Force SSL<br />		</td>
</tr>
<tr>
<td class="head">SSL Domain</td>
<td class="even"><input type=text name="force_ssl_domain" value="www.domain.com" size="40"></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Authentication</th>
</tr>
<tr>
<td class="head" width="50%">Define Access Permissions</td>
<td class="odd">
<input type=checkbox name="auth_denyall" value="false">
Deny by default<br>
<input type=checkbox name="auth_valid_user" value="false"> 
Require valid username<br>
<input type=checkbox name="satisfy_any" value="false">
All if user OR ip matches<br>
<input type=checkbox name="auth_user" value="false">
User Authentication<br>
<input type=checkbox name="auth_group" value="false"> Group Authentication
</td>
</tr>

<tr>
<td class="head">Area Name</td>
<td class="even"> <input type=text name="auth_name" size="40"></td>
</tr>
<tr>
<td class="head">path to users file</td>
<td class="odd"><input type=text name="auth_userpath" size="40"> </td>
</tr>
<tr>
<td class="head">path to groups file</td>
<td class="even"><input type=text name="auth_grouppath" size="40"> </td>
</tr>
<tr>
<td class="head">Allowed Users</td>
<td class="odd"><input type=text name="auth_allow_users" value="" size="40"></td>
</tr>
<tr>
<td class="head">Allowed Groups</td>
<td class="even"><input type=text name="auth_allow_groups" value="" size="40"></td>
</tr>
<tr>
<td class="head">Allowed IP addresses (wildcards and names allowed)</td>
<td class="odd"><input type=text name="auth_allow_ip" value="" size="40"></td>
</tr>
<tr>
<td class="head">Blocked IP addresses (wildcards and names allowed)</td>
<td class="even"><input type=text name="auth_deny_ip" value="" size="40"></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Additional Mime Types</td>  </tr>
<tr>
<td class="head" width="50%"><br />
<p align="center">File extension to mime type mappings are in the following format: </p>
<br />
<div style="width:50%; margin:5px auto;"> mime/type ext 
<br /> for example 
<br /> text/html html 
<br /> application/x-gzip gz<br />
</div></td>
<td class="odd">
<p>
<select name="mime_types[]" size="12" multiple=true>
<?
$fp=fopen("./mime.types","r");
if ($fp)
{ while (!feof($fp))
{
$line=trim(fgets($fp,4096));
$ext=strstr($line," ");
echo "<option value=\"$line\">$ext</option>";
}
fclose($fp);
}
?>
</select>
</p></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Protect System Files</th>
</tr>
<tr>
<td class="head" width="50%">Additional files to protect</td>
<td class="odd">
<input type=text name="protect_files" size="40"></td>
</tr>
<tr>
<td class="head">&nbsp;</td>
<td class="even"><input type=checkbox name="protect"> Protect .htaccess and user and group files</td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">File Cache Control</th>
</tr>
<tr>
<td width="50%" class="head">How often will the client/proxy refresh the file</td>
<td class="odd"><input type=checkbox name="cache"> Specify File Cache Time</td>
</tr>
<tr>
<td class="head">Expire all clients/proxies at the same time</td>
<td class="even"><input type=checkbox name="cache_server"> Modification Based</td>
</tr>
<tr>
<td class="head">Cache Time</td>
<td class="odd">
<select name=cachelength>
<OPTION VALUE="31536000">1 Year</OPTION>
<OPTION VALUE="15768000">6 Months</OPTION>
<OPTION VALUE="78844000">3 Months</OPTION>
<OPTION VALUE="2592000">1 Month</OPTION>
<OPTION VALUE="604800" SELECTED>1 Week</OPTION>
<OPTION VALUE="86400">1 Day</OPTION>
<OPTION VALUE="3600">1 Hour</OPTION>
<OPTION VALUE="60">1 Minutes</OPTION>
</select>
</td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">ModRewrite</th>
</tr>
<tr>
<td class="head" width="50%">Protect Media Files<br />
Check the referrer domain for images, music, and sound files</td>
<td class="odd"><input type=checkbox name="check_media_referrer"> On</td>
</tr>
<tr>
<td class="head">Allowed Domain:</td>
<td class="even"><input type=text name="referrer_domain" value="yourdomain.com" size="40"></td>
</tr>
<tr>
<td class="head">Block E-mail Harvesters<br />
Deny access to e-mail harvesting programs.</td>
<td class="odd">
<input type=checkbox name="block_harvesters"> On</td>
</tr>
<tr>
<td class="head">Page to server:</td>
<td class="even"> <input type=text name="block_doc" value="deny.html" size="40"></td>
</tr>
<tr>
<td class="head">Time-Dependant Page<br />
Serve pages depending on time of day</td>
<td class="odd"><input type=checkbox name="timed_pages"> On</td>
</tr>
<tr>
<td class="head">Page Name : </td>
<td class="even"><input type=text name="timed_page" value="page.html" size="40"></td>
</tr>
<tr>
<td class="head">Daytime Starts :</td>
<td class="odd"><input type=text name="timed_page_start" value="0600" size="40"></td>
</tr>
<tr>
<td class="head">Daytime Ends   :</td>
<td class="even"> <input type=next name="timed_page_end" value="1800" size="40"></td>
</tr>
<tr>
<td class="head">Daytime Page  :</td>
<td class="odd"> <input type=text name="timed_page_day" value="page.day.html" size="40"><br /></td>
</tr>
<tr>
<td class="head">Nighttime Page :</td>
<td class="even"> <input type=text name="timed_page_night" value="page.night.html" size="40"></td>
</tr>
<tr>
<td class="head">Virtual DNS to Folder<br />
Rewrite Virtual Subdomains to subfolders.  Ie: rewrite www.foo.domain.com to www.domain.com/subdomains/foo.  Useful for virtual user domains.</td>
<td class="odd"><input type=checkbox name="user_dir"> On</td>
</tr>
<tr>
<td class="head">Base Domain:</td>
<td class="even"><input type=text name="user_domain" value="domain.com" size="40"></td>
</tr>
<tr>
<td class="head">Path to Folders:</td>
<td class="odd"><input type=text name="user_dir_path" value="home" size="40"></td>
</tr>
<tr>
<td class="head">Redirect Failing URLs To Other Webserver<br />
When a URL is invalid, or would produce an error, redirect to a secondary server.</th>
<td class="even">
On: <input type=checkbox name="failed_redirect"><br /></td>
</tr>
<tr>
<td class="head">Secondary Server:
<td class="odd"><input type=text name="failed_redirect_server" value="server2.domain.com"></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2"><h3>Rewrite Condition</h3></th>
</tr>
<tr>
<td class="head" width="50%">Rewrite Page requested in the URL. Page Name :</td>
<td class="odd"><input type=text name="rewrite_browser_page" size="40"><br /></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Browser Dependant Page</th>
</tr>
<tr>
<td class="head" width="50%">Pagefor Netscape</td>
<td class="odd"><input type=text name="rewrite_browser_page_ns" size="40"></td>
</tr>
<tr>
<td class="head">Page to use for IE</td>
<td class="even"><input type=text name="rewrite_browser_page_ie" size="40"></td>
</tr>
<tr>
<td class="head">Page for Lynx. Use textmode.</td>
<td class="odd"> <input type=text name="rewrite_browser_page_lynx" size="40"></td>
</tr>
<tr>
<td class="head">Default Page used by other browsers</td>
<td class="even"> <input type=text name="rewrite_browser_page_default" size="40"></td>
</tr>
</table>
<br />
<br />
<table class="outer">     
<tr>
<th colspan="2">Country Specific Page</th>
</tr>
<tr>
<td colspan="2" class="odd">Requires the <a href="http://www.maxmind.com/app/mod_geoip">mod_geoip</a> is setup and configured on your server.  Thought the software is free, the datafiles are a commercial product.  Allows you to redirect users to specific pages depending on their country of origin.</td>
</tr>         
<tr>
<td class="head" width="50%">
Country Code<br />
US = United States, GB = United Kingdom, CA = Canada, MX = Mexico, FR = France, NL = Netherlands, A1 = Anonymous<br /></td>
<td class="even"><input type=text name="geoip_country" size="40"></td>
</tr>           
<tr>
<td class="head">Country Specific URLpage to redirect visitors from the country (index.us.html or index.fr.html)</td>
<td class="odd">
<input type=text name="geoip_page" size="40"></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Map Folder To Script</th>
</tr>
<tr>
<td colspan="2" class="odd">This trick will make it possible to run a script that has parameters in the URL.  You can make a custom home page script for your users that they can access like /login/home.html or /login/preferences.html and have them both go to login.php.</td>
</tr>
<tr>
<td class="head" width="50%">Folder Name<br />Folder you will reference in your href and urls (ie login)</td>
<td class="even"><input type=text name="remap_folder" size="40"></td>
</tr>
<tr>
<td class="head">Script Name<br />Script that will be ran (ie login.php, login.cgi, or login.pl)  If you would like the rest of the path as a POST variable, do something like "login.php?page="</td>
<td class="odd"><input type=text name="remap_script" size="40"></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Custom Error Documents</th></tr>
<tr><td colspan="2" class="odd">Allows you to specify custom documents to serve on error conditions</td>
</tr>
<tr>
<td class="head" width="50%">Error 400 - Bad Request</td>
<td class="even"><input type=text name=error_400 size="40"></td>
</tr>
<tr>
<td class="head">Error 401 - Authentication Required</td>
<td class="odd"><input type=text name=error_401 size="40"></td>
</tr>
<tr>
<td class="head">Error 403 - Forbidden</td>
<td class="even"><input type=text name=error_403 size="40"></td>
</tr>
<tr>
<td class="head">Error 404 - Not Found</td>
<td class="odd"><input type=text name=error_404 size="40"></td>
</tr>
<tr>
<td class="head">Error 500 - Server Error </td>
<td class="even"><input type=text name=error_500 size="40"></td>
</tr>
</table>
<br />
<br />
<table class="outer">
<tr>
<th colspan="2">Redirection</th>
</tr>
<tr>
<td class="head" width="50%">Use this option if a document has been moved to a new url.  <br />
It will take care of automatic redirection for the user</td>
<td class="odd"><input type=checkbox name="redirect">
Redirect Moved Document</td>
<tr>
<td class="head">Moved Document</td>
<td class="even"><input type=text name=redirect_file size="40"></td>
</tr>
<tr>
<td class="head">New URL</td>
<td class="odd"><input type=text name=redirect_url size="40"></td>
</tr>
</table>
<br />
<br />
<center><input type=reset name=reset value="Clear Form"><input type=submit name=submit value="Generate .htaccess files"></center>
</form>
<br />
<br />
<p align="right">
dot htaccesser provided by <a href="http://www.bitesizeinc.net/">Bite Size, Inc</a></p>
<?php

include_once "/home/admin/www/plugins/themefooter.html";

?>