<?php
session_start();
include_once "config.php";

// ✅ Remove method check to ensure AJAX works
// if ($_SERVER['REQUEST_METHOD'] === 'POST') { ← REMOVE THIS LINE

if (
    !empty($_POST['fname']) &&
    !empty($_POST['lname']) &&
    !empty($_POST['email']) &&
    !empty($_POST['password']) &&
    isset($_FILES['image']) &&
    $_FILES['image']['error'] === 0
) {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
        if (mysqli_num_rows($check_email) > 0) {
            echo "$email - This email already exists!";
            exit;
        }

        $img_name = $_FILES['image']['name'];
        $img_type = $_FILES['image']['type'];
        $tmp_name = $_FILES['image']['tmp_name'];

        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $valid_ext = ["jpeg", "jpg", "png"];
        $valid_type = ["image/jpeg", "image/jpg", "image/png"];

        if (in_array($img_ext, $valid_ext) && in_array($img_type, $valid_type)) {
            $new_img_name = time() . $img_name;
            if (move_uploaded_file($tmp_name, "images/" . $new_img_name)) {
                $ran_id = rand(time(), 100000000);
                $status = "Active now";
                $encrypt_pass = md5($password);

                $insert_query = mysqli_query($conn, "INSERT INTO users (unique_id, fname, lname, email, password, img, status)
                    VALUES ($ran_id, '$fname', '$lname', '$email', '$encrypt_pass', '$new_img_name', '$status')");

                if ($insert_query) {
                    $select_user = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
                    if (mysqli_num_rows($select_user) > 0) {
                        $user = mysqli_fetch_assoc($select_user);
                        $user_id = $user['id'];

                        // Generate RSA keys silently
                        $keypair = @openssl_pkey_new([
                            "private_key_bits" => 2048,
                            "private_key_type" => OPENSSL_KEYTYPE_RSA,
                        ]);

                        if ($keypair) {
                            @openssl_pkey_export($keypair, $private_key);
                            $public_key = @openssl_pkey_get_details($keypair)['key'];

                            $public_key = mysqli_real_escape_string($conn, $public_key);
                            $private_key = mysqli_real_escape_string($conn, $private_key);

                            mysqli_query($conn, "UPDATE users SET public_key = '$public_key', private_key = '$private_key' WHERE id = $user_id");
                        }

                        // ✅ Success
                        echo "success";
                        exit;
                    }
                } else {
                    echo "Something went wrong. Try again.";
                    exit;
                }
            } else {
                echo "Failed to upload image.";
                exit;
            }
        } else {
            echo "Only jpeg, jpg, png images are allowed.";
            exit;
        }
    } else {
        echo "$email is not a valid email!";
        exit;
    }
} else {
    echo "All input fields are required!";
    exit;
}

?>
