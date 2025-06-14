<?php 
session_start();
if (isset($_SESSION['unique_id'])) {
    include_once "config.php";
    include_once "encryption.php";
    $key = '12345678901234567890123456789012'; // Same key used in encryption.php

    if (isset($_POST['incoming_id']) && !empty($_POST['incoming_id'])) {
        $outgoing_id = $_SESSION['unique_id'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $output = "";

        // Mark incoming messages as read
        $update = "UPDATE messages SET is_read = 1 
                   WHERE incoming_msg_id = {$outgoing_id} 
                   AND outgoing_msg_id = {$incoming_id} 
                   AND is_read = 0";
        mysqli_query($conn, $update);

        // Fetch messages
        $sql = "SELECT * FROM messages 
                LEFT JOIN users ON users.unique_id = messages.outgoing_msg_id
                WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
                OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id}) 
                ORDER BY msg_id";

        $query = mysqli_query($conn, $sql);

        if (mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                $decrypted = decryptMessage($row['msg'], $key);
                $msgText = $decrypted !== false ? htmlspecialchars($decrypted) : "[Decryption error]";
                $msgTime = date("g:i A", strtotime($row['sent_at'])); // Format as 3:45 PM

                if ($row['outgoing_msg_id'] == $outgoing_id) {
                    // Outgoing message
                    $output .= '<div class="chat outgoing">
                                    <div class="details">
                                        <p>' . $msgText . '<br><small class="msg-time">' . $msgTime . '</small></p>
                                    </div>
                                </div>';
                } else {
                    // Incoming message
                    $output .= '<div class="chat incoming">
                                    <img src="php/images/' . $row['img'] . '" alt="">
                                    <div class="details">
                                        <p>' . $msgText . '<br><small class="msg-time">' . $msgTime . '</small></p>
                                    </div>
                                </div>';
                }
            }
        } else {
            $output .= '<div class="text">No messages are available. Once you send a message they will appear here.</div>';
        }

        echo $output;
    } else {
        echo '<div class="text">Missing or invalid chat ID.</div>';
    }

} else {
    header("location: ../login.php");
    exit();
}
?>
