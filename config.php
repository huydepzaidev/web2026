<?php
require_once __DIR__ . '/connect.php';
$config = $conn;
if (!$config) {
    die("KHONG THE KET NOI DEN CSDL ! VUI LONG KIEM TRA LAI");
}

function _query($sql)
{
    global $config;
    return mysqli_query($config, $sql);
}
function _fetch($sql)
{
    return mysqli_fetch_array(_query($sql));
}
function isset_sql($txt)
{
    global $config;
    return mysqli_real_escape_string($config, $txt);
}
function _insert($table, $input, $output)
{
    return "INSERT INTO $table($input) VALUES($output)";
}
function _select($select, $from, $where)
{
    return "SELECT $select FROM $from WHERE $where";
}
function _update($tabname, $input_output, $where)
{
    return "UPDATE $tabname SET $input_output WHERE $where";
}
function _delete($table, $condition)
{
    global $config;
    mysqli_query($config, "DELETE FROM $table WHERE $condition");
}
function show_alert($alert)
{
    echo '<div class="' . $alert[0] . '">' . $alert[1] . '</div>';
}
function _num_rows($result)
{
    return mysqli_num_rows($result);
}
?>