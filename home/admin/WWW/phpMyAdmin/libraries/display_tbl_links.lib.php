<?php
/* $Id: display_tbl_links.lib.php,v 2.6 2004/01/07 18:11:29 lem9 Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:

if (!empty($del_url) && $is_display['del_lnk'] != 'kp') {
    echo '    <td width="10" align="center" valign="' . ($bookmark_go != '' ? 'top' : 'middle') . '" bgcolor="' . $bgcolor . '">' . "\n"
       . '        <input type="checkbox" id="id_rows_to_delete' . $row_no . '" name="rows_to_delete[' . $uva_condition . ']" value="' . $del_query . '" />' . "\n"
       . '    </td>' . "\n";
}
if (!empty($edit_url)) {
    echo '    <td width="10" align="center" valign="' . ($bookmark_go != '' ? 'top' : 'middle') . '" bgcolor="' . $bgcolor . '">' . "\n"
       . PMA_linkOrButton($edit_url, $edit_str, '')
       . $bookmark_go
       . '    </td>' . "\n";
}
if (!empty($del_url)) {
    echo '    <td width="10" align="center" valign="' . ($bookmark_go != '' ? 'top' : 'middle') . '" bgcolor="' . $bgcolor . '">' . "\n"
       . PMA_linkOrButton($del_url, $del_str, (isset($js_conf) ? $js_conf : ''))
       . '    </td>' . "\n";
}
?>
