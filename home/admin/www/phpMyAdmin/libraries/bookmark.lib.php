<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */

function PMA_getBookmarksParam()
{
    global $server;

    $cfgBookmark = '';

    if ($server == 0) {
        return '';
    }

    $cfgBookmark['user']  = $GLOBALS['cfg']['Server']['user'];
    $cfgBookmark['db']    = $GLOBALS['cfg']['Server']['pmadb'];
    $cfgBookmark['table'] = $GLOBALS['cfg']['Server']['bookmarktable'];

    return $cfgBookmark;
} 

function PMA_listBookmarks($db, $cfgBookmark)
{
    global $controllink;

    if (empty($cfgBookmark['db']) || empty($cfgBookmark['table'])) {
        return '';
    }

    $query  = 'SELECT label, id FROM '. PMA_backquote($cfgBookmark['db']) . '.' . PMA_backquote($cfgBookmark['table'])
            . ' WHERE dbase = \'' . PMA_sqlAddslashes($db) . '\''
            . ' AND (user = \'' . PMA_sqlAddslashes($cfgBookmark['user']) . '\''
            . '      OR user = \'\')'
            . ' ORDER BY label';
    $result = PMA_DBI_query($query, $controllink, PMA_DBI_QUERY_STORE);

    if ($result && PMA_DBI_num_rows($result) > 0) {
        while ($row = PMA_DBI_fetch_row($result)) {
            $bookmark_list[$row[1]] = $row[0];
        } 
        return $bookmark_list;
    }
    else {
        return FALSE;
    }
} 

function PMA_queryBookmarks($db, $cfgBookmark, $id, $id_field = 'id', $action_bookmark_all = FALSE)
{
    global $controllink;

    if (empty($cfgBookmark['db']) || empty($cfgBookmark['table'])) {
        return '';
    }

    $query          = 'SELECT query FROM ' . PMA_backquote($cfgBookmark['db']) . '.' . PMA_backquote($cfgBookmark['table'])
                    . ' WHERE dbase = \'' . PMA_sqlAddslashes($db) . '\''
                    . ($action_bookmark_all? '' : ' AND (user = \'' . PMA_sqlAddslashes($cfgBookmark['user']) . '\''
                    . '      OR user = \'\')')
                    . ' AND ' . PMA_backquote($id_field) . ' = ' . $id;
    $result = PMA_DBI_try_query($query, $controllink);
    if (!$result) {
        return FALSE;
    }
    list($bookmark_query) = PMA_DBI_fetch_row($result) or array(FALSE);

    return $bookmark_query;
}


function &PMA_queryDBBookmarks($db, $cfgBookmark, &$table_array)
{
    global $controllink;
    $bookmarks = array();

    if (empty($cfgBookmark['db']) || empty($cfgBookmark['table'])) {
        return $bookmarks;
    }

    $search_for = array();
    foreach ($table_array AS $table => $table_sortkey) {
        $search_for[] = "'" . PMA_sqlAddslashes($table) . "'";
    }

    $query          = 'SELECT label, query FROM ' . PMA_backquote($cfgBookmark['db']) . '.' . PMA_backquote($cfgBookmark['table'])
                    . ' WHERE dbase = \'' . PMA_sqlAddslashes($db) . '\''
                    . (count($search_for) > 0 ? ' AND label IN (' . implode(', ', $search_for) . ')' : '');
    $result = PMA_DBI_try_query($query, $controllink, PMA_DBI_QUERY_STORE);
    if (!$result || PMA_DBI_num_rows($result) < 1) {
        return $bookmarks;
    }
    while ($row = PMA_DBI_fetch_assoc($result)) {
        $bookmarks[$row['label']] = $row['query'];
    }

    return $bookmarks;
} 

function PMA_addBookmarks($fields, $cfgBookmark, $all_users = false)
{
    global $controllink;

    $query = 'INSERT INTO ' . PMA_backquote($cfgBookmark['db']) . '.' . PMA_backquote($cfgBookmark['table'])
           . ' (id, dbase, user, query, label) VALUES (NULL, \'' . PMA_sqlAddslashes($fields['dbase']) . '\', \'' . ($all_users ? '' : PMA_sqlAddslashes($fields['user'])) . '\', \'' . PMA_sqlAddslashes(urldecode($fields['query'])) . '\', \'' . PMA_sqlAddslashes($fields['label']) . '\')';
    $result   = PMA_DBI_query($query, $controllink);

    return TRUE;
}

function PMA_deleteBookmarks($db, $cfgBookmark, $id)
{
    global $controllink;

    $query  = 'DELETE FROM ' . PMA_backquote($cfgBookmark['db']) . '.' . PMA_backquote($cfgBookmark['table'])
            . ' WHERE (user = \'' . PMA_sqlAddslashes($cfgBookmark['user']) . '\''
            . '        OR user = \'\')'
            . ' AND id = ' . $id;
    $result = PMA_DBI_try_query($query, $controllink);
}

if (! isset($GLOBALS['cfgRelation'])) {
    require_once './libraries/relation.lib.php';
    $GLOBALS['cfgRelation'] = PMA_getRelationsParam();
}

if ($GLOBALS['cfgRelation']['bookmarkwork']) {
    $cfg['Bookmark'] = PMA_getBookmarksParam();
} else {
    $cfg['Bookmark'] = array();
}

?>
