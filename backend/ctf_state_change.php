<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/includes/connect.inc.php";
require_once __DIR__ . "/includes/verify.inc.php";

if (!verify_login($conn)) {
    onError($conn, "You are not logged in.");
}

$userInfo = getUserInfo($conn, $_SESSION['userid']);
if ($userInfo['admin'] == 0) {
    onError($conn, "You do not have permission.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'notstarted') {
        date_default_timezone_set('Asia/Singapore');
        $start_time = strtotime("2025-05-14 15:30:00");
        $end_time = $start_time + 5 * 3600;
    } elseif ($action === 'running') {
        $start_time = time() - 300; // 5 mins ago
        $end_time = time() + 5 * 3600;
    } elseif ($action === 'ended') {
        $start_time = time() - 6 * 3600;
        $end_time = time() - 3600;
    } else {
        die("Invalid action.");
    }

    // FORCE casual_mode = 0 when simulating
    $stmt = $conn->prepare("UPDATE ctf_config SET start_time = ?, end_time = ?, casual_mode = 0 WHERE id = 1");
    $stmt->bind_param("ii", $start_time, $end_time);
    $stmt->execute();

    header("Location: ../index.php?filename=adminPanel");
    exit;
} else {
    die("Invalid request.");
}
?>
