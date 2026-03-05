<?php
session_start();
if (isset($_POST["valider"])) {
    $message = htmlspecialchars($_POST['message']);
    $a = (int)$_POST['a'];
    $b = (int)$_POST['b'];
    $c = (int)$_POST['c'];
    $d = (int)$_POST['d'];

    $det = ($a * $d) - ($c * $b);
    $alphabet = range('A', 'Z');
    $resultat = "";
    if ($det == 0) {
        $_SESSION['notif'] = "Le déterminant doit être différent de 0.";
        header("location:../views/hill.php");
        exit();
    }

    $determinant = ($det % 26 + 26) % 26;

    $valeurs_possibles = [1, 3, 5, 7, 9, 11, 15, 17, 19, 21, 23, 25];
    if (!in_array($determinant, $valeurs_possibles)) {
        $_SESSION['notif'] = "Le déterminant ($determinant) n'est pas inversible. Choisissez d'autres valeurs pour a, b, c, d.";
        header("location:../views/hill.php");
        exit();
    }



    foreach (str_split($message, 2) as $mots) {
        $decouper = strtoupper($mots);
        if (strlen($decouper) < 2) {
            $decouper .= "Z";
        }

        $x = array_search($decouper[0], $alphabet);
        $y = array_search($decouper[1], $alphabet);

        $pos1 = (($a * $x) + ($b * $y)) % 26;
        $pos2 = (($c * $x) + ($d * $y)) % 26;

        $resultat .= $alphabet[$pos1];
        $resultat .= $alphabet[$pos2];
    }

    $_SESSION['resultat_hill'] = $resultat;
    header("location:../views/hill.php");
    exit();
}
