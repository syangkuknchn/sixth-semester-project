<?php
session_start();
include_once "config.php";
include_once "encryption.php";  // Make sure this file has decryptMessage()
$key = '12345678901234567890123456789012'; // same key used in encryption.php

$outgoing_id = $_SESSION['unique_id'];
$output = "";

$sql = "SELECT * FROM users WHERE unique_id != {$outgoing_id} ORDER BY unique_id DESC";
$query = mysqli_query($conn, $sql);

if (mysqli_num_rows($query) == 0) {
    $output .= "No users are available to chat";
} else {
    while ($row = mysqli_fetch_assoc($query)) {
        // 1. Get last message
        $sql2 = "SELECT * FROM messages 
                 WHERE (incoming_msg_id = {$row['unique_id']} OR outgoing_msg_id = {$row['unique_id']})
                 AND (outgoing_msg_id = {$outgoing_id} OR incoming_msg_id = {$outgoing_id})
                 ORDER BY msg_id DESC LIMIT 1";
        $query2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($query2);

        // 2. Decrypt message if exists
        if (mysqli_num_rows($query2) > 0) {
            $ciphertext = $row2['msg'];
            $decrypted = decryptMessage($ciphertext, $key);

            $result = $decrypted !== false ? $decrypted : "[Unable to decrypt]";
        } else {
            $result = "No message available";
        }

        // 3. Shorten message
        $msg = strlen($result) > 28 ? substr($result, 0, 28) . '...' : $result;

        // 4. "You:" prefix
        $you = (isset($row2['outgoing_msg_id']) && $outgoing_id == $row2['outgoing_msg_id']) ? "You: " : "";

        // 5. Unread message count
        $sql3 = "SELECT COUNT(*) AS unread_count FROM messages 
                 WHERE outgoing_msg_id = {$row['unique_id']} 
                 AND incoming_msg_id = {$outgoing_id} 
                 AND is_read = 0";
        $query3 = mysqli_query($conn, $sql3);
        $unread_row = mysqli_fetch_assoc($query3);
        $unread_count = (int)$unread_row['unread_count'];

        $badge = $unread_count > 0 ? '<div class="notif-badge">'.$unread_count.'</div>' : "";

        // 6. Status class
        $offline = ($row['status'] == "Offline now") ? "offline" : "";

        // 7. Output
        $output .= '
        <div class="user-wrapper" style="position: relative;">
            <a href="chat.php?user_id='. $row['unique_id'] .'">
                <div class="content">
                    <img src="php/images/'. $row['img'] .'" alt="">
                    <div class="details">
                        <span>'. $row['fname'] .' '. $row['lname'] .'</span>
                        <p>'. $you . htmlspecialchars($msg) .'</p>
                    </div>
                </div>
                <div class="status-dot '. $offline .'"><i class="fas fa-circle"></i></div>
                '. $badge .'
            </a>
        </div>';
    }
}

echo $output;
?>
