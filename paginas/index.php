<?php

include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

$loggedIn = false;

// Se o utilizador está com sessão iniciada, então está logado
if (isset($_SESSION['utilizador'])) {
   $loggedIn = true;
}





?>
<!DOCTYPE html>
<html lang="en">

<head>
   <!-- basic -->
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <!-- mobile metas -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="viewport" content="initial-scale=1, maximum-scale=1">
   <!-- site metas -->
   <title>Felix Bus</title>
   <meta name="keywords" content="">
   <meta name="description" content="">
   <meta name="author" content="">
   <!-- bootstrap css -->
   <link rel="stylesheet" href="bootstrap.min.css">
   <!-- style css -->
   <link rel="stylesheet" href="style.css">
   <!-- Responsive-->
   <link rel="stylesheet" href="responsive.css">
   <!-- fevicon -->
   <link rel="icon" href="fevicon.png" type="image/gif" />
   <!-- Scrollbar Custom CSS -->
   <link rel="stylesheet" href="jquery.mCustomScrollbar.min.css">
   <!-- Tweaks for older IEs-->
   <!-- owl stylesheets -->
   <link rel="stylesheet" href="owl.carousel.min.css">
   <link rel="stylesheet" href="owl.theme.default.min.css">
   <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
</head>
<!-- body -->

<body class="main-layout">
   <!-- Sidebar oculta -->
   <div id="sidebarUser" class="sidebar-user">
      <div class="sidebar-header">
         <span>Olá, <?= $_SESSION['utilizador'] ?? 'Utilizador' ?></span>
         <button onclick="toggleSidebar()" class="close-btn">×</button>
      </div>
      <ul class="sidebar-menu">
         <li><a href="perfil.php">Perfil</a></li>
         <li><a href="carteira.php">Carteira</a></li>
         <li><a href="carteira.php">Carteira</a></li>
         <li><a href="logout.php">Logout</a></li>
      </ul>
   </div>
   <!-- loader  -->
   <div class="loader_bg">
      <div class="loader"><img src="loading.gif" alt="#" /></div>
   </div>
   <!-- end loader -->
   <!-- header -->
   <header>
      <!-- header inner -->
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
                           <!--Verifica se o utilizador está logado-->
                           <?php if ($loggedIn): ?>
                              <li">
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
                              <li class="active"> <a href="#">Início</a> </li>
                              <li> <a href="sobre_nos.php">Sobre nós</a> </li>
                              <li><a href="#travel">Viaja</a></li>
                              <li><a href="#contact">Contacta-nos</a></li>
                           </ul>
                        </nav>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end header inner -->
   </header>
   <!-- end header -->
   <section>
      <div class="banner-main">
         <img src="banner.png" alt="#" style="width: 100%; height: 100%; object-fit: cover;" />
         <!-- text-bg moved directly under banner-main to overlay the image -->
         <div class="text-bg">
            <h1 style="padding-top: 250px;">Viaja da Melhor<br><strong class="white">Maneira!</strong></h1>
            <div class="container" style="padding-bottom: 100px;">
               <div class="row justify-content-center">
                  <div class="col-lg-12 col-md-10 col-sm-12" style="padding-bottom: 70px;">
                     <form class="main-form">
                        <h3 id="procurar">Encontra a tua viagem</h3>
                        <div class="row">
                           <div class="col-md-4 col-sm-6">
                              <label>Origem</label>
                              <input class="form-control" placeholder="" type="text" name="">
                           </div>
                           <div class="col-md-4 col-sm-6">
                              <label>Destino</label>
                              <input class="form-control" type="text" name="">
                           </div>
                           <div class="col-md-4 col-sm-6">
                              <label>Data</label>
                              <input class="form-control" placeholder="Any" type="date" name="Any">
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
   <!-- footer -->
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
                     <h3>CONTACT US</h3>
                     <span>123 Second Street Fifth <br>Avenue,<br>
                        Manhattan, New York<br>
                        +987 654 3210</span>
                  </div>
               </div>
               <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                  <div class="Follow">
                     <h3>ADDITIONAL LINKS</h3>
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
                     <h3> Contact</h3>
                     <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                           <input class="Newsletter" placeholder="Name" type="text">
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                           <input class="Newsletter" placeholder="Email" type="text">
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                           <textarea class="textarea" placeholder="comment" type="text">Comment</textarea>
                        </div>
                     </div>
                     <button class="Subscribe">Submit</button>
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
   <!-- end footer -->
   <!-- Javascript files-->
   <script src="jquery.min.js"></script>
   <script src="popper.min.js"></script>
   <script src="bootstrap.bundle.min.js"></script>
   <script src="jquery-3.0.0.min.js"></script>
   <script src="plugin.js"></script>
   <!-- sidebar -->
   <script src="jquery.mCustomScrollbar.concat.min.js"></script>
   <script src="custom.js"></script>
   <!-- javascript -->
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