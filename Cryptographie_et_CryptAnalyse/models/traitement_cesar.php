<?php
session_start();

if (isset($_POST["valider"])) {
    $operation = (int)$_POST['operation'];
    $message = $_POST['message'];
    $cle = (int)$_POST['cle'];
    $alphabet = range('A', 'Z');
    $resultat = "";

    $message_split = str_split(strtoupper($message));

    foreach ($message_split as $lettre) {
        $position = array_search($lettre, $alphabet);

        if ($position === false) {
            $resultat .= $lettre; 
        } else {
            if ($operation == 1) {
                // Chiffrement
                $position_finale = ($position + $cle) % 26;
            } else {
                // Déchiffrement
                $position_finale = (($position - $cle) % 26 + 26) % 26;
            }
            $resultat .= $alphabet[$position_finale];
        }
    }

    $_SESSION['resultat_cesar'] = $resultat;
    $_SESSION['message_original'] = $message; 

    header("Location: ../views/cesar.php");
    exit();
}
