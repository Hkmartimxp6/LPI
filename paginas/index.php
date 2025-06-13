<?php

include "../basedados/basedados.h";
include "utilizadores.php";

// Inicializar a sessão PHP
session_start();

// Variável para verificar se o utilizador está autenticado
$loggedIn = false;
// Valor padrão para o nome do utilizador
$nomeUtilizador = "utilizador";

// Verificar se o utilizador está autenticado
if (isset($_SESSION["utilizador"]) && is_array($_SESSION["utilizador"])) {
    // O utilizador está autenticado
    $loggedIn = true;
    // Atribuir o nome do utilizador da sessão
    $nomeUtilizador = $_SESSION["utilizador"]["nome"] ?? $_SESSION["utilizador"]["nome_utilizador"];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Felix Bus</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="icon" href="fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="owl.carousel.min.css">
    <link rel="stylesheet" href="owl.theme.default.min.css">
</head>

<body class="main-layout">
    <div id="sidebarUser" class="sidebar-user">
        <div class="sidebar-header">
            <span>Olá, <?php echo htmlspecialchars($nomeUtilizador); ?></span>
            <button onclick="toggleSidebar()" class="close-btn">×</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="pagina_utilizador.php">Perfil</a></li>
            <li><a href="carteira.php">Carteira</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="loader_bg">
        <div class="loader"><img src="loading.gif" alt="#" /></div>
    </div>
    <header>
        <div class="header">
            <div class="header_white_section">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="header_information">
                                <ul class="lista" style="display:flex; align-items: center; height: 100%;">
                                    <li><img src="1.png" alt="#" /> Alameda Cardeal Cerejeira</li>
                                    <li><img src="2.png" alt="#" /> +351 963 961 984</li>
                                    <li><img src="3.png" alt="#" /> felixbus@gmail.com</li>
                                    <!-- Verifica se o utilizador está autenticado para apresentar o botão de login-->
                                    <?php if ($loggedIn): ?>
                                        <li>
                                            <a href="javascript:void(0);" onclick="toggleSidebar()" title="Perfil">
                                                <img src="icon_utilizador.png" alt="Ícone do Utilizador"
                                                    style="width: 48px; height: 48px;
                                                            border-radius: 50%;
                                                            background-color: #fff;">
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li>
                                            <a class="botao_de_login" href="login.php">Faz Login!</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
                        <div class="full">
                            <div class="center-desk">
                                <div class="logo">
                                    <a href="index.php"><img src="logo.png" style="width:300px;" alt="#"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9">
                        <div class="menu-area">
                            <div class="limit-box">
                                <nav class="main-menu">
                                    <ul class="menu-area-main">
                                        <li> <a href="rotas.php">Rotas</a> </li>
                                        <li><a href="viagens.php">Viagens</a></li>
                                        <li> <a href="sobre_nos.php">Sobre nós</a> </li>
                                        <li><a href="#contact">Contacta-nos</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section>
        <div class="banner-main">
            <img src="banner.png" alt="#" style="width: 100%; height: 100%; object-fit: cover;" />
            <div class="text-bg">
                <h1 style="padding-top: 250px;">Viaja da Melhor<br><strong class="white">Maneira!</strong></h1>
                <div class="container" style="padding-bottom: 100px;">
                    <div class="row justify-content-center">
                        <div class="col-lg-12 col-md-10 col-sm-12" style="padding-bottom: 70px;">
                            <form class="main-form" action="viagens.php" method="GET">
                                <h3 id="procurar">Encontra a tua viagem</h3>
                                <div class="row">
                                    <div class="col-md-4 col-sm-6">
                                        <label>Origem</label>
                                        <input class="form-control" placeholder="Ex: Lisboa" type="text" name="origem">
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label>Destino</label>
                                        <input class="form-control" placeholder="Ex: Porto" type="text" name="destino">
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label>Data</label>
                                        <input class="form-control" type="date" name="data">
                                    </div>
                                    <div class="col-12 text-center mt-3">
                                        <button type="submit" class="btn btn-primary" style="background-color: #EE580F; border:#EE580F">Procurar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer>
        <div id="contact" class="footer">
            <div class="container">
                <div class="row pdn-top-30">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <ul class="location_icon">
                            <li> <a href="#"><img src="facebook.png"></a></li>
                            <li> <a href="#"><img src="Twitter.png"></a></li>
                            <li> <a href="#"><img src="linkedin.png"></a></li>
                            <li> <a href="#"><img src="instagram.png"></a></li>
                        </ul>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                        <div class="Follow">
                            <h3>Contacte-nos</h3>
                            <span>123 Segunda Rua Quinta <br>Avenida,<br>
                                Mana Há Tane, Yorke Nova<br>
                                +351 963 961 984</span>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                        <div class="Follow">
                            <h3>LINKS ADICIONAIS</h3>
                            <ul class="link">
                                <li> <a href="#">Sobre nós</a></li>
                                <li> <a href="#">Termos e Condições</a></li>
                                <li> <a href="#">Política de Privacidade</a></li>
                                <li> <a href="#">Notícias</a></li>
                                <li> <a href="#">Contacte-nos</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="Follow">
                            <h3> Contacte</h3>
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <input class="Newsletter" placeholder="Nome" type="text">
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <input class="Newsletter" placeholder="Email" type="text">
                                </div>
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <textarea class="textarea" placeholder="Comentário" type="text"></textarea>
                                </div>
                            </div>
                            <button class="Subscribe">Submeter</button>
                        </div>
                    </div>
                </div>
                <div class="copyright">
                    <div class="container">
                        <p>Copyright 2019 All Right Reserved By <a href="https://html.design/">Free html Templates</a></p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="jquery.min.js"></script>
    <script src="popper.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <script src="jquery-3.0.0.min.js"></script>
    <script src="plugin.js"></script>
    <script src="jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="custom.js"></script>
    <script src="owl.carousel.js"></script>
    <script>
        $(document).ready(function() {
            var owl = $('.owl-carousel');
            owl.owlCarousel({
                margin: 10,
                nav: true,
                loop: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 2
                    },
                    1000: {
                        items: 3
                    }
                }
            })
        })

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebarUser");
            sidebar.classList.toggle("active");
        }
    </script>
</body>

</html>