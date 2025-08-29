<?php
// Script pour configurer la sauvegarde automatique
require_once 'includes/config.php';

echo "=== Configuration de la sauvegarde automatique ===\n\n";

// Créer le répertoire cron s'il n'existe pas
$cronDir = __DIR__ . '/cron';
if (!is_dir($cronDir)) {
    mkdir($cronDir, 0755, true);
    echo "✓ Répertoire cron créé\n";
}

// Créer le répertoire backups s'il n'existe pas
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✓ Répertoire backups créé\n";
}

// Vérifier les permissions
if (!is_writable($backupDir)) {
    echo "✗ Erreur: Le répertoire backups n'est pas accessible en écriture\n";
    exit(1);
}

echo "✓ Permissions vérifiées\n";

// Instructions pour la configuration
echo "\n=== Instructions de configuration ===\n\n";

echo "1. Pour configurer la sauvegarde automatique toutes les heures, ajoutez cette ligne à votre crontab :\n";
echo "   0 * * * * /usr/bin/php " . __DIR__ . "/cron/backup_appointments.php\n\n";

echo "2. Ou utilisez cette commande pour éditer votre crontab :\n";
echo "   crontab -e\n\n";

echo "3. Alternative: Vous pouvez aussi exécuter la sauvegarde via URL :\n";
echo "   " . SITE_URL . "cron/backup_appointments.php?cron_key=backup_cron_2025_secure_key\n\n";

echo "4. Pour tester la sauvegarde manuellement :\n";
echo "   php " . __DIR__ . "/cron/backup_appointments.php\n\n";

// Test de la sauvegarde
echo "=== Test de la sauvegarde ===\n";
try {
    require_once 'includes/backup.php';
    $backupManager = new BackupManager();
    $result = $backupManager->backupAppointments();
    
    if ($result['success']) {
        echo "✓ Test réussi: {$result['filename']}\n";
        echo "✓ Rendez-vous sauvegardés: {$result['count']}\n";
    } else {
        echo "✗ Test échoué: {$result['error']}\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur lors du test: " . $e->getMessage() . "\n";
}

echo "\n=== Configuration terminée ===\n";
?>