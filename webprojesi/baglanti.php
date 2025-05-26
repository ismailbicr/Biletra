<?php
// Veritabanı bağlantısı için sunucu bilgileri
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "biletra";       

// MySQL bağlantısını oluşturur
$baglanti = new mysqli($servername, $username, $password, $dbname); 

if ($baglanti->connect_error) {
    die("Bağlantı hatası: " . $baglanti->connect_error);
}
?>
