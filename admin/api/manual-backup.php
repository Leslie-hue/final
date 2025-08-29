<?php
// API pour déclencher une sauvegarde manuelle
session_start();

// Vérifier l'authentification admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../../includes/backup.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $backupType = $input['type'] ?? 'appointments';
    
    $backupManager = new BackupManager();
    
    if ($backupType === 'full') {
        $result = $backupManager->backupAllData();
    } else {
        $result = $backupManager->backupAppointments();
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Manual backup API error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la sauvegarde manuelle: ' . $e->getMessage()
    ]);
}
?>