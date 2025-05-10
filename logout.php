<?php
// logout.php - Déconnexion de l'utilisateur
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Redirection vers la page de connexion
header('Location: login.php');
exit;
