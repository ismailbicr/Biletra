<?php
session_start(); // Oturumu başlatır 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Eğer kullanıcı giriş yapmamışsa veya admin değilse giriş sayfasına yönlendir
    header("Location: Login.php");
    exit;
}

// Veritabanına bağlan
$conn = new mysqli("localhost", "root", "", "biletra");
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// API: Etkinlikleri JSON olarak döndür 
if (isset($_GET['api']) && $_GET['api'] === 'etkinlikler') {
    header('Content-Type: application/json');
    $etkinlikler = [];
    $result = $conn->query("SELECT id, baslik, aciklama, tarih, ucret, konum, kontenjan FROM etkinlikler ORDER BY tarih ASC");
    while ($row = $result->fetch_assoc()) {
        $etkinlikler[] = $row;
    }
    echo json_encode($etkinlikler);
    exit;
}

// Kullanıcı onaylama 
if (isset($_GET['onayla'])) {
    $user_id = intval($_GET['onayla']);
    $stmt = $conn->prepare("UPDATE adminpaneli SET is_approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-panel.php");
    exit;
}

// Etkinlik onaylama
if (isset($_GET['etkinlik_onayla'])) {
    $etkinlik_id = intval($_GET['etkinlik_onayla']);
    $stmt = $conn->prepare("UPDATE etkinlikler SET onayli = 1 WHERE id = ?");
    $stmt->bind_param("i", $etkinlik_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-panel.php");
    exit;
}

// Duyuru ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ekle'])) {
    $baslik = $_POST['duyuru_baslik'];
    $mesaj = $_POST['duyuru_mesaj'];
    $tarih = date("Y-m-d"); // Şu anki tarihi alır
    $stmt = $conn->prepare("INSERT INTO duyurular (baslik, icerik, tarih) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $baslik, $mesaj, $tarih);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-panel.php");
    exit;
}

// Etkinlik ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etkinlik_ekle'])) {
    $baslik = $_POST['etkinlik_baslik'];
    $aciklama = $_POST['etkinlik_aciklama'];
    $tarih = $_POST['etkinlik_tarih'];
    $ucret = $_POST['etkinlik_ucret'];
    $konum = $_POST['etkinlik_konum'];
    $kontenjan = $_POST['etkinlik_kontenjan'];

    // Etkinlik ilk başta onaysız eklenir (onayli = 0)
    $stmt = $conn->prepare("INSERT INTO etkinlikler (baslik, aciklama, tarih, ucret, konum, kontenjan, onayli) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssdsi", $baslik, $aciklama, $tarih, $ucret, $konum, $kontenjan);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-panel.php");
    exit;
}

// Duyuru silme
if (isset($_GET['sil_duyuru'])) {
    $id = intval($_GET['sil_duyuru']);
    $conn->query("DELETE FROM duyurular WHERE id = $id");
    header("Location: admin-panel.php");
    exit;
}

// Etkinlik silme
if (isset($_GET['sil_etkinlik'])) {
    $id = intval($_GET['sil_etkinlik']);
    $conn->query("DELETE FROM etkinlikler WHERE id = $id");
    header("Location: admin-panel.php");
    exit;
}

// Kullanıcı, etkinlik ve duyuru bilgilerini al
$users = $conn->query("SELECT id, email, is_approved FROM adminpaneli WHERE role = 'user'");
$events = $conn->query("SELECT * FROM etkinlikler");
$announcements = $conn->query("SELECT * FROM duyurular");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Paneli</title>
  <style>
    /* Sayfaya Özel CSS tasarımı */
    body {
      font-family: Arial; 
      padding: 20px; 
      background: #f7f7f7; 
    }
    table {
      width: 100%; 
      border-collapse: collapse;
      margin-bottom: 30px; 
    }
    th, td { 
      border: 1px solid #ccc; 
      padding: 10px; 
      text-align: left; 
    }
    th { 
      background-color: #eee; 
    }
    h2 { 
      margin-top: 40px; 
    }
    .onayla, .sil { 
      padding: 5px 10px; 
      border-radius: 4px; 
      text-decoration: none; 
    }
    .onayla { 
      background: green; 
      color: white; 
    }
    .sil { 
      background: red; 
      color: white; 
    }
    .cikis { 
      float: right; 
      margin-top: -60px; 
      margin-right: 20px; 
    }
    form { 
      background: #fff; 
      padding: 20px; 
      border: 1px solid #ccc; 
      border-radius: 6px; 
      margin-bottom: 30px; 
    }
    form input, form textarea, form button {
      width: 100%; 
      margin: 10px 0; 
      padding: 10px; 
      font-size: 16px;
    }
    form button { 
      background: seagreen; 
      color: white; 
      border: none; 
      border-radius: 4px; 
      cursor: pointer; 
    }
  </style>
