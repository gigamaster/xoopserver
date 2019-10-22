<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */

if (!defined('PMA_MYSQL_CLIENT_API')) {
    $client_api = explode('.', mysqli_get_client_info());
    define('PMA_MYSQL_CLIENT_API', (int)sprintf('%d%02d%02d', $client_api[0], $client_api[1], intval($client_api[2])));
    unset($client_api);
}


if (! defined('MYSQLI_BINARY_FLAG')) {
   define('MYSQLI_BINARY_FLAG', 128);
}


if (! defined('MYSQLI_TYPE_NEWDECIMAL')) {
    define('MYSQLI_TYPE_NEWDECIMAL', 246);
}
if (! defined('MYSQLI_TYPE_BIT')) {
    define('MYSQLI_TYPE_BIT', 16);
}


function PMA_DBI_connect($user, $password, $is_controluser = false)
{
    $server_port   = (empty($GLOBALS['cfg']['Server']['port']))
                   ? false
                   : (int) $GLOBALS['cfg']['Server']['port'];

    if (strtolower($GLOBALS['cfg']['Server']['connect_type']) == 'tcp') {
        $GLOBALS['cfg']['Server']['socket'] = '';
    }

    $server_socket = (empty($GLOBALS['cfg']['Server']['socket']))
                   ? null
                   : $GLOBALS['cfg']['Server']['socket'];

    $link = mysqli_init();

    mysqli_options($link, MYSQLI_OPT_LOCAL_INFILE, true);

    $client_flags = 0;

    /* Optionally compress connection */
    if ($GLOBALS['cfg']['Server']['compress'] && defined('MYSQLI_CLIENT_COMPRESS')) {
        $client_flags |= MYSQLI_CLIENT_COMPRESS;
    }

    /* Optionally enable SSL */
    if ($GLOBALS['cfg']['Server']['ssl'] && defined('MYSQLI_CLIENT_SSL')) {
        $client_flags |= MYSQLI_CLIENT_SSL;
    }

    $return_value = @mysqli_real_connect($link, $GLOBALS['cfg']['Server']['host'], $user, $password, false, $server_port, $server_socket, $client_flags);

    // Retry with empty password if we're allowed to
    if ($return_value == false && isset($cfg['Server']['nopassword']) && $cfg['Server']['nopassword'] && !$is_controluser) {
        $return_value = @mysqli_real_connect($link, $GLOBALS['cfg']['Server']['host'], $user, '', false, $server_port, $server_socket, $client_flags);
    }

    if ($return_value == false) {
        if ($is_controluser) {
            if (! defined('PMA_DBI_CONNECT_FAILED_CONTROLUSER')) {
                define('PMA_DBI_CONNECT_FAILED_CONTROLUSER', true);
            }
            return false;
        }
        PMA_auth_fails();
    } // end if

    PMA_DBI_postConnect($link, $is_controluser);

    return $link;
}


function PMA_DBI_select_db($dbname, $link = null)
{
    if (empty($link)) {
        if (isset($GLOBALS['userlink'])) {
            $link = $GLOBALS['userlink'];
        } else {
            return false;
        }
    }
    if (PMA_MYSQL_INT_VERSION < 40100) {
        $dbname = PMA_convert_charset($dbname);
    }
    return mysqli_select_db($link, $dbname);
}


function PMA_DBI_try_query($query, $link = null, $options = 0)
{
    if ($options == ($options | PMA_DBI_QUERY_STORE)) {
        $method = MYSQLI_STORE_RESULT;
    } elseif ($options == ($options | PMA_DBI_QUERY_UNBUFFERED)) {
        $method = MYSQLI_USE_RESULT;
    } else {
        $method = 0;
    }

    if (empty($link)) {
        if (isset($GLOBALS['userlink'])) {
            $link = $GLOBALS['userlink'];
        } else {
            return false;
        }
    }
    if (defined('PMA_MYSQL_INT_VERSION') && PMA_MYSQL_INT_VERSION < 40100) {
        $query = PMA_convert_charset($query);
    }
    return mysqli_query($link, $query, $method);

}

