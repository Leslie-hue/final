<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

class BackupManager {
    private $db;
    private $backupDir;
    
    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            $this->backupDir = __DIR__ . '/../backups/';
            
            // Créer le répertoire de sauvegarde s'il n'existe pas
            if (!is_dir($this->backupDir)) {
                mkdir($this->backupDir, 0755, true);
            }
        } catch (Exception $e) {
            error_log("BackupManager Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function backupAppointments() {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "appointments_backup_{$timestamp}.json";
            $filepath = $this->backupDir . $filename;
            
            // Récupérer tous les rendez-vous avec les détails
            $stmt = $this->db->query("
                SELECT 
                    a.id as appointment_id,
                    a.status as appointment_status,
                    a.created_at as appointment_created,
                    a.updated_at as appointment_updated,
                    c.id as contact_id,
                    c.name,
                    c.email,
                    c.phone,
                    c.subject,
                    c.message,
                    c.payment_method,
                    c.created_at as contact_created,
                    s.id as slot_id,
                    s.start_time,
                    s.end_time,
                    s.is_booked
                FROM appointments a
                JOIN contacts c ON a.contact_id = c.id
                JOIN appointment_slots s ON a.slot_id = s.id
                ORDER BY s.start_time ASC
            ");
            
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $backupData = [
                'backup_date' => date('Y-m-d H:i:s'),
                'total_appointments' => count($appointments),
                'appointments' => $appointments
            ];
            
            $jsonData = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if (file_put_contents($filepath, $jsonData) !== false) {
                error_log("Backup created successfully: $filename");
                $this->cleanOldBackups();
                return [
                    'success' => true,
                    'filename' => $filename,
                    'count' => count($appointments)
                ];
            } else {
                throw new Exception("Failed to write backup file");
            }
            
        } catch (Exception $e) {
            error_log("Backup error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function backupAllData() {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "full_backup_{$timestamp}.json";
            $filepath = $this->backupDir . $filename;
            
            // Récupérer toutes les données importantes
            $tables = [
                'site_content' => "SELECT * FROM site_content ORDER BY section, key_name",
                'services' => "SELECT * FROM services ORDER BY order_position",
                'team_members' => "SELECT * FROM team_members ORDER BY order_position",
                'news' => "SELECT * FROM news ORDER BY publish_date DESC",
                'events' => "SELECT * FROM events ORDER BY event_date DESC",
                'contacts' => "SELECT * FROM contacts ORDER BY created_at DESC",
                'appointments' => "SELECT * FROM appointments ORDER BY created_at DESC",
                'appointment_slots' => "SELECT * FROM appointment_slots ORDER BY start_time",
                'contact_files' => "SELECT * FROM contact_files ORDER BY uploaded_at DESC"
            ];
            
            $backupData = [
                'backup_date' => date('Y-m-d H:i:s'),
                'backup_type' => 'full',
                'tables' => []
            ];
            
            foreach ($tables as $tableName => $query) {
                $stmt = $this->db->query($query);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $backupData['tables'][$tableName] = [
                    'count' => count($data),
                    'data' => $data
                ];
            }
            
            $jsonData = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if (file_put_contents($filepath, $jsonData) !== false) {
                error_log("Full backup created successfully: $filename");
                return [
                    'success' => true,
                    'filename' => $filename,
                    'tables' => array_keys($tables)
                ];
            } else {
                throw new Exception("Failed to write backup file");
            }
            
        } catch (Exception $e) {
            error_log("Full backup error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function cleanOldBackups() {
        try {
            $files = glob($this->backupDir . 'appointments_backup_*.json');
            
            // Garder seulement les 24 dernières sauvegardes (1 par heure sur 24h)
            if (count($files) > 24) {
                // Trier par date de modification
                usort($files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });
                
                // Supprimer les plus anciens
                $filesToDelete = array_slice($files, 0, count($files) - 24);
                foreach ($filesToDelete as $file) {
                    unlink($file);
                    error_log("Old backup deleted: " . basename($file));
                }
            }
        } catch (Exception $e) {
            error_log("Error cleaning old backups: " . $e->getMessage());
        }
    }
    
    public function getBackupList() {
        try {
            $files = glob($this->backupDir . '*.json');
            $backups = [];
            
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => strpos(basename($file), 'full_backup') === 0 ? 'full' : 'appointments'
                ];
            }
            
            // Trier par date (plus récent en premier)
            usort($backups, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            return $backups;
        } catch (Exception $e) {
            error_log("Error getting backup list: " . $e->getMessage());
            return [];
        }
    }
}
?>