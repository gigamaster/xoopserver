<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */

if (!isset($coming_from_common)) {
   exit;
}

if (function_exists('mcrypt_encrypt') || PMA_dl('mcrypt')) {
  
    require_once './libraries/mcrypt.lib.php';
} else {
    require_once './libraries/blowfish.php';
 
    define('PMA_WARN_FOR_MCRYPT', 1);
}



function PMA_auth()
{
    global $conn_error;

    /* Perform logout to custom URL */
    if (! empty($_REQUEST['old_usr'])
     && ! empty($GLOBALS['cfg']['Server']['LogoutURL'])) {
        PMA_sendHeaderLocation($GLOBALS['cfg']['Server']['LogoutURL']);
        exit;
    }

    if ($GLOBALS['cfg']['LoginCookieRecall']) {
        $default_user   = $GLOBALS['PHP_AUTH_USER'];
        $default_server = $GLOBALS['pma_auth_server'];
        $autocomplete   = '';
    } else {
        $default_user   = '';
        $default_server = '';
        // skip the IE autocomplete feature.
        $autocomplete   = ' autocomplete="off"';
    }

    $cell_align = ($GLOBALS['text_dir'] == 'ltr') ? 'left' : 'right';

    // Defines the charset to be used
    header('Content-Type: text/html; charset=' . $GLOBALS['charset']);
    // Defines the "item" image depending on text direction
    $item_img = $GLOBALS['pmaThemeImage'] . 'item_' . $GLOBALS['text_dir'] . '.png';

    /* HTML header */
    $page_title = 'phpMyAdmin ' . PMA_VERSION;
    require './libraries/header_meta_style.inc.php';
    ?>
<script type="text/javascript">
//<![CDATA[
// show login form in top frame
if (top != self) {
    window.top.location.href=location;
}
//]]>
</script>
</head>

<body class="loginform">

    <?php
    if (file_exists('./config.header.inc.php')) {
          require './config.header.inc.php';
    }
    ?>

<div class="container">
<a href="http://www.phpmyadmin.net" target="_blank" class="logo"><?php
    $logo_image = $GLOBALS['pmaThemeImage'] . 'logo_right.png';
    if (@file_exists($logo_image)) {
        echo '<img src="' . $logo_image . '" id="imLogo" name="imLogo" alt="phpMyAdmin" border="0" />';
    } else {
        echo '<img name="imLogo" id="imLogo" src="' . $GLOBALS['pmaThemeImage'] . 'pma_logo.png' . '" '
           . 'border="0" width="88" height="31" alt="phpMyAdmin" />';
    }
    ?></a>
<h1>
    <?php
    echo sprintf($GLOBALS['strWelcome'],
        '<bdo dir="ltr" xml:lang="en">' . $page_title . '</bdo>');
    ?>
</h1>
    <?php

    // Show error message
    if (! empty($conn_error)) {
        echo '<div class="error"><h1>' . $GLOBALS['strError'] . '</h1>' . "\n";
        echo $conn_error . '</div>' . "\n";
    }

    // Displays the languages form
    if (empty($GLOBALS['cfg']['Lang'])) {
        require_once './libraries/display_select_lang.lib.php';
        PMA_select_language(true);
    }

    // Displays the warning message and the login form
    if (empty($GLOBALS['cfg']['blowfish_secret'])) {
        ?>
        <div class="error"><h1><?php echo $GLOBALS['strError']; ?></h1>
            <?php echo $GLOBALS['strSecretRequired']; ?>
        </div>
        <?php
        echo '</div>' . "\n";
        if (file_exists('./config.footer.inc.php')) {
            require './config.footer.inc.php';
        }
        echo '</body></html>';
        exit;
    }
    ?>
<br />
<!-- Login form -->
<form method="post" action="index.php" name="login_form"<?php echo $autocomplete; ?> target="_top" class="login">
    <fieldset>
    <legend>
<?php 
    echo $GLOBALS['strLogin']; 
    echo '<a href="./Documentation.html" target="documentation" ' .
        'title="' . $GLOBALS['strPmaDocumentation'] . '">';
    if ($GLOBALS['cfg']['ReplaceHelpImg']) {
        echo '<img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 'b_help.png" width="11" height="11" alt="' . $GLOBALS['strPmaDocumentation'] . '" />';
    } else {
        echo '(*)';
    }
    echo '</a>';
?>
</legend>

<?php if ($GLOBALS['cfg']['AllowArbitraryServer']) { ?>
        <div class="item">
            <label for="input_servername"><?php echo $GLOBALS['strLogServer']; ?></label>
            <input type="text" name="pma_servername" id="input_servername" value="<?php echo htmlspecialchars($default_server); ?>" size="24" class="textfield" />
        </div>
<?php } ?>
        <div class="item">
            <label for="input_username"><?php echo $GLOBALS['strLogUsername']; ?></label>
            <input type="text" name="pma_username" id="input_username" value="<?php echo htmlspecialchars($default_user); ?>" size="24" class="textfield" />
        </div>
        <div class="item">
            <label for="input_password"><?php echo $GLOBALS['strLogPassword']; ?></label>
            <input type="password" name="pma_password" id="input_password" value="" size="24" class="textfield" />
        </div>
    <?php
    if (count($GLOBALS['cfg']['Servers']) > 1) {
        ?>
        <div class="item">
            <label for="select_server"><?php echo $GLOBALS['strServerChoice']; ?>:</label>
            <select name="server" id="select_server"
        <?php
        if ($GLOBALS['cfg']['AllowArbitraryServer']) {
            echo ' onchange="document.forms[\'login_form\'].elements[\'pma_servername\'].value = \'\'" ';
        }
        echo '>';

        require_once './libraries/select_server.lib.php';
        PMA_select_server(false, false);

        echo '</select></div>';
    } else {
        echo '    <input type="hidden" name="server" value="' . $GLOBALS['server'] . '" />';
    } // end if (server choice)
    ?>
    </fieldset>
    <fieldset class="tblFooters">
        <input value="<?php echo $GLOBALS['strGo']; ?>" type="submit" />
        <input type="hidden" name="lang" value="<?php echo $GLOBALS['lang']; ?>" />
        <input type="hidden" name="convcharset" value="<?php echo $GLOBALS['convcharset']; ?>" />
    <?php
    if (!empty($GLOBALS['target'])) {
        echo '            <input type="hidden" name="target" value="' . htmlspecialchars($GLOBALS['target']) . '" />' . "\n";
    }
    if (!empty($GLOBALS['db'])) {
        echo '            <input type="hidden" name="db" value="' . htmlspecialchars($GLOBALS['db']) . '" />' . "\n";
    }
    if (!empty($GLOBALS['table'])) {
        echo '            <input type="hidden" name="table" value="' . htmlspecialchars($GLOBALS['table']) . '" />' . "\n";
    }
    ?>
    </fieldset>
    <?php

    if (empty($_COOKIE)) {
        echo '<div class="notice">' . $GLOBALS['strCookiesRequired'] . '</div>' . "\n";
    }
    if (! empty($GLOBALS['PMA_errors']) && is_array($GLOBALS['PMA_errors'])) {
        foreach ($GLOBALS['PMA_errors'] as $error) {
            echo '<div class="error">' . $error . '</div>' . "\n";
        }
    }
    ?>
</div>
<script type="text/javascript">
// <![CDATA[
function PMA_focusInput()
{
    var input_username = document.getElementById('input_username');
    var input_password = document.getElementById('input_password');
    if (input_username.value == '') {
        input_username.focus();
    } else {
        input_password.focus();
    }
}

window.setTimeout('PMA_focusInput()', 500);
// ]]>
</script>
</body>
</html>
    <?php
    if (file_exists('./config.footer.inc.php')) {
         require './config.footer.inc.php';
    }
    exit;
}


