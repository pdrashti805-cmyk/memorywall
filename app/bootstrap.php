<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

function load_env_file(): void {
    $path = ROOT_PATH . '/.env';
    if (!is_file($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) putenv("$key=$value");
    }
}
load_env_file();

function env_value(string $key, string $default = ''): string {
    $v = getenv($key);
    return $v === false ? $default : (string)$v;
}
function app_config(): array {
    return [
        'name' => env_value('APP_NAME', 'MemoryWall'),
        'env' => env_value('APP_ENV', 'development'),
        'url' => env_value('APP_URL', 'http://localhost:8000'),
        'db_host' => env_value('DB_HOST', '127.0.0.1'),
        'db_port' => max(1, (int)env_value('DB_PORT', '3306')),
        'db_name' => env_value('DB_NAME', 'memorywall'),
        'db_user' => env_value('DB_USER', 'root'),
        'db_pass' => env_value('DB_PASS', ''),
        'db_charset' => env_value('DB_CHARSET', 'utf8mb4'),
        'uploads' => ROOT_PATH . '/' . ltrim(env_value('UPLOAD_DIR', 'storage/uploads'), '/'),
        'max_mb' => max(1, (int)env_value('MAX_UPLOAD_MB', '5')),
        'max_files' => max(1, min(20, (int)env_value('MAX_FILES_PER_UPLOAD', '8'))),
        'slide_ms' => max(2000, (int)env_value('SLIDE_DURATION_MS', '5000')),
        'admin_password' => env_value('ADMIN_PASSWORD', 'CHANGE_THIS_TO_A_LONG_RANDOM_PASSWORD'),
    ];
}
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $c = app_config();
    @mkdir($c['uploads'], 0775, true);
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $c['db_host'], $c['db_port'], $c['db_name'], $c['db_charset']
    );
    $pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);
    return $pdo;
}
function start_app_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('memorywall_session');
        session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax', 'secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')]);
        session_start();
    }
}
start_app_session();
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419); exit('Your session expired. Please go back, refresh the page, and try again.');
    }
}
function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}
function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}
function visitor_by_email(string $email): ?array {
    $s = db()->prepare('SELECT * FROM visitors WHERE email = ?');
    $s->execute([$email]);
    $row = $s->fetch();
    return $row ?: null;
}
