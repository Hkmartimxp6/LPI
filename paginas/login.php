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

<body>
    <section class="h-100 gradient-form" style="background-color: #eee;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-xxl-10">
                    <div class="card rounded-3 text-black">
                        <div class="row g-0">
                            <div class="col-lg-6">
                                <div class="card-body p-md-5 mx-md-4">

                                    <div class="text-center">
                                        <img src="logo.png" class="img-fluid" style="width: 185px;" alt="logo">
                                    </div>
                                    <form action="verifica_login.php" method="POST">
                                        <label class="form-label" for="form2Example11">Utilizador</label>
                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <input type="text" id="form2Example11" class="form-control"
                                                placeholder="O seu nome de utilizador" name="utilizador" />
                                        </div>
                                        <label class="form-label" for="form2Example22">Password</label>
                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <input type="password" id="form2Example22" class="form-control"
                                                placeholder="A sua password" name="password" />
                                        </div>
                                        <div class="text-center pt-1 mb-5 pb-1">
                                            <button data-mdb-button-init data-mdb-ripple-init
                                                class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3"
                                                type="submit" style="height: 40px;">
                                                Login
                                            </button>

                                        </div>
                                        <div class="d-flex align-items-center justify-content-between pb-4">
                                            <p class="mb-0 me-2">Não tens conta?</p>
                                            <a href="registo.php" type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-primary">Regista-te!</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                                <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                                    <h1 class="mb-4">Queres viajar à <b>baizis</b>?</h1>
                                    <p class="medium mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
                                        exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>