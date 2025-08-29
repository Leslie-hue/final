<?php
// API pour vérifier le statut des sauvegardes
session_start();

// Vérifier l'authentification admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/../../includes/backup.php';

try {
    $backupManager = new BackupManager();
    $backups = $backupManager->getBackupList();
    
    // Trouver la dernière sauvegarde d'appointments
    $lastAppointmentBackup = null;
    foreach ($backups as $backup) {
        if ($backup['type'] === 'appointments') {
            $lastAppointmentBackup = $backup['date'];
            break;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'lastBackup' => $lastAppointmentBackup,
        'totalBackups' => count($backups),
        'backups' => array_slice($backups, 0, 5) // Les 5 dernières sauvegardes
    ]);
    
} catch (Exception $e) {
    error_log("Backup status API error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la vérification du statut de sauvegarde'
    ]);
}
?>