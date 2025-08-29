<?php
// Script de sauvegarde automatique des rendez-vous
// À exécuter toutes les heures via cron job

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/backup.php';

// Vérifier que le script est exécuté en ligne de commande ou par cron
if (php_sapi_name() !== 'cli' && !isset($_GET['cron_key'])) {
    http_response_code(403);
    die('Accès interdit');
}

// Clé de sécurité pour l'exécution via URL (optionnel)
$cronKey = 'backup_cron_2025_secure_key';
if (isset($_GET['cron_key']) && $_GET['cron_key'] !== $cronKey) {
    http_response_code(403);
    die('Clé de sécurité invalide');
}

try {
    echo "=== Sauvegarde automatique des rendez-vous ===\n";
    echo "Date: " . date('Y-m-d H:i:s') . "\n";
    
    $backupManager = new BackupManager();
    $result = $backupManager->backupAppointments();
    
    if ($result['success']) {
        echo "✓ Sauvegarde réussie: {$result['filename']}\n";
        echo "✓ Nombre de rendez-vous sauvegardés: {$result['count']}\n";
        
        // Log dans le fichier d'erreur pour traçabilité
        error_log("Automatic backup completed: {$result['filename']} ({$result['count']} appointments)");
    } else {
        echo "✗ Erreur lors de la sauvegarde: {$result['error']}\n";
        error_log("Automatic backup failed: {$result['error']}");
    }
    
    echo "=== Fin de la sauvegarde ===\n";
    
} catch (Exception $e) {
    echo "✗ Erreur critique: " . $e->getMessage() . "\n";
    error_log("Critical backup error: " . $e->getMessage());
}

// Si exécuté via URL, retourner JSON
if (isset($_GET['cron_key'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => isset($result) ? $result['success'] : false,
        'message' => isset($result) ? ($result['success'] ? 'Backup completed' : $result['error']) : 'Script error',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>