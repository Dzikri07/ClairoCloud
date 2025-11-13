<?php
// file_functions.php - helper functions for file listing and upload

// Load MinIO functions if available
$minioFunctionsPath = __DIR__ . '/minio_functions.php';
if (file_exists($minioFunctionsPath)) {
    require_once $minioFunctionsPath;
}

function get_upload_dir()
{
    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir;
}

function list_files()
{
    $dir = get_upload_dir();
    $items = [];
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_file($path)) {
            $items[] = [
                'name' => $f,
                'size' => filesize($path),
                'url' => 'uploads/' . rawurlencode($f),
                'mime' => mime_content_type($path)
            ];
        }
    }
    return $items;
}

function human_filesize($bytes, $decimals = 2)
{
    // jika bukan nilai numerik, kembalikan apa adanya (mis. sudah terformat)
    if (!is_numeric($bytes)) {
        return (string) $bytes;
    }

    $bytes = (float) $bytes;
    if ($bytes < 1024) {
        return sprintf("%d B", (int) round($bytes));
    }

    $units = ['B','K','M','G','T','P'];
    $factor = (int) floor(log($bytes, 1024));
    $factor = max(0, min($factor, count($units) - 1));

    return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
}

function handle_upload($field = 'upload_file')
{
    if (!isset($_FILES[$field])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error code: ' . $file['error']];
    }

    $upload_dir = get_upload_dir();
    $original = basename($file['name']);
    // sanitize filename
    $original = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $original);
    $target = $upload_dir . '/' . $original;
    $i = 1;
    while (file_exists($target)) {
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $nameOnly = pathinfo($original, PATHINFO_FILENAME);
        $target = $upload_dir . '/' . $nameOnly . '-' . $i . ($ext ? '.' . $ext : '');
        $i++;
    }

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file.'];
    }

    $result = ['success' => true, 'file' => basename($target), 'storage' => 'local'];

    // Try to upload to MinIO if available
    if (function_exists('get_active_storage') && get_active_storage() === 'minio') {
        $objectKey = basename($target);
        $username = current_user()['username'] ?? 'guest';
        
        $minioResult = minio_upload_file($target, $objectKey, [
            'uploaded-by' => $username,
            'original-name' => $original
        ]);
        
        if ($minioResult['success']) {
            $result['storage'] = 'minio';
            $result['minio_key'] = $minioResult['object_key'];
            $result['minio_url'] = $minioResult['url'];
            
            // Optionally delete local file after successful MinIO upload
            // Uncomment if you want to use MinIO as primary storage
            // @unlink($target);
        }
    }

    // optional: store metadata in database if config available
    $configPath = __DIR__ . '/config.php';
    if (file_exists($configPath)) {
        try {
            require_once $configPath;
            if (function_exists('get_db_pdo')) {
                $pdo = get_db_pdo();
                if ($pdo) {
                    $stmt = $pdo->prepare('INSERT INTO files (filename, original_name, mime, size, storage_type, minio_key) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        basename($target), 
                        $original, 
                        mime_content_type($target), 
                        filesize($target),
                        $result['storage'],
                        $result['minio_key'] ?? null
                    ]);
                    $result['db_id'] = $pdo->lastInsertId();
                }
            }
        } catch (Exception $e) {
            // ignore DB errors for now but return file uploaded successfully
            $result['db_error'] = $e->getMessage();
        }
    }

    return $result;
}

// -- user store persistence (users.json) --
define('USERS_JSON', __DIR__ . '/users.json');

/**
 * load_user_store()
 * Returns associative array username => userdata
 * If users.json not present but auth_users.php exists, initialize from it.
 */
function load_user_store()
{
    // prefer users.json
    if (file_exists(USERS_JSON)) {
        $raw = file_get_contents(USERS_JSON);
        $data = json_decode($raw, true);
        if (is_array($data)) return $data;
    }

    // fallback to auth_users.php if present
    $configPath = __DIR__ . '/auth_users.php';
    if (file_exists($configPath)) {
        $store = include $configPath;
        if (is_array($store)) {
            // normalize: ensure fields password, role, email, quota (bytes)
            $out = [];
            foreach ($store as $u => $meta) {
                $out[$u] = [
                    'password' => $meta['password'] ?? '',
                    'role' => $meta['role'] ?? 'user',
                    'email' => $meta['email'] ?? '',
                    'quota' => isset($meta['quota']) ? (int)$meta['quota'] : (5 * 1024 * 1024 * 1024) // default 5GB
                ];
            }
            // materialize to users.json for future edits
            file_put_contents(USERS_JSON, json_encode($out, JSON_PRETTY_PRINT));
            return $out;
        }
    }

    // default dev users
    $default = [
        'admin' => [
            'password' => password_hash('adminpass', PASSWORD_DEFAULT),
            'role' => 'admin',
            'email' => 'admin@example.com',
            'quota' => 5 * 1024 * 1024 * 1024
        ],
        'user' => [
            'password' => password_hash('userpass', PASSWORD_DEFAULT),
            'role' => 'user',
            'email' => 'user@example.com',
            'quota' => 5 * 1024 * 1024 * 1024
        ],
    ];
    file_put_contents(USERS_JSON, json_encode($default, JSON_PRETTY_PRINT));
    return $default;
}

