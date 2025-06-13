<?php

include "../basedados/basedados.h"; // Certifique-se de que o caminho para basedados.h está correto
include "utilizadores.php"; // Certifique-se de que o caminho para utilizadores.php está correto

// Inicializar a sessão PHP
session_start();

// Variável para verificar se o utilizador está autenticado
$autenticado = false;

// Se o utilizador está com sessão iniciada, então está autenticado
if (isset($_SESSION["utilizador"])) {
    $autenticado = true;
}

// Inicializar variáveis de filtro e ordenação a partir dos parâmetros GET
$filtro_origem = $_GET['origem'] ?? '';
$filtro_destino = $_GET['destino'] ?? '';

// Novas variáveis para ordenação
$ordenar_por = $_GET['ordenar_por'] ?? 'origem'; // Padrão: ordenar por origem
$direcao_ordenacao = $_GET['direcao'] ?? 'ASC'; // Padrão: ascendente

// Validar a direção da ordenação para evitar SQL Injection
if (!in_array(strtoupper($direcao_ordenacao), ['ASC', 'DESC'])) {
    $direcao_ordenacao = 'ASC'; // Valor por defeito
}

// Construir a query SQL para buscar rotas
$sql = "
    SELECT DISTINCT
        r.id_rota,
        origem_loc.localidade AS origem,
        destino_loc.localidade AS destino
    FROM rota r
    INNER JOIN localidade origem_loc ON r.id_origem = origem_loc.id_localidade
    INNER JOIN localidade destino_loc ON r.id_destino = destino_loc.id_localidade
    WHERE 1=1
";

// Adicionar condições de filtro
if (!empty($filtro_origem)) {
    $sql .= " AND origem_loc.localidade LIKE ?"; // adiciona isto à query 
}
// Verifica se o filtro de destino não está vazio
if (!empty($filtro_destino)) {
    $sql .= " AND destino_loc.localidade LIKE ?"; // adiciona isto à query
}

// Adicionar ordenação dinâmica
$atributo_ordenacao = "";
// Verifica o parâmetro de ordenação e define o atributo de ordenação correspondente
switch ($ordenar_por) {
    case 'origem':
        $atributo_ordenacao = "origem_loc.localidade";
        break;
    case 'destino':
        $atributo_ordenacao = "destino_loc.localidade";
        break;
    default: // Caso padrão se nenhum for especificado ou um valor inválido
        $atributo_ordenacao = "origem_loc.localidade"; // Padrão para Origem
        break;
}

// Adiciona a cláusula ORDER BY à query
$sql .= " ORDER BY " . $atributo_ordenacao
    . " " . $direcao_ordenacao
    . ", destino_loc.localidade ASC";

// Preparar a query
$stmt = $conn->prepare($sql);

// Verifica se a preparação da query foi bem-sucedida
if ($stmt === false) {
    die("Erro na preparação da query: " . $conn->error);
}

// Ligar os parâmetros com o bind_param
$parametros = [];
// Inicializa a string de tipos para bind_param
$tipos = "";

// Verifica se o filtro de origem não está vazio
if (!empty($filtro_origem)) {
    // Adiciona o filtro de origem como parâmetro
    $parametros[] = "%" . $filtro_origem . "%";
    // Adiciona o tipo de dado para o bind_param
    $tipos .= "s";
}
// Verifica se o filtro de destino não está vazio
if (!empty($filtro_destino)) {
    // Adiciona o filtro de destino como parâmetro
    $parametros[] = "%" . $filtro_destino . "%";
    // Adiciona o tipo de dado para o bind_param
    $tipos .= "s";
}

// Verifica se há parâmetros para bind_param
if (!empty($parametros)) {
    // Faz o bind dos parâmetros à query
    $stmt->bind_param($tipos, ...$parametros);
}

// Executar a query
$stmt->execute();

