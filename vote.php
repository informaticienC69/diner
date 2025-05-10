<?php
// vote.php - Gestionnaire des votes
header('Content-Type: application/json');
session_start();
require 'db.php';

// 1) Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// 2) Lire l'ID du candidat depuis le POST JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID du candidat manquant']);
    exit;
}

// Convertir les IDs en entiers pour la sécurité
$candId = (int)$data['id'];
$userId = (int)$_SESSION['user_id'];

// Vérifier si le candidat existe
$checkCandidate = $pdo->prepare("SELECT id FROM candidates WHERE id = ?");
$checkCandidate->execute([$candId]);
if (!$checkCandidate->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Candidat inexistant']);
    exit;
}

// Vérifier combien de votes l'utilisateur a déjà faits
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE user_id = :uid");
$countStmt->execute(['uid' => $userId]);
$currentVoteCount = (int)$countStmt->fetchColumn();

// Vérifier si l'utilisateur a déjà voté pour ce candidat
$check = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE user_id = :uid AND candidate_id = :cid");
$check->execute(['uid' => $userId, 'cid' => $candId]);
$alreadyVoted = (int)$check->fetchColumn();

// Si déjà 2 votes et essaie d'ajouter un nouveau vote
if ($currentVoteCount >= 2 && !$alreadyVoted) {
    echo json_encode([
        'success' => false,
        'message' => 'Tu as déjà voté pour 2 personnes. Retire un vote avant d\'en ajouter un autre.'
    ]);
    exit;
}

try {
    // Gestion du vote (ajout ou suppression)
    if ($alreadyVoted) {
        // Supprimer le vote existant
        $del = $pdo->prepare("DELETE FROM vote WHERE user_id = :uid AND candidate_id = :cid");
        $del->execute(['uid' => $userId, 'cid' => $candId]);
        $action = 'removed';
    } else {
        // Ajouter un nouveau vote
        $ins = $pdo->prepare("INSERT INTO vote (user_id, candidate_id) VALUES (:uid, :cid)");
        $ins->execute(['uid' => $userId, 'cid' => $candId]);
        $action = 'added';
    }

    // Recalculer le total des votes pour ce candidat
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE candidate_id = :cid");
    $countStmt->execute(['cid' => $candId]);
    $count = (int)$countStmt->fetchColumn();

    // Répondre en JSON avec le résultat
    echo json_encode([
        'success' => true, 
        'count' => $count,
        'action' => $action
    ]);
    
} catch (Exception $e) {
    // En cas d'erreur serveur
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
