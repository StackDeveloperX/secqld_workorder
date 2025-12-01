<?php
// function.php
function getEncryptionKey() {
    return base64_decode('q7gEr5TP8Y7elYr4iUQr5h+tbzyvU+jxxvlOeV5xUOc=');
}

function encrypt($string) {
    $key = getEncryptionKey();
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($string, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt($encryptedString) {
    $key = getEncryptionKey();
    $data = base64_decode($encryptedString);
    if ($data === false || strlen($data) <= 16) return false;

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);

    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

?>