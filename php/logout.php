<?php
session_start();

if (isset($_SESSION['unique_id'])) {
    include_once "config.php";

    if (isset($_GET['logout_id'])) {
        $logout_id = mysqli_real_escape_string($conn, $_GET['logout_id']);

        if (!empty($logout_id)) {
            $status = "Offline now";
            $sql = mysqli_query($conn, "UPDATE users SET status = '{$status}' WHERE unique_id = {$logout_id}");

            if ($sql) {
                session_unset();
                session_destroy();
                header("location: ../login.php");
                exit();
            } else {
                echo "Failed to update status.";
            }
        } else {
            header("location: ../users.php");
            exit();
        }
    } else {
        header("location: ../users.php");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}
?>
