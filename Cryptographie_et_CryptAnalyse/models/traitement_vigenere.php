<?php
session_start();

if (isset($_POST["valider"])) {
    $operation = htmlspecialchars($_POST['operation']);
    $message = htmlspecialchars($_POST['message']);
    $cle = htmlspecialchars($_POST['cle']);
    $alphabet = range('A', 'Z');
    $alphabets = range('A', 'Z');
    $resultat = "";
    $index = 0;
    $taille_cle = strlen($cle);

    $tabeau_cle = str_split($cle);

    foreach (str_split($message) as $mot) {
        if ($index < $taille_cle) {
            $lettre = strtoupper($mot);
            $position = array_search($lettre, $alphabet);
            $lettre_cle = $tabeau_cle[$index];
            $Lettre_Cle = strtoupper($lettre_cle);
            $position_cle = array_search($Lettre_Cle, $alphabets);
        } else {
            $index = 0;
            $lettre = strtoupper($mot);
            $position = array_search($lettre, $alphabet);
            $lettre_cle = $tabeau_cle[$index];
            $Lettre_Cle = strtoupper($lettre_cle);
            $position_cle = array_search($Lettre_Cle, $alphabets);
        }

        if ($operation == 1) {
            $position_chiffrer = ($position + $position_cle) % 26;
        } else {
            $position_chiffrer = (($position - $position_cle) % 26 + 26) % 26;
        }
        
        $resultat .= $alphabet[$position_chiffrer];
        $index++;
    }

    $_SESSION['resultat_vigenere'] = $resultat;
    
    // Redirection propre sans paramètre dans l'URL
    header("location:../views/vigenere.php");
    exit(); 
}