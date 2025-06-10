<?php

include "../basedados/basedados.h"; // Certifique-se de que o caminho para basedados.h está correto
include "utilizadores.php"; // Certifique-se de que o caminho para utilizadores.php está correto

session_start();

$id_viagem = null;

// Tenta obter o id_viagem da URL (GET) ou da sessão
if (isset($_GET['id_viagem'])) {
    $id_viagem = intval($_GET['id_viagem']);
    // Guarda o ID da viagem na sessão APENAS se vier via GET e for válido
    if ($id_viagem > 0) {
        $_SESSION['id_viagem'] = $id_viagem;
    } else {
        // Se o ID da viagem via GET for inválido, limpa a sessão
        unset($_SESSION['id_viagem']);
        $id_viagem = null; // Garante que $id_viagem é null
    }
} elseif (isset($_SESSION['id_viagem'])) {
    $id_viagem = $_SESSION['id_viagem']; // Recupera o ID da viagem da sessão
}

// Se, após todas as tentativas, o id_viagem ainda for inválido, exibe erro e para
if (!$id_viagem || $id_viagem <= 0) {
    echo "<p style='color: red; background-color: #ffe6e6; padding: 10px; border: 1px solid red; border-radius: 5px;'>Erro de validação: ID da viagem inválido ou em falta.</p>";
    // Opcional: Redirecionar para a página de viagens se o ID da viagem for inválido
    // header("Location: viagens.php");
    // exit();
    // Parar a execução aqui para evitar mais erros
    exit();
}

// Verifica se o utilizador está logado
if (!isset($_SESSION["utilizador"])) {
    // Redirecionar para login se o utilizador não estiver logado
    header("Location: login.php");
    exit();
}

$id_utilizador = $_SESSION["utilizador"]; // Supondo que guardas o ID do utilizador na sessão

// Inicializa num_passageiros antes do POST para o formulário
$num_passageiros = 1; // Valor padrão para o formulário no formulário HTML

