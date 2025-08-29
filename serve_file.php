<?php
// Enable error reporting for debugging
ini_set('display_errors', 0); // Hide errors from users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log'); // Log errors to error.log in project root
error_reporting(E_ALL);

// Log request details
error_log('Request received: ' . $_SERVER['REQUEST_URI']);

// Start session for authentication
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    error_log('Access denied: User not authenticated');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['error' => 'Accès refusé : Veuillez vous connecter']));
}

// Database connection
require_once 'includes/config.php';
require_once 'includes/Database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    error_log('Database connection successful');
} catch (Exception $e) {
    http_response_code(500);
    error_log('Database connection failed: ' . $e->getMessage());
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['error' => 'Erreur serveur : Impossible de se connecter à la base de données']));
}

// Get file ID from query parameter
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
error_log('File ID requested: ' . $file_id);

if ($file_id <= 0) {
    http_response_code(400);
    error_log('Invalid file ID: ' . $file_id);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['error' => 'ID de fichier invalide']));
}

// Fetch file details from the database
$stmt = $conn->prepare("SELECT file_path, file_type, original_name, contact_id FROM contact_files WHERE id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    http_response_code(404);
    error_log('File not found in database for ID: ' . $file_id);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['error' => 'Fichier non trouvé dans la base de données']));
}
error_log('File found in database: ' . json_encode($file));

// Validate file path to ensure it's in Uploads/contact_{contact_id}/
if (!preg_match('/^Uploads\/contact_' . $file['contact_id'] . '\//', $file['file_path'])) {
    http_response_code(403);
    error_log('Invalid file path: ' . $file['file_path']);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['error' => 'Chemin de fichier invalide']));
}

// Construct full file path
$file_path = str_replace('/', DIRECTORY_SEPARATOR, $file['file_path']);
$full_path = realpath(__DIR__ . DIRECTORY_SEPARATOR . $file_path);
error_log('Attempting to access file: ' . $full_path);

// Verify file exists and is within Uploads directory
$uploads_dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Uploads');
if (!$full_path || strpos($full_path, $uploads_dir) !== 0 || !file_exists($full_path)) {
    http_response_code(404);
    error_log('File not found or outside allowed directory: ' . $full_path);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['error' => 'Fichier non trouvé sur le serveur']));
}

// Set appropriate headers based on file type
$mime_types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$file_type = strtolower($file['file_type']);
$mime_type = isset($mime_types[$file_type]) ? $mime_types[$file_type] : 'application/octet-stream';
error_log('Serving file type: ' . $file_type . ', MIME: ' . $mime_type);

// Determine if the file should be displayed inline or downloaded
$disposition = (in_array($file_type, ['pdf', 'jpg', 'jpeg', 'png']) && isset($_GET['view'])) ? 'inline' : 'attachment';
error_log('Content-Disposition: ' . $disposition);

// Set headers
header('Content-Type: ' . $mime_type);
header('Content-Disposition: ' . $disposition . '; filename="' . $file['original_name'] . '"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'none\'; img-src \'self\'; object-src \'self\';');
header('Accept-Ranges: bytes');

// Output the file
readfile($full_path);
error_log('File served successfully: ' . $full_path);
exit;
?>