// Obter o resultado
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Felix Bus - Rotas Disponíveis</title>
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
    <style>
        .rota-card {
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            outline: 1px solid black;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rota-info {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 20px;
        }

        .rota-info span {
            margin: 0;
            font-size: 18px;
        }

        .procurar-btn {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .procurar-btn:hover {
            background-color: #0056b3;
        }

        .filter-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-form .form-group {
            margin-bottom: 15px;
        }

        .filter-form .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }

        .filter-form .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        /* Novos estilos para a ordenação */
        .sort-options {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            /* Permite que os botões quebrem a linha em telas pequenas */
        }

        .sort-options a {
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            color: #333;
            background-color: #f8f9fa;
        }

        .sort-options a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .sort-options a:hover:not(.active) {
            background-color: #e2e6ea;
        }

        .sort-options .sort-label {
            font-weight: bold;
            margin-right: 5px;
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
                                    <?php if ($autenticado): ?>
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
                                        <li><a href="viagens.php">Viagens</a></li>
                                        <li class="active"><a href="rotas.php">Rotas</a></li>
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

    <div class="container mt-5">
        <h1 class="mb-4">Filtrar Rotas</h1>
        <div class="filter-form">
            <form action="rotas.php" method="GET">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="origem">Origem:</label>
                            <input type="text" class="form-control" id="origem" name="origem" value="<?php echo htmlspecialchars($filtro_origem); ?>" placeholder="Ex: Lisboa">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="destino">Destino:</label>
                            <input type="text" class="form-control" id="destino" name="destino" value="<?php echo htmlspecialchars($filtro_destino); ?>" placeholder="Ex: Porto">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container mt-3">
        <h1 class="mb-4">Rotas Disponíveis</h1>

        <div class="sort-options">
            <span class="sort-label">Ordenar por:</span>
            <?php
            // Função auxiliar para ordenação
            function obterUrlOrdenacaoRotas($parametro, $ordenar_atual, $direcao_atual, $filtro_origem, $filtro_destino)
            {
                // Gera a ordenação com base no parâmetro e direção atuais
                $direcao = 'ASC';
                // Se o parâmetro atual for igual ao parâmetro passado, alterna a direção
                if ($ordenar_atual == $parametro && $direcao_atual == 'ASC') {
                    $direcao = 'DESC'; // Alternar para DESC se já estiver ASC no mesmo parâmetro
                }
                // Monta a URL com os parâmetros de ordenação e filtros
                $url = 'rotas.php?ordenar_por=' . $parametro . '&direcao=' . $direcao;
                if (!empty($filtro_origem)) {
                    $url .= '&origem=' . urlencode($filtro_origem);
                }
                if (!empty($filtro_destino)) {
                    $url .= '&destino=' . urlencode($filtro_destino);
                }
                return $url;
            }

            // Classes 'active' para os botões de ordenação atuais
            $classe_origem = ($ordenar_por == 'origem') ? 'active' : '';
            $classe_destino = ($ordenar_por == 'destino') ? 'active' : '';

            // Ícones de direção (opcional, para feedback visual)
            $icone_origem = ($ordenar_por == 'origem' && $direcao_ordenacao == 'ASC') ? ' &#9650;' : (($ordenar_por == 'origem' && $direcao_ordenacao == 'DESC') ? ' &#9660;' : '');
            $icone_destino = ($ordenar_por == 'destino' && $direcao_ordenacao == 'ASC') ? ' &#9650;' : (($ordenar_por == 'destino' && $direcao_ordenacao == 'DESC') ? ' &#9660;' : '');
            ?>
            <a href="<?php echo obterUrlOrdenacaoRotas('origem', $ordenar_por, $direcao_ordenacao, $filtro_origem, $filtro_destino); ?>" class="<?php echo $classe_origem; ?>">Origem<?php echo $icone_origem; ?></a>
            <a href="<?php echo obterUrlOrdenacaoRotas('destino', $ordenar_por, $direcao_ordenacao, $filtro_origem, $filtro_destino); ?>" class="<?php echo $classe_destino; ?>">Destino<?php echo $icone_destino; ?></a>
        </div>


        <?php

        // Mostra as rotas filtradas
        // Verifica se o resultado contém linhas
        if ($resultado && $resultado->num_rows > 0) {
            // Itera sobre os resultados e exibe as rotas
            while ($linha = $resultado->fetch_assoc()) {
                $id_rota = htmlspecialchars($linha["id_rota"]);
                echo "<div class='rota-card'>";
                echo "<div class='rota-info'>";
                echo "<span><strong>Origem:</strong> " . htmlspecialchars($linha["origem"]) . "</span>";
                echo "<span><strong>Destino:</strong> " . htmlspecialchars($linha["destino"]) . "</span>";
                echo "</div>";
                echo "<div>";
                echo "<a href='viagens.php?origem=" . urlencode($linha["origem"]) . "&destino=" . urlencode($linha["destino"]) . "' class='procurar-btn'>Procurar Viagens</a>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>Não foram encontradas rotas.</p>";
        }
        // Fecha a declaração e o resultado
        $conn->close();
        ?>
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
            })
        })

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebarUser");
            sidebar.classList.toggle("active");
        }
    </script>
</body>
</html>