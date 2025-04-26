<?php
require_once "includes/connect.inc.php";

function getCTFStatus($conn) {
    $sql = "SELECT start_time, end_time FROM ctf_config WHERE id = 1";
    $result = fetchDataFromQuery($conn, $sql, [], "");

    if (empty($result)) {
        return ['status' => 'notconfigured'];
    }

    $now = time();
    $start_time = $result[0]['start_time'];
    $end_time = $result[0]['end_time'];

    if ($now < $start_time) {
        return ['status' => 'notstarted', 'time_left' => $start_time - $now];
    } else if ($now > $end_time) {
        return ['status' => 'ended'];
    } else {
        return ['status' => 'running', 'time_left' => $end_time - $now];
    }
}
?>
