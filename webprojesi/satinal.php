<?php
session_start(); // Oturumu başlat

include("baglanti.php"); 

header("Content-Type: application/json; charset=utf-8"); // JSON çıktısı ve Türkçe karakter desteği için

// Eğer kullanıcı giriş yapmamışsa hata mesajı döndürülür
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["durum" => "hata", "mesaj" => "Giriş yapmanız gerekiyor."], JSON_UNESCAPED_UNICODE);
    exit;
}

// JSON verisi alınır ve diziye dönüştürülür
$veri = json_decode(file_get_contents("php://input"), true);

// Veri geçersiz veya dizi değilse hata döndürülür
if (!$veri || !is_array($veri)) {
    echo json_encode(["durum" => "hata", "mesaj" => "Geçersiz veri formatı."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Her sepet öğesi için işlem yapılır
foreach ($veri as $item) {
    $etkinlik_id = intval($item["id"]);               
    $bilet_sayisi = intval($item["adet"]);            
    $tur = $baglanti->real_escape_string($item["tur"]);       
    $odeme = $baglanti->real_escape_string($item["odeme"]);  
    $kullanici_id = intval($_SESSION['user_id']);     

    // İlgili etkinliğin kontenjanı sorgulanır
    $stmt = $baglanti->prepare("SELECT kontenjan FROM etkinlikler WHERE id = ?");
    $stmt->bind_param("i", $etkinlik_id);
    $stmt->execute();
    $sonuc = $stmt->get_result();

    // Etkinlik bulunduysa devam edilir
    if ($sonuc->num_rows > 0) {
        $satir = $sonuc->fetch_assoc();
        $mevcut = intval($satir["kontenjan"]); 

        // Yeterli kontenjan varsa kontenjan azaltılır
        if ($mevcut >= $bilet_sayisi) {
            $yeni = $mevcut - $bilet_sayisi;
            $guncelle = $baglanti->prepare("UPDATE etkinlikler SET kontenjan = ? WHERE id = ?");
            $guncelle->bind_param("ii", $yeni, $etkinlik_id);
            $guncelle->execute();
        } else {
            // Yeterli kontenjan yoksa hata mesajı verilir
            echo json_encode(["durum" => "hata", "mesaj" => "Etkinlik ID $etkinlik_id için yeterli kontenjan yok."], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        // Etkinlik veritabanında bulunamazsa
        echo json_encode(["durum" => "hata", "mesaj" => "Etkinlik bulunamadı."], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Eğer tüm işlemler başarılıysa başarı mesajı döndürülür
echo json_encode("Satın alma işlemi başarıyla tamamlandı.", JSON_UNESCAPED_UNICODE);
?>
