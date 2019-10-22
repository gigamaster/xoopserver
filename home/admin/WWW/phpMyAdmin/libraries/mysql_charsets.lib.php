<?php
/* $Id: mysql_charsets.lib.php,v 2.7.2.1 2004/02/02 09:30:01 rabus Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:

if (PMA_MYSQL_INT_VERSION >= 40100){

    $res = PMA_mysql_query('SHOW CHARACTER SET;', $userlink)
        or PMA_mysqlDie(PMA_mysql_error($userlink), 'SHOW CHARACTER SET;');

    $mysql_charsets = array();
    while ($row = PMA_mysql_fetch_array($res, MYSQL_ASSOC)) {
        $mysql_charsets[] = $row['Charset'];
        $mysql_charsets_maxlen[$row['Charset']] = $row['Maxlen'];
        $mysql_charsets_descriptions[$row['Charset']] = $row['Description'];
    }
    @mysql_free_result($res);
    unset($res, $row);

    $res = PMA_mysql_query('SHOW COLLATION;', $userlink)
        or PMA_mysqlDie(PMA_mysql_error($userlink), 'SHOW COLLATION;');

    $mysql_charsets_count = count($mysql_charsets);
    sort($mysql_charsets, SORT_STRING);

    $mysql_collations = array_flip($mysql_charsets);
    $mysql_default_collations = $mysql_collations_flat = array();;
    while ($row = PMA_mysql_fetch_array($res, MYSQL_ASSOC)) {
        if (!is_array($mysql_collations[$row['Charset']])) {
            $mysql_collations[$row['Charset']] = array($row['Collation']);
        } else {
            $mysql_collations[$row['Charset']][] = $row['Collation'];
        }
        $mysql_collations_flat[] = $row['Collation'];
        if ((isset($row['D']) && $row['D'] == 'Y') || (isset($row['Default']) && $row['Default'] == 'Yes')) {
            $mysql_default_collations[$row['Charset']] = $row['Collation'];
        }
    }

    $mysql_collations_count = count($mysql_collations_flat);
    sort($mysql_collations_flat, SORT_STRING);
    foreach($mysql_collations AS $key => $value) {
        sort($mysql_collations[$key], SORT_STRING);
        reset($mysql_collations[$key]);
    }

    @mysql_free_result($res);
    unset($res, $row);

    function PMA_getCollationDescr($collation) {
        if ($collation == 'binary') {
            return $GLOBALS['strBinary'];
        }
        $parts = explode('_', $collation);
        if (count($parts) == 1) {
            $parts[1] = 'general';
        } elseif ($parts[1] == 'ci' || $parts[1] == 'cs') {
            $parts[2] = $parts[1];
            $parts[1] = 'general';
        }
        $descr = '';
        switch ($parts[1]) {
            case 'bulgarian':
                $descr = $GLOBALS['strBulgarian'];
                break;
            case 'chinese':
                if ($parts[0] == 'gb2312' || $parts[0] == 'gbk') {
                    $descr = $GLOBALS['strSimplifiedChinese'];
                } elseif ($parts[0] == 'big5') {
                    $descr = $GLOBALS['strTraditionalChinese'];
                }
                break;
            case 'ci':
                $descr = $GLOBALS['strCaseInsensitive'];
                break;
            case 'cs':
                $descr = $GLOBALS['strCaseSensitive'];
                break;
            case 'croatian':
                $descr = $GLOBALS['strCroatian'];
                break;
            case 'czech':
                $descr = $GLOBALS['strCzech'];
                break;
            case 'danish':
                $descr = $GLOBALS['strDanish'];
                break;
            case 'english':
                $descr = $GLOBALS['strEnglish'];
                break;
            case 'estonian':
                $descr = $GLOBALS['strEstonian'];
                break;
            case 'german1':
                $descr = $GLOBALS['strGerman'] . ' (' . $GLOBALS['strDictionary'] . ')';
                break;
            case 'german2':
                $descr = $GLOBALS['strGerman'] . ' (' . $GLOBALS['strPhoneBook'] . ')';
                break;
            case 'hungarian':
                $descr = $GLOBALS['strHungarian'];
                break;
            case 'japanese':
                $descr = $GLOBALS['strJapanese'];
                break;
            case 'lithuanian':
                $descr = $GLOBALS['strLithuanian'];
                break;
            case 'korean':
                $descr = $GLOBALS['strKorean'];
                break;
            case 'swedish':
                $descr = $GLOBALS['strSwedish'];
                break;
            case 'thai':
                $descr = $GLOBALS['strThai'];
                break;
            case 'turkish':
                $descr = $GLOBALS['strTurkish'];
                break;
            case 'ukrainian':
                $descr = $GLOBALS['strUkrainian'];
                break;
            case 'bin':
                $is_bin = TRUE;
            case 'general':
                switch ($parts[0]) {
                    // Unicode charsets
                    case 'ucs2':
                    case 'utf8':
                        $descr = $GLOBALS['strUnicode'] . ' (' . $GLOBALS['strMultilingual'] . ')';
                        break;
                    // West European charsets
                    case 'ascii':
                    case 'cp850':
                    case 'dec8':
                    case 'hp8':
                    case 'latin1':
                    case 'macroman':
                        $descr = $GLOBALS['strWestEuropean'] . ' (' . $GLOBALS['strMultilingual'] . ')';
                        break;
                    // Central European charsets
                    case 'cp1250':
                    case 'cp852':
                    case 'latin2':
                    case 'macce':
                        $descr = $GLOBALS['strCentralEuropean'] . ' (' . $GLOBALS['strMultilingual'] . ')';
                        break;
                    // Russian charsets
                    case 'cp866':
                    case 'koi8r':
                        $descr = $GLOBALS['strRussian'];
                        break;
                    // Simplified Chinese charsets
                    case 'gb2312':
                    case 'gbk':
                        $descr = $GLOBALS['strSimplifiedChinese'];
                        break;
                    // Japanese charsets
                    case 'sjis':
                    case 'ujis':
                        $descr = $GLOBALS['strJapanese'];
                        break;
                    // Baltic charsets
                    case 'cp1257':
                    case 'latin7':
                        $descr = $GLOBALS['strBaltic'] . ' (' . $GLOBALS['strMultilingual'] . ')';
                        break;
                    // Other
                    case 'armscii8':
                    case 'armscii':
                        $descr = $GLOBALS['strArmenian'];
                        break;
                    case 'big5':
                        $descr = $GLOBALS['strTraditionalChinese'];
                        break;
                    case 'cp1251':
                        $descr = $GLOBALS['strCyrillic'] . ' (' . $GLOBALS['strMultilingual'] . ')';
                        break;
                    case 'cp1256':
                        $descr = $GLOBALS['strArabic'];
                        break;
                    case 'euckr':
                        $descr = $GLOBALS['strKorean'];
                        break;
                    case 'hebrew':
                        $descr = $GLOBALS['strHebrew'];
                        break;
                    case 'geostd8':
                        $descr = $GLOBALS['strGeorgian'];
                        break;
                    case 'greek':
                        $descr = $GLOBALS['strGreek'];
                        break;
                    case 'keybcs2':
                        $descr = $GLOBALS['strCzechSlovak'];
                        break;
                    case 'koi8u':
                        $descr = $GLOBALS['strUkrainian'];
                        break;
                    case 'latin5':
                        $descr = $GLOBALS['strTurkish'];
                        break;
                    case 'swe7':
                        $descr = $GLOBALS['strSwedish'];
                        break;
                    case 'tis620':
                        $descr = $GLOBALS['strThai'];
                        break;
                    default:
                        $descr = $GLOBALS['strUnknown'];
                        break;
                }
                if (!empty($is_bin)) {
                    $descr .= ', ' . $GLOBALS['strBinary'];
                }
                break;
            default: return '';
        }
        if (!empty($parts[2])) {
            if ($parts[2] == 'ci') {
                $descr .= ', ' . $GLOBALS['strCaseInsensitive'];
            } elseif ($parts[2] == 'cs') {
                $descr .= ', ' . $GLOBALS['strCaseSensitive'];
            }
        }
        return $descr;
    }

    function PMA_getDbCollation($db) {
        global $userlink;

        if (PMA_MYSQL_INT_VERSION >= 40101) {
            // MySQL 4.1.0 does not support seperate charset settings
            // for databases.

            $sql_query = 'SHOW CREATE DATABASE ' . PMA_backquote($db) . ';';
            $res = PMA_mysql_query($sql_query, $userlink) or PMA_mysqlDie(PMA_mysql_error($userlink), $sql_query);
            $row = PMA_mysql_fetch_row($res);
            mysql_free_result($res);
            $tokenized = explode(' ', $row[1]);
            unset($row, $res, $sql_query);

            for ($i = 1; $i + 3 < count($tokenized); $i++) {
                if ($tokenized[$i] == 'DEFAULT' && $tokenized[$i + 1] == 'CHARACTER' && $tokenized[$i + 2] == 'SET') {
                    // We've found the character set!
                    if (isset($tokenized[$i + 5]) && $tokenized[$i + 4] == 'COLLATE') {
                        return $tokenized[$i + 5]; // We found the collation!
                    } else {
                        // We did not find the collation, so let's return the
                        // default collation for the charset we've found.
                        return $GLOBALS['mysql_default_collations'][$tokenized [$i + 3]];
                    }
                }
            }
        }
        return '';
    }

}

?>
