// Système de monitoring des sauvegardes automatiques
class BackupMonitor {
    constructor() {
        this.checkInterval = 60000; // Vérifier toutes les minutes
        this.lastBackupCheck = null;
        this.init();
    }

    init() {
        // Démarrer le monitoring seulement si on est dans l'admin
        if (window.location.pathname.startsWith('/admin')) {
            this.startMonitoring();
            this.addBackupStatus();
        }
    }

    startMonitoring() {
        setInterval(() => {
            this.checkBackupStatus();
        }, this.checkInterval);

        // Vérification initiale
        this.checkBackupStatus();
    }

    async checkBackupStatus() {
        try {
            const response = await fetch('/admin/api/backup-status');
            const data = await response.json();
            
            if (data.success) {
                this.updateBackupIndicator(data.lastBackup);
            }
        } catch (error) {
            console.warn('Backup status check failed:', error);
        }
    }

    addBackupStatus() {
        // Ajouter un indicateur de statut de sauvegarde dans l'interface admin
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            const backupStatus = document.createElement('div');
            backupStatus.id = 'backup-status';
            backupStatus.style.cssText = `
                position: absolute;
                bottom: 80px;
                left: 1rem;
                right: 1rem;
                background: rgba(16, 185, 129, 0.1);
                border: 1px solid #10b981;
                border-radius: 8px;
                padding: 0.75rem;
                font-size: 0.8rem;
                color: #065f46;
            `;
            backupStatus.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <div style="font-weight: 600;">Sauvegarde auto</div>
                        <div id="backup-last-time">Vérification...</div>
                    </div>
                </div>
            `;
            sidebar.appendChild(backupStatus);
        }
    }

    updateBackupIndicator(lastBackup) {
        const indicator = document.getElementById('backup-last-time');
        if (indicator && lastBackup) {
            const backupDate = new Date(lastBackup);
            const now = new Date();
            const diffHours = Math.floor((now - backupDate) / (1000 * 60 * 60));
            
            if (diffHours < 1) {
                indicator.textContent = 'Récente (< 1h)';
                indicator.parentElement.parentElement.style.borderColor = '#10b981';
                indicator.parentElement.parentElement.style.background = 'rgba(16, 185, 129, 0.1)';
            } else if (diffHours < 2) {
                indicator.textContent = `Il y a ${diffHours}h`;
                indicator.parentElement.parentElement.style.borderColor = '#f59e0b';
                indicator.parentElement.parentElement.style.background = 'rgba(245, 158, 11, 0.1)';
            } else {
                indicator.textContent = `Il y a ${diffHours}h`;
                indicator.parentElement.parentElement.style.borderColor = '#ef4444';
                indicator.parentElement.parentElement.style.background = 'rgba(239, 68, 68, 0.1)';
            }
        }
    }

    // Méthode pour déclencher une sauvegarde manuelle
    async triggerManualBackup() {
        try {
            const response = await fetch('/admin/api/manual-backup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ type: 'appointments' })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Sauvegarde manuelle réussie', 'success');
                this.checkBackupStatus();
            } else {
                this.showNotification('Erreur lors de la sauvegarde: ' + data.error, 'error');
            }
        } catch (error) {
            this.showNotification('Erreur de connexion lors de la sauvegarde', 'error');
        }
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            max-width: 300px;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Initialiser le monitoring des sauvegardes
document.addEventListener('DOMContentLoaded', () => {
    window.backupMonitor = new BackupMonitor();
});