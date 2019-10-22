<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @version $Id: display_tbl.lib.php 10524 2007-07-24 11:39:54Z lem9 $
 */

/**
 *
 */
require_once './libraries/Table.class.php';



function PMA_setDisplayMode(&$the_disp_mode, &$the_total)
{
    global $db, $table;
    global $unlim_num_rows, $fields_meta;
    global $err_url;

    // 1. Initializes the $do_display array
    $do_display              = array();
    $do_display['edit_lnk']  = $the_disp_mode[0] . $the_disp_mode[1];
    $do_display['del_lnk']   = $the_disp_mode[2] . $the_disp_mode[3];
    $do_display['sort_lnk']  = (string) $the_disp_mode[4];
    $do_display['nav_bar']   = (string) $the_disp_mode[5];
    $do_display['ins_row']   = (string) $the_disp_mode[6];
    $do_display['bkm_form']  = (string) $the_disp_mode[7];
    $do_display['text_btn']  = (string) $the_disp_mode[8];
    $do_display['pview_lnk'] = (string) $the_disp_mode[9];

    // 2. Display mode is not "false for all elements" -> updates the
    // display mode
    if ($the_disp_mode != 'nnnn000000') {
        // 2.0 Print view -> set all elements to false!
        if (isset($GLOBALS['printview']) && $GLOBALS['printview'] == '1') {
            $do_display['edit_lnk']  = 'nn'; // no edit link
            $do_display['del_lnk']   = 'nn'; // no delete link
            $do_display['sort_lnk']  = (string) '0';
            $do_display['nav_bar']   = (string) '0';
            $do_display['ins_row']   = (string) '0';
            $do_display['bkm_form']  = (string) '0';
            $do_display['text_btn']  = (string) '0';
            $do_display['pview_lnk'] = (string) '0';
        }
        // 2.1 Statement is a "SELECT COUNT", a
        //     "CHECK/ANALYZE/REPAIR/OPTIMIZE", an "EXPLAIN" one or
        //     contains a "PROC ANALYSE" part
        elseif ($GLOBALS['is_count'] || $GLOBALS['is_analyse'] || $GLOBALS['is_maint'] || $GLOBALS['is_explain']) {
            $do_display['edit_lnk']  = 'nn'; // no edit link
            $do_display['del_lnk']   = 'nn'; // no delete link
            $do_display['sort_lnk']  = (string) '0';
            $do_display['nav_bar']   = (string) '0';
            $do_display['ins_row']   = (string) '0';
            $do_display['bkm_form']  = (string) '1';
            if ($GLOBALS['is_maint']) {
                $do_display['text_btn']  = (string) '1';
            } else {
                $do_display['text_btn']  = (string) '0';
            }
            $do_display['pview_lnk'] = (string) '1';
        }
        // 2.2 Statement is a "SHOW..."
        elseif ($GLOBALS['is_show']) {
            /**
             * 2.2.1
             * @todo defines edit/delete links depending on show statement
             */
            $tmp = preg_match('@^SHOW[[:space:]]+(VARIABLES|(FULL[[:space:]]+)?PROCESSLIST|STATUS|TABLE|GRANTS|CREATE|LOGS|DATABASES|FIELDS)@i', $GLOBALS['sql_query'], $which);
            if (isset($which[1]) && strpos(' ' . strtoupper($which[1]), 'PROCESSLIST') > 0) {
                $do_display['edit_lnk'] = 'nn'; // no edit link
                $do_display['del_lnk']  = 'kp'; // "kill process" type edit link
            } else {
                // Default case -> no links
                $do_display['edit_lnk'] = 'nn'; // no edit link
                $do_display['del_lnk']  = 'nn'; // no delete link
            }
            // 2.2.2 Other settings
            $do_display['sort_lnk']  = (string) '0';
            $do_display['nav_bar']   = (string) '0';
            $do_display['ins_row']   = (string) '0';
            $do_display['bkm_form']  = (string) '1';
            $do_display['text_btn']  = (string) '1';
            $do_display['pview_lnk'] = (string) '1';
        }

        else {
            $prev_table = $fields_meta[0]->table;
            $do_display['text_btn']  = (string) '1';
            for ($i = 0; $i < $GLOBALS['fields_cnt']; $i++) {
                $is_link = ($do_display['edit_lnk'] != 'nn'
                            || $do_display['del_lnk'] != 'nn'
                            || $do_display['sort_lnk'] != '0'
                            || $do_display['ins_row'] != '0');
                // 2.3.2 Displays edit/delete/sort/insert links?
                if ($is_link
                    && ($fields_meta[$i]->table == '' || $fields_meta[$i]->table != $prev_table)) {
                    $do_display['edit_lnk'] = 'nn'; // don't display links
                    $do_display['del_lnk']  = 'nn';
                    /**
                     * @todo May be problematic with same fields names in two joined table.
                     */
                    // $do_display['sort_lnk'] = (string) '0';
                    $do_display['ins_row']  = (string) '0';
                    if ($do_display['text_btn'] == '1') {
                        break;
                    }
                } // end if (2.3.2)
                // 2.3.3 Always display print view link
                $do_display['pview_lnk']    = (string) '1';
                $prev_table = $fields_meta[$i]->table;
            } // end for
        } // end if..elseif...else (2.1 -> 2.3)
    } // end if (2)

    // 3. Gets the total number of rows if it is unknown
    if (isset($unlim_num_rows) && $unlim_num_rows != '') {
        $the_total = $unlim_num_rows;
    } elseif (($do_display['nav_bar'] == '1' || $do_display['sort_lnk'] == '1')
             && (strlen($db) && !empty($table))) {
        $the_total   = PMA_Table::countRecords($db, $table, true);
    }

    if ($do_display['nav_bar'] == '1' || $do_display['sort_lnk'] == '1') {

  
        if (isset($unlim_num_rows) && $unlim_num_rows < 2 && ! PMA_Table::isView($db, $table)) {
            // garvin: force display of navbar for vertical/horizontal display-choice.
            // $do_display['nav_bar']  = (string) '0';
            $do_display['sort_lnk'] = (string) '0';
        }
    } // end if (3)

    // 5. Updates the synthetic var
    $the_disp_mode = join('', $do_display);

    return $do_display;
} // end of the 'PMA_setDisplayMode()' function