function PMA_mysqli_fetch_array($result, $type = false)
{
    if ($type != false) {
        $data = @mysqli_fetch_array($result, $type);
    } else {
        $data = @mysqli_fetch_array($result);
    }

    if (! $data) {
        return $data;
    }

    if (!defined('PMA_MYSQL_INT_VERSION') || PMA_MYSQL_INT_VERSION >= 40100
      || !(isset($GLOBALS['cfg']['AllowAnywhereRecoding'])
        && $GLOBALS['cfg']['AllowAnywhereRecoding']
        && $GLOBALS['allow_recoding'])) {
        return $data;
    }

    $ret    = array();
    $num    = mysqli_num_fields($result);
    if ($num > 0) {
        $fields = PMA_DBI_get_fields_meta($result);
    }

    if (!$fields) {
        return $data;
    }
    $i = 0;
    for ($i = 0; $i < $num; $i++) {
        if (!isset($fields[$i]->type)) {
     
            if (isset($data[$i])) {
                $ret[$i] = PMA_convert_display_charset($data[$i]);
            }
            if (isset($fields[$i]->name) && isset($data[$fields[$i]->name])) {
                $ret[PMA_convert_display_charset($fields[$i]->name)] =
                    PMA_convert_display_charset($data[$fields[$i]->name]);
            }
        } else {
       
            if (stristr($fields[$i]->type, 'BLOB')
              || stristr($fields[$i]->type, 'BINARY')) {
                if (isset($data[$i])) {
                    $ret[$i] = $data[$i];
                }
                if (isset($data[$fields[$i]->name])) {
                    $ret[PMA_convert_display_charset($fields[$i]->name)] =
                        $data[$fields[$i]->name];
                }
            } else {
                if (isset($data[$i])) {
                    $ret[$i] = PMA_convert_display_charset($data[$i]);
                }
                if (isset($data[$fields[$i]->name])) {
                    $ret[PMA_convert_display_charset($fields[$i]->name)] =
                        PMA_convert_display_charset($data[$fields[$i]->name]);
                }
            }
        }
    }
    return $ret;
}

function PMA_DBI_fetch_array($result)
{
    return PMA_mysqli_fetch_array($result, MYSQLI_BOTH);
}

function PMA_DBI_fetch_assoc($result)
{
    return PMA_mysqli_fetch_array($result, MYSQLI_ASSOC);
}


function PMA_DBI_fetch_row($result)
{
    return PMA_mysqli_fetch_array($result, MYSQLI_NUM);
}


function PMA_DBI_free_result()
{
    foreach (func_get_args() as $result) {
        if (is_object($result)
          && is_a($result, 'mysqli_result')) {
            mysqli_free_result($result);
        }
    }
}


function PMA_DBI_get_host_info($link = null)
{
    if (null === $link) {
        if (isset($GLOBALS['userlink'])) {
            $link = $GLOBALS['userlink'];
        } else {
            return false;
        }
    }
    return mysqli_get_host_info($link);
}


function PMA_DBI_get_proto_info($link = null)
{
    if (null === $link) {
        if (isset($GLOBALS['userlink'])) {
            $link = $GLOBALS['userlink'];
        } else {
            return false;
        }
    }
    return mysqli_get_proto_info($link);
}


function PMA_DBI_get_client_info()
{
    return mysqli_get_client_info();
}


function PMA_DBI_getError($link = null)
{
    $GLOBALS['errno'] = 0;

    if (null === $link && isset($GLOBALS['userlink'])) {
        $link =& $GLOBALS['userlink'];

    }

    if (null !== $link) {
        $error_number = mysqli_errno($link);
        $error_message = mysqli_error($link);
    } else {
        $error_number = mysqli_connect_errno();
        $error_message = mysqli_connect_error();
    }
    if (0 == $error_number) {
        return false;
    }

    $GLOBALS['errno'] = $error_number;

    if (! empty($error_message)) {
        $error_message = PMA_DBI_convert_message($error_message);
    }

    if ($error_number == 2002) {
        $error = '#' . ((string) $error_number) . ' - ' . $GLOBALS['strServerNotResponding'] . ' ' . $GLOBALS['strSocketProblem'];
    } elseif (defined('PMA_MYSQL_INT_VERSION') && PMA_MYSQL_INT_VERSION >= 40100) {
        $error = '#' . ((string) $error_number) . ' - ' . $error_message;
    } else {
        $error = '#' . ((string) $error_number) . ' - ' . PMA_convert_display_charset($error_message);
    }
    return $error;
}


function PMA_DBI_close($link = null)
{
    if (empty($link)) {
        if (isset($GLOBALS['userlink'])) {
            $link = $GLOBALS['userlink'];
        } else {
            return false;
        }
    }
    return @mysqli_close($link);
}


function PMA_DBI_num_rows($result)
{
    if (!is_bool($result)) {
        return @mysqli_num_rows($result);
    } else {
        return 0;
    }
}


function PMA_DBI_insert_id($link = '')
{
    if (empty($link)) {
        if (isset($GLOBALS['userlink'])) {
            $link = $GLOBALS['userlink'];
        } else {
            return false;
        }
    }
    return mysqli_insert_id($link);
}


function PMA_DBI_affected_rows($link = null)
{
    if (empty($link)) {
        if (isset($GLOBALS['userlink'])) {
            $link = $GLOBALS['userlink'];
        } else {
            return false;
        }
    }
    return mysqli_affected_rows($link);
}