// --- INÍCIO DO BLOCO DE PROCESSAMENTO POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comprar_bilhete'])) {

    // Validar num_passageiros do POST
    if (isset($_POST['num_passageiros']) && filter_var($_POST['num_passageiros'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
        $num_passageiros = intval($_POST['num_passageiros']);
    } else {
        echo "<p style='color: red;'>Número de passageiros inválido. Por favor, insira um número inteiro positivo.</p>";
        // Permite que o formulário seja exibido com a mensagem de erro para o utilizador corrigir
        goto end_of_post_processing; // Salta para o fim do processamento POST
    }

    // 1. Obter o preço da viagem da base de dados e calcular o preco_total
    $sql_preco_viagem = "SELECT preco FROM viagem WHERE id_viagem = ?";
    $stmt_preco = $conn->prepare($sql_preco_viagem);
    if ($stmt_preco === false) {
        die("Erro na preparação da query do preço: " . $conn->error);
    }
    $stmt_preco->bind_param("i", $id_viagem);
    $stmt_preco->execute();
    $result_preco = $stmt_preco->get_result();

    if ($result_preco->num_rows > 0) {
        $linha_preco = $result_preco->fetch_assoc();
        $preco_unitario = $linha_preco['preco'];
        $preco_total = $preco_unitario * $num_passageiros; // Calcula o preço total com base nos passageiros
    } else {
        echo "<p style='color: red;'>Erro: Detalhes da viagem não encontrados.</p>";
        goto end_of_post_processing;
    }
    $stmt_preco->close();


    // 2. Obter o saldo atual da carteira do utilizador
    $sql_saldo = "SELECT saldo FROM carteira WHERE id_utilizador = ?";
    $stmt_saldo = $conn->prepare($sql_saldo);
    if ($stmt_saldo === false) {
        die("Erro na preparação da query do saldo: " . $conn->error);
    }
    $stmt_saldo->bind_param("i", $id_utilizador);
    $stmt_saldo->execute();
    $result_saldo = $stmt_saldo->get_result();

    if ($result_saldo->num_rows > 0) {
        $linha_saldo = $result_saldo->fetch_assoc();
        $saldo_atual_carteira = $linha_saldo['saldo'];
    } else {
        echo "<p style='color: red;'>Erro: Carteira do utilizador não encontrada. Por favor, contacte o suporte ou adicione fundos.</p>";
        goto end_of_post_processing;
    }
    $stmt_saldo->close();

    // 3. Verificar se há saldo suficiente
    if ($saldo_atual_carteira < $preco_total) {
        echo "<p style='color: red;'>Saldo insuficiente na carteira para efetuar a compra.</p>";
        echo '<p><a href="carteira.php">Adicionar fundos à carteira</a></p>';
        goto end_of_post_processing;
    }

    // Se houver saldo suficiente, iniciar uma transação para garantir atomicidade
    $conn->begin_transaction();

    try {
        // 4. Atualizar o saldo na tabela 'carteira'
        $novo_saldo = $saldo_atual_carteira - $preco_total;
        $sql_update_carteira = "UPDATE carteira SET saldo = ? WHERE id_utilizador = ?";
        $stmt_update_carteira = $conn->prepare($sql_update_carteira);
        if ($stmt_update_carteira === false) {
            throw new Exception("Erro na preparação da query de atualização da carteira: " . $conn->error);
        }
        $stmt_update_carteira->bind_param("di", $novo_saldo, $id_utilizador); // 'd' para double (saldo)
        if (!$stmt_update_carteira->execute()) {
            throw new Exception("Erro ao atualizar o saldo da carteira: " . $stmt_update_carteira->error);
        }
        $stmt_update_carteira->close();


        // 5. Registar a transação na tabela 'carteira_log'
        // Assumindo que id_operacao = 3 para 'compra de bilhete' (conforme imagem da tabela operacao)
        $id_operacao_compra_bilhete = 3;

        // Obter o ID da carteira do utilizador (se 'id_carteira' for a PK da tabela 'carteira' e PK em 'carteira_log')
        // Se a sua tabela 'carteira' usa 'id_utilizador' como PK, então pode usar $id_utilizador diretamente no bind_param
        $sql_get_id_carteira = "SELECT id_carteira FROM carteira WHERE id_utilizador = ?";
        $stmt_get_id_carteira = $conn->prepare($sql_get_id_carteira);
        if ($stmt_get_id_carteira === false) {
            throw new Exception("Erro na preparação da query para obter id_carteira: " . $conn->error);
        }
        $stmt_get_id_carteira->bind_param("i", $id_utilizador);
        $stmt_get_id_carteira->execute();
        $result_id_carteira = $stmt_get_id_carteira->get_result();
        if ($result_id_carteira->num_rows > 0) {
            $row_id_carteira = $result_id_carteira->fetch_assoc();
            $id_carteira = $row_id_carteira['id_carteira'];
        } else {
             throw new Exception("ID da carteira não encontrado para o utilizador.");
        }
        $stmt_get_id_carteira->close();

        $sql_insert_carteira_log = "INSERT INTO carteira_log (id_carteira, id_operacao, data, montante) VALUES (?, ?, NOW(), ?)";
        $stmt_insert_carteira_log = $conn->prepare($sql_insert_carteira_log);
        if ($stmt_insert_carteira_log === false) {
            throw new Exception("Erro na preparação da query de log da carteira: " . $conn->error);
        }
        $stmt_insert_carteira_log->bind_param("iid", $id_carteira, $id_operacao_compra_bilhete, $preco_total); // 'd' para double
        if (!$stmt_insert_carteira_log->execute()) {
            throw new Exception("Erro ao registar a transação na carteira_log: " . $stmt_insert_carteira_log->error);
        }
        $stmt_insert_carteira_log->close();


        // 6. Registar o bilhete comprado na tabela 'bilhete'
        // Baseado na sua tabela 'bilhete' que tem: id_bilhete, id_viagem, id_utilizador
        // E adicionando a data_compra, que você concordou em adicionar
        $sql_insert_bilhete = "INSERT INTO bilhete (id_viagem, id_utilizador, data_compra) VALUES (?, ?, NOW())";
        $stmt_insert_bilhete = $conn->prepare($sql_insert_bilhete);
        if ($stmt_insert_bilhete === false) {
            throw new Exception("Erro na preparação da query de inserção de bilhete: " . $conn->error);
        }
        $stmt_insert_bilhete->bind_param("ii", $id_viagem, $id_utilizador);
        if (!$stmt_insert_bilhete->execute()) {
            throw new Exception("Erro ao inserir o bilhete: " . $stmt_insert_bilhete->error);
        }
        $id_bilhete_gerado = $conn->insert_id; // Obtém o ID do bilhete recém-inserido
        $stmt_insert_bilhete->close();


        // Se tudo correu bem, comitar a transação
        $conn->commit();

        echo "<p style='color: green; background-color: #e6ffe6; padding: 10px; border: 1px solid green; border-radius: 5px;'>Bilhete(s) comprado(s) com sucesso! ID do Bilhete: " . $id_bilhete_gerado . "</p>";
        echo "<p>Novo saldo na carteira: " . number_format($novo_saldo, 2, ',', '.') . "€</p>";
        // Opcional: Limpar o ID da viagem da sessão após a compra bem-sucedida para evitar compras acidentais
        // unset($_SESSION['id_viagem']);
        // Redirecionar para uma página de sucesso ou para a página de bilhetes do utilizador
        // header("Location: bilhetes_utilizador.php");
        // exit();

    } catch (Exception $e) {
        // Se algo correu mal, reverter a transação
        $conn->rollback();
        echo "<p style='color: red; background-color: #ffe6e6; padding: 10px; border: 1px solid red; border-radius: 5px;'>Ocorreu um erro inesperado na transação: " . $e->getMessage() . "</p>";
        // Log do erro para depuração
        error_log("Erro na compra de bilhete: " . $e->getMessage());
    }
}
end_of_post_processing: // Label para o goto
// --- FIM DO BLOCO DE PROCESSAMENTO POST ---
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Felix Bus - Confirmar Compra</title>
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
                                    <?php if (isset($_SESSION["utilizador"])): ?>
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
                                        <li> <a href="index.php">Início</a> </li>
                                        <li> <a href="sobre_nos.php">Sobre nós</a> </li>
                                        <li><a href="rotas.php">Rotas</a></li>
                                        <li class="active"><a href="viagens.php">Viagens</a></li>
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

    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="confirm-card">
                        <h1>Confirmar Compra de Bilhete</h1>

                        <?php
                        // Carregar os detalhes da viagem para exibição, mesmo que não seja POST
                        $detalhes_viagem = null;
                        if ($id_viagem) { // Verifica se $id_viagem é válido antes de buscar detalhes
                            $sql_detalhes = "
                                SELECT
                                    v.data,
                                    v.hora,
                                    v.hora_chegada,
                                    v.preco,
                                    origem_loc.localidade AS origem,
                                    destino_loc.localidade AS destino
                                FROM viagem v
                                INNER JOIN rota r ON v.id_rota = r.id_rota
                                INNER JOIN localidade origem_loc ON r.id_origem = origem_loc.id_localidade
                                INNER JOIN localidade destino_loc ON r.id_destino = destino_loc.id_localidade
                                WHERE v.id_viagem = ?";
                            $stmt_detalhes = $conn->prepare($sql_detalhes);
                            if ($stmt_detalhes === false) {
                                die("Erro na preparação da query de detalhes da viagem: " . $conn->error);
                            }
                            $stmt_detalhes->bind_param("i", $id_viagem);
                            $stmt_detalhes->execute();
                            $result_detalhes = $stmt_detalhes->get_result();

                            if ($result_detalhes->num_rows > 0) {
                                $detalhes_viagem = $result_detalhes->fetch_assoc();
                            }
                            $stmt_detalhes->close();
                        }

                        if ($detalhes_viagem) {
                            ?>
                            <div class="confirm-info">
                                <p><strong>Viagem:</strong> <?php echo htmlspecialchars($detalhes_viagem['origem']); ?> para
                                    <?php echo htmlspecialchars($detalhes_viagem['destino']); ?></p>
                                <p><strong>Data:</strong> <?php echo htmlspecialchars($detalhes_viagem['data']); ?></p>
                                <p><strong>Hora:</strong> <?php echo htmlspecialchars($detalhes_viagem['hora']); ?> -
                                    <?php echo htmlspecialchars($detalhes_viagem['hora_chegada']); ?></p>
                                <p><strong>Preço por passageiro:</strong>
                                    <?php echo number_format($detalhes_viagem['preco'], 2, ',', '.'); ?>€</p>
                            </div>

                            <form action="comprar_bilhete.php" method="POST">
                                <div class="form-group">
                                    <label for="num_passageiros">Número de Passageiros:</label>
                                    <input type="number" class="form-control" id="num_passageiros" name="num_passageiros"
                                        value="<?php echo htmlspecialchars($num_passageiros); ?>" min="1" required>
                                </div>
                                <button type="submit" name="comprar_bilhete" class="btn-comprar">Comprar
                                    Bilhete(s)</button>
                            </form>
                            <?php
                        } else {
                            echo "<p>Detalhes da viagem não encontrados ou ID da viagem inválido.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="contact" class="contact">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="titlepage">
                        <h2>Contacta-nos</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                    <form class="main_form">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <input class="form-control" placeholder="Nome" type="text" name="Nome">
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <input class="form-control" placeholder="Email" type="text" name="Email">
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <input class="form-control" placeholder="Número de Telefone" type="text"
                                    name="Phone Number">
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <textarea class="textarea" placeholder="Mensagem" type="text"
                                    name="Mensagem"></textarea>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <button class="send">Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                    <div class="img-frame">
                        <figure><img src="bus.jpg" alt="Bus image" /></figure>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <div id="contact" class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-5">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="footerl">
                                    <a href="index.php"><img src="logo2.png" alt="#" /></a>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="fid_box">
                                    <p>Este é um projeto escolar que tem como objetivo a criação de um website para
                                        uma empresa de autocarros fictícia.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <h3>Menu</h3>
                            <ul>
                                <li> <a href="index.php">Início</a></li>
                                <li> <a href="sobre_nos.php">Sobre nós</a></li>
                                <li> <a href="rotas.php">Rotas</a></li>
                                <li> <a href="viagens.php">Viagens</a></li>
                                <li> <a href="#contact">Contacta-nos</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info">
                            <h3>Contacta-nos</h3>
                            <ul>
                                <li><img src="1.png" alt="#" /> Alameda Cardeal Cerejeira</li>
                                <li><img src="2.png" alt="#" /> +351 963 961 984</li>
                                <li><img src="3.png" alt="#" /> felixbus@gmail.com</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <p>© 2024 Todos os Direitos Reservados. <a href="https://html.design/"> Free Html
                                    Templates</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.0.0.min.js"></script>
    <script src="js/plugin.js"></script>
    <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/owl.carousel.js"></script>
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