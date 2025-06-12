<?php
// Inclui o ficheiro de conexão com o banco de dados
include "../basedados/basedados.h";
// Inclui o ficheiro de utilizadores, se contiver funções ou classes relacionadas
// include "utilizadores.php"; // Remova ou mantenha se tiver outras funções úteis
include "carteira_funcoes.php"; // Inclua o novo ficheiro de funções da carteira

// Inicia a sessão PHP
session_start();

// Redireciona se o utilizador não estiver logado
if (!isset($_SESSION['utilizador'])) {
    header("Location: login.php");
    exit();
}

$nome_user = $_SESSION['utilizador'];
$saldoAtual = 0;
$mensagem = "";
$tipo_mensagem = "";

// Obter os IDs das operações da tabela operacao
$operacao_ids = getOperacaoIds($conn);
$id_operacao_adicionar_saldo = $operacao_ids['adicionar'];
$id_operacao_retirar_saldo = $operacao_ids['retirar'];

// Verifica se os IDs das operações foram encontrados
if ($id_operacao_adicionar_saldo === null || $id_operacao_retirar_saldo === null) {
    $mensagem = "Erro de configuração: Não foi possível encontrar os IDs das operações 'Adicionar Saldo' ou 'Retirar Saldo' na base de dados. Por favor, verifique a tabela 'operacao'.";
    $tipo_mensagem = "danger";
}

// Determinar qual aba (depósito/levantamento) está ativa, padrão é depósito
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'depositTab';

// Recuperar mensagem e tipo da URL após redirecionamento (Pós/Redirecionamento/GET)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $mensagem = urldecode($_GET['msg']);
    $tipo_mensagem = $_GET['type'];
    // Ajusta a aba ativa com base no que veio da URL, ou mantém o padrão
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : (isset($_POST['active_tab']) ? $_POST['active_tab'] : 'depositTab');
}

// Lógica para processar depósitos e levantamentos (quando o formulário é submetido)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_carteira_do_utilizador_post = getIdCarteiraUtilizador($conn, $nome_user);

    if ($id_carteira_do_utilizador_post === null) {
        $mensagem = "Erro: Não foi possível encontrar a carteira do utilizador para processar a transação.";
        $tipo_mensagem = "danger";
    } else {
        // Processar Depósito
        if (isset($_POST['depositar'])) {
            $valor_deposito = filter_input(INPUT_POST, 'valor_deposito', FILTER_VALIDATE_FLOAT);
            $active_tab = "depositTab";

            if ($valor_deposito === false || $valor_deposito <= 0) {
                $mensagem = "Valor de depósito inválido. Por favor, insira um valor positivo.";
                $tipo_mensagem = "danger";
            } elseif ($id_operacao_adicionar_saldo === null) {
                $mensagem = "Erro de configuração: ID de operação 'Adicionar Saldo' não definido.";
                $tipo_mensagem = "danger";
            } else {
                $resultado = adicionarSaldo($conn, $id_carteira_do_utilizador_post, $valor_deposito, $id_operacao_adicionar_saldo);
                $mensagem = $resultado['message'];
                $tipo_mensagem = $resultado['success'] ? 'success' : 'danger';
                if ($resultado['success']) {
                    header("Location: carteira.php?msg=" . urlencode($mensagem) . "&type=" . $tipo_mensagem . "&tab=" . $active_tab);
                    exit();
                }
            }
        }
        // Processar Levantamento
        elseif (isset($_POST['levantar'])) {
            $valor_levantamento = filter_input(INPUT_POST, 'valor_levantamento', FILTER_VALIDATE_FLOAT);
            $active_tab = "withdrawTab";

            if ($valor_levantamento === false || $valor_levantamento <= 0) {
                $mensagem = "Valor de levantamento inválido. Por favor, insira um valor positivo.";
                $tipo_mensagem = "danger";
            } elseif ($id_operacao_retirar_saldo === null) {
                $mensagem = "Erro de configuração: ID de operação 'Retirar Saldo' não definido.";
                $tipo_mensagem = "danger";
            } else {
                $resultado = retirarSaldo($conn, $id_carteira_do_utilizador_post, $valor_levantamento, $id_operacao_retirar_saldo);
                $mensagem = $resultado['message'];
                $tipo_mensagem = $resultado['success'] ? 'success' : 'danger';
                if ($resultado['success']) {
                    header("Location: carteira.php?msg=" . urlencode($mensagem) . "&type=" . $tipo_mensagem . "&tab=" . $active_tab);
                    exit();
                }
            }
        }
    }
}

