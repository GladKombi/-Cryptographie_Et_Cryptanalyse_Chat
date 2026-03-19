<?php
/**
 * Configuration de l'application de chiffrement
 * Sécurité : Stockage des clés et configuration
 */

// Clé de chiffrement par défaut (à modifier en production)
define('ENCRYPTION_KEY', 'votre_cle_secrete_ici_32_caracteres!!');

// Méthode de chiffrement AES
define('AES_METHOD', 'aes-256-cbc');

// Méthode de chiffrement DES
define('DES_METHOD', 'des-ede3-cbc');

// Taille d'IV (Initialization Vector) pour AES
define('AES_IV_SIZE', 16);

// Taille d'IV pour DES
define('DES_IV_SIZE', 8);

// Configuration de la session
session_start();

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>