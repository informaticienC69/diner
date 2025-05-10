<?php
// count.php - Récupération des compteurs de votes
header('Content-Type: application/json');
session_start();
require 'db.php';

// Si non connecté, on renvoie un tableau vide
if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Requête pour compter les votes par candidat
$stmt = $pdo->query("
    SELECT candidate_id, COUNT(*) AS cnt
    FROM vote
    GROUP BY candidate_id
");
$results = $stmt->fetchAll();

// Construction du JSON : [ id => total, … ]
$counts = [];
foreach ($results as $row) {
    $counts[$row['candidate_id']] = (int)$row['cnt'];
}

// Ajouter l'information sur les votes de l'utilisateur
$userVotes = $pdo->prepare("
    SELECT candidate_id 
    FROM vote 
    WHERE user_id = ?
");
$userVotes->execute([$userId]);

// Marquer les candidats pour lesquels l'utilisateur a déjà voté
while ($vote = $userVotes->fetch()) {
    $counts["user_voted_" . $vote['candidate_id']] = true;
}

echo json_encode($counts);
