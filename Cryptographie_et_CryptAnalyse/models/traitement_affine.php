<?php
session_start();

if (isset($_POST["valider"])) {

    $message = htmlspecialchars($_POST['message']);
    $cle1 = htmlspecialchars($_POST['cle1']);
    $cle2 = htmlspecialchars($_POST['cle2']);
    $alphabet = range('A', 'Z');
    $resultat = "";

    foreach (str_split($message) as $mot) {

        $lettre = strtoupper($mot);
        $position = array_search($lettre, $alphabet);

        $position_chiffrer = (($position * $cle1) + $cle2) % 26;
        $resultat .= $alphabet[$position_chiffrer];
    }

    $_SESSION['resultat_affine'] = $resultat;
    header("location:../views/affine.php");
    exit();
}
