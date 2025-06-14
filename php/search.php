<?php
session_start();
include_once "config.php";
include_once "encryption.php";  // <-- Make sure this exists and contains decryptMessage()
$key = '12345678901234567890123456789012';  // Same key used for encryption

if(!isset($_SESSION['unique_id'])){
  exit; // User not logged in
}

$outgoing_id = $_SESSION['unique_id'];

if(isset($_POST['searchTerm'])){
  $searchTerm = mysqli_real_escape_string($conn, $_POST['searchTerm']);
  $output = "";

  $sql = "SELECT * FROM users 
          WHERE unique_id != {$outgoing_id} 
            AND (fname LIKE '%{$searchTerm}%' OR lname LIKE '%{$searchTerm}%')
          ORDER BY status DESC, fname ASC";
  
  $query = mysqli_query($conn, $sql);

  if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
      $sql2 = "SELECT * FROM messages 
               WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$row['unique_id']}) 
                  OR (outgoing_msg_id = {$row['unique_id']} AND incoming_msg_id = {$outgoing_id}) 
               ORDER BY msg_id DESC LIMIT 1";

      $query2 = mysqli_query($conn, $sql2);
      $last_msg = "No messages yet";
      if(mysqli_num_rows($query2) > 0){
        $msg_row = mysqli_fetch_assoc($query2);
        
        // Decrypt the message
        $decrypted = decryptMessage($msg_row['msg'], $key);
        $msg_content = $decrypted !== false ? $decrypted : "[Unable to decrypt]";

        $prefix = ($msg_row['outgoing_msg_id'] == $outgoing_id) ? "You: " : "";
        $msg_text = strlen($msg_content) > 28 ? substr($msg_content, 0, 28) . '...' : $msg_content;
        $last_msg = $prefix . htmlspecialchars($msg_text);
      }

      $offline = ($row['status'] == "Offline now") ? "offline" : "";

      $output .= '<a href="chat.php?user_id=' . $row['unique_id'] . '">
                    <div class="content">
                      <img src="php/images/' . htmlspecialchars($row['img']) . '" alt="User image">
                      <div class="details">
                        <span>' . htmlspecialchars($row['fname'] . " " . $row['lname']) . '</span>
                        <p>' . $last_msg . '</p>
                      </div>
                    </div>
                    <div class="status-dot ' . $offline . '"><i class="fas fa-circle"></i></div>
                  </a>';
    }
  } else {
    $output = "<p class='no-users'>No users found matching your search.</p>";
  }

  echo $output;
} else {
  echo "<p class='no-users'>Invalid search request.</p>";
}
