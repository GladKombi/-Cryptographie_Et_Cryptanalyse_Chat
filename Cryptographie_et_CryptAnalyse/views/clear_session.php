<?php
session_start();

$sessions_a_effacer = [
    'resultat_cesar', 
    'message_original',
    'resultat_affine', 
    'error_affine',
    'resultat_vigenere',
    'resultat_hill',
    'notif' 
];


foreach ($sessions_a_effacer as $cle) {
    if (isset($_SESSION[$cle])) {
        unset($_SESSION[$cle]);
    }
}

// Redirection vers la page précédente
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';

header("Location: " . $referer);
exit();