<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';

class AdminController {
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            http_response_code(500);
            die("Erreur de connexion à la base de données");
        }
    }

    public function login() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $error = 'Token CSRF invalide';
                include 'views/admin/login.php';
                return;
            }

            $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Nom d\'utilisateur et mot de passe requis';
                include 'views/admin/login.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("SELECT id, username, password FROM admin_users WHERE username = ? AND is_active = 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];

                    $stmt = $this->db->prepare("UPDATE admin_users SET last_login = datetime('now') WHERE id = ?");
                    $stmt->execute([$admin['id']]);

                    redirect('admin/dashboard');
                } else {
                    $error = 'Nom d\'utilisateur ou mot de passe incorrect';
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'Erreur lors de la connexion';
            }
        }

        include 'views/admin/login.php';
    }

    public function dashboard() {
        if (!isLoggedIn()) {
            redirect('admin');
        }

        try {
            $stats = $this->getStats();
            $recent_contacts = $this->getRecentContacts();
            $upcoming_appointments = $this->getUpcomingAppointments();
        } catch (PDOException $e) {
            error_log("Dashboard data fetch error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors du chargement des données'];
            include 'views/admin/error.php';
            return;
        }

        include 'views/admin/dashboard.php';
    }

    public function content() {
        if (!isLoggedIn()) {
            redirect('admin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleContentUpdate();
        }

        try {
            $content = $this->getContent();
            $services = $this->getServices();
            $team = $this->getTeam();
            $news = $this->getNews();
            $events = $this->getEvents();
        } catch (PDOException $e) {
            error_log("Content data fetch error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors du chargement des données'];
            include 'views/admin/error.php';
            return;
        }

        include 'views/admin/content.php';
    }

    public function contacts() {
        if (!isLoggedIn()) {
            redirect('admin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleContactAction();
        }

        try {
            $contacts = $this->getContacts();
        } catch (PDOException $e) {
            error_log("Contacts data fetch error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors du chargement des contacts'];
            include 'views/admin/error.php';
            return;
        }

        include 'views/admin/contacts.php';
    }

    public function schedule() {
        if (!isLoggedIn()) {
            redirect('admin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleScheduleAction();
        }

        try {
            $slots = $this->getAppointmentSlots();
            $stats = $this->getStats();
        } catch (PDOException $e) {
            error_log("Schedule data fetch error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors du chargement des créneaux'];
            include 'views/admin/error.php';
            return;
        }

        include 'views/admin/schedule.php';
    }

    public function settings() {
        if (!isLoggedIn()) {
            redirect('admin');
        }

        include 'views/admin/settings.php';
    }

    public function messageDetail($id) {
        if (!isLoggedIn()) {
            redirect('admin');
        }

        try {
            $contact = $this->getContactById($id);
            if (!$contact) {
                $_SESSION['flash_message'] = ['success' => false, 'message' => 'Message non trouvé'];
                redirect('admin/contacts');
            }

            $files = $this->getContactFiles($id);
            $this->markContactAsRead($id);
        } catch (PDOException $e) {
            error_log("Message detail fetch error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors du chargement du message'];
            include 'views/admin/error.php';
            return;
        }

        include 'views/admin/message-detail.php';
    }

    public function logout() {
        destroySession();
        redirect('admin');
    }

    private function getStats() {
        $stats = [];

        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM contacts");
            $stats['contacts'] = $stmt->fetchColumn();

            $stmt = $this->db->query("SELECT COUNT(*) FROM contacts WHERE status = 'new'");
            $stats['new_contacts'] = $stmt->fetchColumn();

            $stmt = $this->db->query("SELECT COUNT(*) FROM appointments WHERE status IN ('pending', 'confirmed')");
            $stats['appointments'] = $stmt->fetchColumn();

            $stmt = $this->db->query("SELECT COUNT(*) FROM services WHERE is_active = 1");
            $stats['services'] = $stmt->fetchColumn();

            $stmt = $this->db->query("SELECT COUNT(*) FROM team_members WHERE is_active = 1");
            $stats['team_members'] = $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Stats fetch error: " . $e->getMessage());
            throw $e;
        }

        return $stats;
    }

    private function getRecentContacts() {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       a.status as appointment_status,
                       s.start_time as appointment_time
                FROM contacts c
                LEFT JOIN appointments a ON c.appointment_id = a.id
                LEFT JOIN appointment_slots s ON a.slot_id = s.id
                ORDER BY c.created_at DESC 
                LIMIT 5
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Recent contacts fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getUpcomingAppointments() {
        try {
            $stmt = $this->db->prepare("
                SELECT c.name, c.email, 
                       a.status as appointment_status,
                       s.start_time as appointment_time
                FROM contacts c
                JOIN appointments a ON c.appointment_id = a.id
                JOIN appointment_slots s ON a.slot_id = s.id
                WHERE s.start_time > datetime('now')
                ORDER BY s.start_time ASC 
                LIMIT 5
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Upcoming appointments fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getContent() {
        try {
            $stmt = $this->db->query("SELECT section, key_name, value FROM site_content");
            $content = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $content[$row['section']][$row['key_name']] = $row['value'];
            }
            return $content;
        } catch (PDOException $e) {
            error_log("Content fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getServices() {
        try {
            $stmt = $this->db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY order_position");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Services fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getTeam() {
        try {
            $stmt = $this->db->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY order_position");
            $team = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($team as &$member) {
                $member['image_path'] = $member['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $member['image_path'])
                    ? $member['image_path']
                    : '/public/uploads/team/default_team_member.jpeg';
            }
            return $team;
        } catch (PDOException $e) {
            error_log("Team fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getNews() {
        try {
            $stmt = $this->db->query("SELECT * FROM news WHERE is_active = 1 ORDER BY publish_date DESC");
            $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($news as &$item) {
                $item['image_path'] = $item['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $item['image_path'])
                    ? $item['image_path']
                    : '/public/uploads/news/default_news.jpg';
            }
            return $news;
        } catch (PDOException $e) {
            error_log("News fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getEvents() {
        try {
            $stmt = $this->db->query("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date DESC");
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($events as &$event) {
                $event['image_path'] = $event['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $event['image_path'])
                    ? $event['image_path']
                    : '/public/uploads/events/default_event.jpg';
            }
            return $events;
        } catch (PDOException $e) {
            error_log("Events fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getContacts() {
        try {
            $stmt = $this->db->query("SELECT * FROM contacts ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Contacts fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getAppointmentSlots() {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       COUNT(a.id) as appointment_count
                FROM appointment_slots s
                LEFT JOIN appointments a ON s.id = a.slot_id
                GROUP BY s.id
                ORDER BY s.start_time ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment slots fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getContactById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Contact fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getContactFiles($contactId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contact_files WHERE contact_id = ? ORDER BY uploaded_at DESC");
            $stmt->execute([$contactId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Contact files fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    private function markContactAsRead($id) {
        try {
            $stmt = $this->db->prepare("UPDATE contacts SET status = 'read', updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Mark contact as read error: " . $e->getMessage());
            throw $e;
        }
    }

    private function handleContentUpdate() {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Token CSRF invalide'];
            redirect('admin/content');
            return;
        }

        $action = trim(filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->db->beginTransaction();

        try {
            switch ($action) {
                case 'update_content':
                    $this->updateSiteContent();
                    break;
                case 'add_service':
                    $this->addService();
                    break;
                case 'update_service':
                    $this->updateService();
                    break;
                case 'delete_service':
                    $this->deleteService();
                    break;
                case 'add_team_member':
                    $this->addTeamMember();
                    break;
                case 'update_team_member':
                    $this->updateTeamMember();
                    break;
                case 'delete_team_member':
                    $this->deleteTeamMember();
                    break;
                case 'add_news':
                    $this->addNews();
                    break;
                case 'update_news':
                    $this->updateNews();
                    break;
                case 'delete_news':
                    $this->deleteNews();
                    break;
                case 'add_event':
                    $this->addEvent();
                    break;
                case 'update_event':
                    $this->updateEvent();
                    break;
                case 'delete_event':
                    $this->deleteEvent();
                    break;
                default:
                    throw new Exception('Action non reconnue');
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Content update error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()];
            redirect('admin/content');
        }
    }

    private function updateSiteContent() {
        $sections = ['hero', 'about', 'services', 'team', 'news', 'events', 'contact', 'footer', 'values'];

        foreach ($sections as $section) {
            if (isset($_POST[$section]) && is_array($_POST[$section])) {
                foreach ($_POST[$section] as $key => $value) {
                    $value = trim(filter_input(INPUT_POST, $section . '[' . $key . ']', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
                    $stmt = $this->db->prepare("
                        INSERT OR REPLACE INTO site_content (section, key_name, value, updated_at) 
                        VALUES (?, ?, ?, datetime('now'))
                    ");
                    $stmt->execute([$section, $key, $value]);
                }
            }
        }

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Contenu mis à jour avec succès'];
        redirect('admin/content');
    }

    private function addService() {
        $title = trim(filter_input(INPUT_POST, 'service_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'service_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $icon = trim(filter_input(INPUT_POST, 'service_icon', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: 'fas fa-gavel';
        $color = trim(filter_input(INPUT_POST, 'service_color', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: '#3b82f6';
        $detailed_content = trim(filter_input(INPUT_POST, 'service_detailed_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        if (empty($title) || empty($description)) {
            throw new Exception('Titre et description requis');
        }

        $stmt = $this->db->prepare("
            INSERT INTO services (title, description, icon, color, detailed_content, order_position, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, (SELECT COALESCE(MAX(order_position), 0) + 1 FROM services), 1, datetime('now'), datetime('now'))
        ");
        $stmt->execute([$title, $description, $icon, $color, $detailed_content]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Service ajouté avec succès'];
        redirect('admin/content');
    }

    private function updateService() {
        $id = filter_input(INPUT_POST, 'service_id', FILTER_SANITIZE_NUMBER_INT);
        $title = trim(filter_input(INPUT_POST, 'service_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'service_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $icon = trim(filter_input(INPUT_POST, 'service_icon', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: 'fas fa-gavel';
        $color = trim(filter_input(INPUT_POST, 'service_color', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: '#3b82f6';
        $detailed_content = trim(filter_input(INPUT_POST, 'service_detailed_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $is_active = isset($_POST['service_is_active']) ? 1 : 0;

        if (empty($id) || empty($title) || empty($description)) {
            throw new Exception('Données manquantes');
        }

        $stmt = $this->db->prepare("
            UPDATE services 
            SET title = ?, description = ?, icon = ?, color = ?, detailed_content = ?, is_active = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $icon, $color, $detailed_content, $is_active, $id]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Service mis à jour avec succès'];
        redirect('admin/content');
    }

    private function deleteService() {
        $id = filter_input(INPUT_POST, 'service_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($id)) {
            throw new Exception('ID manquant');
        }

        $stmt = $this->db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Service supprimé avec succès'];
        redirect('admin/content');
    }

    private function addTeamMember() {
        $name = trim(filter_input(INPUT_POST, 'team_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $position = trim(filter_input(INPUT_POST, 'team_position', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'team_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        if (empty($name) || empty($position) || empty($description)) {
            throw new Exception('Tous les champs sont requis');
        }

        $image_path = $this->handleImageUpload('team_image', 'team');

        $stmt = $this->db->prepare("
            INSERT INTO team_members (name, position, description, image_path, order_position, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(order_position), 0) + 1 FROM team_members), 1, datetime('now'), datetime('now'))
        ");
        $stmt->execute([$name, $position, $description, $image_path ?: '/public/uploads/team/default_team_member.jpeg']);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Membre d\'équipe ajouté avec succès'];
        redirect('admin/content');
    }

    private function updateTeamMember() {
        $id = filter_input(INPUT_POST, 'team_id', FILTER_SANITIZE_NUMBER_INT);
        $name = trim(filter_input(INPUT_POST, 'team_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $position = trim(filter_input(INPUT_POST, 'team_position', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'team_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $is_active = isset($_POST['team_is_active']) ? 1 : 0;

        if (empty($id) || empty($name) || empty($position) || empty($description)) {
            throw new Exception('Données manquantes');
        }

        $image_path = $this->handleImageUpload('team_image', 'team');

        if ($image_path) {
            $stmt = $this->db->prepare("
                UPDATE team_members 
                SET name = ?, position = ?, description = ?, image_path = ?, is_active = ?, updated_at = datetime('now')
                WHERE id = ?
            ");
            $stmt->execute([$name, $position, $description, $image_path, $is_active, $id]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE team_members 
                SET name = ?, position = ?, description = ?, is_active = ?, updated_at = datetime('now')
                WHERE id = ?
            ");
            $stmt->execute([$name, $position, $description, $is_active, $id]);
        }

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Membre d\'équipe mis à jour avec succès'];
        redirect('admin/content');
    }

    private function deleteTeamMember() {
        $id = filter_input(INPUT_POST, 'team_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($id)) {
            throw new Exception('ID manquant');
        }

        $stmt = $this->db->prepare("SELECT image_path FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member && $member['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $member['image_path']) && strpos($member['image_path'], 'default_team_member.jpeg') === false) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $member['image_path']);
        }

        $stmt = $this->db->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Membre d\'équipe supprimé avec succès'];
        redirect('admin/content');
    }

    private function addNews() {
        $title = trim(filter_input(INPUT_POST, 'news_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $content = trim(filter_input(INPUT_POST, 'news_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $publish_date = trim(filter_input(INPUT_POST, 'news_publish_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: date('Y-m-d H:i:s');

        if (empty($title) || empty($content)) {
            throw new Exception('Titre et contenu requis');
        }

        $image_path = $this->handleImageUpload('news_image', 'news');

        $stmt = $this->db->prepare("
            INSERT INTO news (title, content, image_path, publish_date, order_position, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(order_position), 0) + 1 FROM news), 1, datetime('now'), datetime('now'))
        ");
        $stmt->execute([$title, $content, $image_path ?: '/public/uploads/news/default_news.jpg', $publish_date]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Actualité ajoutée avec succès'];
        redirect('admin/content');
    }

    private function updateNews() {
        $id = filter_input(INPUT_POST, 'news_id', FILTER_SANITIZE_NUMBER_INT);
        $title = trim(filter_input(INPUT_POST, 'news_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $content = trim(filter_input(INPUT_POST, 'news_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $publish_date = trim(filter_input(INPUT_POST, 'news_publish_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: date('Y-m-d H:i:s');
        $is_active = isset($_POST['news_is_active']) ? 1 : 0;

        if (empty($id) || empty($title) || empty($content)) {
            throw new Exception('Données manquantes');
        }

        $image_path = $this->handleImageUpload('news_image', 'news');

        if ($image_path) {
            $stmt = $this->db->prepare("
                UPDATE news 
                SET title = ?, content = ?, image_path = ?, publish_date = ?, is_active = ?, updated_at = datetime('now')
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $image_path, $publish_date, $is_active, $id]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE news 
                SET title = ?, content = ?, publish_date = ?, is_active = ?, updated_at = datetime('now')
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $publish_date, $is_active, $id]);
        }

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Actualité mise à jour avec succès'];
        redirect('admin/content');
    }

    private function deleteNews() {
        $id = filter_input(INPUT_POST, 'news_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($id)) {
            throw new Exception('ID manquant');
        }

        $stmt = $this->db->prepare("SELECT image_path FROM news WHERE id = ?");
        $stmt->execute([$id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($news && $news['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $news['image_path']) && strpos($news['image_path'], 'default_news.jpg') === false) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $news['image_path']);
        }

        $stmt = $this->db->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Actualité supprimée avec succès'];
        redirect('admin/content');
    }

    private function addEvent() {
        $title = trim(filter_input(INPUT_POST, 'event_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $content = trim(filter_input(INPUT_POST, 'event_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $event_date = trim(filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: date('Y-m-d H:i:s');

        if (empty($title) || empty($content)) {
            throw new Exception('Titre et contenu requis');
        }

        $image_path = $this->handleImageUpload('event_image', 'events');

        $stmt = $this->db->prepare("
            INSERT INTO events (title, content, image_path, event_date, order_position, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(order_position), 0) + 1 FROM events), 1, datetime('now'), datetime('now'))
        ");
        $stmt->execute([$title, $content, $image_path ?: '/public/uploads/events/default_event.jpg', $event_date]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Événement ajouté avec succès'];
        redirect('admin/content');
    }

    private function updateEvent() {
        $id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
        $title = trim(filter_input(INPUT_POST, 'event_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $content = trim(filter_input(INPUT_POST, 'event_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $event_date = trim(filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: date('Y-m-d H:i:s');
        $is_active = isset($_POST['event_is_active']) ? 1 : 0;

        if (empty($id) || empty($title) || empty($content)) {
            throw new Exception('Données manquantes');
        }

        $image_path = $this->handleImageUpload('event_image', 'events');

        if ($image_path) {
            $stmt = $this->db->prepare("
                UPDATE events 
                SET title = ?, content = ?, image_path = ?, event_date = ?, is_active = ?, updated_at = datetime('now')
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $image_path, $event_date, $is_active, $id]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE events 
                SET title = ?, content = ?, event_date = ?, is_active = ?, updated_at = datetime('now')
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $event_date, $is_active, $id]);
        }

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Événement mis à jour avec succès'];
        redirect('admin/content');
    }

    private function deleteEvent() {
        $id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($id)) {
            throw new Exception('ID manquant');
        }

        $stmt = $this->db->prepare("SELECT image_path FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event && $event['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $event['image_path']) && strpos($event['image_path'], 'default_event.jpg') === false) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $event['image_path']);
        }

        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_message'] = ['success' => true, 'message' => 'Événement supprimé avec succès'];
        redirect('admin/content');
    }

    private function handleImageUpload($fieldName, $folder) {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
            throw new Exception('Type de fichier non autorisé ou fichier trop volumineux');
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/public/uploads/$folder/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $folder . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Échec du téléversement du fichier');
        }

        return "/public/uploads/$folder/$filename";
    }

    private function handleContactAction() {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Token CSRF invalide'];
            redirect('admin/contacts');
            return;
        }

        $action = trim(filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($id)) {
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'ID manquant'];
            redirect('admin/contacts');
            return;
        }

        try {
            switch ($action) {
                case 'mark_read':
                    $stmt = $this->db->prepare("UPDATE contacts SET status = 'read', updated_at = datetime('now') WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['flash_message'] = ['success' => true, 'message' => 'Message marqué comme lu'];
                    break;
                case 'mark_new':
                    $stmt = $this->db->prepare("UPDATE contacts SET status = 'new', updated_at = datetime('now') WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['flash_message'] = ['success' => true, 'message' => 'Message marqué comme nouveau'];
                    break;
                case 'delete':
                    $stmt = $this->db->prepare("DELETE FROM contacts WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['flash_message'] = ['success' => true, 'message' => 'Message supprimé'];
                    break;
                default:
                    throw new Exception('Action non reconnue');
            }
        } catch (Exception $e) {
            error_log("Contact action error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors de l\'action : ' . $e->getMessage()];
        }
        redirect('admin/contacts');
    }

    private function handleScheduleAction() {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Token CSRF invalide'];
            redirect('admin/schedule');
            return;
        }

        $action = trim(filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->db->beginTransaction();

        try {
            switch ($action) {
                case 'add_daily_slots':
                    $this->addDailySlots();
                    break;
                case 'delete_slot':
                    $this->deleteSlot();
                    break;
                default:
                    throw new Exception('Action non reconnue');
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Schedule action error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['success' => false, 'message' => 'Erreur lors de l\'action : ' . $e->getMessage()];
            redirect('admin/schedule');
        }
    }

    private function addDailySlots() {
        $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $allDay = isset($_POST['all_day']);
        $startTime = $allDay ? '09:00' : (trim(filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: '09:00');
        $endTime = $allDay ? '18:00' : (trim(filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?: '18:00');
        $breakStart = trim(filter_input(INPUT_POST, 'break_start', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $breakEnd = trim(filter_input(INPUT_POST, 'break_end', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        if (empty($date)) {
            throw new Exception('Date requise');
        }

        $slots = [];
        $current = new DateTime("$date $startTime");
        $end = new DateTime("$date $endTime");
        $breakStartTime = $breakStart ? new DateTime("$date $breakStart") : null;
        $breakEndTime = $breakEnd ? new DateTime("$date $breakEnd") : null;

        while ($current < $end) {
            $slotEnd = clone $current;
            $slotEnd->add(new DateInterval('PT30M'));

            if ($breakStartTime && $breakEndTime && $current >= $breakStartTime && $current < $breakEndTime) {
                $current->add(new DateInterval('PT30M'));
                continue;
            }

            if ($slotEnd <= $end) {
                $slots[] = [
                    'start_time' => $current->format('Y-m-d H:i:s'),
                    'end_time' => $slotEnd->format('Y-m-d H:i:s')
                ];
            }

            $current->add(new DateInterval('PT30M'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO appointment_slots (start_time, end_time, is_booked, created_at, updated_at) 
            VALUES (?, ?, 0, datetime('now'), datetime('now'))
        ");

        $count = 0;
        foreach ($slots as $slot) {
            $checkStmt = $this->db->prepare("SELECT id FROM appointment_slots WHERE start_time = ? AND end_time = ?");
            $checkStmt->execute([$slot['start_time'], $slot['end_time']]);
            
            if (!$checkStmt->fetch()) {
                $stmt->execute([$slot['start_time'], $slot['end_time']]);
                $count++;
            }
        }

        $_SESSION['flash_message'] = ['success' => true, 'message' => "$count créneaux ajoutés avec succès"];
        redirect('admin/schedule');
    }

    private function deleteSlot() {
        $slotId = filter_input(INPUT_POST, 'slot_id', FILTER_SANITIZE_NUMBER_INT);

        if (empty($slotId)) {
            throw new Exception('ID de créneau manquant');
        }

        $stmt = $this->db->prepare("DELETE FROM appointment_slots WHERE id = ? AND is_booked = 0");
        $stmt->execute([$slotId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['flash_message'] = ['success' => true, 'message' => 'Créneau supprimé avec succès'];
        } else {
            throw new Exception('Impossible de supprimer ce créneau');
        }
        redirect('admin/schedule');
    }
}
?>