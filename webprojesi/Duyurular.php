<?php
session_start(); // Oturum başlatılır

include(__DIR__ . "/baglanti.php");

// Giriş yapılmamışsa login sayfasına yönlendir
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if (!isset($baglanti)) {
    die("Bağlantı oluşmadı! baglanti.php dosyası hatalı olabilir.");
}

// Eğer "json" parametresi gönderilmişse, duyuruları JSON formatında döndürür
if (isset($_GET['json'])) {
    $duyuruSorgu = mysqli_query($baglanti, "SELECT * FROM duyurular ORDER BY tarih DESC");
    $duyurular = [];
    while ($satir = mysqli_fetch_assoc($duyuruSorgu)) {
        $duyurular[] = $satir; 
    }
    echo json_encode($duyurular); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Font Awesome ikonları için bağlantı -->
    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        crossorigin="anonymous" 
        referrerpolicy="no-referrer" 
    />

    <link rel="stylesheet" href="styles/style.css" />
    <title>Biletra - Duyurular</title>

    <!-- Sayfaya özel css tasarımı -->
    <style>
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
            overflow-x: hidden;
            color: #fff;
        }

        /* Hava durumu bileşeni konumu ve biçimi */
        .hava-wrapper {
            position: relative;
            display: inline-block;
        }

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
            color: black;
        }

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

        /* Duyurular kısmı */
        .duyurular {
            padding: 0rem 1rem 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .duyurular h2 {
            text-align: center;
            color: #fff;
            text-shadow: 5px 1px 3px rgba(0, 0, 0, 0.97);
            font-size: 8rem;
            margin: 4rem 4rem;
        }

        #duyuruListesi {
            width: 100%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            list-style: disc;
            padding-left: 2rem;
        }

        #duyuruListesi li {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.4);
            font-size: 1.2rem;
            color: #f1f1f1;
            text-align: left;
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        #duyuruListesi li:hover {
            transform: translateY(-2px);
            background-color: rgba(0, 0, 0, 0.85);
        }

        #duyuruListesi li::marker {
            font-size: 1.5rem;
            color: black;
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
        <a href="./Anasayfa.php" class="active">Anasayfa</a>
        <a href="Duyurular.php">Duyurular</a>
        <a href="./Etkinlikler.php">Etkinlikler</a>
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

<!-- Duyurular başlığı ve liste alanı -->
<section class="duyurular">
    <h2>Duyurular</h2>
    <ul id="duyuruListesi"></ul>
</section>

<!-- Duyuruları dinamik olarak çeken JS -->
<script>
    async function duyurulariGetir() {
        try {
            const yanit = await fetch("Duyurular.php?json=1"); 
            const duyurular = await yanit.json();
            const liste = document.getElementById("duyuruListesi");
            liste.innerHTML = "";

            // Her duyuruyu listeye ekler
            duyurular.forEach(duyuru => {
                const li = document.createElement("li");
                li.textContent = `${duyuru.baslik} - ${duyuru.mesaj || duyuru.icerik}`;
                liste.appendChild(li); 
            });
        } catch (hata) {
            console.error("Duyurular yüklenemedi:", hata);
        }
    }

    window.onload = duyurulariGetir; 
</script>

<!-- Hava durumu verisini getiren JS -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const btn = document.getElementById("havadurumubtn");
        const kutu = document.getElementById("havaTahminKutusu");
        const liste = document.getElementById("havaListesi");
        let goster = false;

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                // Butona tıklandığında 5 günlük tahmin göster/gizle
                btn.addEventListener("click", function () {
                    if (goster) {
                        kutu.style.display = "none";
                        goster = false;
                        return;
                    }

                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min&timezone=auto`)
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
                            goster = true;
                        });
                });
            });
        }
    });
</script>

</body>
</html>