function PMA_auth_check()
{
   
    $GLOBALS['pma_auth_server'] = '';

    $GLOBALS['PHP_AUTH_USER'] = $GLOBALS['PHP_AUTH_PW'] = '';
    $GLOBALS['from_cookie'] = false;

    // avoid an error in mcrypt
    if (empty($GLOBALS['cfg']['blowfish_secret'])) {
        return false;
    }

    if (defined('PMA_CLEAR_COOKIES')) {
        foreach($GLOBALS['cfg']['Servers'] as $key => $val) {
            PMA_removeCookie('pmaPass-' . $key);
            PMA_removeCookie('pmaServer-' . $key);
            PMA_removeCookie('pmaUser-' . $key);
        }
        return false;
    }

    if (! empty($_REQUEST['old_usr'])) {
   
        session_destroy(); 
        // -> delete password cookie(s)
        if ($GLOBALS['cfg']['LoginCookieDeleteAll']) {
            foreach($GLOBALS['cfg']['Servers'] as $key => $val) {
                PMA_removeCookie('pmaPass-' . $key);
                if (isset($_COOKIE['pmaPass-' . $key])) {
                    unset($_COOKIE['pmaPass-' . $key]);
                }
            }
        } else {
            PMA_removeCookie('pmaPass-' . $GLOBALS['server']);
            if (isset($_COOKIE['pmaPass-' . $GLOBALS['server']])) {
                unset($_COOKIE['pmaPass-' . $GLOBALS['server']]);
            }
        }
    }

    if (! empty($_REQUEST['pma_username'])) {
        // The user just logged in
        $GLOBALS['PHP_AUTH_USER'] = $_REQUEST['pma_username'];
        $GLOBALS['PHP_AUTH_PW']   = empty($_REQUEST['pma_password']) ? '' : $_REQUEST['pma_password'];
        if ($GLOBALS['cfg']['AllowArbitraryServer']) {
            $GLOBALS['pma_auth_server'] = $_REQUEST['pma_servername'];
        }
        return true;
    }


    if ($GLOBALS['cfg']['AllowArbitraryServer']
     && ! empty($_COOKIE['pmaServer-' . $GLOBALS['server']])) {
        $GLOBALS['pma_auth_server'] = $_COOKIE['pmaServer-' . $GLOBALS['server']];
    }

    if (empty($_COOKIE['pmaUser-' . $GLOBALS['server']])) {
        return false;
    }

    $GLOBALS['PHP_AUTH_USER'] = PMA_blowfish_decrypt(
        $_COOKIE['pmaUser-' . $GLOBALS['server']],
        $GLOBALS['cfg']['blowfish_secret']);

    if (empty($_SESSION['last_access_time'])) {
        return false;
    }

    if ($_SESSION['last_access_time'] < time() - $GLOBALS['cfg']['LoginCookieValidity']) {
        $GLOBALS['no_activity'] = true;
        PMA_auth_fails();
        exit;
    }

    if (empty($_COOKIE['pmaPass-' . $GLOBALS['server']])) {
        return false;
    }

    $GLOBALS['PHP_AUTH_PW'] = PMA_blowfish_decrypt(
        $_COOKIE['pmaPass-' . $GLOBALS['server']],
        $GLOBALS['cfg']['blowfish_secret'] /* . $_SESSION['last_access_time'] */);

    if ($GLOBALS['PHP_AUTH_PW'] == "\xff(blank)") {
        $GLOBALS['PHP_AUTH_PW'] = '';
    }

    $GLOBALS['from_cookie'] = true;

    return true;
} // end of the 'PMA_auth_check()' function


