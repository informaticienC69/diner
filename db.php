<?php
// db.php - Fichier de connexion à la base de données
$host = '127.0.0.1';
$db   = 'dinner';
$user = 'root';      
$pass = '';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
  // Création de l'objet PDO avec les options pour la gestion des erreurs
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Afficher les erreurs sous forme d'exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Retourner les résultats sous forme de tableaux associatifs
  ]);
} catch (PDOException $e) {
  // En cas d'erreur de connexion, afficher un message et arrêter le script
  exit('Erreur connexion DB : '.$e->getMessage());
}
