<?php
declare(strict_types=1);

$envFile = __DIR__.'/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        if (($value[0] ?? '') === '"' && str_ends_with($value, '"')) $value = substr($value, 1, -1);
        $_ENV[trim($key)] = $value;
    }
}

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_ENV['APP_ENV'] ?? 'development') === 'production');
session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax']);
session_start();

function envv(string $key, mixed $default = null): mixed { return $_ENV[$key] ?? getenv($key) ?: $default; }
function e(mixed $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function cfg(?string $key = null): mixed {
    static $config;
    if ($config === null) $config = [
        'app_name' => envv('APP_NAME', 'VideoEarn'), 'base_url' => rtrim((string)envv('BASE_URL', ''), '/'),
        'timezone' => envv('TIMEZONE', 'Asia/Jakarta'), 'upload_max_mb' => (int)envv('UPLOAD_MAX_MB', 200),
        'admin' => ['email' => strtolower((string)envv('ADMIN_EMAIL', ''))],
        'google' => ['client_id' => (string)envv('GOOGLE_CLIENT_ID', ''), 'client_secret' => (string)envv('GOOGLE_CLIENT_SECRET', ''), 'folder_id' => (string)envv('GOOGLE_FOLDER_ID', '')],
    ];
    return $key === null ? $config : ($config[$key] ?? null);
}
date_default_timezone_set((string)cfg('timezone'));
function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $driver = strtolower((string)envv('DB_DRIVER', 'sqlite'));
    if ($driver === 'mysql') {
        $dsn = 'mysql:host='.envv('DB_HOST', 'localhost').';dbname='.envv('DB_NAME', '').';charset=utf8mb4';
        $pdo = new PDO($dsn, (string)envv('DB_USER', ''), (string)envv('DB_PASS', ''), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } else {
        $path = __DIR__.'/'.ltrim((string)envv('DB_PATH', 'storage/videoearn.sqlite'), '/');
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
        $pdo = new PDO('sqlite:'.$path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT,email VARCHAR(190) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,username VARCHAR(60) NOT NULL,tokens BIGINT NOT NULL DEFAULT 0,role VARCHAR(10) NOT NULL DEFAULT 'user',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS videos (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,drive_file_id VARCHAR(150) NOT NULL,mime_type VARCHAR(100) NOT NULL,caption VARCHAR(500) NOT NULL DEFAULT '',status VARCHAR(10) NOT NULL DEFAULT 'pending',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS token_ledger (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,amount BIGINT NOT NULL,description VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (name VARCHAR(100) PRIMARY KEY,value TEXT NOT NULL)");
    return $pdo;
}
function current_user(): ?array { if (empty($_SESSION['uid'])) return null; $q = db()->prepare('SELECT id,email,username,tokens,role FROM users WHERE id=?'); $q->execute([(int)$_SESSION['uid']]); return $q->fetch() ?: null; }
function require_login(): void { if (!current_user()) { header('Location: index.php?page=login'); exit; } }
function require_admin(): void { $u = current_user(); if (!$u || $u['role'] !== 'admin') { http_response_code(403); exit('Akses ditolak'); } }
function flash(string $s): void { $_SESSION['flash'] = $s; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function verify_csrf(): void { if (!hash_equals((string)($_SESSION['csrf'] ?? ''), (string)($_POST['csrf'] ?? ''))) { http_response_code(419); exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.'); } }
function rate_limit(string $key, int $max = 10, int $window = 60): void { $now = time(); $bucket = $_SESSION['_rate'][$key] ?? ['start' => $now, 'count' => 0]; if ($now - $bucket['start'] > $window) $bucket = ['start' => $now, 'count' => 0]; $bucket['count']++; $_SESSION['_rate'][$key] = $bucket; if ($bucket['count'] > $max) { http_response_code(429); exit('Terlalu banyak percobaan. Coba lagi nanti.'); } }
function google_token(): string { $q = db()->query("SELECT value FROM settings WHERE name='google_refresh_token'"); $r = $q->fetch(); return $r['value'] ?? ''; }
function google_access_token(): string { $refresh = google_token(); if (!$refresh) throw new RuntimeException('Google Drive belum terhubung. Admin perlu menghubungkan akun Google.'); $g = cfg('google'); $ch = curl_init('https://oauth2.googleapis.com/token'); curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_POSTFIELDS=>http_build_query(['client_id'=>$g['client_id'],'client_secret'=>$g['client_secret'],'refresh_token'=>$refresh,'grant_type'=>'refresh_token']),CURLOPT_TIMEOUT=>30]); $raw=curl_exec($ch); $err=curl_error($ch); curl_close($ch); if (!$raw) throw new RuntimeException('Google OAuth gagal: '.$err); $data=json_decode($raw,true); if (empty($data['access_token'])) throw new RuntimeException('Token Google gagal diperbarui.'); return $data['access_token']; }
function google_api(string $method, string $url, string $token, array $headers = [], ?string $body = null): array { $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>array_merge(['Authorization: Bearer '.$token],$headers),CURLOPT_TIMEOUT=>180]); if ($body!==null) curl_setopt($ch,CURLOPT_POSTFIELDS,$body); $out=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch); if ($out===false) throw new RuntimeException($err); return [$status,$out]; }
function google_make_public(string $fileId): void { [$status, $raw] = google_api('POST', 'https://www.googleapis.com/drive/v3/files/'.rawurlencode($fileId).'/permissions', google_access_token(), ['Content-Type: application/json'], json_encode(['type'=>'anyone','role'=>'reader'], JSON_THROW_ON_ERROR)); if ($status < 200 || $status >= 300) throw new RuntimeException('File tersimpan, tetapi izin publik gagal.'); }