function PMA_DBI_get_fields_meta($result)
{
    // Build an associative array for a type look up
    $typeAr = array();
    $typeAr[MYSQLI_TYPE_DECIMAL]     = 'real';
    $typeAr[MYSQLI_TYPE_NEWDECIMAL]  = 'real';
    $typeAr[MYSQLI_TYPE_BIT]         = 'int';
    $typeAr[MYSQLI_TYPE_TINY]        = 'int';
    $typeAr[MYSQLI_TYPE_SHORT]       = 'int';
    $typeAr[MYSQLI_TYPE_LONG]        = 'int';
    $typeAr[MYSQLI_TYPE_FLOAT]       = 'real';
    $typeAr[MYSQLI_TYPE_DOUBLE]      = 'real';
    $typeAr[MYSQLI_TYPE_NULL]        = 'null';
    $typeAr[MYSQLI_TYPE_TIMESTAMP]   = 'timestamp';
    $typeAr[MYSQLI_TYPE_LONGLONG]    = 'int';
    $typeAr[MYSQLI_TYPE_INT24]       = 'int';
    $typeAr[MYSQLI_TYPE_DATE]        = 'date';
    $typeAr[MYSQLI_TYPE_TIME]        = 'time';
    $typeAr[MYSQLI_TYPE_DATETIME]    = 'datetime';
    $typeAr[MYSQLI_TYPE_YEAR]        = 'year';
    $typeAr[MYSQLI_TYPE_NEWDATE]     = 'date';
    $typeAr[MYSQLI_TYPE_ENUM]        = 'unknown';
    $typeAr[MYSQLI_TYPE_SET]         = 'unknown';
    $typeAr[MYSQLI_TYPE_TINY_BLOB]   = 'blob';
    $typeAr[MYSQLI_TYPE_MEDIUM_BLOB] = 'blob';
    $typeAr[MYSQLI_TYPE_LONG_BLOB]   = 'blob';
    $typeAr[MYSQLI_TYPE_BLOB]        = 'blob';
    $typeAr[MYSQLI_TYPE_VAR_STRING]  = 'string';
    $typeAr[MYSQLI_TYPE_STRING]      = 'string';

    $typeAr[MYSQLI_TYPE_GEOMETRY]    = 'unknown';

    $fields = mysqli_fetch_fields($result);

    if (!is_array($fields)) {
        return false;
    }

    foreach ($fields as $k => $field) {
        $fields[$k]->_type = $field->type;
        $fields[$k]->type = $typeAr[$field->type];
        $fields[$k]->_flags = $field->flags;
        $fields[$k]->flags = PMA_DBI_field_flags($result, $k);


        $fields[$k]->multiple_key
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_MULTIPLE_KEY_FLAG);
        $fields[$k]->primary_key
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_PRI_KEY_FLAG);
        $fields[$k]->unique_key
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_UNIQUE_KEY_FLAG);
        $fields[$k]->not_null
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_NOT_NULL_FLAG);
        $fields[$k]->unsigned
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_UNSIGNED_FLAG);
        $fields[$k]->zerofill
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_ZEROFILL_FLAG);
        $fields[$k]->numeric
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_NUM_FLAG);
        $fields[$k]->blob
            = (int) (bool) ($fields[$k]->_flags & MYSQLI_BLOB_FLAG);
    }
    return $fields;
}


function PMA_DBI_num_fields($result)
{
    return mysqli_num_fields($result);
}


function PMA_DBI_field_len($result, $i)
{
    $info = mysqli_fetch_field_direct($result, $i);

    return @$info->length;
}


function PMA_DBI_field_name($result, $i)
{
    $info = mysqli_fetch_field_direct($result, $i);
    return $info->name;
}


function PMA_DBI_field_flags($result, $i)
{
    $f = mysqli_fetch_field_direct($result, $i);
    $f = $f->flags;
    $flags = '';
    if ($f & MYSQLI_UNIQUE_KEY_FLAG)     { $flags .= 'unique ';}
    if ($f & MYSQLI_NUM_FLAG)            { $flags .= 'num ';}
    if ($f & MYSQLI_PART_KEY_FLAG)       { $flags .= 'part_key ';}
    if ($f & MYSQLI_TYPE_SET)            { $flags .= 'set ';}
    if ($f & MYSQLI_TIMESTAMP_FLAG)      { $flags .= 'timestamp ';}
    if ($f & MYSQLI_AUTO_INCREMENT_FLAG) { $flags .= 'auto_increment ';}
    if ($f & MYSQLI_TYPE_ENUM)           { $flags .= 'enum ';}
    if ($f & MYSQLI_BINARY_FLAG)         { $flags .= 'binary ';}
    if ($f & MYSQLI_ZEROFILL_FLAG)       { $flags .= 'zerofill ';}
    if ($f & MYSQLI_UNSIGNED_FLAG)       { $flags .= 'unsigned ';}
    if ($f & MYSQLI_BLOB_FLAG)           { $flags .= 'blob ';}
    if ($f & MYSQLI_MULTIPLE_KEY_FLAG)   { $flags .= 'multiple_key ';}
    if ($f & MYSQLI_UNIQUE_KEY_FLAG)     { $flags .= 'unique_key ';}
    if ($f & MYSQLI_PRI_KEY_FLAG)        { $flags .= 'primary_key ';}
    if ($f & MYSQLI_NOT_NULL_FLAG)       { $flags .= 'not_null ';}
    return PMA_convert_display_charset(trim($flags));
}

?>
