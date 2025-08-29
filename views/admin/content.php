<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du contenu - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            display: block;
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
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

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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

        .tabs {
            display: flex;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .tab {
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .item-list {
            list-style: none;
        }

        .item-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .item-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.1);
        }

        .item-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .item-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            flex: 1;
        }

        .item-actions {
            display: flex;
            gap: 0.5rem;
        }

        .color-picker {
            width: 50px;
            height: 40px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 600px;
            margin: 5% auto;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
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
                display: none;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
            
            .tab {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Cabinet Excellence'; ?></h2>
                <p>Administration</p>
            </div>
            <ul class="sidebar-nav">
                <li><a href="/admin/dashboard">
                    <i class="fas fa-chart-line"></i>
                    Tableau de bord
                </a></li>
                <li><a href="/admin/content" class="active">
                    <i class="fas fa-edit"></i>
                    Contenu du site
                </a></li>
                <li><a href="/admin/contacts">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a></li>
                <li><a href="/admin/schedule">
                    <i class="fas fa-calendar-alt"></i>
                    Planning
                </a></li>
                <li><a href="/admin/settings">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a></li>
                <li><a href="/" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    Voir le site
                </a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Gestion du contenu</h1>
                <div class="breadcrumb">Administration / Contenu du site</div>
            </div>

            <!-- Flash Message -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash_message']['success'] ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $_SESSION['flash_message']['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <!-- Tabs Navigation -->
            <div class="tabs">
                <button class="tab active" onclick="showTab('site-content')">
                    <i class="fas fa-home"></i>
                    Contenu du site
                </button>
                <button class="tab" onclick="showTab('services')">
                    <i class="fas fa-gavel"></i>
                    Services
                </button>
                <button class="tab" onclick="showTab('team')">
                    <i class="fas fa-users"></i>
                    Équipe
                </button>
                <button class="tab" onclick="showTab('news')">
                    <i class="fas fa-newspaper"></i>
                    Actualités
                </button>
                <button class="tab" onclick="showTab('events')">
                    <i class="fas fa-calendar"></i>
                    Événements
                </button>
            </div>

            <!-- Site Content Tab -->
            <div id="site-content" class="tab-content active">
                <div class="section-card">
                    <h2 class="section-title">
                        <i class="fas fa-edit"></i>
                        Modifier le contenu du site
                    </h2>
                    <form method="POST" action="/admin/content">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                        <input type="hidden" name="action" value="update_content">

                        <!-- Hero Section -->
                        <h3 style="margin-bottom: 1rem; color: #1f2937;">Section Hero</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="hero_title">Titre principal</label>
                                <input type="text" class="form-control" id="hero_title" name="hero[title]" 
                                       value="<?php echo htmlspecialchars($content['hero']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="hero_subtitle">Sous-titre</label>
                                <textarea class="form-control" id="hero_subtitle" name="hero[subtitle]"><?php echo htmlspecialchars($content['hero']['subtitle'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- About Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section À propos</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="about_title">Titre</label>
                                <input type="text" class="form-control" id="about_title" name="about[title]" 
                                       value="<?php echo htmlspecialchars($content['about']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="about_subtitle">Description</label>
                                <textarea class="form-control" id="about_subtitle" name="about[subtitle]"><?php echo htmlspecialchars($content['about']['subtitle'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Services Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section Services</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="services_title">Titre</label>
                                <input type="text" class="form-control" id="services_title" name="services[title]" 
                                       value="<?php echo htmlspecialchars($content['services']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="services_subtitle">Description</label>
                                <textarea class="form-control" id="services_subtitle" name="services[subtitle]"><?php echo htmlspecialchars($content['services']['subtitle'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Team Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section Équipe</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="team_title">Titre</label>
                                <input type="text" class="form-control" id="team_title" name="team[title]" 
                                       value="<?php echo htmlspecialchars($content['team']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="team_subtitle">Description</label>
                                <textarea class="form-control" id="team_subtitle" name="team[subtitle]"><?php echo htmlspecialchars($content['team']['subtitle'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- News Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section Actualités</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="news_title">Titre</label>
                                <input type="text" class="form-control" id="news_title" name="news[title]" 
                                       value="<?php echo htmlspecialchars($content['news']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="news_subtitle">Description</label>
                                <textarea class="form-control" id="news_subtitle" name="news[subtitle]"><?php echo htmlspecialchars($content['news']['subtitle'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Events Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section Événements</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="events_title">Titre</label>
                                <input type="text" class="form-control" id="events_title" name="events[title]" 
                                       value="<?php echo htmlspecialchars($content['events']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="events_subtitle">Description</label>
                                <textarea class="form-control" id="events_subtitle" name="events[subtitle]"><?php echo htmlspecialchars($content['events']['subtitle'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Contact Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section Contact</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="contact_title">Titre</label>
                                <input type="text" class="form-control" id="contact_title" name="contact[title]" 
                                       value="<?php echo htmlspecialchars($content['contact']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contact_address">Adresse</label>
                                <textarea class="form-control" id="contact_address" name="contact[address]"><?php echo htmlspecialchars($content['contact']['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="contact_phone">Téléphone</label>
                                <input type="text" class="form-control" id="contact_phone" name="contact[phone]" 
                                       value="<?php echo htmlspecialchars($content['contact']['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contact_email">Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact[email]" 
                                       value="<?php echo htmlspecialchars($content['contact']['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Values Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section Valeurs</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="values_title">Titre</label>
                                <input type="text" class="form-control" id="values_title" name="values[title]" 
                                       value="<?php echo htmlspecialchars($content['values']['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="values_subtitle">Description</label>
                                <textarea class="form-control" id="values_subtitle" name="values[subtitle]"><?php echo htmlspecialchars($content['values']['subtitle'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Footer Section -->
                        <h3 style="margin: 2rem 0 1rem; color: #1f2937;">Section Footer</h3>
                        <div class="form-group">
                            <label for="footer_copyright">Copyright</label>
                            <input type="text" class="form-control" id="footer_copyright" name="footer[copyright]" 
                                   value="<?php echo htmlspecialchars($content['footer']['copyright'] ?? ''); ?>">
                        </div>

                        <div style="text-align: center; margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Sauvegarder les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Services Tab -->
            <div id="services" class="tab-content">
                <div class="section-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 class="section-title">
                            <i class="fas fa-gavel"></i>
                            Gestion des services
                        </h2>
                        <button class="btn btn-success" onclick="openServiceModal()">
                            <i class="fas fa-plus"></i>
                            Ajouter un service
                        </button>
                    </div>

                    <ul class="item-list">
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $service): ?>
                                <li class="item-card">
                                    <div class="item-header">
                                        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                            <div style="width: 50px; height: 50px; border-radius: 10px; background: <?php echo htmlspecialchars($service['color']); ?>; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                                            </div>
                                            <div>
                                                <h4 class="item-title"><?php echo htmlspecialchars($service['title']); ?></h4>
                                                <p style="color: #6b7280; margin: 0;"><?php echo htmlspecialchars($service['description']); ?></p>
                                            </div>
                                        </div>
                                        <div class="item-actions">
                                            <button class="btn btn-primary" onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                                <i class="fas fa-edit"></i>
                                                Modifier
                                            </button>
                                            <form method="POST" action="/admin/content" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                                <input type="hidden" name="action" value="delete_service">
                                                <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service['id']); ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce service ?')">
                                                    <i class="fas fa-trash"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-gavel" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p>Aucun service configuré</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Team Tab -->
            <div id="team" class="tab-content">
                <div class="section-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 class="section-title">
                            <i class="fas fa-users"></i>
                            Gestion de l'équipe
                        </h2>
                        <button class="btn btn-success" onclick="openTeamModal()">
                            <i class="fas fa-plus"></i>
                            Ajouter un membre
                        </button>
                    </div>

                    <ul class="item-list">
                        <?php if (!empty($team)): ?>
                            <?php foreach ($team as $member): ?>
                                <li class="item-card">
                                    <div class="item-header">
                                        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                            <img src="<?php echo htmlspecialchars($member['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($member['name']); ?>"
                                                 style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                            <div>
                                                <h4 class="item-title"><?php echo htmlspecialchars($member['name']); ?></h4>
                                                <p style="color: #6b7280; margin: 0; font-weight: 600;"><?php echo htmlspecialchars($member['position']); ?></p>
                                                <p style="color: #6b7280; margin: 0; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($member['description'], 0, 100)) . (strlen($member['description']) > 100 ? '...' : ''); ?></p>
                                            </div>
                                        </div>
                                        <div class="item-actions">
                                            <button class="btn btn-primary" onclick="editTeamMember(<?php echo htmlspecialchars(json_encode($member)); ?>)">
                                                <i class="fas fa-edit"></i>
                                                Modifier
                                            </button>
                                            <form method="POST" action="/admin/content" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                                <input type="hidden" name="action" value="delete_team_member">
                                                <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($member['id']); ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce membre ?')">
                                                    <i class="fas fa-trash"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p>Aucun membre d'équipe configuré</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- News Tab -->
            <div id="news" class="tab-content">
                <div class="section-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 class="section-title">
                            <i class="fas fa-newspaper"></i>
                            Gestion des actualités
                        </h2>
                        <button class="btn btn-success" onclick="openNewsModal()">
                            <i class="fas fa-plus"></i>
                            Ajouter une actualité
                        </button>
                    </div>

                    <ul class="item-list">
                        <?php if (!empty($news)): ?>
                            <?php foreach ($news as $item): ?>
                                <li class="item-card">
                                    <div class="item-header">
                                        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 style="width: 80px; height: 60px; border-radius: 8px; object-fit: cover;">
                                            <div>
                                                <h4 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                                <p style="color: #6b7280; margin: 0; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($item['content'], 0, 150)) . (strlen($item['content']) > 150 ? '...' : ''); ?></p>
                                                <p style="color: #6b7280; margin: 0.5rem 0 0; font-size: 0.8rem;">
                                                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($item['publish_date'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="item-actions">
                                            <button class="btn btn-primary" onclick="editNews(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                <i class="fas fa-edit"></i>
                                                Modifier
                                            </button>
                                            <form method="POST" action="/admin/content" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                                <input type="hidden" name="action" value="delete_news">
                                                <input type="hidden" name="news_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette actualité ?')">
                                                    <i class="fas fa-trash"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p>Aucune actualité configurée</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Events Tab -->
            <div id="events" class="tab-content">
                <div class="section-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 class="section-title">
                            <i class="fas fa-calendar"></i>
                            Gestion des événements
                        </h2>
                        <button class="btn btn-success" onclick="openEventModal()">
                            <i class="fas fa-plus"></i>
                            Ajouter un événement
                        </button>
                    </div>

                    <ul class="item-list">
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                                <li class="item-card">
                                    <div class="item-header">
                                        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                            <img src="<?php echo htmlspecialchars($event['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($event['title']); ?>"
                                                 style="width: 80px; height: 60px; border-radius: 8px; object-fit: cover;">
                                            <div>
                                                <h4 class="item-title"><?php echo htmlspecialchars($event['title']); ?></h4>
                                                <p style="color: #6b7280; margin: 0; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($event['content'], 0, 150)) . (strlen($event['content']) > 150 ? '...' : ''); ?></p>
                                                <p style="color: #6b7280; margin: 0.5rem 0 0; font-size: 0.8rem;">
                                                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="item-actions">
                                            <button class="btn btn-primary" onclick="editEvent(<?php echo htmlspecialchars(json_encode($event)); ?>)">
                                                <i class="fas fa-edit"></i>
                                                Modifier
                                            </button>
                                            <form method="POST" action="/admin/content" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                                <input type="hidden" name="action" value="delete_event">
                                                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cet événement ?')">
                                                    <i class="fas fa-trash"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-calendar" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p>Aucun événement configuré</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <!-- Service Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="serviceModalTitle">Ajouter un service</h3>
                <button class="modal-close" onclick="closeServiceModal()">&times;</button>
            </div>
            <form id="serviceForm" method="POST" action="/admin/content">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                <input type="hidden" name="action" value="add_service" id="serviceAction">
                <input type="hidden" name="service_id" id="serviceId">

                <div class="form-group">
                    <label for="service_title">Titre du service *</label>
                    <input type="text" class="form-control" id="service_title" name="service_title" required>
                </div>

                <div class="form-group">
                    <label for="service_description">Description *</label>
                    <textarea class="form-control" id="service_description" name="service_description" required></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="service_icon">Icône (classe FontAwesome)</label>
                        <input type="text" class="form-control" id="service_icon" name="service_icon" placeholder="fas fa-gavel">
                    </div>
                    <div class="form-group">
                        <label for="service_color">Couleur</label>
                        <input type="color" class="color-picker" id="service_color" name="service_color" value="#3b82f6">
                    </div>
                </div>

                <div class="form-group">
                    <label for="service_detailed_content">Contenu détaillé</label>
                    <textarea class="form-control" id="service_detailed_content" name="service_detailed_content" rows="8"></textarea>
                </div>

                <div class="form-group" id="serviceActiveGroup" style="display: none;">
                    <label>
                        <input type="checkbox" id="service_is_active" name="service_is_active" checked>
                        Service actif
                    </label>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeServiceModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Team Modal -->
    <div id="teamModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="teamModalTitle">Ajouter un membre</h3>
                <button class="modal-close" onclick="closeTeamModal()">&times;</button>
            </div>
            <form id="teamForm" method="POST" action="/admin/content" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                <input type="hidden" name="action" value="add_team_member" id="teamAction">
                <input type="hidden" name="team_id" id="teamId">

                <div class="form-group">
                    <label for="team_name">Nom complet *</label>
                    <input type="text" class="form-control" id="team_name" name="team_name" required>
                </div>

                <div class="form-group">
                    <label for="team_position">Poste *</label>
                    <input type="text" class="form-control" id="team_position" name="team_position" required>
                </div>

                <div class="form-group">
                    <label for="team_description">Description *</label>
                    <textarea class="form-control" id="team_description" name="team_description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="team_image">Photo (optionnel)</label>
                    <input type="file" class="form-control" id="team_image" name="team_image" accept="image/*">
                </div>

                <div class="form-group" id="teamActiveGroup" style="display: none;">
                    <label>
                        <input type="checkbox" id="team_is_active" name="team_is_active" checked>
                        Membre actif
                    </label>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeTeamModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- News Modal -->
    <div id="newsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="newsModalTitle">Ajouter une actualité</h3>
                <button class="modal-close" onclick="closeNewsModal()">&times;</button>
            </div>
            <form id="newsForm" method="POST" action="/admin/content" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                <input type="hidden" name="action" value="add_news" id="newsAction">
                <input type="hidden" name="news_id" id="newsId">

                <div class="form-group">
                    <label for="news_title">Titre *</label>
                    <input type="text" class="form-control" id="news_title" name="news_title" required>
                </div>

                <div class="form-group">
                    <label for="news_content">Contenu *</label>
                    <textarea class="form-control" id="news_content" name="news_content" rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label for="news_publish_date">Date de publication</label>
                    <input type="datetime-local" class="form-control" id="news_publish_date" name="news_publish_date">
                </div>

                <div class="form-group">
                    <label for="news_image">Image (optionnel)</label>
                    <input type="file" class="form-control" id="news_image" name="news_image" accept="image/*">
                </div>

                <div class="form-group" id="newsActiveGroup" style="display: none;">
                    <label>
                        <input type="checkbox" id="news_is_active" name="news_is_active" checked>
                        Actualité active
                    </label>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeNewsModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Event Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="eventModalTitle">Ajouter un événement</h3>
                <button class="modal-close" onclick="closeEventModal()">&times;</button>
            </div>
            <form id="eventForm" method="POST" action="/admin/content" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                <input type="hidden" name="action" value="add_event" id="eventAction">
                <input type="hidden" name="event_id" id="eventId">

                <div class="form-group">
                    <label for="event_title">Titre *</label>
                    <input type="text" class="form-control" id="event_title" name="event_title" required>
                </div>

                <div class="form-group">
                    <label for="event_content">Description *</label>
                    <textarea class="form-control" id="event_content" name="event_content" rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label for="event_date">Date de l'événement *</label>
                    <input type="datetime-local" class="form-control" id="event_date" name="event_date" required>
                </div>

                <div class="form-group">
                    <label for="event_image">Image (optionnel)</label>
                    <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                </div>

                <div class="form-group" id="eventActiveGroup" style="display: none;">
                    <label>
                        <input type="checkbox" id="event_is_active" name="event_is_active" checked>
                        Événement actif
                    </label>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeEventModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Button -->
    <button class="logout-btn" onclick="logout()" title="Se déconnecter">
        <i class="fas fa-sign-out-alt"></i>
    </button>

    <script>
        // Tab management
        function showTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }

        // Service Modal Functions
        function openServiceModal() {
            document.getElementById('serviceModalTitle').textContent = 'Ajouter un service';
            document.getElementById('serviceAction').value = 'add_service';
            document.getElementById('serviceId').value = '';
            document.getElementById('serviceForm').reset();
            document.getElementById('serviceActiveGroup').style.display = 'none';
            document.getElementById('serviceModal').style.display = 'block';
        }

        function editService(service) {
            document.getElementById('serviceModalTitle').textContent = 'Modifier le service';
            document.getElementById('serviceAction').value = 'update_service';
            document.getElementById('serviceId').value = service.id;
            document.getElementById('service_title').value = service.title;
            document.getElementById('service_description').value = service.description;
            document.getElementById('service_icon').value = service.icon;
            document.getElementById('service_color').value = service.color;
            document.getElementById('service_detailed_content').value = service.detailed_content || '';
            document.getElementById('service_is_active').checked = service.is_active == 1;
            document.getElementById('serviceActiveGroup').style.display = 'block';
            document.getElementById('serviceModal').style.display = 'block';
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').style.display = 'none';
        }

        // Team Modal Functions
        function openTeamModal() {
            document.getElementById('teamModalTitle').textContent = 'Ajouter un membre';
            document.getElementById('teamAction').value = 'add_team_member';
            document.getElementById('teamId').value = '';
            document.getElementById('teamForm').reset();
            document.getElementById('teamActiveGroup').style.display = 'none';
            document.getElementById('teamModal').style.display = 'block';
        }

        function editTeamMember(member) {
            document.getElementById('teamModalTitle').textContent = 'Modifier le membre';
            document.getElementById('teamAction').value = 'update_team_member';
            document.getElementById('teamId').value = member.id;
            document.getElementById('team_name').value = member.name;
            document.getElementById('team_position').value = member.position;
            document.getElementById('team_description').value = member.description;
            document.getElementById('team_is_active').checked = member.is_active == 1;
            document.getElementById('teamActiveGroup').style.display = 'block';
            document.getElementById('teamModal').style.display = 'block';
        }

        function closeTeamModal() {
            document.getElementById('teamModal').style.display = 'none';
        }

        // News Modal Functions
        function openNewsModal() {
            document.getElementById('newsModalTitle').textContent = 'Ajouter une actualité';
            document.getElementById('newsAction').value = 'add_news';
            document.getElementById('newsId').value = '';
            document.getElementById('newsForm').reset();
            document.getElementById('newsActiveGroup').style.display = 'none';
            document.getElementById('newsModal').style.display = 'block';
        }

        function editNews(news) {
            document.getElementById('newsModalTitle').textContent = 'Modifier l\'actualité';
            document.getElementById('newsAction').value = 'update_news';
            document.getElementById('newsId').value = news.id;
            document.getElementById('news_title').value = news.title;
            document.getElementById('news_content').value = news.content;
            
            // Convert publish_date to datetime-local format
            const publishDate = new Date(news.publish_date);
            const localDateTime = publishDate.getFullYear() + '-' + 
                                String(publishDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(publishDate.getDate()).padStart(2, '0') + 'T' + 
                                String(publishDate.getHours()).padStart(2, '0') + ':' + 
                                String(publishDate.getMinutes()).padStart(2, '0');
            document.getElementById('news_publish_date').value = localDateTime;
            
            document.getElementById('news_is_active').checked = news.is_active == 1;
            document.getElementById('newsActiveGroup').style.display = 'block';
            document.getElementById('newsModal').style.display = 'block';
        }

        function closeNewsModal() {
            document.getElementById('newsModal').style.display = 'none';
        }

        // Event Modal Functions
        function openEventModal() {
            document.getElementById('eventModalTitle').textContent = 'Ajouter un événement';
            document.getElementById('eventAction').value = 'add_event';
            document.getElementById('eventId').value = '';
            document.getElementById('eventForm').reset();
            document.getElementById('eventActiveGroup').style.display = 'none';
            document.getElementById('eventModal').style.display = 'block';
        }

        function editEvent(event) {
            document.getElementById('eventModalTitle').textContent = 'Modifier l\'événement';
            document.getElementById('eventAction').value = 'update_event';
            document.getElementById('eventId').value = event.id;
            document.getElementById('event_title').value = event.title;
            document.getElementById('event_content').value = event.content;
            
            // Convert event_date to datetime-local format
            const eventDate = new Date(event.event_date);
            const localDateTime = eventDate.getFullYear() + '-' + 
                                String(eventDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(eventDate.getDate()).padStart(2, '0') + 'T' + 
                                String(eventDate.getHours()).padStart(2, '0') + ':' + 
                                String(eventDate.getMinutes()).padStart(2, '0');
            document.getElementById('event_date').value = localDateTime;
            
            document.getElementById('event_is_active').checked = event.is_active == 1;
            document.getElementById('eventActiveGroup').style.display = 'block';
            document.getElementById('eventModal').style.display = 'block';
        }

        function closeEventModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = ['serviceModal', 'teamModal', 'newsModal', 'eventModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Logout function
        function logout() {
            if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
                window.location.href = '/admin/logout';
            }
        }

        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>