function save_user_store(array $store)
{
    file_put_contents(USERS_JSON, json_encode($store, JSON_PRETTY_PRINT));
}

/**
 * get_user_store() - keep compatibility with existing callers
 */
function get_user_store()
{
    return load_user_store();
}

/**
 * add_user($username, $password, $role='user', $email='', $quota_bytes=null)
 * Returns ['success'=>bool,'message'=>string]
 */
function add_user($username, $password, $role = 'user', $email = '', $quota_bytes = null)
{
    $username = trim((string)$username);
    if ($username === '') return ['success' => false, 'message' => 'Username kosong'];
    $store = load_user_store();
    if (isset($store[$username])) return ['success' => false, 'message' => 'User sudah ada'];
    $store[$username] = [
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
        'email' => $email,
        'quota' => $quota_bytes === null ? (5 * 1024 * 1024 * 1024) : (int)$quota_bytes
    ];
    save_user_store($store);
    return ['success' => true, 'message' => 'User ditambahkan'];
}

/**
 * delete_user($username)
 */
function delete_user($username)
{
    $username = (string)$username;
    $store = load_user_store();
    if (!isset($store[$username])) return ['success' => false, 'message' => 'User tidak ditemukan'];
    unset($store[$username]);
    save_user_store($store);
    return ['success' => true, 'message' => 'User dihapus'];
}

/**
 * set_user_quota($username, $bytes)
 */
function set_user_quota($username, $bytes)
{
    $username = (string)$username;
    $store = load_user_store();
    if (!isset($store[$username])) return ['success' => false, 'message' => 'User tidak ditemukan'];
    $store[$username]['quota'] = (int)$bytes;
    save_user_store($store);
    return ['success' => true, 'message' => 'Kuota diperbarui'];
}

/**
 * get_user_quota($username) - returns bytes or null
 */
function get_user_quota($username)
{
    $store = load_user_store();
    return isset($store[$username]['quota']) ? (int)$store[$username]['quota'] : null;
}

// --- Authentication helpers (admin & user) ---
function ensure_session()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * authenticate_user($username, $password)
 * Returns ['success' => bool, 'message' => string]
 * On success stores user info in $_SESSION['user'].
 */
function authenticate_user($username, $password)
{
    ensure_session();
    $users = get_user_store();
    if (!isset($users[$username])) {
        return ['success' => false, 'message' => 'Unknown user'];
    }

    $hash = $users[$username]['password'] ?? '';
    if (!is_string($hash) || $hash === '') {
        return ['success' => false, 'message' => 'Invalid user configuration'];
    }

    if (password_verify($password, $hash)) {
        $_SESSION['user'] = [
            'username' => $username,
            'role' => $users[$username]['role'] ?? 'user'
        ];
        // regenerate session id for safety
        session_regenerate_id(true);
        return ['success' => true, 'message' => 'Authenticated'];
    }

    return ['success' => false, 'message' => 'Invalid credentials'];
}

function current_user()
{
    ensure_session();
    return $_SESSION['user'] ?? null;
}

function is_logged_in()
{
    return (bool) current_user();
}

function is_admin()
{
    $u = current_user();
    return $u && isset($u['role']) && $u['role'] === 'admin';
}

/**
 * require_login($role = null)
 * Returns array similar to authenticate_user: ['success'=>bool, 'message'=>...]
 * Does not perform redirects; caller should handle response/redirect.
 */
function require_login($role = null)
{
    ensure_session();
    if (!is_logged_in()) {
        return ['success' => false, 'message' => 'not_logged_in'];
    }
    if ($role !== null) {
        $u = current_user();
        if (!isset($u['role']) || $u['role'] !== $role) {
            return ['success' => false, 'message' => 'forbidden'];
        }
    }
    return ['success' => true, 'user' => current_user()];
}

function logout_user()
{
    ensure_session();
    unset($_SESSION['user']);
    // optionally destroy session:
    session_regenerate_id(true);
    return ['success' => true];
}

// --- TRASH helpers ---
define('TRASH_DIR', __DIR__ . '/uploads/.trash');
define('TRASH_JSON', __DIR__ . '/trash_index.json');