function PMA_displayTableNavigation($pos_next, $pos_prev, $sql_query)
{
    global $db, $table, $goto;
    global $num_rows, $unlim_num_rows;
    global $is_innodb;
    global $showtable;

    // here, using htmlentities() would cause problems if the query
    // contains accented characters
    $html_sql_query = htmlspecialchars($sql_query);

    /**
     * @todo move this to a central place
     * @todo for other future table types
     */
    $is_innodb = (isset($showtable['Type']) && $showtable['Type'] == 'InnoDB');

    ?>

<!-- Navigation bar -->
<table border="0" cellpadding="2" cellspacing="0">
<tr>
    <?php
    // Move to the beginning or to the previous page
    if ($_SESSION['userconf']['pos'] && $_SESSION['userconf']['max_rows'] != 'all') {
        // loic1: patch #474210 from Gosha Sakovich - part 1
        if ($GLOBALS['cfg']['NavigationBarIconic']) {
            $caption1 = '&lt;&lt;';
            $caption2 = ' &lt; ';
            $title1   = ' title="' . $GLOBALS['strPos1'] . '"';
            $title2   = ' title="' . $GLOBALS['strPrevious'] . '"';
        } else {
            $caption1 = $GLOBALS['strPos1'] . ' &lt;&lt;';
            $caption2 = $GLOBALS['strPrevious'] . ' &lt;';
            $title1   = '';
            $title2   = '';
        } // end if... else...
        ?>
<td>
    <form action="sql.php" method="post">
        <?php echo PMA_generate_common_hidden_inputs($db, $table); ?>
        <input type="hidden" name="sql_query" value="<?php echo $html_sql_query; ?>" />
        <input type="hidden" name="pos" value="0" />
        <input type="hidden" name="goto" value="<?php echo $goto; ?>" />
        <input type="submit" name="navig" value="<?php echo $caption1; ?>"<?php echo $title1; ?> />
    </form>
</td>
<td>
    <form action="sql.php" method="post">
        <?php echo PMA_generate_common_hidden_inputs($db, $table); ?>
        <input type="hidden" name="sql_query" value="<?php echo $html_sql_query; ?>" />
        <input type="hidden" name="pos" value="<?php echo $pos_prev; ?>" />
        <input type="hidden" name="goto" value="<?php echo $goto; ?>" />
        <input type="submit" name="navig" value="<?php echo $caption2; ?>"<?php echo $title2; ?> />
    </form>
</td>
        <?php
    } // end move back
    ?>
<td>
    &nbsp;&nbsp;&nbsp;
</td>
<td align="center">
<?php // if displaying a VIEW, $unlim_num_rows could be zero because
      // of $cfg['MaxExactCountViews']; in this case, avoid passing
      // the 5th parameter to checkFormElementInRange() 
      // (this means we can't validate the upper limit ?>
    <form action="sql.php" method="post"
onsubmit="return (checkFormElementInRange(this, 'session_max_rows', '<?php echo str_replace('\'', '\\\'', $GLOBALS['strInvalidRowNumber']); ?>', 1) &amp;&amp; checkFormElementInRange(this, 'pos', '<?php echo str_replace('\'', '\\\'', $GLOBALS['strInvalidRowNumber']); ?>', 0<?php echo $unlim_num_rows > 0 ? ',' . $unlim_num_rows - 1 : ''; ?>))">
        <?php echo PMA_generate_common_hidden_inputs($db, $table); ?>
        <input type="hidden" name="sql_query" value="<?php echo $html_sql_query; ?>" />
        <input type="hidden" name="goto" value="<?php echo $goto; ?>" />
        <input type="submit" name="navig" value="<?php echo $GLOBALS['strShow']; ?> :" />
        <input type="text" name="session_max_rows" size="3" value="<?php echo (($_SESSION['userconf']['max_rows'] != 'all') ? $_SESSION['userconf']['max_rows'] : $GLOBALS['cfg']['MaxRows']); ?>" class="textfield" onfocus="this.select()" />
        <?php echo $GLOBALS['strRowsFrom'] . "\n"; ?>
        <input type="text" name="pos" size="6" value="<?php echo (($pos_next >= $unlim_num_rows) ? 0 : $pos_next); ?>" class="textfield" onfocus="this.select()" />
        <br />
    <?php
    // Display mode (horizontal/vertical and repeat headers)
    $param1 = '            <select name="disp_direction">' . "\n"
            . '                <option value="horizontal"' . (($_SESSION['userconf']['disp_direction'] == 'horizontal') ? ' selected="selected"': '') . '>' . $GLOBALS['strRowsModeHorizontal'] . '</option>' . "\n"
            . '                <option value="horizontalflipped"' . (($_SESSION['userconf']['disp_direction'] == 'horizontalflipped') ? ' selected="selected"': '') . '>' . $GLOBALS['strRowsModeFlippedHorizontal'] . '</option>' . "\n"
            . '                <option value="vertical"' . (($_SESSION['userconf']['disp_direction'] == 'vertical') ? ' selected="selected"': '') . '>' . $GLOBALS['strRowsModeVertical'] . '</option>' . "\n"
            . '            </select>' . "\n"
            . '           ';
    $param2 = '            <input type="text" size="3" name="repeat_cells" value="' . $_SESSION['userconf']['repeat_cells'] . '" class="textfield" />' . "\n"
            . '           ';
    echo '    ' . sprintf($GLOBALS['strRowsModeOptions'], "\n" . $param1, "\n" . $param2) . "\n";
    ?>
    </form>
</td>
<td>
    &nbsp;&nbsp;&nbsp;
</td>
    <?php
    // Move to the next page or to the last one
    if (($_SESSION['userconf']['pos'] + $_SESSION['userconf']['max_rows'] < $unlim_num_rows) && $num_rows >= $_SESSION['userconf']['max_rows']
        && $_SESSION['userconf']['max_rows'] != 'all') {
        // loic1: patch #474210 from Gosha Sakovich - part 2
        if ($GLOBALS['cfg']['NavigationBarIconic']) {
            $caption3 = ' &gt; ';
            $caption4 = '&gt;&gt;';
            $title3   = ' title="' . $GLOBALS['strNext'] . '"';
            $title4   = ' title="' . $GLOBALS['strEnd'] . '"';
        } else {
            $caption3 = '&gt; ' . $GLOBALS['strNext'];
            $caption4 = '&gt;&gt; ' . $GLOBALS['strEnd'];
            $title3   = '';
            $title4   = '';
        } // end if... else...
        echo "\n";
        ?>
<td>
    <form action="sql.php" method="post">
        <?php echo PMA_generate_common_hidden_inputs($db, $table); ?>
        <input type="hidden" name="sql_query" value="<?php echo $html_sql_query; ?>" />
        <input type="hidden" name="pos" value="<?php echo $pos_next; ?>" />
        <input type="hidden" name="goto" value="<?php echo $goto; ?>" />
        <input type="submit" name="navig" value="<?php echo $caption3; ?>"<?php echo $title3; ?> />
    </form>
</td>
<td>
    <form action="sql.php" method="post"
        onsubmit="return <?php echo (($_SESSION['userconf']['pos'] + $_SESSION['userconf']['max_rows'] < $unlim_num_rows && $num_rows >= $_SESSION['userconf']['max_rows']) ? 'true' : 'false'); ?>">
        <?php echo PMA_generate_common_hidden_inputs($db, $table); ?>
        <input type="hidden" name="sql_query" value="<?php echo $html_sql_query; ?>" />
        <input type="hidden" name="pos" value="<?php echo @((ceil($unlim_num_rows / $_SESSION['userconf']['max_rows'])- 1) * $_SESSION['userconf']['max_rows']); ?>" />
        <?php
        if ($is_innodb && $unlim_num_rows > $GLOBALS['cfg']['MaxExactCount']) {
            echo '<input type="hidden" name="find_real_end" value="1" />' . "\n";
            // no backquote around this message
            $onclick = ' onclick="return confirmAction(\'' . PMA_jsFormat($GLOBALS['strLongOperation'], false) . '\')"';
        }
        ?>
        <input type="hidden" name="goto" value="<?php echo $goto; ?>" />
        <input type="submit" name="navig" value="<?php echo $caption4; ?>"<?php echo $title4; ?> <?php echo (empty($onclick) ? '' : $onclick); ?>/>
    </form>
</td>
        <?php
    } // end move toward


    //page redirection
    $pageNow = @floor($_SESSION['userconf']['pos'] / $_SESSION['userconf']['max_rows']) + 1;
    $nbTotalPage = @ceil($unlim_num_rows / $_SESSION['userconf']['max_rows']);

    if ($nbTotalPage > 1){ //if1
       ?>
   <td>
       &nbsp;&nbsp;&nbsp;
   </td>
   <td>
        <?php //<form> for keep the form alignment of button < and << ?>
        <form action="none">
        <?php echo PMA_pageselector(
                     'sql.php?sql_query='   . urlencode($sql_query) .
                        '&amp;goto='        . $goto .
                        '&amp;'             . PMA_generate_common_url($db, $table) .
                        '&amp;',
                     $_SESSION['userconf']['max_rows'],
                     $pageNow,
                     $nbTotalPage,
                     200,
                     5,
                     5,
                     20,
                     10,
                     $GLOBALS['strPageNumber']
              );
        ?>
        </form>
    </td>
        <?php
    } //_if1


    // Show all the records if allowed
    if ($GLOBALS['cfg']['ShowAll'] && ($num_rows < $unlim_num_rows)) {
        echo "\n";
        ?>
<td>
    &nbsp;&nbsp;&nbsp;
</td>
<td>
    <form action="sql.php" method="post">
        <?php echo PMA_generate_common_hidden_inputs($db, $table); ?>
        <input type="hidden" name="sql_query" value="<?php echo $html_sql_query; ?>" />
        <input type="hidden" name="pos" value="0" />
        <input type="hidden" name="session_max_rows" value="all" />
        <input type="hidden" name="goto" value="<?php echo $goto; ?>" />
        <input type="submit" name="navig" value="<?php echo $GLOBALS['strShowAll']; ?>" />
    </form>
</td>
        <?php
    } // end show all
    echo "\n";
    ?>
</tr>
</table>

    <?php
} // end of the 'PMA_displayTableNavigation()' function



