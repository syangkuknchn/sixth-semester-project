<?php
function encryptMessage($plaintext, $key) {
    $iv = openssl_random_pseudo_bytes(16); // 16 bytes for AES-256-CBC
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $encrypted = base64_encode($iv . $ciphertext); // Store both IV and ciphertext
    return $encrypted;
}

function decryptMessage($encrypted, $key) {
    $data = base64_decode($encrypted);
    if ($data === false || strlen($data) < 17) return ''; // sanity check

    $iv = substr($data, 0, 16);
    $ciphertext = substr($data, 16);

    return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}
?>
