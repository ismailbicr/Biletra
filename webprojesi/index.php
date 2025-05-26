<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <!-- Font Awesome ikon kütüphanesi -->
    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
        crossorigin="anonymous" 
        referrerpolicy="no-referrer" 
    />
    
    <title>Biletra</title>

    <!-- Dış stil dosyası -->
    <link rel="stylesheet" href="styles/style.css" />

    <!-- Sayfaya özel css tasarımı -->
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .home {
            min-height: 100vh; 
            background: url(images/arkaplan.gif) no-repeat center center;
            background-size: cover; 
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-top: -17.25rem; 
        }
    </style>
</head>
<body>

    <!--! Navbar -->
    <header class="header">
        <a href="#" class="logo">
            <img src="images/logo.png" alt="logo" />
        </a>
        <nav class="navbar">
            <a href="./Anasayfa.php" class="active">Anasayfa</a>
            <a href="./Duyurular.php">Duyurular</a>
            <a href="./Etkinlikler.php">Etkinlikler</a>
            <a href="./Login.php">Giriş/Kayıt Ol</a>
        </nav>
    </header>
    <section class="home">
        <div class="content">
            <h3> Etkinlik Keyfi <br/>Biletra’da Başlar!</h3>
            <p>
            Etkinlik tutkunları için bilet almanın en kolay ve güvenli yolu burada!<br/>
            Konserlerden tiyatrolara, festivallerden atölyelere kadar yüzlerce etkinlik tek tıkla cebinizde.<br/>
            Hemen keşfedin, yerinizi ayırtın!
            </p>
        </div>
    </section>

</body>
</html>
