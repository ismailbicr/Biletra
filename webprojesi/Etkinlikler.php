<?php
session_start(); // Oturumu başlat

// Eğer giriş yapılmamışsa veya kullanıcı rolü 'user' değilse login sayfasına yönlendir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

include("baglanti.php");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletra | Etkinlikler</title>

    <!-- Font Awesome ikonları -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" />

    <!-- Harici stil dosyası -->
    <link rel="stylesheet" href="styles/style.css" />

    <!-- Sayfaya özel CSS tasarımı -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url(images/arkaplan.png) no-repeat center center fixed;
            background-size: cover;
            margin: 0;
        }

        /* Hava tahmini kutusu */
        #havaTahminKutusu {
            display: none;
            background: #ffffff;
            padding: 16px;
            margin-top: 65px;
            border: 1px solid #ccc;
            border-radius: 12px;
            width: 100vw;
            max-width: 600px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: left;
            position: absolute;
            top: 100%;
            left: -290%;
            transform: translateX(-50%);
            z-index: 1000;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Hava durumu liste öğeleri */
        #havaListesi {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 0;
        }

        #havaListesi li {
            flex: 1;
            list-style: none;
            padding: 8px;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-align: center;
            font-size: 0.9rem;
            border: 1px solid #ddd;
        }

        .hava-wrapper {
            position: relative;
            display: inline-block;
        }

        h2 {
            text-align: center;
            color: #fff;
            text-shadow: 5px 1px 3px rgba(0, 0, 0, 0.97);
            font-size: 8rem;
            margin: 4rem 0 2rem;
        }

        /* Etkinlik kutularının yerleşimi */
        .etkinlik-kutulari {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 0 5rem 5rem;
        }

        /* Her bir etkinlik kutusu */
        .etkinlik {
            background-color: rgba(0, 0, 0, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            color: white;
            position: relative;
            width: 300px;
        }

        .etkinlik h3 {
            margin-top: 0;
            font-size: 1.8rem;
        }

        .etkinlik p {
            font-size: 0.95rem;
            color: #ddd;
        }

        .etkinlik button {
            margin-top: 10px;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 6px;
            background-color: dodgerblue;
            color: white;
            cursor: pointer;
        }

        .hava-bilgisi {
            position: absolute;
            top: 10px;
            left: 190px;
            font-size: 0.85rem;
            color: #ccc;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 5px 6px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<header class="header">
    <a href="#" class="logo">
        <img src="images/logo.png" alt="logo" />
    </a>
    <nav class="navbar">
        <a href="Anasayfa.php" class="active">Anasayfa</a>
        <a href="Duyurular.php">Duyurular</a>
        <a href="Etkinlikler.php">Etkinlikler</a>
    </nav>
    <div class="buttons">
        <button>
            <a href="sepet.php">
                <i class="fa-solid fa-cart-shopping" style="color: black;"></i>
            </a>
        </button>
        <div class="hava-wrapper">
            <button id="havadurumubtn" title="Hava Durumu">
                <i class="fas fa-cloud-sun"></i>
            </button>
            <div id="havaTahminKutusu">
                <strong>5 Günlük Hava Tahmini</strong>
                <ul id="havaListesi"></ul>
            </div>
        </div>
        <button>
            <a href="Anasayfa.php?cikis=1">
                <i class="fa-solid fa-right-from-bracket" style="color: black;"></i>
            </a>
        </button>
    </div>
</header>

<h2>Etkinlikler</h2>
<div class="etkinlik-kutulari" id="etkinlikAlani">
    <?php
    // Veritabanından onaylı etkinlikleri çek
    $sorgu = $baglanti->query("SELECT * FROM etkinlikler WHERE onayli = 1 ORDER BY tarih ASC");

    // Her etkinliği ekrana bastır
    while ($row = $sorgu->fetch_assoc()): ?>
        <div class="etkinlik">
            <p class="hava-bilgisi" data-konum="<?= htmlspecialchars($row['konum']) ?>">🌤️ Sıcaklık: yükleniyor...</p>
            <h3><?= htmlspecialchars($row['baslik']) ?></h3>
            <p><?= htmlspecialchars($row['aciklama']) ?></p>
            <p><strong>Tarih:</strong> <?= htmlspecialchars($row['tarih']) ?></p>
            <p><strong>Yer:</strong> <?= htmlspecialchars($row['konum']) ?></p>
            <p><strong>Ücret:</strong> <?= number_format($row['ucret'], 2) ?> ₺</p>
            <button onclick="sepeteEkle(
                <?= $row['id'] ?>,
                '<?= htmlspecialchars($row['baslik']) ?>',
                <?= $row['ucret'] ?>,
                '<?= htmlspecialchars($row['tarih']) ?>',
            )">Bilet Al</button>
        </div>
    <?php endwhile; ?>
</div>

<!-- Hava durumu JS -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("havadurumubtn");
    const kutu = document.getElementById("havaTahminKutusu");
    const liste = document.getElementById("havaListesi");
    let tahminGosteriliyor = false;

    // Konum verisi alınabiliyorsa
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            // Anlık hava durumu
            fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`)
                .then(response => response.json())
                .then(data => {
                    const weather = data.current_weather;
                    btn.title = `Sıcaklık: ${weather.temperature}°C | Rüzgar: ${weather.windspeed} km/h`;

                    // Tüm etkinlik kartlarına sıcaklık bilgisi ekle
                    document.querySelectorAll(".hava-bilgisi").forEach(el => {
                        el.textContent = `🌤️ Sıcaklık: ${weather.temperature}°C`;
                    });
                });

            // 5 günlük tahmin kutusunu aç/kapat
            btn.addEventListener("click", function () {
                if (tahminGosteriliyor) {
                    kutu.style.display = "none";
                    tahminGosteriliyor = false;
                    return;
                }

                fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min,weathercode&timezone=auto`)
                    .then(res => res.json())
                    .then(data => {
                        const days = data.daily.time;
                        const max = data.daily.temperature_2m_max;
                        const min = data.daily.temperature_2m_min;

                        liste.innerHTML = "";
                        for (let i = 0; i < 5; i++) {
                            liste.innerHTML += `<li><strong>${days[i]}</strong><br>${min[i]}°C - ${max[i]}°C</li>`;
                        }

                        kutu.style.display = "block";
                        tahminGosteriliyor = true;
                    });
            });
        });
    }
});
</script>

<!-- Sepete ekleme işlemi -->
<script>
    function sepeteEkle(id, baslik, ucret, tarih) {
        // localStorage'dan sepeti al veya boş dizi oluştur
        let sepet = JSON.parse(localStorage.getItem("sepet")) || [];

        // Aynı etkinlik sepette varsa uyarı ver
        const mevcut = sepet.find(item => item.id === id);
        if (mevcut) {
            alert("Bu etkinlik zaten sepette!");
            return;
        }

        sepet.push({
            id,
            baslik,
            ucret,
            tarih
        });

        // Güncel sepeti localStorage'a kaydet
        localStorage.setItem("sepet", JSON.stringify(sepet));

        alert("Etkinlik sepete eklendi!");
    }
</script>

</body>
</html>
