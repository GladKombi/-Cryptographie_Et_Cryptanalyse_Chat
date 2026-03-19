<?php
require_once 'config.php';

// Fonctions de chiffrement
function encryptAES($plaintext, $key = null) {
    $key = $key ?: ENCRYPTION_KEY;
    $iv = openssl_random_pseudo_bytes(AES_IV_SIZE);
    $ciphertext = openssl_encrypt($plaintext, AES_METHOD, $key, OPENSSL_RAW_DATA, $iv);
    $result = base64_encode($iv . $ciphertext);
    return $result;
}

function encryptDES($plaintext, $key = null) {
    $key = $key ?: ENCRYPTION_KEY;
    // Pour DES, nous utilisons une clé de 8 caractères
    $key = str_pad(substr($key, 0, 8), 8, "\0");
    $iv = openssl_random_pseudo_bytes(DES_IV_SIZE);
    $ciphertext = openssl_encrypt($plaintext, DES_METHOD, $key, OPENSSL_RAW_DATA, $iv);
    $result = base64_encode($iv . $ciphertext);
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $algorithm = $_POST['algorithm'] ?? 'aes';
    $key = $_POST['key'] ?? '';
    $plaintext = $_POST['plaintext'] ?? '';
    
    if (empty($plaintext)) {
        $_SESSION['message'] = 'Veuillez entrer un texte à chiffrer.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit();
    }
    
    try {
        if ($algorithm === 'aes') {
            $encrypted = encryptAES($plaintext, $key);
        } else {
            $encrypted = encryptDES($plaintext, $key);
        }
        
        // Ajouter à l'historique
        if (!isset($_SESSION['history'])) {
            $_SESSION['history'] = [];
        }
        
        $_SESSION['history'][] = [
            'date' => date('d/m/Y H:i:s'),
            'operation' => 'chiffrement',
            'algorithm' => $algorithm,
            'result' => $encrypted
        ];
        
        // Stocker le résultat pour l'affichage
        $_SESSION['encryption_result'] = $encrypted;
        $_SESSION['encryption_algorithm'] = $algorithm;
        
        header('Location: index.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['message'] = 'Erreur lors du chiffrement: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>