<?php
session_start(); // Oturumu başlatır

// Giriş yapmamış veya kullanıcı rolü 'user' olmayanlar login sayfasına yönlendirilir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Çıkış yapılmak istenmişse oturum temizlenip ana sayfaya yönlendirilir
if (isset($_GET['cikis'])) {
    session_unset(); // Tüm oturum değişkenlerini sıfırla
    session_destroy(); // Oturumu tamamen sonlandır
    header("Location: index.php");
    exit;
}

include("baglanti.php"); // Veritabanı bağlantısı
$email = $_SESSION['email']; // Giriş yapan kullanıcının e-posta adresi
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <!-- Font Awesome ikon kütüphanesi -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" />
    
    <!-- Özel stil dosyası -->
    <link rel="stylesheet" href="styles/style.css" />
    
    <!-- Sayfaya özel css tasarımı -->
    <style>
    /* Hava tahmini kutusunun görünümünü ayarlar */
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
    
    /* Hava tahmini içindeki liste tasarımı */
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

    /* Ana ekran stil ayarları */
    .home {
        min-height: 100vh;
        background: url(images/arkaplan.png) no-repeat center center;
        background-size: cover;
        margin-top: -17.25rem;
        padding: 17rem 3rem;
        text-align: left;
    }

    .home .content h3 {
        font-size: 5rem;
        color: #000;
        margin-bottom: 2rem;
    }
    </style>

    <title>Biletra | Dashboard</title>
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
              <ul id="havaListesi"></ul> <!-- JavaScript ile doldurulacak -->
          </div>
        </div>
        <button>
            <a href="Anasayfa.php?cikis=1">
                <i class="fa-solid fa-right-from-bracket" style="color: black;"></i>
            </a>
        </button>
    </div>
</header>

<!-- Ana içerik bölümü -->
<section class="home">
    <div class="content">
        <h3>Biletra'ya tekrar <br> hoş geldin, 
        <h3>Eğlence dolu etkinlikler <br> seni bekliyor!</h3>
    </div>
</section>

<!-- Hava durumu JavaScript kodu -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("havadurumubtn");
    const kutu = document.getElementById("havaTahminKutusu");
    const liste = document.getElementById("havaListesi");
    let tahminGosteriliyor = false;

    // Tarayıcı konum desteği varsa
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;  
            const lon = position.coords.longitude; 

            // Şu anki hava durumu bilgisini al
            fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`)
                .then(response => response.json())
                .then(data => {
                    const weather = data.current_weather;
                    // Butonun üstüne anlık sıcaklık ve rüzgar bilgisi gösterilir
                    btn.title = `Sıcaklık: ${weather.temperature}°C | Rüzgar: ${weather.windspeed} km/h`;
                });

            // Butona tıklandığında 5 günlük hava tahmini gösterilir/gizlenir
            btn.addEventListener("click", function () {
                if (tahminGosteriliyor) {
                    kutu.style.display = "none";
                    tahminGosteriliyor = false;
                    return;
                }

                // 5 günlük hava tahminini çek
                fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min,weathercode&timezone=auto`)
                    .then(res => res.json())
                    .then(data => {
                        const days = data.daily.time;
                        const max = data.daily.temperature_2m_max;
                        const min = data.daily.temperature_2m_min;

                        liste.innerHTML = ""; 
                        for (let i = 0; i < 5; i++) {
                            // Günlük tahminler listeye eklenir
                            liste.innerHTML += `<li><strong>${days[i]}</strong><br>${min[i]}°C - ${max[i]}°C</li>`;
                        }

                        kutu.style.display = "block";
                        tahminGosteriliyor = true;
                    });
            });

        }, function () {
            btn.title = "📍 Konum erişimi reddedildi.";
        });
    } else {
        btn.title = "🔒 Tarayıcınız konum desteklemiyor.";
    }
});
</script>

</body>
</html>