/**
 * load_trash_index(): returns associative array id => metadata
 */
function load_trash_index()
{
    if (file_exists(TRASH_JSON)) {
        $raw = file_get_contents(TRASH_JSON);
        $data = json_decode($raw, true);
        if (is_array($data)) return $data;
    }
    return [];
}

function save_trash_index(array $index)
{
    if (!is_dir(TRASH_DIR)) {
        @mkdir(TRASH_DIR, 0755, true);
    }
    file_put_contents(TRASH_JSON, json_encode($index, JSON_PRETTY_PRINT));
}

/**
 * move_to_trash($path, $original_name, $deleted_by)
 * Moves a file into trash and records metadata.
 * Returns ['success'=>bool,'message'=>...,'id'=>...]
 */
function move_to_trash($path, $original_name = null, $deleted_by = null)
{
    if (!is_file($path)) {
        return ['success' => false, 'message' => 'File not found'];
    }
    if (!is_dir(TRASH_DIR)) {
        if (!@mkdir(TRASH_DIR, 0755, true)) {
            return ['success' => false, 'message' => 'Gagal membuat folder trash'];
        }
    }

    $original_name = $original_name ?? basename($path);
    $stored = time() . '_' . bin2hex(random_bytes(6)) . '_' . basename($original_name);
    $dst = TRASH_DIR . DIRECTORY_SEPARATOR . $stored;

    if (!@rename($path, $dst)) {
        return ['success' => false, 'message' => 'Gagal memindahkan file ke trash'];
    }

    $meta = [
        'id' => $id = uniqid('trash_', true),
        'original_name' => $original_name,
        'stored_name' => $stored,
        'deleted_at' => time(),
        'size' => filesize($dst),
        'mime' => mime_content_type($dst) ?: 'application/octet-stream',
        'deleted_by' => $deleted_by ?? (current_user()['username'] ?? null),
    ];

    $index = load_trash_index();
    $index[$id] = $meta;
    save_trash_index($index);

    return ['success' => true, 'message' => 'File dipindahkan ke Sampah', 'id' => $id];
}

/**
 * restore_from_trash($id)
 * Moves file back to uploads (avoid overwrite by suffix) and removes index entry.
 */
function restore_from_trash($id)
{
    $index = load_trash_index();
    if (!isset($index[$id])) return ['success' => false, 'message' => 'Item tidak ditemukan'];
    $meta = $index[$id];
    $src = TRASH_DIR . DIRECTORY_SEPARATOR . $meta['stored_name'];
    if (!is_file($src)) {
        unset($index[$id]);
        save_trash_index($index);
        return ['success' => false, 'message' => 'File trash tidak ditemukan'];
    }

    $uploads = get_upload_dir();
    $target = $uploads . DIRECTORY_SEPARATOR . $meta['original_name'];
    $base = pathinfo($target, PATHINFO_FILENAME);
    $ext = pathinfo($target, PATHINFO_EXTENSION);
    $i = 1;
    while (file_exists($target)) {
        $target = $uploads . DIRECTORY_SEPARATOR . ($base . " ({$i})") . ($ext ? '.' . $ext : '');
        $i++;
    }

    if (!@rename($src, $target)) {
        return ['success' => false, 'message' => 'Gagal mengembalikan file'];
    }

    unset($index[$id]);
    save_trash_index($index);

    return ['success' => true, 'message' => 'File dikembalikan'];
}

/**
 * delete_trash_item($id)
 * Permanently delete trash item and remove from index.
 */
function delete_trash_item($id)
{
    $index = load_trash_index();
    if (!isset($index[$id])) return ['success' => false, 'message' => 'Item tidak ditemukan'];
    $meta = $index[$id];
    $src = TRASH_DIR . DIRECTORY_SEPARATOR . $meta['stored_name'];
    if (is_file($src)) {
        @unlink($src);
    }
    unset($index[$id]);
    save_trash_index($index);
    return ['success' => true, 'message' => 'Dihapus permanen'];
}

/**
 * purge_old_trash($days = 30)
 * Removes trash items older than $days.
 */
function purge_old_trash($days = 30)
{
    $cut = time() - ($days * 86400);
    $index = load_trash_index();
    $changed = false;
    foreach ($index as $id => $meta) {
        if (($meta['deleted_at'] ?? 0) < $cut) {
            $file = TRASH_DIR . DIRECTORY_SEPARATOR . ($meta['stored_name'] ?? '');
            if (is_file($file)) @unlink($file);
            unset($index[$id]);
            $changed = true;
        }
    }
    if ($changed) save_trash_index($index);
    return true;
}