function PMA_auth_set_user()
{
    global $cfg;


    if ($cfg['Server']['user'] != $GLOBALS['PHP_AUTH_USER']) {
        foreach ($cfg['Servers'] as $idx => $current) {
            if ($current['host'] == $cfg['Server']['host']
             && $current['port'] == $cfg['Server']['port']
             && $current['socket'] == $cfg['Server']['socket']
             && $current['ssl'] == $cfg['Server']['ssl']
             && $current['connect_type'] == $cfg['Server']['connect_type']
             && $current['user'] == $GLOBALS['PHP_AUTH_USER']) {
                $GLOBALS['server'] = $idx;
                $cfg['Server']     = $current;
                break;
            }
        } // end foreach
    } // end if

    $pma_server_changed = false;
    if ($GLOBALS['cfg']['AllowArbitraryServer']
     && ! empty($GLOBALS['pma_auth_server'])
     && $cfg['Server']['host'] != $GLOBALS['pma_auth_server']) {
        $cfg['Server']['host'] = $GLOBALS['pma_auth_server'];
        $pma_server_changed = true;
    }
    $cfg['Server']['user']     = $GLOBALS['PHP_AUTH_USER'];
    $cfg['Server']['password'] = $GLOBALS['PHP_AUTH_PW'];

    $_SESSION['last_access_time'] = time();


    PMA_setCookie('pmaUser-' . $GLOBALS['server'],
        PMA_blowfish_encrypt($cfg['Server']['user'],
            $GLOBALS['cfg']['blowfish_secret']));

    PMA_setCookie('pmaPass-' . $GLOBALS['server'],
        PMA_blowfish_encrypt(!empty($cfg['Server']['password']) ? $cfg['Server']['password'] : "\xff(blank)",
            $GLOBALS['cfg']['blowfish_secret'] /* . $_SESSION['last_access_time'] */),
        null,
        $GLOBALS['cfg']['LoginCookieStore']);

    if (! $GLOBALS['from_cookie']) {
        if ($GLOBALS['cfg']['AllowArbitraryServer']) {
            if (! empty($GLOBALS['pma_auth_server'])) {
                // Duration = one month for serverrname
                PMA_setCookie('pmaServer-' . $GLOBALS['server'], $cfg['Server']['host']);
            } else {
                // Delete servername cookie
                PMA_removeCookie('pmaServer-' . $GLOBALS['server']);
            }
        }

        // URL where to go:
        $redirect_url = $cfg['PmaAbsoluteUri'] . 'index.php';

        // any parameters to pass?
        $url_params = array();
        if (strlen($GLOBALS['db'])) {
            $url_params['db'] = $GLOBALS['db'];
        }
        if (strlen($GLOBALS['table'])) {
            $url_params['table'] = $GLOBALS['table'];
        }
        // Language change from the login panel needs to be remembered
        if (! empty($GLOBALS['lang'])) {
            $url_params['lang'] = $GLOBALS['lang'];
        }
        // any target to pass?
        if (! empty($GLOBALS['target']) && $GLOBALS['target'] != 'index.php') {
            $url_params['target'] = $GLOBALS['target'];
        }

        /**
         * whether we come from a fresh cookie login
         */
        define('PMA_COMING_FROM_COOKIE_LOGIN', true);
        PMA_sendHeaderLocation($redirect_url . PMA_generate_common_url($url_params, '&'));
        exit();
    } // end if

    return true;
} 


function PMA_auth_fails()
{
    global $conn_error;

    // Deletes password cookie and displays the login form
    PMA_removeCookie('pmaPass-' . $GLOBALS['server']);

    if (! empty($GLOBALS['allowDeny_forbidden'])) {
        $conn_error = $GLOBALS['strAccessDenied'];
    } elseif (! empty($GLOBALS['no_activity'])) {
        $conn_error = sprintf($GLOBALS['strNoActivity'], $GLOBALS['cfg']['LoginCookieValidity']);
        // Remember where we got timeout to return on same place
        if (PMA_getenv('SCRIPT_NAME')) {
            $GLOBALS['target'] = basename(PMA_getenv('SCRIPT_NAME'));
        }
    } elseif (PMA_DBI_getError()) {
        $conn_error = PMA_sanitize(PMA_DBI_getError());
    } elseif (isset($php_errormsg)) {
        $conn_error = $php_errormsg;
    } else {
        $conn_error = $GLOBALS['strCannotLogin'];
    }

    PMA_auth();
}

?>
