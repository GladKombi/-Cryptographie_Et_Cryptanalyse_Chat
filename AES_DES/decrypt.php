<?php
require_once 'config.php';

// Fonctions de déchiffrement
function decryptAES($ciphertext, $key = null) {
    $key = $key ?: ENCRYPTION_KEY;
    $data = base64_decode($ciphertext);
    $iv = substr($data, 0, AES_IV_SIZE);
    $ciphertext = substr($data, AES_IV_SIZE);
    $plaintext = openssl_decrypt($ciphertext, AES_METHOD, $key, OPENSSL_RAW_DATA, $iv);
    return $plaintext;
}

function decryptDES($ciphertext, $key = null) {
    $key = $key ?: ENCRYPTION_KEY;
    // Pour DES, nous utilisons une clé de 8 caractères
    $key = str_pad(substr($key, 0, 8), 8, "\0");
    $data = base64_decode($ciphertext);
    $iv = substr($data, 0, DES_IV_SIZE);
    $ciphertext = substr($data, DES_IV_SIZE);
    $plaintext = openssl_decrypt($ciphertext, DES_METHOD, $key, OPENSSL_RAW_DATA, $iv);
    return $plaintext;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $algorithm = $_POST['algorithm'] ?? 'aes';
    $key = $_POST['key'] ?? '';
    $ciphertext = $_POST['ciphertext'] ?? '';
    
    if (empty($ciphertext)) {
        $_SESSION['message'] = 'Veuillez entrer un texte chiffré.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit();
    }
    
    try {
        if ($algorithm === 'aes') {
            $decrypted = decryptAES($ciphertext, $key);
        } else {
            $decrypted = decryptDES($ciphertext, $key);
        }
        
        if ($decrypted === false) {
            $_SESSION['message'] = 'Échec du déchiffrement. Vérifiez la clé et l\'algorithme.';
            $_SESSION['message_type'] = 'error';
        } else {
            // Ajouter à l'historique
            if (!isset($_SESSION['history'])) {
                $_SESSION['history'] = [];
            }
            
            $_SESSION['history'][] = [
                'date' => date('d/m/Y H:i:s'),
                'operation' => 'déchiffrement',
                'algorithm' => $algorithm,
                'result' => $decrypted
            ];
            
            // Stocker le résultat pour l'affichage
            $_SESSION['decryption_result'] = $decrypted;
            $_SESSION['decryption_algorithm'] = $algorithm;
            
            $_SESSION['message'] = 'Déchiffrement réussi!';
            $_SESSION['message_type'] = 'success';
        }
        
        header('Location: index.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['message'] = 'Erreur lors du déchiffrement: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>