<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Administration du planning pour Cabinet Excellence">
    <title>Planning - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1f2937;
            line-height: 1.6;
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, #1f2937, #111827);
            color: white;
            padding: 2rem 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 1rem 2rem;
            border-bottom: 1px solid #374151;
            margin-bottom: 2rem;
        }

        .sidebar-header h2 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: rgba(59, 130, 246, 0.1);
            border-left-color: #3b82f6;
        }

        .sidebar-nav i {
            margin-right: 0.75rem;
            width: 20px;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-new {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pending {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            max-height: 100vh;
            overflow-y: auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .breadcrumb {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .section-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control.error {
            border-color: #ef4444;
            background: #fee2e2;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .slots-list {
            list-style: none;
        }

        .slot-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.3s ease;
        }

        .slot-item:hover {
            background: #f8fafc;
        }

        .slot-item:last-child {
            border-bottom: none;
        }

        .slot-info {
            flex: 1;
        }

        .slot-info h4 {
            font-size: 1rem;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .slot-info p {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background: #d1fae5;
            color: #065f46;
        }

        .status-booked {
            background: #fef3c7;
            color: #92400e;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
            margin-bottom: 2rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .filter-select, .filter-input {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }

        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .logout-btn {
            position: fixed;
            bottom: 2rem;
            left: 2rem;
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 50px;
            height: 50px;
            z-index: 1000;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                width: 100%;
                height: auto;
                z-index: 1000;
                display: none;
            }

            .sidebar.active {
                display: block;
            }

            .main-content {
                padding: 1rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .logout-btn {
                bottom: 1rem;
                left: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Cabinet Excellence'; ?></h2>
                <p>Administration</p>
            </div>
            <ul class="sidebar-nav">
                <li><a href="/admin/dashboard" aria-current="<?php echo $_SERVER['REQUEST_URI'] === '/admin/dashboard' ? 'page' : 'false'; ?>">
                    <i class="fas fa-chart-line"></i>
                    Tableau de bord
                </a></li>
                <li><a href="/admin/content" aria-current="<?php echo $_SERVER['REQUEST_URI'] === '/admin/content' ? 'page' : 'false'; ?>">
                    <i class="fas fa-edit"></i>
                    Contenu du site
                </a></li>
                <li><a href="/admin/contacts" aria-current="<?php echo $_SERVER['REQUEST_URI'] === '/admin/contacts' ? 'page' : 'false'; ?>">
                    <i class="fas fa-envelope"></i>
                    Messages
                    <?php if ($stats['new_contacts'] > 0): ?>
                        <span class="status-badge status-new"><?php echo htmlspecialchars($stats['new_contacts']); ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="/admin/schedule" class="active" aria-current="page">
                    <i class="fas fa-calendar-alt"></i>
                    Planning
                    <?php if ($stats['appointments'] > 0): ?>
                        <span class="status-badge status-pending"><?php echo htmlspecialchars($stats['appointments']); ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="/admin/settings" aria-current="<?php echo $_SERVER['REQUEST_URI'] === '/admin/settings' ? 'page' : 'false'; ?>">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a></li>
                <li><a href="/" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-external-link-alt"></i>
                    Voir le site
                </a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <button class="btn btn-secondary sidebar-toggle" style="display: none; margin-bottom: 1rem;" onclick="toggleSidebar()" aria-label="Basculer le menu">
                <i class="fas fa-bars"></i>
                Menu
            </button>
            <div class="page-header">
                <h1>Planning</h1>
                <div class="breadcrumb">Administration / Planning</div>
            </div>

            <!-- Flash Message -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash_message']['success'] ? 'success' : 'error'; ?>" role="alert">
                    <i class="fas fa-<?php echo $_SESSION['flash_message']['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <!-- Add Daily Availability Form -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-plus-circle"></i>
                    Ajouter disponibilité quotidienne
                </h2>
                <form action="/admin/schedule" method="POST" id="add-slots-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                    <input type="hidden" name="action" value="add_daily_slots">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="date">Date <span aria-hidden="true">*</span></label>
                            <input type="date" id="date" name="date" class="form-control" required aria-required="true" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="all_day">
                                <input type="checkbox" id="all_day" name="all_day" checked>
                                Disponible toute la journée (09:00 - 18:00)
                            </label>
                        </div>
                    </div>
                    <div id="availability_times" style="display: none;" class="form-grid">
                        <div class="form-group">
                            <label for="start_time">Heure de début <span aria-hidden="true">*</span></label>
                            <input type="time" id="start_time" name="start_time" class="form-control" value="09:00" aria-required="true">
                        </div>
                        <div class="form-group">
                            <label for="end_time">Heure de fin <span aria-hidden="true">*</span></label>
                            <input type="time" id="end_time" name="end_time" class="form-control" value="18:00" aria-required="true">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="break_start">Début de la pause (optionnel)</label>
                            <input type="time" id="break_start" name="break_start" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="break_end">Fin de la pause (optionnel)</label>
                            <input type="time" id="break_end" name="break_end" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="add-slots-btn">
                        <i class="fas fa-plus"></i>
                        Générer les slots
                    </button>
                </form>
            </div>

            <!-- Filters Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-filter"></i>
                    Filtrer les créneaux
                </h2>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter_month" class="filter-label">Mois</label>
                        <select id="filter_month" class="filter-select" onchange="filterSlots()" aria-label="Filtrer par mois">
                            <option value="">Tous les mois</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo (date('m') == sprintf('%02d', $i)) ? 'selected' : ''; ?>>
                                    <?php echo strftime('%B', mktime(0, 0, 0, $i, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter_year" class="filter-label">Année</label>
                        <select id="filter_year" class="filter-select" onchange="filterSlots()" aria-label="Filtrer par année">
                            <?php for ($year = date('Y'); $year <= date('Y') + 2; $year++): ?>
                                <option value="<?php echo $year; ?>" <?php echo (date('Y') == $year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter_date" class="filter-label">Date spécifique</label>
                        <input type="date" id="filter_date" class="filter-input" onchange="filterSlots()" aria-label="Filtrer par date spécifique">
                    </div>
                    <div class="filter-group">
                        <label for="filter_status" class="filter-label">Statut</label>
                        <select id="filter_status" class="filter-select" onchange="filterSlots()" aria-label="Filtrer par statut">
                            <option value="">Tous les statuts</option>
                            <option value="available">Disponible</option>
                            <option value="booked">Réservé</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()" aria-label="Réinitialiser les filtres">
                            <i class="fas fa-undo"></i>
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Slots List -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-calendar-alt"></i>
                    Créneaux disponibles <span id="slots-count">(<?php echo count($slots); ?>)</span>
                </h2>
                <div id="slots-container">
                    <?php if (empty($slots)): ?>
                        <p id="no-slots-message" style="color: #6b7280; text-align: center; padding: 2rem;">Aucun créneau disponible pour le moment.</p>
                    <?php else: ?>
                        <ul class="slots-list" id="slots-list">
                            <?php foreach ($slots as $slot): ?>
                                <li class="slot-item" 
                                    data-date="<?php echo date('Y-m-d', strtotime($slot['start_time'])); ?>"
                                    data-month="<?php echo date('m', strtotime($slot['start_time'])); ?>"
                                    data-year="<?php echo date('Y', strtotime($slot['start_time'])); ?>"
                                    data-status="<?php echo ($slot['is_booked'] ?? false) ? 'booked' : 'available'; ?>">
                                    <div class="slot-info">
                                        <h4><?php echo date('d/m/Y H:i', strtotime($slot['start_time'])); ?> - <?php echo date('H:i', strtotime($slot['end_time'])); ?></h4>
                                        <p>
                                            <strong>Statut :</strong>
                                            <span class="status-badge <?php echo ($slot['is_booked'] ?? false) ? 'status-booked' : 'status-available'; ?>">
                                                <?php echo ($slot['is_booked'] ?? false) ? 'Réservé' : 'Disponible'; ?>
                                            </span>
                                        </p>
                                        <?php if ($slot['appointment_count'] > 0): ?>
                                            <p><strong>Rendez-vous :</strong> <?php echo htmlspecialchars($slot['appointment_count']); ?> associé(s)</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-actions">
                                        <?php if (isset($slot['is_booked']) && !$slot['is_booked']): ?>
                                            <form action="/admin/schedule" method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                                <input type="hidden" name="action" value="delete_slot">
                                                <input type="hidden" name="slot_id" value="<?php echo htmlspecialchars($slot['id']); ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce créneau ?');" aria-label="Supprimer le créneau">
                                                    <i class="fas fa-trash"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Logout Button -->
    <button class="logout-btn" onclick="logout()" title="Se déconnecter" aria-label="Se déconnecter">
        <i class="fas fa-sign-out-alt"></i>
    </button>

    <script>
        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Logout confirmation
        function logout() {
            if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
                window.location.href = '/admin/logout';
            }
        }

        // Store original slots for filtering
        const originalSlots = Array.from(document.querySelectorAll('.slot-item'));

        // Filter slots
        function filterSlots() {
            const month = document.getElementById('filter_month').value;
            const year = document.getElementById('filter_year').value;
            const date = document.getElementById('filter_date').value;
            const status = document.getElementById('filter_status').value;

            let filteredSlots = originalSlots.filter(slot => {
                const slotMonth = slot.dataset.month;
                const slotYear = slot.dataset.year;
                const slotDate = slot.dataset.date;
                const slotStatus = slot.dataset.status;

                if (month && slotMonth !== month) return false;
                if (year && slotYear !== year) return false;
                if (date && slotDate !== date) return false;
                if (status && slotStatus !== status) return false;
                return true;
            });

            const slotsList = document.getElementById('slots-list');
            const noSlotsMessage = document.getElementById('no-slots-message');
            const slotsCount = document.getElementById('slots-count');

            if (slotsList) {
                slotsList.innerHTML = '';
                filteredSlots.forEach(slot => slotsList.appendChild(slot.cloneNode(true)));
            }

            slotsCount.textContent = `(${filteredSlots.length})`;

            if (filteredSlots.length === 0) {
                if (!noSlotsMessage && slotsList) {
                    const message = document.createElement('p');
                    message.id = 'no-slots-message';
                    message.style.cssText = 'color: #6b7280; text-align: center; padding: 2rem;';
                    message.textContent = 'Aucun créneau ne correspond aux critères sélectionnés.';
                    slotsList.parentNode.appendChild(message);
                }
            } else {
                const existingMessage = document.getElementById('no-slots-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
            }
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('filter_month').value = '';
            document.getElementById('filter_year').value = '<?php echo date('Y'); ?>';
            document.getElementById('filter_date').value = '';
            document.getElementById('filter_status').value = '';
            filterSlots();
        }

        // Form validation and availability toggle
        document.addEventListener('DOMContentLoaded', () => {
            const allDayCheckbox = document.getElementById('all_day');
            const availabilityTimes = document.getElementById('availability_times');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            const dateInput = document.getElementById('date');
            const form = document.getElementById('add-slots-form');

            // Prevent past dates
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);

            // Toggle availability times
            function toggleAvailability() {
                if (allDayCheckbox.checked) {
                    availabilityTimes.style.display = 'none';
                    startTime.required = false;
                    endTime.required = false;
                } else {
                    availabilityTimes.style.display = 'grid';
                    startTime.required = true;
                    endTime.required = true;
                }
            }

            allDayCheckbox.addEventListener('change', toggleAvailability);
            toggleAvailability();

            // Form validation
            form.addEventListener('submit', (e) => {
                let valid = true;
                form.querySelectorAll('[required]').forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                        input.classList.add('error');
                        input.setAttribute('aria-invalid', 'true');
                    } else {
                        input.classList.remove('error');
                        input.setAttribute('aria-invalid', 'false');
                    }
                });

                // Validate time range
                if (!allDayCheckbox.checked && startTime.value && endTime.value) {
                    if (startTime.value >= endTime.value) {
                        valid = false;
                        startTime.classList.add('error');
                        endTime.classList.add('error');
                        showMessage('L\'heure de début doit être antérieure à l\'heure de fin.', 'error');
                    }
                }

                // Validate break times
                const breakStart = document.getElementById('break_start').value;
                const breakEnd = document.getElementById('break_end').value;
                if (breakStart && breakEnd && breakStart >= breakEnd) {
                    valid = false;
                    document.getElementById('break_start').classList.add('error');
                    document.getElementById('break_end').classList.add('error');
                    showMessage('Le début de la pause doit être antérieur à la fin de la pause.', 'error');
                }

                if (!valid) {
                    e.preventDefault();
                    showMessage('Veuillez corriger les erreurs dans le formulaire.', 'error');
                }
            });

            form.addEventListener('input', (e) => {
                const input = e.target;
                if (input.required && !input.value.trim()) {
                    input.classList.add('error');
                    input.setAttribute('aria-invalid', 'true');
                } else {
                    input.classList.remove('error');
                    input.setAttribute('aria-invalid', 'false');
                }
            });

            // Initialize filters
            filterSlots();

            // Show flash message for 7 seconds
            const flash = document.querySelector('.alert');
            if (flash) {
                setTimeout(() => {
                    flash.style.display = 'none';
                }, 7000);
            }

            // Toggle sidebar button visibility on mobile
            const toggleBtn = document.querySelector('.sidebar-toggle');
            const mediaQuery = window.matchMedia('(max-width: 768px)');
            function handleMediaQuery(e) {
                toggleBtn.style.display = e.matches ? 'block' : 'none';
            }
            handleMediaQuery(mediaQuery);
            mediaQuery.addEventListener('change', handleMediaQuery);
        });

        // Show message
        function showMessage(text, type) {
            const messageContainer = document.createElement('div');
            messageContainer.className = `alert alert-${type}`;
            messageContainer.setAttribute('role', 'alert');
            messageContainer.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
                ${text}
            `;
            document.querySelector('.main-content').prepend(messageContainer);
            messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => {
                messageContainer.remove();
            }, 7000);
        }
    </script>
</body>
</html>