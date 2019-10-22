<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */

require_once './libraries/Theme.class.php';

/**
 *
 */
class PMA_Theme_Manager
{
    var $_themes_path;
    var $themes = array();
    var $cookie_name = 'pma_theme';
    var $per_server = false;
    var $active_theme = 'xoopscube';
    var $theme = null;
    var $theme_default = 'xoopscube';

    function __construct()
    {
        $this->init();
    }

    function setThemesPath($path)
    {
        if (! $this->_checkThemeFolder($path)) {
            return false;
        }

        $this->_themes_path = trim($path);
        return true;
    }

    function getThemesPath()
    {
        return $this->_themes_path;
    }

    function setThemePerServer($per_server)
    {
        $this->per_server  = (bool) $per_server;
    }

    function init()
    {
        $this->themes = array();
        $this->theme_default = 'xoopscube';
        $this->active_theme = '';

        if (! $this->setThemesPath($GLOBALS['cfg']['ThemePath'])) {
            return false;
        }

        $this->setThemePerServer($GLOBALS['cfg']['ThemePerServer']);

        $this->loadThemes();

        $this->theme = new PMA_Theme;


        if (! $this->checkTheme($GLOBALS['cfg']['ThemeDefault'])) {
            $GLOBALS['PMA_errors'][] = sprintf($GLOBALS['strThemeDefaultNotFound'],
                htmlspecialchars($GLOBALS['cfg']['ThemeDefault']));
            trigger_error(
                sprintf($GLOBALS['strThemeDefaultNotFound'],
                    htmlspecialchars($GLOBALS['cfg']['ThemeDefault'])),
                E_USER_WARNING);
            $GLOBALS['cfg']['ThemeDefault'] = false;
        }

        $this->theme_default = $GLOBALS['cfg']['ThemeDefault'];

        if (! $this->getThemeCookie()
         || ! $this->setActiveTheme($this->getThemeCookie())) {
            if ($GLOBALS['cfg']['ThemeDefault']) {
                $this->setActiveTheme($GLOBALS['cfg']['ThemeDefault']);
            } else {
                $this->setActiveTheme('original');
            }
        }
    }

    function checkConfig()
    {
        if ($this->_themes_path != trim($GLOBALS['cfg']['ThemePath'])
         || $this->theme_default != $GLOBALS['cfg']['ThemeDefault']) {
            $this->init();
        } else {
            $this->loadThemes();
        }
    }

    function setActiveTheme($theme = null)
    {
        if (! $this->checkTheme($theme)) {
            $GLOBALS['PMA_errors'][] = sprintf($GLOBALS['strThemeNotFound'],
                htmlspecialchars($theme));
            return false;
        }

        $this->active_theme = $theme;
        $this->theme = $this->themes[$theme];
        return true;
    }
    function getThemeCookieName()
    {
        if (isset($GLOBALS['server']) && $this->per_server) {
            return $this->cookie_name . '-' . $GLOBALS['server'];
        } else {
            return $this->cookie_name;
        }
    }

    function getThemeCookie()
    {
        if (isset($_COOKIE[$this->getThemeCookieName()])) {
            return $_COOKIE[$this->getThemeCookieName()];
        }

        return false;
    }

    function setThemeCookie()
    {
        PMA_setCookie($this->getThemeCookieName(), $this->theme->id,
            $this->theme_default);
        $_SESSION['PMA_Config']->set('theme-update', $this->theme->id);
        return true;
    }
    function PMA_Theme_Manager()
    {
        $this->__construct();
    }

    /*private*/ function _checkThemeFolder($folder)
    {
        if (! is_dir($folder)) {
            $GLOBALS['PMA_errors'][] =
                sprintf($GLOBALS['strThemePathNotFound'],
                    htmlspecialchars($folder));
            trigger_error(
                sprintf($GLOBALS['strThemePathNotFound'],
                    htmlspecialchars($folder)),
                E_USER_WARNING);
            return false;
        }

        return true;
    }

    function loadThemes()
    {
        $this->themes = array();

        if ($handleThemes = opendir($this->getThemesPath())) {
            while (false !== ($PMA_Theme = readdir($handleThemes))) {
                if (array_key_exists($PMA_Theme, $this->themes)) {
                    continue;
                }
                $new_theme = PMA_Theme::load($this->getThemesPath() . '/' . $PMA_Theme);
                if ($new_theme) {
                    $new_theme->setId($PMA_Theme);
                    $this->themes[$PMA_Theme] = $new_theme;
                }
            } // end get themes
            closedir($handleThemes);
        } else {
            trigger_error(
                'phpMyAdmin-ERROR: cannot open themes folder: ' . $this->getThemesPath(),
                E_USER_WARNING);
            return false;
        } // end check for themes directory

        ksort($this->themes);
        return true;
    }

    function checkTheme($theme)
    {
        if (! array_key_exists($theme, $this->themes)) {
            return false;
        }

        return true;
    }

    function getHtmlSelectBox($form = true)
    {
        $select_box = '';

        if ($form) {
            $select_box .= '<form name="setTheme" method="post" action="index.php"'
                .' target="_parent">';
            $select_box .=  PMA_generate_common_hidden_inputs();
        }

        $theme_selected = FALSE;
        $theme_preview_path= './themes.php';
        $theme_preview_href = '<a href="' . $theme_preview_path . '" target="themes" onclick="'
                            . "window.open('" . $theme_preview_path . "','themes','left=10,top=20,width=510,height=350,scrollbars=yes,status=yes,resizable=yes');"
                            . '">';
        $select_box .=  $theme_preview_href . $GLOBALS['strTheme'] . '</a>:' . "\n";

        $select_box .=  '<select name="set_theme" xml:lang="en" dir="ltr"'
            .' onchange="this.form.submit();" >';
        foreach ($this->themes as $each_theme_id => $each_theme) {
            $select_box .=  '<option value="' . $each_theme_id . '"';
            if ($this->active_theme === $each_theme_id) {
                $select_box .=  ' selected="selected"';
            }
            $select_box .=  '>' . htmlspecialchars($each_theme->getName()) . '</option>';
        }
        $select_box .=  '</select>';

        if ($form) {
            $select_box .=  '<noscript><input type="submit" value="' . $GLOBALS['strGo'] . '" /></noscript>';
            $select_box .=  '</form>';
        }

        return $select_box;
    }
    function makeBc()
    {
        $GLOBALS['theme']           = $this->theme->getId();
        $GLOBALS['pmaThemePath']    = $this->theme->getPath();
        $GLOBALS['pmaThemeImage']   = $this->theme->getImgPath();

        if (@file_exists($GLOBALS['pmaThemePath'] . 'layout.inc.php')) {
            include $GLOBALS['pmaThemePath'] . 'layout.inc.php';
        }


    }
    function printPreviews()
    {
        foreach ($this->themes as $each_theme) {
            $each_theme->printPreview();
        } // end 'open themes'
    }
    function getFallBackTheme()
    {
        if (isset($this->themes['original'])) {
            return $this->themes['original'];
        }

        return false;
    }

    function printCss($type)
    {
        if ($this->theme->loadCss($type)) {
            return true;
        }

        $fallback_theme = $this->getFallBackTheme();
        if ($fallback_theme && $fallback_theme->loadCss($type)) {
            return true;
        }

        return false;
    }
}
?>
