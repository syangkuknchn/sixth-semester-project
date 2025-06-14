<?php 
session_start();
if (isset($_SESSION['unique_id'])) {
    include_once "config.php";
    include_once "encryption.php";  

    if (isset($_POST['incoming_id']) && isset($_POST['message'])) {
        $outgoing_unique_id = $_SESSION['unique_id'];
        $incoming_unique_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = $_POST['message']; 

        if (!empty($message)) {

            $key = '12345678901234567890123456789012'; // 32 chars for AES-256

            
            $encrypted_message = encryptMessage($message, $key);

            
            $encrypted_message = mysqli_real_escape_string($conn, $encrypted_message);

            // Fetch actual sender and receiver IDs from users table
            $sender_result = mysqli_query($conn, "SELECT id FROM users WHERE unique_id = {$outgoing_unique_id}");
            $receiver_result = mysqli_query($conn, "SELECT id FROM users WHERE unique_id = {$incoming_unique_id}");

            if (mysqli_num_rows($sender_result) > 0 && mysqli_num_rows($receiver_result) > 0) {
                $sender_row = mysqli_fetch_assoc($sender_result);
                $receiver_row = mysqli_fetch_assoc($receiver_result);

                $sender_id = $sender_row['id'];
                $receiver_id = $receiver_row['id'];

                // Insert with foreign key values, store encrypted message!
                $sql = mysqli_query($conn, "INSERT INTO messages (msg, sender_id, receiver_id, incoming_msg_id, outgoing_msg_id)
                                            VALUES ('{$encrypted_message}', {$sender_id}, {$receiver_id}, {$incoming_unique_id}, {$outgoing_unique_id})")
                       or die(mysqli_error($conn));

                if ($sql) {
                    echo "Message inserted with encryption!";
                }
            } else {
                echo "Sender or Receiver not found in database";
            }
        }
    } else {
        echo "Missing required fields.";
    }
} else {
    header("location: ../login.php");
    exit();
}
?>
