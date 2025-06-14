<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();}
include_once "config.php";

$outgoing_id = $_SESSION['unique_id'];
$output = "";
$seen_ids = []; // To track already outputted users

// Fetch all users except the current logged-in user
$sql = "SELECT * FROM users WHERE unique_id != {$outgoing_id} ORDER BY unique_id DESC";
$query = mysqli_query($conn, $sql);

if (mysqli_num_rows($query) == 0) {
    $output .= "No users are available to chat";
} elseif (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        // Skip if already processed
        if (in_array($row['unique_id'], $seen_ids)) continue;
        $seen_ids[] = $row['unique_id'];

        // Get last message between current user and each listed user
        $sql2 = "SELECT * FROM messages WHERE 
                    (incoming_msg_id = {$row['unique_id']} OR outgoing_msg_id = {$row['unique_id']}) 
                    AND 
                    (outgoing_msg_id = {$outgoing_id} OR incoming_msg_id = {$outgoing_id}) 
                    ORDER BY msg_id DESC LIMIT 1";
        $query2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($query2);

        $result = (mysqli_num_rows($query2) > 0) ? $row2['msg'] : "No message available";

        // Truncate long messages
        $msg = (strlen($result) > 28) ? substr($result, 0, 28) . '...' : $result;

        // Prefix 'You:' if last message was sent by logged-in user
        $you = (isset($row2['outgoing_msg_id']) && $outgoing_id == $row2['outgoing_msg_id']) ? "You: " : "";

        $offline = ($row['status'] == "Offline now") ? "offline" : "";

        $output .= '<a href="chat.php?user_id=' . $row['unique_id'] . '">
                        <div class="content">
                            <img src="php/images/' . $row['img'] . '" alt="">
                            <div class="details">
                                <span>' . $row['fname'] . " " . $row['lname'] . '</span>
                                <p>' . $you . $msg . '</p>
                            </div>
                        </div>
                        <div class="status-dot ' . $offline . '"><i class="fas fa-circle"></i></div>
                    </a>';
    }
}

echo $output;
?>
