<?php
session_start();
include_once "config.php";
include_once "encryption.php";

if (!isset($_SESSION['unique_id'])) {
    die("No session");
}

$outgoing_id = $_SESSION['unique_id'];
$incoming_id = $_POST['incoming_id'] ?? 0;
$message = $_POST['message'] ?? '';

if (empty($incoming_id) || empty($message)) {
    die("Missing fields");
}

$sql = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $incoming_id, $outgoing_id, $message);
if ($stmt->execute()) {
    echo "Inserted successfully";
} else {
    echo "Insert failed: " . $stmt->error;
}
?>
