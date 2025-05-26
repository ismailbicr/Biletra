<?php
session_start(); // Oturumu başlat

// Kullanıcı giriş yapmamışsa veya rolü "user" değilse login sayfasına yönlendir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Biletra | Sepet</title>

    <!-- FontAwesome ikon kütüphanesi -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" />

    <!-- Harici stil dosyası -->
    <link rel="stylesheet" href="styles/style.css" />

    <!-- Sayfaya özel CSS -->
    <style>
        /* Tüm sayfada kullanılan temel yazı tipi ve arka plan ayarları */
        body {
            font-family: Arial, sans-serif;
            background: url(images/arkaplan.png) no-repeat center center fixed;
            background-size: cover;
            margin: 0;
        }

        /* İçeriği ortalayan ve kutu haline getiren ana konteyner */
        .container {
            max-width: 750px; 
            margin: 80px auto; 
            background-color: rgba(255, 255, 255, 0.95); 
            padding: 30px;
            border-radius: 12px; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.2); 
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
        }

        .sepet-item {
            border-bottom: 1px solid #ccc;
            padding: 12px 0;
        }

        .sepet-item h3 {
            margin: 0;
        }

        .sepet-item p {
            margin: 5px 0;
        }

        .toplam {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 20px;
            text-align: right;
        }

        .form-controls {
            margin-top: 20px;
        }

        .form-controls label {
            display: block;
            margin-bottom: 10px;
        }

        .bos {
            text-align: center;
            color: #888;
            font-size: 1.2rem;
        }

        select, input[type="number"] {
            margin-left: 10px;
            padding: 4px;
        }

        .butonlar {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .butonlar button {
            padding: 12px 28px;
            font-size: 1.1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .satinal {
            background-color: dodgerblue;
            color: white;
        }

        .iptal {
            background-color: crimson;
            color: white;
        }
        
    </style>

</head>
<body>

<!-- Navbar -->
<header class="header">
    <a href="Anasayfa.php" class="logo">
        <img src="images/logo.png" alt="logo" />
    </a>
    <nav class="navbar">
        <a href="Anasayfa.php" class="active">Anasayfa</a>
        <a href="Duyurular.php">Duyurular</a>
        <a href="Etkinlikler.php">Etkinlikler</a>
    </nav>
    <div class="buttons">
        <button>
            <a href="Anasayfa.php?cikis=1" title="Çıkış Yap">
                <i class="fa-solid fa-right-from-bracket" style="color: black;"></i>
            </a>
        </button>
    </div>
</header>

<!-- Sepet içeriği -->
<div class="container">
    <h1>Sepetiniz</h1>
    <div id="sepetAlani"></div>

    <div class="form-controls">
        <label>
            Ödeme Yöntemi:
            <select id="odemeYontemi">
                <option value="kredi kartı">Kredi Kartı</option>
                <option value="havale">Havale/EFT</option>
                <option value="kapida">Kapıda Ödeme</option>
            </select>
        </label>
    </div>

    <div class="toplam" id="toplamFiyat"></div>

    <div class="butonlar">
        <button class="satinal" onclick="satınAl()">Satın Al</button>
        <button class="iptal" onclick="iptalEt()">İptal Et</button>
    </div>
</div>

<script>
// Sepeti localStorage'dan al
let sepet = JSON.parse(localStorage.getItem("sepet")) || [];

// Dinamik fiyat heabı
function fiyatHesapla() {
    let toplam = 0;
    sepet.forEach(item => {
        const id = item.id;
        const adetInput = document.querySelector(`.bilet-sayisi[data-id="${id}"]`);
        const turSelect = document.querySelector(`.bilet-turu[data-id="${id}"]`);
        const adet = Number(adetInput.value);
        const tur = turSelect.value;

        let fiyat = item.ucret;
        if (tur === "ogrenci") {
            fiyat *= 0.7; // %30 indirim
        }

        toplam += fiyat * adet;
    });

    document.getElementById("toplamFiyat").textContent = "Toplam: " + toplam.toFixed(2) + " ₺";
}

// Sayfa yüklendiğinde sepeti listele
document.addEventListener("DOMContentLoaded", function () {
    const alan = document.getElementById("sepetAlani");

    if (sepet.length === 0) {
        alan.innerHTML = "<p class='bos'>Sepetinizde ürün yok.</p>";
        document.querySelector(".butonlar").style.display = "none";
        return;
    }

    // Sepet içeriğini HTML'e yaz
    sepet.forEach(item => {
        alan.innerHTML += `
            <div class="sepet-item">
                <h3>${item.baslik}</h3>
                <p><strong>Tarih:</strong> ${item.tarih}</p>
                <p><strong>Ücret:</strong> ${item.ucret} ₺</p>
                <label>Bilet Sayısı:
                    <input type="number" min="1" max="10" value="1" class="bilet-sayisi" data-id="${item.id}" onchange="fiyatHesapla()">
                </label>
                <label>Bilet Türü:
                    <select class="bilet-turu" data-id="${item.id}" onchange="fiyatHesapla()">
                        <option value="tam">Tam</option>
                        <option value="ogrenci">Öğrenci (%30 indirim)</option>
                    </select>
                </label>
            </div>
        `;
    });

    fiyatHesapla(); 
});

// Sepet verilerini sunucuya göndererek satın alma işlemini tamamla
function satınAl() {
    const sayilar = document.querySelectorAll(".bilet-sayisi");
    const odeme = document.getElementById("odemeYontemi").value;
    const gonderilecek = [];

    sayilar.forEach(input => {
        const id = input.dataset.id;
        const adet = Number(input.value);
        const tur = document.querySelector(`.bilet-turu[data-id="${id}"]`).value;
        const etkinlik = sepet.find(item => item.id == id);

        let fiyat = etkinlik.ucret;
        if (tur === "ogrenci") {
            fiyat *= 0.7;
        }

        if (etkinlik) {
            gonderilecek.push({
                id: etkinlik.id,
                adet: adet,
                tur: tur,
                fiyat: fiyat,
                odeme: odeme
            });
        }
    });

    // Sunucuya JSON formatında veri gönder
    fetch("satinal.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(gonderilecek)
    })
    .then(res => res.text())
    .then(veri => {
        alert(veri);
        localStorage.removeItem("sepet"); 
        location.reload(); 
    });
}

function iptalEt() {
    if (confirm("Sepetiniz temizlenecek. Emin misiniz?")) {
        localStorage.removeItem("sepet");
        location.reload();
    }
}
</script>

</body>
</html>