// Lógica para obter o saldo atual do utilizador para exibição
$id_carteira_do_utilizador = getIdCarteiraUtilizador($conn, $nome_user);

if ($id_carteira_do_utilizador !== null) {
    $saldoAtual = getSaldoAtual($conn, $id_carteira_do_utilizador);
} else {
    // Se o ID da carteira não for encontrado, a mensagem já deve ter sido definida.
    // Garantir que a mensagem de erro é exibida se não houver ID de carteira para exibição.
    if (empty($mensagem)) {
        $mensagem = "Erro: ID da carteira do utilizador não encontrado para '$nome_user'.";
        $tipo_mensagem = "danger";
    }
}

// Fechar a conexão com a base de dados
$conn->close();

// Variável para determinar se o utilizador está logado, usada no HTML
$loggedIn = true; // Se chegou aqui, está logado

?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Carteira - Felix Bus</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* CSS para remover as setas (spinners) dos inputs type="number" */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none; /* Para navegadores baseados em WebKit (Chrome, Safari, Opera) */
            margin: 0; /* Corrige um bug de margem no Chrome/Safari */
        }

        input[type="number"] {
            -moz-appearance: textfield; /* Para Firefox */
            appearance: textfield; /* Padrão */
        }
    </style>
</head>

<body class="main-layout">
    <div id="sidebarUser" class="sidebar-user">
        <div class="sidebar-header">
            <span>Olá, <?php echo $_SESSION["utilizador"] ?? "utilizador" ?></span>
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
                                        <li> <a href="sobre_nos..php">Sobre nós</a> </li>
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

    <div class="wallet-container">
        <div class="container">
            <div class="wallet-card">
                <div class="wallet-header">
                    <h2>Minha Carteira</h2>
                    <p>Saldo Atual:</p>
                    <div class="balance-display"><?php echo number_format($saldoAtual, 2, ',', '.'); ?>€</div>
                </div>

                <?php if (!empty($mensagem)): ?>
                    <div class="alerts">
                        <div class="alert <?php echo $tipo_mensagem == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo $mensagem; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="wallet-body">
                    <div class="tab-buttons">
                        <a href="carteira.php?tab=depositTab" class="tab-button <?php echo ($active_tab == 'depositTab') ? 'active' : ''; ?>">Depositar</a>
                        <a href="carteira.php?tab=withdrawTab" class="tab-button <?php echo ($active_tab == 'withdrawTab') ? 'active' : ''; ?>">Levantar</a>
                    </div>

                    <div id="depositTab" class="tab-content <?php echo ($active_tab == 'depositTab') ? 'active' : ''; ?>">
                        <h3 class="section-title">Depositar Dinheiro</h3>
                        <form method="post" action="carteira.php">
                            <div class="form-group">
                                <label for="valor_deposito">Valor do Depósito (€)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="valor_deposito" name="valor_deposito" required>
                            </div>
                            <input type="hidden" name="active_tab" value="depositTab">
                            <button type="submit" name="depositar" class="btn-action">Confirmar Depósito</button>
                        </form>
                    </div>

                    <div id="withdrawTab" class="tab-content <?php echo ($active_tab == 'withdrawTab') ? 'active' : ''; ?>">
                        <h3 class="section-title">Levantar Dinheiro</h3>
                        <form method="post" action="carteira.php">
                            <div class="form-group">
                                <label for="valor_levantamento">Valor do Levantamento (€)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="valor_levantamento" name="valor_levantamento" required>
                            </div>
                            <input type="hidden" name="active_tab" value="withdrawTab">
                            <button type="submit" name="levantar" class="btn-action btn-withdraw">Confirmar Levantamento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
            });

            // Se o custom.js não estiver a funcionar ou o loader_bg não estiver a desaparecer,
            // pode descomentar a linha abaixo PARA TESTE (não para produção)
            // $(".loader_bg").hide();
        });

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebarUser");
            sidebar.classList.toggle("active");
        }
    </script>
</body>

</html>