function PMA_displayTableHeaders(&$is_display, &$fields_meta, $fields_cnt = 0, $analyzed_sql = '')
{
    global $db, $table, $goto;
    global $sql_query, $num_rows;
    global $vertical_display, $highlight_columns;

    if ($analyzed_sql == '') {
        $analyzed_sql = array();
    }

    // can the result be sorted?
    if ($is_display['sort_lnk'] == '1') {

        // Just as fallback
        $unsorted_sql_query     = $sql_query;
        if (isset($analyzed_sql[0]['unsorted_query'])) {
            $unsorted_sql_query = $analyzed_sql[0]['unsorted_query'];
        }



        $sort_expression = trim(str_replace('  ', ' ', $analyzed_sql[0]['order_by_clause']));

  
        preg_match('@(.*)([[:space:]]*(ASC|DESC))@si', $sort_expression, $matches);
        $sort_expression_nodir = isset($matches[1]) ? trim($matches[1]) : $sort_expression;

        // sorting by indexes, only if it makes sense (only one table ref)
        if (isset($analyzed_sql) && isset($analyzed_sql[0]) &&
            isset($analyzed_sql[0]['querytype']) && $analyzed_sql[0]['querytype'] == 'SELECT' &&
            isset($analyzed_sql[0]['table_ref']) && count($analyzed_sql[0]['table_ref']) == 1) {

            // grab indexes data:
            PMA_DBI_select_db($db);
            if (!defined('PMA_IDX_INCLUDED')) {
                $ret_keys = PMA_get_indexes($table);
            }

            $prev_index = '';
            foreach ($ret_keys as $row) {

                if ($row['Key_name'] != $prev_index){
                    $indexes[]  = $row['Key_name'];
                    $prev_index = $row['Key_name'];
                }
                $indexes_info[$row['Key_name']]['Sequences'][]     = $row['Seq_in_index'];
                $indexes_info[$row['Key_name']]['Non_unique']      = $row['Non_unique'];
                if (isset($row['Cardinality'])) {
                    $indexes_info[$row['Key_name']]['Cardinality'] = $row['Cardinality'];
                }
            //    I don't know what does the following column mean....
            //    $indexes_info[$row['Key_name']]['Packed']          = $row['Packed'];
                $indexes_info[$row['Key_name']]['Comment']         = (isset($row['Comment']))
                                                                   ? $row['Comment']
                                                                   : '';
                $indexes_info[$row['Key_name']]['Index_type']      = (isset($row['Index_type']))
                                                                   ? $row['Index_type']
                                                                   : '';

                $indexes_data[$row['Key_name']][$row['Seq_in_index']]['Column_name']  = $row['Column_name'];
                if (isset($row['Sub_part'])) {
                    $indexes_data[$row['Key_name']][$row['Seq_in_index']]['Sub_part'] = $row['Sub_part'];
                }
            } // end while

            // do we have any index?
            if (isset($indexes_data)) {

                if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
                 || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
                    $span = $fields_cnt;
                    if ($is_display['edit_lnk'] != 'nn') {
                        $span++;
                    }
                    if ($is_display['del_lnk'] != 'nn') {
                        $span++;
                    }
                    if ($is_display['del_lnk'] != 'kp' && $is_display['del_lnk'] != 'nn') {
                        $span++;
                    }
                } else {
                    $span = $num_rows + floor($num_rows/$_SESSION['userconf']['repeat_cells']) + 1;
                }

                echo '<form action="sql.php" method="post">' . "\n";
                echo PMA_generate_common_hidden_inputs($db, $table, 5);
                echo $GLOBALS['strSortByKey'] . ': <select name="sql_query" onchange="this.form.submit();">' . "\n";
                $used_index = false;
                $local_order = (isset($sort_expression) ? $sort_expression : '');
                foreach ($indexes_data AS $key => $val) {
                    $asc_sort = '';
                    $desc_sort = '';
                    foreach ($val AS $key2 => $val2) {
                        $asc_sort .= PMA_backquote($val2['Column_name']) . ' ASC , ';
                        $desc_sort .= PMA_backquote($val2['Column_name']) . ' DESC , ';
                    }
                    $asc_sort = substr($asc_sort, 0, -3);
                    $desc_sort = substr($desc_sort, 0, -3);
                    $used_index = $used_index || $local_order == $asc_sort || $local_order == $desc_sort;
                    echo '<option value="' . htmlspecialchars($unsorted_sql_query . ' ORDER BY ' . $asc_sort) . '"' . ($local_order == $asc_sort ? ' selected="selected"' : '') . '>' . htmlspecialchars($key) . ' (' . $GLOBALS['strAscending'] . ')</option>';
                    echo "\n";
                    echo '<option value="' . htmlspecialchars($unsorted_sql_query . ' ORDER BY ' . $desc_sort) . '"' . ($local_order == $desc_sort ? ' selected="selected"' : '') . '>' . htmlspecialchars($key) . ' (' . $GLOBALS['strDescending'] . ')</option>';
                    echo "\n";
                }
                echo '<option value="' . htmlspecialchars($unsorted_sql_query) . '"' . ($used_index ? '' : ' selected="selected"') . '>' . $GLOBALS['strNone'] . '</option>';
                echo "\n";
                echo '</select>' . "\n";
                echo '<noscript><input type="submit" value="' . $GLOBALS['strGo'] . '" /></noscript>';
                echo "\n";
                echo '</form>' . "\n";
            }
        }
    }


    $vertical_display['emptypre']   = 0;
    $vertical_display['emptyafter'] = 0;
    $vertical_display['textbtn']    = '';


    // Start of form for multi-rows delete

    if ($is_display['del_lnk'] == 'dr' || $is_display['del_lnk'] == 'kp') {
        echo '<form method="post" action="tbl_row_action.php" name="rowsDeleteForm" id="rowsDeleteForm">' . "\n";
        echo PMA_generate_common_hidden_inputs($db, $table, 1);
        echo '<input type="hidden" name="goto"             value="sql.php" />' . "\n";
    }

    echo '<table id="table_results" class="data">' . "\n";
    if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
     || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
        echo '<thead><tr>' . "\n";
    }

    // 1. Displays the full/partial text button (part 1)...
    if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
     || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
        $colspan  = ($is_display['edit_lnk'] != 'nn' && $is_display['del_lnk'] != 'nn')
                  ? ' colspan="3"'
                  : '';
    } else {
        $rowspan  = ($is_display['edit_lnk'] != 'nn' && $is_display['del_lnk'] != 'nn')
                  ? ' rowspan="3"'
                  : '';
    }
    $url_params = array(
        'db' => $db,
        'table' => $table,
        'sql_query' => $sql_query,
        'goto' => $goto,
    );
    $text_message = '<img class="fulltext" width="50" height="20"';
    if ($_SESSION['userconf']['dontlimitchars']) {
        $url_params['dontlimitchars'] = '0';
        $text_message .= ''
            . ' src="' . $GLOBALS['pmaThemeImage'] . 's_partialtext.png"'
            . ' alt="' . $GLOBALS['strPartialText'] . '"'
            . ' title="' . $GLOBALS['strPartialText'] . '"';
    } else {
        $url_params['dontlimitchars'] = '1';
        $text_message .= ''
            . ' src="' . $GLOBALS['pmaThemeImage'] . 's_fulltext.png"'
            . ' alt="' . $GLOBALS['strFullText'] . '"'
            . ' title="' . $GLOBALS['strFullText'] . '"';
    }
    $text_message .= ' />';
    $text_url = 'sql.php' . PMA_generate_common_url($url_params);
    $text_link = PMA_linkOrButton($text_url, $text_message, array(), false);

    //     ... before the result table
    if (($is_display['edit_lnk'] == 'nn' && $is_display['del_lnk'] == 'nn')
        && $is_display['text_btn'] == '1') {
        $vertical_display['emptypre'] = ($is_display['edit_lnk'] != 'nn' && $is_display['del_lnk'] != 'nn') ? 3 : 0;
        if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
         || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
            ?>
    <th colspan="<?php echo $fields_cnt; ?>"><?php echo $text_link; ?></th>
</tr>
<tr>
            <?php
        } // end horizontal/horizontalflipped mode
        else {
            ?>
<tr>
    <th colspan="<?php echo $num_rows + floor($num_rows/$_SESSION['userconf']['repeat_cells']) + 1; ?>">
        <?php echo $text_link; ?></th>
</tr>
            <?php
        } // end vertical mode
    }

    //     ... at the left column of the result table header if possible
    //     and required
    elseif ($GLOBALS['cfg']['ModifyDeleteAtLeft'] && $is_display['text_btn'] == '1') {
        $vertical_display['emptypre'] = ($is_display['edit_lnk'] != 'nn' && $is_display['del_lnk'] != 'nn') ? 3 : 0;
        if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
         || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
            ?>
    <th <?php echo $colspan; ?>><?php echo $text_link; ?></th>
            <?php
        } // end horizontal/horizontalflipped mode
        else {
            $vertical_display['textbtn'] = '    <th ' . $rowspan . ' valign="middle">' . "\n"
                                         . '        ' . $text_link . "\n"
                                         . '    </th>' . "\n";
        } // end vertical mode
    }

    //     ... elseif no button, displays empty(ies) col(s) if required
    elseif ($GLOBALS['cfg']['ModifyDeleteAtLeft']
             && ($is_display['edit_lnk'] != 'nn' || $is_display['del_lnk'] != 'nn')) {
        $vertical_display['emptypre'] = ($is_display['edit_lnk'] != 'nn' && $is_display['del_lnk'] != 'nn') ? 3 : 0;
        if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
         || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
            ?>
    <td<?php echo $colspan; ?>></td>
            <?php
        } // end horizontal/horizontalfipped mode
        else {
            $vertical_display['textbtn'] = '    <td' . $rowspan . '></td>' . "\n";
        } // end vertical mode
    }


    if ($GLOBALS['cfg']['ShowBrowseComments']
     && ($GLOBALS['cfgRelation']['commwork']
      || PMA_MYSQL_INT_VERSION >= 40100)
     && $_SESSION['userconf']['disp_direction'] != 'horizontalflipped') {
        $comments_map = array();
        if (isset($analyzed_sql[0]) && is_array($analyzed_sql[0])) {
            foreach ($analyzed_sql[0]['table_ref'] as $tbl) {
                $tb = $tbl['table_true_name'];
                $comments_map[$tb] = PMA_getComments($db, $tb);
                unset($tb);
            }
        }
    }

    if ($GLOBALS['cfgRelation']['commwork'] && $GLOBALS['cfgRelation']['mimework'] && $GLOBALS['cfg']['BrowseMIME']) {
        require_once './libraries/transformations.lib.php';
        $GLOBALS['mime_map'] = PMA_getMIME($db, $table);
    }

    if ($is_display['sort_lnk'] == '1') {
        $is_join = (isset($analyzed_sql[0]['queryflags']['join']) ? true : false);
        $select_expr = $analyzed_sql[0]['select_expr_clause'];
    } else {
        $is_join = false;
    }


    $highlight_columns = array();
    if (isset($analyzed_sql) && isset($analyzed_sql[0]) &&
        isset($analyzed_sql[0]['where_clause_identifiers'])) {

        $wi = 0;
        if (isset($analyzed_sql[0]['where_clause_identifiers']) && is_array($analyzed_sql[0]['where_clause_identifiers'])) {
            foreach ($analyzed_sql[0]['where_clause_identifiers'] AS $wci_nr => $wci) {
                $highlight_columns[$wci] = 'true';
            }
        }
    }

    for ($i = 0; $i < $fields_cnt; $i++) {

        if (isset($highlight_columns[$fields_meta[$i]->name]) || isset($highlight_columns[PMA_backquote($fields_meta[$i]->name)])) {
            $condition_field = true;
        } else {
            $condition_field = false;
        }

        if (isset($comments_map) &&
                isset($comments_map[$fields_meta[$i]->table]) &&
                isset($comments_map[$fields_meta[$i]->table][$fields_meta[$i]->name])) {
            $comments = '<span class="tblcomment">' . htmlspecialchars($comments_map[$fields_meta[$i]->table][$fields_meta[$i]->name]) . '</span>';
        } else {
            $comments = '';
        }

        if ($is_display['sort_lnk'] == '1') {

  
            if (isset($fields_meta[$i]->table) && strlen($fields_meta[$i]->table)) {
                $sort_tbl = PMA_backquote($fields_meta[$i]->table) . '.';
            } else {
                $sort_tbl = '';
            }

      
            if (empty($sort_expression)) {
                $is_in_sort = false;
            } else {
   
                $is_in_sort = ($sort_tbl . PMA_backquote($fields_meta[$i]->name) == $sort_expression_nodir ? true : false);
            }
    
            if (strpos($fields_meta[$i]->name, '`') !== false) {
                $sort_order = ' ORDER BY ' . PMA_backquote($fields_meta[$i]->name) . ' ';
            } else {
                $sort_order = ' ORDER BY ' . $sort_tbl . PMA_backquote($fields_meta[$i]->name) . ' ';
            }

            // 2.1.4 Do define the sorting URL
            if (! $is_in_sort) {
                // loic1: patch #455484 ("Smart" order)
                $GLOBALS['cfg']['Order'] = strtoupper($GLOBALS['cfg']['Order']);
                if ($GLOBALS['cfg']['Order'] === 'SMART') {
                    $sort_order .= (preg_match('@time|date@i', $fields_meta[$i]->type)) ? 'DESC' : 'ASC';
                } else {
                    $sort_order .= $GLOBALS['cfg']['Order'];
                }
                $order_img   = '';
            } elseif (preg_match('@[[:space:]]DESC$@i', $sort_expression)) {
                $sort_order .= ' ASC';
                $order_img   = ' <img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 's_desc.png" width="11" height="9" alt="'. $GLOBALS['strDescending'] . '" title="'. $GLOBALS['strDescending'] . '" id="soimg' . $i . '" />';
            } else {
                $sort_order .= ' DESC';
                $order_img   = ' <img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 's_asc.png" width="11" height="9" alt="'. $GLOBALS['strAscending'] . '" title="'. $GLOBALS['strAscending'] . '" id="soimg' . $i . '" />';
            }

            if (preg_match('@(.*)([[:space:]](LIMIT (.*)|PROCEDURE (.*)|FOR UPDATE|LOCK IN SHARE MODE))@i', $unsorted_sql_query, $regs3)) {
                $sorted_sql_query = $regs3[1] . $sort_order . $regs3[2];
            } else {
                $sorted_sql_query = $unsorted_sql_query . $sort_order;
            }
            $url_query = PMA_generate_common_url($db, $table)
                       . '&amp;sql_query=' . urlencode($sorted_sql_query);
            $order_url  = 'sql.php?' . $url_query;

     
            $order_link_params = array();
            if (isset($order_img) && $order_img!='') {
                if (strstr($order_img, 'asc')) {
                    $order_link_params['onmouseover'] = 'if(document.getElementById(\'soimg' . $i . '\')){ document.getElementById(\'soimg' . $i . '\').src=\'' . $GLOBALS['pmaThemeImage'] . 's_desc.png\'; }';
                    $order_link_params['onmouseout']  = 'if(document.getElementById(\'soimg' . $i . '\')){ document.getElementById(\'soimg' . $i . '\').src=\'' . $GLOBALS['pmaThemeImage'] . 's_asc.png\'; }';
                } elseif (strstr($order_img, 'desc')) {
                    $order_link_params['onmouseover'] = 'if(document.getElementById(\'soimg' . $i . '\')){ document.getElementById(\'soimg' . $i . '\').src=\'' . $GLOBALS['pmaThemeImage'] . 's_asc.png\'; }';
                    $order_link_params['onmouseout']  = 'if(document.getElementById(\'soimg' . $i . '\')){ document.getElementById(\'soimg' . $i . '\').src=\'' . $GLOBALS['pmaThemeImage'] . 's_desc.png\'; }';
                }
            }
            if ($_SESSION['userconf']['disp_direction'] == 'horizontalflipped'
             && $GLOBALS['cfg']['HeaderFlipType'] == 'css') {
                $order_link_params['style'] = 'direction: ltr; writing-mode: tb-rl;';
            }
            $order_link_params['title'] = $GLOBALS['strSort'];
            $order_link_content = ($_SESSION['userconf']['disp_direction'] == 'horizontalflipped' && $GLOBALS['cfg']['HeaderFlipType'] == 'fake' ? PMA_flipstring(htmlspecialchars($fields_meta[$i]->name), "<br />\n") : htmlspecialchars($fields_meta[$i]->name));
            $order_link = PMA_linkOrButton($order_url, $order_link_content . $order_img, $order_link_params, false, true);

            if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
             || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
                echo '<th';
                if ($condition_field) {
                    echo ' class="condition"';
                }
                if ($_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
                    echo ' valign="bottom"';
                }
                echo '>' . $order_link . $comments . '</th>';
            }
            $vertical_display['desc'][] = '    <th '
                . ($condition_field ? ' class="condition"' : '') . '>' . "\n"
                . $order_link . $comments . '    </th>' . "\n";
        } // end if (2.1)

        // 2.2 Results can't be sorted
        else {
            if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
             || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
                echo '<th';
                if ($condition_field) {
                    echo ' class="condition"';
                }
                if ($_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
                    echo ' valign="bottom"';
                }
                if ($_SESSION['userconf']['disp_direction'] == 'horizontalflipped'
                 && $GLOBALS['cfg']['HeaderFlipType'] == 'css') {
                    echo ' style="direction: ltr; writing-mode: tb-rl;"';
                }
                echo '>';
                if ($_SESSION['userconf']['disp_direction'] == 'horizontalflipped'
                 && $GLOBALS['cfg']['HeaderFlipType'] == 'fake') {
                    echo PMA_flipstring(htmlspecialchars($fields_meta[$i]->name), '<br />');
                } else {
                    echo htmlspecialchars($fields_meta[$i]->name);
                }
                echo "\n" . $comments . '</th>';
            }
            $vertical_display['desc'][] = '    <th '
                . ($condition_field ? ' class="condition"' : '') . '>' . "\n"
                . '        ' . htmlspecialchars($fields_meta[$i]->name) . "\n"
                . $comments . '    </th>';
        } // end else (2.2)
    } // end for

  
    if ($GLOBALS['cfg']['ModifyDeleteAtRight']
        && ($is_display['edit_lnk'] != 'nn' || $is_display['del_lnk'] != 'nn')
        && $is_display['text_btn'] == '1') {
        $vertical_display['emptyafter'] = ($is_display['edit_lnk'] != 'nn' && $is_display['del_lnk'] != 'nn') ? 3 : 1;
        if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
         || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
            echo "\n";
            ?>
<th <?php echo $colspan; ?>>
    <?php echo $text_link; ?>
</th>
            <?php
        } 
        else {
            $vertical_display['textbtn'] = '    <th ' . $rowspan . ' valign="middle">' . "\n"
                                         . '        ' . $text_link . "\n"
                                         . '    </th>' . "\n";
        }
    }


    elseif ($GLOBALS['cfg']['ModifyDeleteAtRight']
             && ($is_display['edit_lnk'] == 'nn' && $is_display['del_lnk'] == 'nn')
             && (!$GLOBALS['is_header_sent'])) {
        $vertical_display['emptyafter'] = ($is_display['edit_lnk'] != 'nn' && $is_display['del_lnk'] != 'nn') ? 3 : 1;
        if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
         || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
            echo "\n";
            ?>
<td<?php echo $colspan; ?>></td>
            <?php
        } 
        else {
            $vertical_display['textbtn'] = '    <td' . $rowspan . '></td>' . "\n";
        } 
    }

    if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
     || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
        ?>
