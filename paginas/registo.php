<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Felix Bus - Registo</title> <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="icon" href="fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="owl.carousel.min.css">
    <link rel="stylesheet" href="owl.theme.default.min.css">
    <style>
        /* Estilos adicionais para o layout */
        @media (min-width: 768px) {
            .gradient-form {
                height: 100vh !important;
            }
        }

        @media (min-width: 769px) {
            .gradient-custom-2 {
                border-top-right-radius: .3rem;
                border-bottom-right-radius: .3rem;
            }
        }
        /* O gradiente 'gradient-custom-2' é esperado no seu ficheiro style.css ou em outro local */
        /* Se não estiver, por favor, adicione-o ao seu style.css:
        .gradient-custom-2 {
            background: #fccb90;
            background: -webkit-linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);
            background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);
        }
        */
    </style>
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
                                    <form action="criar_registo.php" method="post">
                                        <label class="form-label" for="nome">Nome</label>
                                        <div class="form-outline mb-4">
                                            <input type="text" id="nome" class="form-control" placeholder="O seu nome" name="nome" required />
                                        </div>

                                        <label class="form-label" for="morada">Morada</label>
                                        <div class="form-outline mb-4">
                                            <input type="text" id="morada" class="form-control" placeholder="A sua morada" name="morada" required />
                                        </div>

                                        <label class="form-label" for="telemovel">Telemóvel</label>
                                        <div class="form-outline mb-4">
                                            <input type="tel" id="telemovel" class="form-control" placeholder="O seu número de telemóvel" name="telemovel" required />
                                        </div>

                                        <label class="form-label" for="utilizador_registo">Utilizador</label>
                                        <div class="form-outline mb-4">
                                            <input type="text" id="utilizador_registo" class="form-control" placeholder="O seu nome de utilizador" name="utilizador_registo" required />
                                        </div>

                                        <label class="form-label" for="email_registo">Email</label>
                                        <div class="form-outline mb-4">
                                            <input type="email" id="email_registo" class="form-control" placeholder="O seu email" name="email_registo" required />
                                        </div>

                                        <label class="form-label" for="password_registo">Password</label>
                                        <div class="form-outline mb-4">
                                            <input type="password" id="password_registo" class="form-control" placeholder="A sua password" name="password_registo" required />
                                        </div>

                                        <label class="form-label" for="confirmar_password">Repita a Password</label>
                                        <div class="form-outline mb-4">
                                            <input type="password" id="confirmar_password" class="form-control" placeholder="A sua password" name="confirmar_password" required />
                                        </div>

                                        <div class="text-center pt-1 mb-5 pb-1">
                                            <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit" style="height: 40px;">
                                                Registar
                                            </button>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pb-4">
                                            <p class="mb-0 me-2">Já tens conta?</p>
                                            <a href="login.php" class="btn btn-outline-primary">Faz Login!</a>
                                        </div>
                                    </form>

                                </div>
                            </div>
                            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                                <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                                    <h1 class="mb-4">Queres viajar à <b>baizis</b>?</h1>
                                    <p class="medium mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed
                                        do eiusmod
                                        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                        quis nostrud
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