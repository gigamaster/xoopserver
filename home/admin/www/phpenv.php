<?php

/*
	XOOPS Cube :: XOOPSERVER 5
	Nuno Luciano aka da/gigamaster
	http://xoopserver.com
	31-10-2008
*/

include_once "/home/admin/www/plugins/themeheader.html";

echo "<h1>XOOPSERVER 5 :: Configuration</h1><br>";

function ss_timing_start ($name = 'default') {
    global $ss_timing_start_times;
    $ss_timing_start_times[$name] = explode(' ', microtime());
}

function ss_timing_stop ($name = 'default') {
    global $ss_timing_stop_times;
    $ss_timing_stop_times[$name] = explode(' ', microtime());
}

function ss_timing_current ($name = 'default') {
    global $ss_timing_start_times, $ss_timing_stop_times;
    if (!isset($ss_timing_start_times[$name])) {
        return 0;
    }
    if (!isset($ss_timing_stop_times[$name])) {
        $stop_time = explode(' ', microtime());
    }
    else {
        $stop_time = $ss_timing_stop_times[$name];
    }
    // do the big numbers first so the small ones aren't lost
    $current = $stop_time[1] - $ss_timing_start_times[$name][1];
    $current += $stop_time[0] - $ss_timing_start_times[$name][0];
    return $current;
}

ss_timing_start();
phpinfo();
ss_timing_stop();

echo "<hr><br>
      <div class=\"confirm\">The page was executed in : ".ss_timing_current()." seconds.</div>";

include_once "/home/admin/www/plugins/themefooter.html";

?>