</head>
<body>
<h1>Admin Paneli</h1>
<a href="index.php" class="cikis">Çıkış Yap</a>

<!-- Kullanıcı Onay Tablosu -->
<h2>Kullanıcı Onayı</h2>
<table>
  <tr><th>Email</th><th>Onay Durumu</th><th>İşlem</th></tr>
  <?php while ($u = $users->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= $u['is_approved'] ? "Onaylı" : "Bekliyor" ?></td>
      <td>
        <?php if (!$u['is_approved']): ?>
          <a class="onayla" href="admin-panel.php?onayla=<?= $u['id'] ?>">Onayla</a>
        <?php else: ?>
          ✅
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<!-- Etkinlik Ekleme Formu -->
<h2>Etkinlik Ekle</h2>
<form method="POST">
  <input name="etkinlik_baslik" placeholder="Etkinlik Başlığı" required>
  <textarea name="etkinlik_aciklama" placeholder="Etkinlik Açıklaması" required></textarea>
  <input type="date" name="etkinlik_tarih" required>
  <input type="number" name="etkinlik_ucret" step="0.01" placeholder="Etkinlik Ücreti" required>
  <input type="text" name="etkinlik_konum" placeholder="Etkinlik Yeri (Konum)" required>
  <input type="number" name="etkinlik_kontenjan" placeholder="Kontenjan" required>
  <button name="etkinlik_ekle">Etkinlik Ekle</button>
</form>

<!-- Etkinlik Listesi -->
<h2>Etkinlikler</h2>
<table>
  <tr><th>Başlık</th><th>Açıklama</th><th>Tarih</th><th>Ücret</th><th>Yer</th><th>Kontenjan</th><th>Durum</th><th>İşlem</th></tr>
  <?php while ($e = $events->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($e['baslik']) ?></td>
      <td><?= htmlspecialchars($e['aciklama']) ?></td>
      <td><?= htmlspecialchars($e['tarih']) ?></td>
      <td><?= number_format($e['ucret'], 2) ?> ₺</td>
      <td><?= htmlspecialchars($e['konum']) ?></td>
      <td><?= (int)$e['kontenjan'] ?></td>
      <td><?= $e['onayli'] ? "✅ Onaylı" : "⏳ Bekliyor" ?></td>
      <td>
        <?php if (!$e['onayli']): ?>
          <a class="onayla" href="admin-panel.php?etkinlik_onayla=<?= $e['id'] ?>">Onayla</a>
        <?php endif; ?>
        <a class="sil" href="admin-panel.php?sil_etkinlik=<?= $e['id'] ?>">Sil</a>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<!-- Duyuru Ekleme Formu -->
<h2>Duyuru Ekle</h2>
<form method="POST">
  <input name="duyuru_baslik" placeholder="Duyuru Başlığı" required>
  <textarea name="duyuru_mesaj" placeholder="Duyuru Mesajı" required></textarea>
  <button name="duyuru_ekle">Duyuru Ekle</button>
</form>

<!-- Duyurular Listesi -->
<h2>Duyurular</h2>
<table>
  <tr><th>Başlık</th><th>Mesaj</th><th>Tarih</th><th>İşlem</th></tr>
  <?php while ($d = $announcements->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($d['baslik']) ?></td>
      <td><?= htmlspecialchars($d['icerik']) ?></td>
      <td><?= htmlspecialchars($d['tarih']) ?></td>
      <td><a class="sil" href="admin-panel.php?sil_duyuru=<?= $d['id'] ?>">Sil</a></td>
    </tr>
  <?php endwhile; ?>
</table>

</body>
</html>