</tr>
</thead>
        <?php
    }

    return true;
} 


function PMA_displayTableBody(&$dt_result, &$is_display, $map, $analyzed_sql) {
    global $db, $table, $goto;
    global $sql_query, $fields_meta, $fields_cnt;
    global $vertical_display, $highlight_columns;
    global $row; 

    $url_sql_query          = $sql_query;



    if (isset($analyzed_sql) && isset($analyzed_sql[0]) &&
        isset($analyzed_sql[0]['querytype']) && $analyzed_sql[0]['querytype'] == 'SELECT' &&
        strlen($sql_query) > 200) {

        $url_sql_query = 'SELECT ';
        if (isset($analyzed_sql[0]['queryflags']['distinct'])) {
            $url_sql_query .= ' DISTINCT ';
        }
        $url_sql_query .= $analyzed_sql[0]['select_expr_clause'];
        if (!empty($analyzed_sql[0]['from_clause'])) {
            $url_sql_query .= ' FROM ' . $analyzed_sql[0]['from_clause'];
        }
    }

    if (!is_array($map)) {
        $map = array();
    }
    $row_no                         = 0;
    $vertical_display['edit']       = array();
    $vertical_display['delete']     = array();
    $vertical_display['data']       = array();
    $vertical_display['row_delete'] = array();



    $odd_row = true;
    while ($row = PMA_DBI_fetch_row($dt_result)) {
        // lem9: "vertical display" mode stuff
        if ($row_no != 0 && $_SESSION['userconf']['repeat_cells'] != 0 && !($row_no % $_SESSION['userconf']['repeat_cells'])
          && ($_SESSION['userconf']['disp_direction'] == 'horizontal'
           || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped'))
        {
            echo '<tr>' . "\n";
            if ($vertical_display['emptypre'] > 0) {
                echo '    <th colspan="' . $vertical_display['emptypre'] . '">' . "\n"
                    .'        &nbsp;</th>' . "\n";
            }

            foreach ($vertical_display['desc'] as $val) {
                echo $val;
            }

            if ($vertical_display['emptyafter'] > 0) {
                echo '    <th colspan="' . $vertical_display['emptyafter'] . '">' . "\n"
                    .'        &nbsp;</th>' . "\n";
            }
            echo '</tr>' . "\n";
        } // end if

        $class = $odd_row ? 'odd' : 'even';
        $odd_row = ! $odd_row;
        if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
         || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
            // loic1: pointer code part
            echo '    <tr class="' . $class . '">' . "\n";
            $class = '';
        }


        $unique_condition     = urlencode(PMA_getUniqueCondition($dt_result, $fields_cnt, $fields_meta, $row));

        $url_query  = PMA_generate_common_url($db, $table);

        if ($is_display['edit_lnk'] != 'nn' || $is_display['del_lnk'] != 'nn') {

            if ($GLOBALS['cfg']['PropertiesIconic'] === 'both') {
                $iconic_spacer = '<div class="nowrap">';
            } else {
                $iconic_spacer = '';
            }

            if ($is_display['edit_lnk'] == 'ur') { // update row case
                $lnk_goto = 'sql.php';

                $edit_url = 'tbl_change.php'
                          . '?' . $url_query
                          . '&amp;primary_key=' . $unique_condition
                          . '&amp;sql_query=' . urlencode($url_sql_query)
                          . '&amp;goto=' . urlencode($lnk_goto);
                if ($GLOBALS['cfg']['PropertiesIconic'] === false) {
                    $edit_str = $GLOBALS['strEdit'];
                } else {
                    $edit_str = $iconic_spacer . '<img class="icon" width="16" height="16" src="' . $GLOBALS['pmaThemeImage'] . 'b_edit.png" alt="' . $GLOBALS['strEdit'] . '" title="' . $GLOBALS['strEdit'] . '" />';
                    if ($GLOBALS['cfg']['PropertiesIconic'] === 'both') {
                        $edit_str .= ' ' . $GLOBALS['strEdit'] . '</div>';
                    }
                }
            } // end if (1.2.1)

            if (isset($GLOBALS['cfg']['Bookmark']['table']) && isset($GLOBALS['cfg']['Bookmark']['db']) && $table == $GLOBALS['cfg']['Bookmark']['table'] && $db == $GLOBALS['cfg']['Bookmark']['db'] && isset($row[1]) && isset($row[0])) {
                $bookmark_go = '<a href="import.php?'
                                . PMA_generate_common_url($row[1], '')
                                . '&amp;id_bookmark=' . $row[0]
                                . '&amp;action_bookmark=0'
                                . '&amp;action_bookmark_all=1'
                                . '&amp;SQL=' . $GLOBALS['strExecuteBookmarked']
                                .' " title="' . $GLOBALS['strExecuteBookmarked'] . '">';

                if ($GLOBALS['cfg']['PropertiesIconic'] === false) {
                    $bookmark_go .= $GLOBALS['strExecuteBookmarked'];
                } else {
                    $bookmark_go .= $iconic_spacer . '<img class="icon" width="16" height="16" src="' . $GLOBALS['pmaThemeImage'] . 'b_bookmark.png" alt="' . $GLOBALS['strExecuteBookmarked'] . '" title="' . $GLOBALS['strExecuteBookmarked'] . '" />';
                    if ($GLOBALS['cfg']['PropertiesIconic'] === 'both') {
                        $bookmark_go .= ' ' . $GLOBALS['strExecuteBookmarked'] . '</div>';
                    }
                }

                $bookmark_go .= '</a>';
            } else {
                $bookmark_go = '';
            }

            // 1.2.2 Delete/Kill link(s)
            if ($is_display['del_lnk'] == 'dr') { // delete row case
                $lnk_goto = 'sql.php'
                          . '?' . str_replace('&amp;', '&', $url_query)
                          . '&sql_query=' . urlencode($url_sql_query)
                          . '&zero_rows=' . urlencode(htmlspecialchars($GLOBALS['strDeleted']))
                          . '&goto=' . (empty($goto) ? 'tbl_sql.php' : $goto);
                $del_query = urlencode('DELETE FROM ' . PMA_backquote($table) . ' WHERE') . $unique_condition . '+LIMIT+1';
                $del_url  = 'sql.php'
                          . '?' . $url_query
                          . '&amp;sql_query=' . $del_query
                          . '&amp;zero_rows=' . urlencode(htmlspecialchars($GLOBALS['strDeleted']))
                          . '&amp;goto=' . urlencode($lnk_goto);
                $js_conf  = 'DELETE FROM ' . PMA_jsFormat($table)
                          . ' WHERE ' . trim(PMA_jsFormat(urldecode($unique_condition), false))
                          . ' LIMIT 1';
                if ($GLOBALS['cfg']['PropertiesIconic'] === false) {
                    $del_str = $GLOBALS['strDelete'];
                } else {
                    $del_str = $iconic_spacer . '<img class="icon" width="16" height="16" src="' . $GLOBALS['pmaThemeImage'] . 'b_drop.png" alt="' . $GLOBALS['strDelete'] . '" title="' . $GLOBALS['strDelete'] . '" />';
                    if ($GLOBALS['cfg']['PropertiesIconic'] === 'both') {
                        $del_str .= ' ' . $GLOBALS['strDelete'] . '</div>';
                    }
                }
            } elseif ($is_display['del_lnk'] == 'kp') { // kill process case
                $lnk_goto = 'sql.php'
                          . '?' . str_replace('&amp;', '&', $url_query)
                          . '&sql_query=' . urlencode($url_sql_query)
                          . '&goto=main.php';
                $del_url  = 'sql.php?'
                          . PMA_generate_common_url('mysql')
                          . '&amp;sql_query=' . urlencode('KILL ' . $row[0])
                          . '&amp;goto=' . urlencode($lnk_goto);
                $del_query = urlencode('KILL ' . $row[0]);
                $js_conf  = 'KILL ' . $row[0];
                if ($GLOBALS['cfg']['PropertiesIconic'] === false) {
                    $del_str = $GLOBALS['strKill'];
                } else {
                    $del_str = $iconic_spacer . '<img class="icon" width="16" height="16" src="' . $GLOBALS['pmaThemeImage'] . 'b_drop.png" alt="' . $GLOBALS['strKill'] . '" title="' . $GLOBALS['strKill'] . '" />';
                    if ($GLOBALS['cfg']['PropertiesIconic'] === 'both') {
                        $del_str .= ' ' . $GLOBALS['strKill'] . '</div>';
                    }
                }
            } // end if (1.2.2)

            // 1.3 Displays the links at left if required
            if ($GLOBALS['cfg']['ModifyDeleteAtLeft']
             && ($_SESSION['userconf']['disp_direction'] == 'horizontal'
              || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped')) {
                $doWriteModifyAt = 'left';
                require './libraries/display_tbl_links.lib.php';
            } 
        } 

        for ($i = 0; $i < $fields_cnt; ++$i) {
            $meta    = $fields_meta[$i];
     
            $pointer = (function_exists('is_null') ? $i : $meta->name);
      
            if (isset($highlight_columns) && (isset($highlight_columns[$meta->name]) || isset($highlight_columns[PMA_backquote($meta->name)]))) {
                $condition_field = true;
            } else {
                $condition_field = false;
            }

            $mouse_events = '';
            if ($_SESSION['userconf']['disp_direction'] == 'vertical' && (!isset($GLOBALS['printview']) || ($GLOBALS['printview'] != '1'))) {
                if ($GLOBALS['cfg']['BrowsePointerEnable'] == true) {
                    $mouse_events .= ' onmouseover="setVerticalPointer(this, ' . $row_no . ', \'over\', \'odd\', \'even\', \'hover\', \'marked\');"'
                              . ' onmouseout="setVerticalPointer(this, ' . $row_no . ', \'out\', \'odd\', \'even\', \'hover\', \'marked\');" ';
                }
                if ($GLOBALS['cfg']['BrowseMarkerEnable'] == true) {
                    $mouse_events .= ' onmousedown="setVerticalPointer(this, ' . $row_no . ', \'click\', \'odd\', \'even\', \'hover\', \'marked\'); setCheckboxColumn(\'id_rows_to_delete' . $row_no . '\');" ';
                } else {
                    $mouse_events .= ' onmousedown="setCheckboxColumn(\'id_rows_to_delete' . $row_no . '\');" ';
                }
            }

            $default_function = 'default_function'; 
            $transform_function = $default_function;
            $transform_options = array();

            if ($GLOBALS['cfgRelation']['mimework'] && $GLOBALS['cfg']['BrowseMIME']) {

                if (isset($GLOBALS['mime_map'][$meta->name]['mimetype']) && isset($GLOBALS['mime_map'][$meta->name]['transformation']) && !empty($GLOBALS['mime_map'][$meta->name]['transformation'])) {
                    $include_file = PMA_sanitizeTransformationFile($GLOBALS['mime_map'][$meta->name]['transformation']);

                    if (file_exists('./libraries/transformations/' . $include_file)) {
                        $transformfunction_name = preg_replace('@(\.inc\.php3?)$@i', '', $GLOBALS['mime_map'][$meta->name]['transformation']);

                        require_once './libraries/transformations/' . $include_file;

                        if (function_exists('PMA_transformation_' . $transformfunction_name)) {
                            $transform_function = 'PMA_transformation_' . $transformfunction_name;
                            $transform_options  = PMA_transformation_getOptions((isset($GLOBALS['mime_map'][$meta->name]['transformation_options']) ? $GLOBALS['mime_map'][$meta->name]['transformation_options'] : ''));
                            $meta->mimetype     = str_replace('_', '/', $GLOBALS['mime_map'][$meta->name]['mimetype']);
                        }
                    } // end if file_exists
                } // end if transformation is set
            } // end if mime/transformation works.

            $transform_options['wrapper_link'] = '?'
                                                . (isset($url_query) ? $url_query : '')
                                                . '&amp;primary_key=' . (isset($unique_condition) ? $unique_condition : '')
                                                . '&amp;sql_query=' . (empty($sql_query) ? '' : urlencode($url_sql_query))
                                                . '&amp;goto=' . (isset($sql_goto) ? urlencode($lnk_goto) : '')
                                                . '&amp;transform_key=' . urlencode($meta->name);

            if ($meta->numeric == 1) {

                if (!isset($row[$i]) || is_null($row[$i])) {
                    $vertical_display['data'][$row_no][$i]     = '    <td align="right"' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . '"><i>NULL</i></td>' . "\n";
                } elseif ($row[$i] != '') {
                    $vertical_display['data'][$row_no][$i]     = '    <td align="right"' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . ' nowrap">';

                    if (isset($analyzed_sql[0]['select_expr']) && is_array($analyzed_sql[0]['select_expr'])) {
                        foreach ($analyzed_sql[0]['select_expr'] AS $select_expr_position => $select_expr) {
                            $alias = $analyzed_sql[0]['select_expr'][$select_expr_position]['alias'];
                            if (isset($alias) && strlen($alias)) {
                                $true_column = $analyzed_sql[0]['select_expr'][$select_expr_position]['column'];
                                if ($alias == $meta->name) {
                                    $meta->name = $true_column;
                                } // end if
                            } // end if
                        } // end while
                    }

                    if (isset($map[$meta->name])) {
                        if (isset($map[$meta->name][2]) && strlen($map[$meta->name][2])) {
                            $dispsql     = 'SELECT ' . PMA_backquote($map[$meta->name][2])
                                         . ' FROM ' . PMA_backquote($map[$meta->name][3]) . '.' . PMA_backquote($map[$meta->name][0])
                                         . ' WHERE ' . PMA_backquote($map[$meta->name][1])
                                         . ' = ' . $row[$i];
                            $dispresult  = PMA_DBI_try_query($dispsql, null, PMA_DBI_QUERY_STORE);
                            if ($dispresult && PMA_DBI_num_rows($dispresult) > 0) {
                                list($dispval) = PMA_DBI_fetch_row($dispresult, 0);
                            } else {
                                $dispval = $GLOBALS['strLinkNotFound'];
                            }
                            @PMA_DBI_free_result($dispresult);
                        } else {
                            $dispval     = '';
                        } 

                        if (isset($GLOBALS['printview']) && $GLOBALS['printview'] == '1') {
                            $vertical_display['data'][$row_no][$i] .= ($transform_function != $default_function ? $transform_function($row[$i], $transform_options, $meta) : $transform_function($row[$i], array(), $meta)) . ' <code>[-&gt;' . $dispval . ']</code>';
                        } else {
                            $title = (!empty($dispval))? ' title="' . htmlspecialchars($dispval) . '"' : '';

                            $vertical_display['data'][$row_no][$i] .= '<a href="sql.php?'
                                                                   .  PMA_generate_common_url($map[$meta->name][3], $map[$meta->name][0])
                                                                   .  '&amp;pos=0'
                                                                   .  '&amp;sql_query=' . urlencode('SELECT * FROM ' . PMA_backquote($map[$meta->name][0]) . ' WHERE ' . PMA_backquote($map[$meta->name][1]) . ' = ' . $row[$i]) . '"' . $title . '>'
                                                                   .  ($transform_function != $default_function ? $transform_function($row[$i], $transform_options, $meta) : $transform_function($row[$i], array(), $meta)) . '</a>';
                        }
                    } else {
                        $vertical_display['data'][$row_no][$i] .= ($transform_function != $default_function ? $transform_function($row[$i], $transform_options, $meta) : $transform_function($row[$i], array(), $meta));
                    }
                    $vertical_display['data'][$row_no][$i]     .= '</td>' . "\n";
                } else {
                    $vertical_display['data'][$row_no][$i]     = '    <td align="right"' . $mouse_events . ' class="' . $class . ' nowrap' . ($condition_field ? ' condition' : '') . '">&nbsp;</td>' . "\n";
                }

            } elseif ($GLOBALS['cfg']['ShowBlob'] == false && stristr($meta->type, 'BLOB')) {
           
                $field_flags = PMA_DBI_field_flags($dt_result, $i);
                if (stristr($field_flags, 'BINARY')) {
                    $blobtext = '[BLOB';
                    if (!isset($row[$i]) || is_null($row[$i])) {
                        $blobtext .= ' - NULL';
                        $blob_size = 0;
                    } elseif (isset($row[$i])) {
                        $blob_size = strlen($row[$i]);
                        $display_blob_size = PMA_formatByteDown($blob_size, 3, 1);
                        $blobtext .= ' - '. $display_blob_size[0] . ' ' . $display_blob_size[1];
                        unset($display_blob_size);
                    }

                    $blobtext .= ']';
                    if (strpos($transform_function, 'octetstream')) {
                        $blobtext = $row[$i];
                    }
                    if ($blob_size > 0) {
                        $blobtext = ($default_function != $transform_function ? $transform_function($blobtext, $transform_options, $meta) : $default_function($blobtext, array(), $meta));
                    }
                    unset($blob_size);

                    $vertical_display['data'][$row_no][$i]      = '    <td align="left"' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . '">' . $blobtext . '</td>';
                } else {
                    if (!isset($row[$i]) || is_null($row[$i])) {
                        $vertical_display['data'][$row_no][$i] = '    <td' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . '"><i>NULL</i></td>' . "\n";
                    } elseif ($row[$i] != '') {
                        if (PMA_strlen($row[$i]) > $GLOBALS['cfg']['LimitChars'] && ! $_SESSION['userconf']['dontlimitchars']) {
                            $row[$i] = PMA_substr($row[$i], 0, $GLOBALS['cfg']['LimitChars']) . '...';
                        }
                
                        $row[$i]     = ($default_function != $transform_function ? $transform_function($row[$i], $transform_options, $meta) : $default_function($row[$i], array(), $meta));

                        $vertical_display['data'][$row_no][$i] = '    <td' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . '">' . $row[$i] . '</td>' . "\n";
                    } else {
                        $vertical_display['data'][$row_no][$i] = '    <td' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . '">&nbsp;</td>' . "\n";
                    }
                }
            } else {
                if (!isset($row[$i]) || is_null($row[$i])) {
                    $vertical_display['data'][$row_no][$i]     = '    <td' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . '"><i>NULL</i></td>' . "\n";
                } elseif ($row[$i] != '') {
                    $relation_id = $row[$i];

            
                    if (PMA_strlen($row[$i]) > $GLOBALS['cfg']['LimitChars'] && ! $_SESSION['userconf']['dontlimitchars'] && !strpos($transform_function, 'link') === true) {
                        $row[$i] = PMA_substr($row[$i], 0, $GLOBALS['cfg']['LimitChars']) . '...';
                    }

                    $field_flags = PMA_DBI_field_flags($dt_result, $i);
                    if (isset($meta->_type) && $meta->_type === MYSQLI_TYPE_BIT) {
                        $db_value = $row[$i];
                        $row[$i]  = '';
                        for ($j = 0; $j < ceil($meta->length / 8); $j++) {
                            $row[$i] .= sprintf('%08d', decbin(ord(substr($db_value, $j, 1))));
                        }
                        $row[$i]     = substr($row[$i], -$meta->length);
                    } elseif (stristr($field_flags, 'BINARY')) {
                        $row[$i]     = str_replace("\x00", '\0', $row[$i]);
                        $row[$i]     = str_replace("\x08", '\b', $row[$i]);
                        $row[$i]     = str_replace("\x0a", '\n', $row[$i]);
                        $row[$i]     = str_replace("\x0d", '\r', $row[$i]);
                        $row[$i]     = str_replace("\x1a", '\Z', $row[$i]);
                        $row[$i]     = ($default_function != $transform_function ? $transform_function($row[$i], $transform_options, $meta) : $default_function($row[$i], array(), $meta));
                    }
                
                    else {
                        $row[$i]     = ($default_function != $transform_function ? $transform_function($row[$i], $transform_options, $meta) : $default_function($row[$i], array(), $meta));
                    }

                    $function_nowrap = $transform_function . '_nowrap';
                    $bool_nowrap = (($default_function != $transform_function && function_exists($function_nowrap)) ? $function_nowrap($transform_options) : false);

                    $nowrap = ((preg_match('@DATE|TIME@i', $meta->type) || $bool_nowrap) ? ' nowrap' : '');
                    $vertical_display['data'][$row_no][$i]     = '    <td' . $mouse_events . ' class="' . $class . $nowrap . ($condition_field ? ' condition' : '') . '">';

                    if (isset($analyzed_sql[0]['select_expr']) && is_array($analyzed_sql[0]['select_expr'])) {
                        foreach ($analyzed_sql[0]['select_expr'] AS $select_expr_position => $select_expr) {
                            $alias = $analyzed_sql[0]['select_expr'][$select_expr_position]['alias'];
                            if (isset($alias) && strlen($alias)) {
                                $true_column = $analyzed_sql[0]['select_expr'][$select_expr_position]['column'];
                                if ($alias == $meta->name) {
                                    $meta->name = $true_column;
                                } 
                            }
                        } 
                    }

                    if (isset($map[$meta->name])) {
                        if (isset($map[$meta->name][2]) && strlen($map[$meta->name][2])) {
                            $dispsql     = 'SELECT ' . PMA_backquote($map[$meta->name][2])
                                         . ' FROM ' . PMA_backquote($map[$meta->name][3]) . '.' . PMA_backquote($map[$meta->name][0])
                                         . ' WHERE ' . PMA_backquote($map[$meta->name][1])
                                         . ' = \'' . PMA_sqlAddslashes($row[$i]) . '\'';
                            $dispresult  = PMA_DBI_try_query($dispsql, null, PMA_DBI_QUERY_STORE);
                            if ($dispresult && PMA_DBI_num_rows($dispresult) > 0) {
                                list($dispval) = PMA_DBI_fetch_row($dispresult);
                                @PMA_DBI_free_result($dispresult);
                            } else {
                                $dispval = $GLOBALS['strLinkNotFound'];
                            }
                        } else {
                            $dispval = '';
                        }
                        $title = (!empty($dispval))? ' title="' . htmlspecialchars($dispval) . '"' : '';

                        $vertical_display['data'][$row_no][$i] .= '<a href="sql.php?'
                                                               .  PMA_generate_common_url($map[$meta->name][3], $map[$meta->name][0])
                                                               .  '&amp;pos=0'
                                                               .  '&amp;sql_query=' . urlencode('SELECT * FROM ' . PMA_backquote($map[$meta->name][0]) . ' WHERE ' . PMA_backquote($map[$meta->name][1]) . ' = \'' . PMA_sqlAddslashes($relation_id) . '\'') . '"' . $title . '>'
                                                               .  $row[$i] . '</a>';
                    } else {
                        $vertical_display['data'][$row_no][$i] .= $row[$i];
                    }
                    $vertical_display['data'][$row_no][$i]     .= '</td>' . "\n";
                } else {
                    $vertical_display['data'][$row_no][$i]     = '    <td' . $mouse_events . ' class="' . $class . ($condition_field ? ' condition' : '') . '">&nbsp;</td>' . "\n";
                }
            }

            // lem9: output stored cell
            if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
             || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
                echo $vertical_display['data'][$row_no][$i];
            }

            if (isset($vertical_display['rowdata'][$i][$row_no])) {
                $vertical_display['rowdata'][$i][$row_no] .= $vertical_display['data'][$row_no][$i];
            } else {
                $vertical_display['rowdata'][$i][$row_no] = $vertical_display['data'][$row_no][$i];
            }
        } // end for (2)

        // 3. Displays the modify/delete links on the right if required
        if ($GLOBALS['cfg']['ModifyDeleteAtRight']
         && ($_SESSION['userconf']['disp_direction'] == 'horizontal'
          || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped')) {
                $doWriteModifyAt = 'right';
                require './libraries/display_tbl_links.lib.php';
        } // end if (3)

        if ($_SESSION['userconf']['disp_direction'] == 'horizontal'
         || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') {
            ?>
</tr>
            <?php
        } // end if

        // 4. Gather links of del_urls and edit_urls in an array for later
        //    output
        if (!isset($vertical_display['edit'][$row_no])) {
            $vertical_display['edit'][$row_no]       = '';
            $vertical_display['delete'][$row_no]     = '';
            $vertical_display['row_delete'][$row_no] = '';
        }

        $column_style_vertical = '';
        if ($GLOBALS['cfg']['BrowsePointerEnable'] == true) {
            $column_style_vertical .= ' onmouseover="setVerticalPointer(this, ' . $row_no . ', \'over\', \'odd\', \'even\', \'hover\', \'marked\');"'
                         . ' onmouseout="setVerticalPointer(this, ' . $row_no . ', \'out\', \'odd\', \'even\', \'hover\', \'marked\');"';
        }
        $column_marker_vertical = '';
        if ($GLOBALS['cfg']['BrowseMarkerEnable'] == true) {
            $column_marker_vertical .= 'setVerticalPointer(this, ' . $row_no . ', \'click\', \'odd\', \'even\', \'hover\', \'marked\');';
        }

        if (!empty($del_url) && $is_display['del_lnk'] != 'kp') {
            $vertical_display['row_delete'][$row_no] .= '    <td align="center" class="' . $class . '" ' . $column_style_vertical . '>' . "\n"
                                                     .  '        <input type="checkbox" id="id_rows_to_delete' . $row_no . '[%_PMA_CHECKBOX_DIR_%]" name="rows_to_delete[' . $unique_condition . ']"'
                                                     .  ' onclick="' . $column_marker_vertical . 'copyCheckboxesRange(\'rowsDeleteForm\', \'id_rows_to_delete' . $row_no . '\',\'[%_PMA_CHECKBOX_DIR_%]\');"'
                                                     .  ' value="' . $del_query . '" ' . (isset($GLOBALS['checkall']) ? 'checked="checked"' : '') . ' />' . "\n"
                                                     .  '    </td>' . "\n";
        } else {
            unset($vertical_display['row_delete'][$row_no]);
        }

        if (isset($edit_url)) {
            $vertical_display['edit'][$row_no]   .= '    <td align="center" class="' . $class . '" ' . $column_style_vertical . '>' . "\n"
                                                 . PMA_linkOrButton($edit_url, $edit_str, array(), false)
                                                 . $bookmark_go
                                                 .  '    </td>' . "\n";
        } else {
            unset($vertical_display['edit'][$row_no]);
        }

        if (isset($del_url)) {
            $vertical_display['delete'][$row_no] .= '    <td align="center" class="' . $class . '" ' . $column_style_vertical . '>' . "\n"
                                                 . PMA_linkOrButton($del_url, $del_str, (isset($js_conf) ? $js_conf : ''), false)
                                                 .  '    </td>' . "\n";
        } else {
            unset($vertical_display['delete'][$row_no]);
        }

        echo (($_SESSION['userconf']['disp_direction'] == 'horizontal' || $_SESSION['userconf']['disp_direction'] == 'horizontalflipped') ? "\n" : '');
        $row_no++;
    } // end while

    if (isset($url_query)) {
        $GLOBALS['url_query'] = $url_query;
    }

    return true;
} // end of the 'PMA_displayTableBody()' function


function PMA_displayVerticalTable()
{
    global $vertical_display;

    // Displays "multi row delete" link at top if required
    if ($GLOBALS['cfg']['ModifyDeleteAtLeft'] && is_array($vertical_display['row_delete']) && (count($vertical_display['row_delete']) > 0 || !empty($vertical_display['textbtn']))) {
        echo '<tr>' . "\n";
        echo $vertical_display['textbtn'];
        $foo_counter = 0;
        foreach ($vertical_display['row_delete'] as $val) {
            if (($foo_counter != 0) && ($_SESSION['userconf']['repeat_cells'] != 0) && !($foo_counter % $_SESSION['userconf']['repeat_cells'])) {
                echo '<th>&nbsp;</th>' . "\n";
            }

            echo str_replace('[%_PMA_CHECKBOX_DIR_%]', '', $val);
            $foo_counter++;
        } // end while
        echo '</tr>' . "\n";
    } // end if

    // Displays "edit" link at top if required
    if ($GLOBALS['cfg']['ModifyDeleteAtLeft'] && is_array($vertical_display['edit']) && (count($vertical_display['edit']) > 0 || !empty($vertical_display['textbtn']))) {
        echo '<tr>' . "\n";
        if (!is_array($vertical_display['row_delete'])) {
            echo $vertical_display['textbtn'];
        }
        $foo_counter = 0;
        foreach ($vertical_display['edit'] as $val) {
            if (($foo_counter != 0) && ($_SESSION['userconf']['repeat_cells'] != 0) && !($foo_counter % $_SESSION['userconf']['repeat_cells'])) {
                echo '    <th>&nbsp;</th>' . "\n";
            }

            echo $val;
            $foo_counter++;
        } // end while
        echo '</tr>' . "\n";
    } // end if

    // Displays "delete" link at top if required
    if ($GLOBALS['cfg']['ModifyDeleteAtLeft'] && is_array($vertical_display['delete']) && (count($vertical_display['delete']) > 0 || !empty($vertical_display['textbtn']))) {
        echo '<tr>' . "\n";
        if (!is_array($vertical_display['edit']) && !is_array($vertical_display['row_delete'])) {
            echo $vertical_display['textbtn'];
        }
        $foo_counter = 0;
        foreach ($vertical_display['delete'] as $val) {
            if (($foo_counter != 0) && ($_SESSION['userconf']['repeat_cells'] != 0) && !($foo_counter % $_SESSION['userconf']['repeat_cells'])) {
                echo '<th>&nbsp;</th>' . "\n";
            }

            echo $val;
            $foo_counter++;
        } // end while
        echo '</tr>' . "\n";
    } // end if

    foreach ($vertical_display['desc'] AS $key => $val) {

        echo '<tr>' . "\n";
        echo $val;

        $foo_counter = 0;
        foreach ($vertical_display['rowdata'][$key] as $subval) {
            if (($foo_counter != 0) && ($_SESSION['userconf']['repeat_cells'] != 0) and !($foo_counter % $_SESSION['userconf']['repeat_cells'])) {
                echo $val;
            }

            echo $subval;
            $foo_counter++;
        } // end while

        echo '</tr>' . "\n";
    } // end while

    if ($GLOBALS['cfg']['ModifyDeleteAtRight'] && is_array($vertical_display['row_delete']) && (count($vertical_display['row_delete']) > 0 || !empty($vertical_display['textbtn']))) {
        echo '<tr>' . "\n";
        echo $vertical_display['textbtn'];
        $foo_counter = 0;
        foreach ($vertical_display['row_delete'] as $val) {
            if (($foo_counter != 0) && ($_SESSION['userconf']['repeat_cells'] != 0) && !($foo_counter % $_SESSION['userconf']['repeat_cells'])) {
                echo '<th>&nbsp;</th>' . "\n";
            }

            echo str_replace('[%_PMA_CHECKBOX_DIR_%]', 'r', $val);
            $foo_counter++;
        } // end while
        echo '</tr>' . "\n";
    } // end if

    if ($GLOBALS['cfg']['ModifyDeleteAtRight'] && is_array($vertical_display['edit']) && (count($vertical_display['edit']) > 0 || !empty($vertical_display['textbtn']))) {
        echo '<tr>' . "\n";
        if (!is_array($vertical_display['row_delete'])) {
            echo $vertical_display['textbtn'];
        }
        $foo_counter = 0;
        foreach ($vertical_display['edit'] as $val) {
            if (($foo_counter != 0) && ($_SESSION['userconf']['repeat_cells'] != 0) && !($foo_counter % $_SESSION['userconf']['repeat_cells'])) {
                echo '<th>&nbsp;</th>' . "\n";
            }

            echo $val;
            $foo_counter++;
        } // end while
        echo '</tr>' . "\n";
    } // end if

    // Displays "delete" link at bottom if required
    if ($GLOBALS['cfg']['ModifyDeleteAtRight'] && is_array($vertical_display['delete']) && (count($vertical_display['delete']) > 0 || !empty($vertical_display['textbtn']))) {
        echo '<tr>' . "\n";
        if (!is_array($vertical_display['edit']) && !is_array($vertical_display['row_delete'])) {
            echo $vertical_display['textbtn'];
        }
        $foo_counter = 0;
        foreach ($vertical_display['delete'] as $val) {
            if (($foo_counter != 0) && ($_SESSION['userconf']['repeat_cells'] != 0) && !($foo_counter % $_SESSION['userconf']['repeat_cells'])) {
                echo '<th>&nbsp;</th>' . "\n";
            }

            echo $val;
            $foo_counter++;
        } // end while
        echo '</tr>' . "\n";
    }

    return true;
} // end of the 'PMA_displayVerticalTable' function


function PMA_displayTable_checkConfigParams()
{
    $sql_key = md5($GLOBALS['sql_query']);

    $_SESSION['userconf']['query'][$sql_key]['sql'] = $GLOBALS['sql_query'];

    if (PMA_isValid($_REQUEST['disp_direction'], array('horizontal', 'vertical', 'horizontalflipped'))) {
        $_SESSION['userconf']['query'][$sql_key]['disp_direction'] = $_REQUEST['disp_direction'];
        unset($_REQUEST['disp_direction']);
    } elseif (empty($_SESSION['userconf']['query'][$sql_key]['disp_direction'])) {
        $_SESSION['userconf']['query'][$sql_key]['disp_direction'] = $GLOBALS['cfg']['DefaultDisplay'];
    }

    if (PMA_isValid($_REQUEST['repeat_cells'], 'numeric')) {
        $_SESSION['userconf']['query'][$sql_key]['repeat_cells'] = $_REQUEST['repeat_cells'];
        unset($_REQUEST['repeat_cells']);
    } elseif (empty($_SESSION['userconf']['query'][$sql_key]['repeat_cells'])) {
        $_SESSION['userconf']['query'][$sql_key]['repeat_cells'] = $GLOBALS['cfg']['RepeatCells'];
    }

    if (PMA_isValid($_REQUEST['session_max_rows'], 'numeric') || $_REQUEST['session_max_rows'] == 'all') {
        $_SESSION['userconf']['query'][$sql_key]['max_rows'] = $_REQUEST['session_max_rows'];
        unset($_REQUEST['session_max_rows']);
    } elseif (empty($_SESSION['userconf']['query'][$sql_key]['max_rows'])) {
        $_SESSION['userconf']['query'][$sql_key]['max_rows'] = $GLOBALS['cfg']['MaxRows'];
    }

    if (PMA_isValid($_REQUEST['pos'], 'numeric')) {
        $_SESSION['userconf']['query'][$sql_key]['pos'] = $_REQUEST['pos'];
        unset($_REQUEST['pos']);
    } elseif (empty($_SESSION['userconf']['query'][$sql_key]['pos'])) {
        $_SESSION['userconf']['query'][$sql_key]['pos'] = 0;
    }

    if (PMA_isValid($_REQUEST['dontlimitchars'], array('0', '1'))) {
        $_SESSION['userconf']['query'][$sql_key]['dontlimitchars'] = (int) $_REQUEST['dontlimitchars'];
        unset($_REQUEST['dontlimitchars']);
    } elseif (empty($_SESSION['userconf']['query'][$sql_key]['dontlimitchars'])) {
        $_SESSION['userconf']['query'][$sql_key]['dontlimitchars'] = 0;
    }


    $tmp = $_SESSION['userconf']['query'][$sql_key];
    unset($_SESSION['userconf']['query'][$sql_key]);
    $_SESSION['userconf']['query'][$sql_key] = $tmp;

    if (count($_SESSION['userconf']['query']) > 10) {
        array_shift($_SESSION['userconf']['query']);
    }

    // populate query configuration
    $_SESSION['userconf']['dontlimitchars'] = $_SESSION['userconf']['query'][$sql_key]['dontlimitchars'];
    $_SESSION['userconf']['pos'] = $_SESSION['userconf']['query'][$sql_key]['pos'];
    $_SESSION['userconf']['max_rows'] = $_SESSION['userconf']['query'][$sql_key]['max_rows'];
    $_SESSION['userconf']['repeat_cells'] = $_SESSION['userconf']['query'][$sql_key]['repeat_cells'];
    $_SESSION['userconf']['disp_direction'] = $_SESSION['userconf']['query'][$sql_key]['disp_direction'];



}


function PMA_displayTable(&$dt_result, &$the_disp_mode, $analyzed_sql)
{
    global $db, $table, $goto;
    global $sql_query, $num_rows, $unlim_num_rows, $fields_meta, $fields_cnt;
    global $vertical_display, $highlight_columns;
    global $cfgRelation;
    global $showtable;

    PMA_displayTable_checkConfigParams();


    $is_innodb = (isset($showtable['Type']) && $showtable['Type'] == 'InnoDB');

    if ($is_innodb
     && ! isset($analyzed_sql[0]['queryflags']['union'])
     && ! isset($analyzed_sql[0]['table_ref'][1]['table_name'])
     && (empty($analyzed_sql[0]['where_clause'])
      || $analyzed_sql[0]['where_clause'] == '1 ')) {
        // "j u s t   b r o w s i n g"
        $pre_count = '~';
        $after_count = '[sup]1[/sup]';
    } else {
        $pre_count = '';
        $after_count = '';
    }

 
    $total      = '';
    $is_display = PMA_setDisplayMode($the_disp_mode, $total);

    if ($is_display['nav_bar'] == '1') {
        if ($_SESSION['userconf']['max_rows'] == 'all') {
            $pos_next     = 0;
            $pos_prev     = 0;
        } else {
            $pos_next     = $_SESSION['userconf']['pos'] + $_SESSION['userconf']['max_rows'];
            $pos_prev     = $_SESSION['userconf']['pos'] - $_SESSION['userconf']['max_rows'];
            if ($pos_prev < 0) {
                $pos_prev = 0;
            }
        }
    } // end if

    $encoded_sql_query = urlencode($sql_query);

    if ($is_display['nav_bar'] == '1' && isset($pos_next)) {
        if (isset($unlim_num_rows) && $unlim_num_rows != $total) {
            $selectstring = ', ' . $unlim_num_rows . ' ' . $GLOBALS['strSelectNumRows'];
        } else {
            $selectstring = '';
        }
        $last_shown_rec = ($_SESSION['userconf']['max_rows'] == 'all' || $pos_next > $total)
                        ? $total - 1
                        : $pos_next - 1;
        PMA_showMessage($GLOBALS['strShowingRecords'] . ' ' . $_SESSION['userconf']['pos'] . ' - ' . $last_shown_rec . ' (' . $pre_count . PMA_formatNumber($total, 0) . $after_count . ' ' . $GLOBALS['strTotal'] . $selectstring . ', ' . sprintf($GLOBALS['strQueryTime'], $GLOBALS['querytime']) . ')');

        if (PMA_Table::isView($db, $table) && $total ==  $GLOBALS['cfg']['MaxExactCount']) {
            echo '<div class="notice">' . "\n";
            echo PMA_sanitize(sprintf($GLOBALS['strViewMaxExactCount'], PMA_formatNumber($GLOBALS['cfg']['MaxExactCount'], 0), '[a@./Documentation.html#cfg_MaxExactCount@_blank]', '[/a]')) . "\n";
            echo '</div>' . "\n";
        }
        if ($pre_count) {
            echo '<div class="notice">' . "\n";
            echo '<sup>1</sup>' . PMA_sanitize($GLOBALS['strApproximateCount']) . "\n";
            echo '</div>' . "\n";
        }

    } elseif (!isset($GLOBALS['printview']) || $GLOBALS['printview'] != '1') {
        PMA_showMessage($GLOBALS['strSQLQuery']);
    }

    if (! strlen($table)) {
        if (isset($analyzed_sql[0]['query_type'])
           && $analyzed_sql[0]['query_type'] == 'SELECT') {
            $table = $fields_meta[0]->table;
        } else {
            $table = '';
        }
    }

    if ($is_display['nav_bar'] == '1') {
        PMA_displayTableNavigation($pos_next, $pos_prev, $sql_query);
        echo "\n";
    } elseif (!isset($GLOBALS['printview']) || $GLOBALS['printview'] != '1') {
        echo "\n" . '<br /><br />' . "\n";
    }

    $map = array();

    $target=array();
    if (isset($analyzed_sql[0]['table_ref']) && is_array($analyzed_sql[0]['table_ref'])) {
        foreach ($analyzed_sql[0]['table_ref'] AS $table_ref_position => $table_ref) {
           $target[] = $analyzed_sql[0]['table_ref'][$table_ref_position]['table_true_name'];
        }
    }
    $tabs    = '(\'' . join('\',\'', $target) . '\')';

    if ($cfgRelation['displaywork']) {
        if (! strlen($table)) {
            $exist_rel = false;
        } else {
            $exist_rel = PMA_getForeigners($db, $table, '', 'both');
            if ($exist_rel) {
                foreach ($exist_rel AS $master_field => $rel) {
                    $display_field = PMA_getDisplayField($rel['foreign_db'], $rel['foreign_table']);
                    $map[$master_field] = array($rel['foreign_table'],
                                          $rel['foreign_field'],
                                          $display_field,
                                          $rel['foreign_db']);
                } 
            } 
        } 
    } 
    

    PMA_displayTableHeaders($is_display, $fields_meta, $fields_cnt, $analyzed_sql);
    $url_query='';
    echo '<tbody>' . "\n";
    PMA_displayTableBody($dt_result, $is_display, $map, $analyzed_sql);
    if ($_SESSION['userconf']['disp_direction'] == 'vertical') {
        PMA_displayVerticalTable();
    } 
    unset($vertical_display);
    echo '</tbody>' . "\n";
    ?>
</table>

    <?php

    if ($is_display['del_lnk'] == 'dr' && $is_display['del_lnk'] != 'kp') {

        $delete_text = $is_display['del_lnk'] == 'dr' ? $GLOBALS['strDelete'] : $GLOBALS['strKill'];

        $uncheckall_url = 'sql.php?'
                  . PMA_generate_common_url($db, $table)
                  . '&amp;sql_query=' . urlencode($sql_query)
                  . '&amp;goto=' . $goto;
        $checkall_url = $uncheckall_url . '&amp;checkall=1';

        if ($_SESSION['userconf']['disp_direction'] == 'vertical') {
            $checkall_params['onclick'] = 'if (setCheckboxes(\'rowsDeleteForm\', true)) return false;';
            $uncheckall_params['onclick'] = 'if (setCheckboxes(\'rowsDeleteForm\', false)) return false;';
        } else {
            $checkall_params['onclick'] = 'if (markAllRows(\'rowsDeleteForm\')) return false;';
            $uncheckall_params['onclick'] = 'if (unMarkAllRows(\'rowsDeleteForm\')) return false;';
        }
        $checkall_link = PMA_linkOrButton($checkall_url, $GLOBALS['strCheckAll'], $checkall_params, false);
        $uncheckall_link = PMA_linkOrButton($uncheckall_url, $GLOBALS['strUncheckAll'], $uncheckall_params, false);
        if ($_SESSION['userconf']['disp_direction'] != 'vertical') {
            echo '<img class="selectallarrow" width="38" height="22"'
                .' src="' . $GLOBALS['pmaThemeImage'] . 'arrow_' . $GLOBALS['text_dir'] . '.png' . '"'
                .' alt="' . $GLOBALS['strWithChecked'] . '" />';
        }
        echo $checkall_link . "\n"
            .' / ' . "\n"
            .$uncheckall_link . "\n"
            .'<i>' . $GLOBALS['strWithChecked'] . '</i>' . "\n";

        if ($GLOBALS['cfg']['PropertiesIconic']) {
            PMA_buttonOrImage('submit_mult', 'mult_submit',
                'submit_mult_change', $GLOBALS['strChange'], 'b_edit.png');
            PMA_buttonOrImage('submit_mult', 'mult_submit',
                'submit_mult_delete', $delete_text, 'b_drop.png');
            if ($analyzed_sql[0]['querytype'] == 'SELECT') {
                PMA_buttonOrImage('submit_mult', 'mult_submit',
                    'submit_mult_export', $GLOBALS['strExport'],
                    'b_tblexport.png');
            }
            echo "\n";
        } else {
            echo ' <input type="submit" name="submit_mult"'
                .' value="' . htmlspecialchars($GLOBALS['strEdit']) . '"'
                .' title="' . $GLOBALS['strEdit'] . '" />' . "\n";
            echo ' <input type="submit" name="submit_mult"'
                .' value="' . htmlspecialchars($delete_text) . '"'
                .' title="' . $delete_text . '" />' . "\n";
            if ($analyzed_sql[0]['querytype'] == 'SELECT') {
                echo ' <input type="submit" name="submit_mult"'
                    .' value="' . htmlspecialchars($GLOBALS['strExport']) . '"'
                    .' title="' . $GLOBALS['strExport'] . '" />' . "\n";
            }
        }
        echo '<input type="hidden" name="sql_query"'
            .' value="' . htmlspecialchars($sql_query) . '" />' . "\n";
        echo '<input type="hidden" name="url_query"'
            .' value="' . $GLOBALS['url_query'] . '" />' . "\n";
        echo '</form>' . "\n";
    }

    if ($is_display['nav_bar'] == '1') {
        echo '<br />' . "\n";
        PMA_displayTableNavigation($pos_next, $pos_prev, $sql_query);
    } elseif (!isset($GLOBALS['printview']) || $GLOBALS['printview'] != '1') {
        echo "\n" . '<br /><br />' . "\n";
    }
    
    if (!isset($GLOBALS['printview']) || $GLOBALS['printview'] != '1') {
        PMA_displayResultsOperations($the_disp_mode, $analyzed_sql);
    }
}

function default_function($buffer) {
    $buffer = htmlspecialchars($buffer);
    $buffer = str_replace("\011", ' &nbsp;&nbsp;&nbsp;',
        str_replace('  ', ' &nbsp;', $buffer));
    $buffer = preg_replace("@((\015\012)|(\015)|(\012))@", '<br />', $buffer);

    return $buffer;
}

function PMA_displayResultsOperations($the_disp_mode, $analyzed_sql) {
    global $db, $table, $sql_query, $unlim_num_rows;

    $header_shown = FALSE;
    $header = '<fieldset><legend>' . $GLOBALS['strQueryResultsOperations'] . '</legend>';

    if ($the_disp_mode[6] == '1' || $the_disp_mode[9] == '1') {
        if ($the_disp_mode[9] == '1') {

            if (!$header_shown) {
                echo $header;
                $header_shown = TRUE;
            }

            $url_query = '?'
                       . PMA_generate_common_url($db, $table)
                       . '&amp;printview=1'
                       . '&amp;sql_query=' . urlencode($sql_query);
            echo '    <!-- Print view -->' . "\n";
            echo PMA_linkOrButton(
                'sql.php' . $url_query,
                ($GLOBALS['cfg']['PropertiesIconic'] ? '<img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 'b_print.png" height="16" width="16" alt="' . $GLOBALS['strPrintView'] . '"/>' : '') . $GLOBALS['strPrintView'],
                '', true, true, 'print_view') . "\n";

            if (! $_SESSION['userconf']['dontlimitchars']) {
                echo   '    &nbsp;&nbsp;' . "\n";
                echo PMA_linkOrButton(
                    'sql.php' . $url_query . '&amp;dontlimitchars=1',
                    ($GLOBALS['cfg']['PropertiesIconic'] ? '<img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 'b_print.png" height="16" width="16" alt="' . $GLOBALS['strPrintViewFull'] . '"/>' : '') . $GLOBALS['strPrintViewFull'],
                    '', true, true, 'print_view') . "\n";
            }
        }

        echo "\n";
    }

    if (isset($analyzed_sql[0]) && $analyzed_sql[0]['querytype'] == 'SELECT' && !isset($printview)) {
        if (isset($analyzed_sql[0]['table_ref'][0]['table_true_name']) && !isset($analyzed_sql[0]['table_ref'][1]['table_true_name'])) {
            $single_table   = '&amp;single_table=true';
        } else {
            $single_table   = '';
        }
        if (!$header_shown) {
            echo $header;
            $header_shown = TRUE;
        }
        echo '    <!-- Export -->' . "\n";
        echo   '    &nbsp;&nbsp;' . "\n";
        echo PMA_linkOrButton(
            'tbl_export.php' . $url_query . '&amp;unlim_num_rows=' . $unlim_num_rows . $single_table,
            ($GLOBALS['cfg']['PropertiesIconic'] ? '<img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 'b_tblexport.png" height="16" width="16" alt="' . $GLOBALS['strExport'] . '" />' : '') . $GLOBALS['strExport'],
            '', true, true, '') . "\n";
    }

    if (PMA_MYSQL_INT_VERSION >= 50000) {
        if (!$header_shown) {
            echo $header;
            $header_shown = TRUE;
        }
        echo '    <!-- Create View -->' . "\n";
        echo '    &nbsp;&nbsp;' . "\n";
        echo PMA_linkOrButton(
            'view_create.php' . $url_query,
            ($GLOBALS['cfg']['PropertiesIconic'] ? '<img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 's_views.png" height="16" width="16" alt="CREATE VIEW" />' : '') . 'CREATE VIEW',
            '', true, true, '') . "\n";
    }

    if ($header_shown) {
        echo '</fieldset><br />';
    }
}
?>
