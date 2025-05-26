<?php
session_start(); // Oturumu başlatır
$hata = "";      // Hata mesajı tutulur
$mesaj = "";     // Başarı mesajı tutulur

$conn = new mysqli("localhost", "root", "", "biletra");
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// === ŞİFRE DEĞİŞTİRME İŞLEMİ ===
if (isset($_POST['sifre_degistir'])) {
    $yeniSifre = $_POST['yeni_sifre'] ?? '';
    $kullaniciID = $_SESSION['user_id'] ?? 0;

    if (!empty($yeniSifre) && $kullaniciID) {
        $stmt = $conn->prepare("UPDATE adminpaneli SET password = ?, sifre_degisti = 1 WHERE id = ?");
        $stmt->bind_param("si", $yeniSifre, $kullaniciID);
        if ($stmt->execute()) {
            unset($_SESSION['ilk_giris']); // İlk giriş durumu kaldırılır
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin-panel.php");
            } else {
                header("Location: Anasayfa.php");
            }
            exit;
        } else {
            $hata = "Şifre güncellenemedi: " . $stmt->error;
        }
    } else {
        $hata = "Lütfen yeni bir şifre girin.";
    }
}

// === GİRİŞ İŞLEMİ ===
if (isset($_POST['giris'])) {
    $email = $_POST['email'] ?? '';
    $inputPassword = $_POST['psw'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, is_approved, role, sifre_degisti FROM adminpaneli WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($inputPassword !== $user['password']) {
            $hata = "❌ Şifre yanlış!";
        } elseif ($user['is_approved'] == 0) {
            $hata = "⏳ Giriş reddedildi. Henüz admin onayı verilmemiş.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $user['role'];

            if ($user['sifre_degisti'] == 0) {
                $_SESSION['ilk_giris'] = true; // Şifre değiştirilmemişse yönlendir
            } else {
                if ($user['role'] === 'admin') {
                    header("Location: admin-panel.php");
                } else {
                    header("Location: Anasayfa.php");
                }
                exit;
            }
        }
    } else {
        $hata = "❌ Bu email ile kayıtlı kullanıcı bulunamadı.";
    }
}

// === KAYIT İŞLEMİ ===
if (isset($_POST['kaydol'])) {
    $email = $_POST['email'] ?? '';
    $plainPassword = $_POST['psw'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM adminpaneli WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $hata = "❌ Bu email zaten kayıtlı. Lütfen giriş yapın.";
    } else {
        $stmt = $conn->prepare("INSERT INTO adminpaneli (email, password, is_approved, role, sifre_degisti) VALUES (?, ?, 0, 'user', 0)");
        $stmt->bind_param("ss", $email, $plainPassword);
        if ($stmt->execute()) {
            $mesaj = "✅ Kayıt başarılı! Admin onayı bekleniyor. Giriş yapabilirsiniz.";
        } else {
            $hata = "Kayıt sırasında hata oluştu: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <title>Biletra | Giriş / Kayıt</title>
  <!-- Sayfaya özel css tasarımı -->
  <style>
    /* Tüm elemanların kenar boşlukları sıfırlanır ve kutu modeli ayarlanır */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background: url(images/arkaplan.png) no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Formu çevreleyen kutu */
    .container {
      background: rgba(255, 255, 255, 0.95); 
      padding: 30px;
      border-radius: 12px;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3); 
      text-align: center;
    }

    h2 {
      margin-bottom: 20px;
      color: #333;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
    }

    button {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      font-size: 1rem;
      border: none;
      border-radius: 6px;
      color: white;
      background-color: dodgerblue;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button[name="kaydol"] {
      background-color: seagreen;
    }

    button:hover {
      background-color: #005bb5;
    }

    .hata {
      color: red;
      margin-bottom: 10px;
    }

    .mesaj {
      color: green;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
<div class="container">
  <!-- Duruma göre başlık değişir -->
  <h2>
    <?php if (isset($_SESSION['ilk_giris']) && $_SESSION['ilk_giris']): ?>
      İlk Giriş - Şifre Değiştir
    <?php else: ?>
      Giriş Yap / Kayıt Ol
    <?php endif; ?>
  </h2>

  <!-- Hata ve başarı mesajları -->
  <?php if ($hata): ?><div class="hata"><?= $hata ?></div><?php endif; ?>
  <?php if ($mesaj): ?><div class="mesaj"><?= $mesaj ?></div><?php endif; ?>

  <!-- İlk girişte şifre değiştirme formu -->
  <?php if (isset($_SESSION['ilk_giris']) && $_SESSION['ilk_giris']): ?>
    <form method="post">
      <input type="password" name="yeni_sifre" placeholder="Yeni Şifre" required>
      <button type="submit" name="sifre_degistir">Şifreyi Güncelle</button>
    </form>

  <!-- Giriş ve kayıt formu -->
  <?php else: ?>
    <form method="post">
      <input type="text" name="email" placeholder="E-posta" required>
      <input type="password" name="psw" placeholder="Şifre" required>
      <button type="submit" name="giris">Giriş Yap</button>
      <button type="submit" name="kaydol">Kayıt Ol</button>
    </form>
  <?php endif; ?>
</div>

</body>
</html>
